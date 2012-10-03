<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CP Class
 */

class Site_manager_client_mcp
{
	var $EE;
	var $ajax;
	var $return_data;

	var $module_label = "Site Manager";
	var $page_title = "Site Manager";
	
	
	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->file(PATH_THIRD."site_manager_client/ajax.site_manager_client.php");
		$this->ajax = new Site_manager_client_ajax();

		//Ensure RequireJS is installed
		if(!property_exists($this->EE, "requirejs")){
			show_error("The Site Manager module needs the <a href='https://github.com/ckimrie/RequireJS-for-EE'>RequireJS-for-EE</a> extension to be installed in order to function correctly.", 500, "Module Required");
		}

		

		//What resources do we need?
		//Requirejs::load("css!third_party/site_manager_client/css/site_manager_client.css");
		$this->EE->cp->add_to_head("<link href='".$this->EE->config->item("theme_folder_url")."third_party/site_manager_client/css/site_manager_client.css' rel='stylesheet'/>");



		//PHP Resources & view fragments
		$this->EE->load->model("site_data");
		$this->EE->load->helper("navigation");

		$data = array();
		$data['add_url'] = methodUrl("add_site");
		$this->EE->load->vars(array(
			"js_api" 		=> methodUrl("ajax"),
			"navigation"	=> $this->EE->load->view("embeds/navigation-top", $data, TRUE)
		));
	}



	public function index()
	{
		Requirejs::load("third_party/site_manager_client/js/index/index");


		$data['sites'] = $this->EE->site_data->get_all();
		

		return $this->view("index/index", $data);
	}


	public function add_site()
	{
		$data = array();
		
		$this->page_title = "Add Site";
		
		$this->EE->load->library("form_validation");
		$this->EE->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->EE->form_validation->set_rules("site_settings", "Site Settings", "required|callback__valid_settings");
		$this->EE->form_validation->set_message("_valid_settings", "The settings you have entered are invalid");
		

		if($this->EE->form_validation->run() !== FALSE) {
			//Success!
			
			//Remember there could be more than one site!
			$data['back_url'] = methodUrl("add_site");
			$data['verify_site_form_declaration'] = $this->EE->functions->form_declaration(array(
				'action' => methodUrl("insert_site"),
				"hidden_fields" => array(
					'XID' => XID_SECURE_HASH
				)
			));
			$data['sites'] = $this->EE->site_data->decode_settings_payload($this->EE->input->post("site_settings"));
			
			return $this->view("add_site/verify", $data);
		}

		//Initial load or invalid form submission
		$data['add_site_form_declaration'] = $this->EE->functions->form_declaration(array(
			'action' => methodUrl("add_site"),
			"hidden_fields" => array(
				'XID' => XID_SECURE_HASH
			)
		));
		
		return $this->view("add_site/index", $data);
	}



	public function insert_site()
	{
		if(!$this->EE->input->post('sites')) show_error("No site data recieved");
		
		
		foreach ($this->EE->input->post('sites', TRUE) as $key => $site) {
			
			$data = array(
				"site_id"					=> $site['site_id'],
				"public_key"				=> $site['public_key'],
				"private_key"				=> $site['private_key'],
				"cp_url"					=> $site['cp_url'],
				"site_name"					=> $site['site_name'],
				"base_url"					=> $site['base_url'],
				"index_page"				=> $site['index_page'],
				"user_id"					=> $site['user_id'],
				"channel_nomenclature"		=> $site['channel_nomenclature'],
				"action_id"					=> $site['action_id']
			);

			try {
				$this->EE->site_data->newSite($data);	
			} catch (Exception $e) {
				show_error($e->getMessage());
			}
			
		}

		redirectToMethod("index");
	}



	public function site_details()
	{
		$site_id = $this->EE->input->get("site_id");
		


		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details");


		//Add script tag to head to circumvent cross domain scripting security
		//$this->EE->cp->add_to_foot("<script type='text/javascript' src='".$data['site']->ping_url()."'></script>");
		Requirejs::load("third_party/site_manager_client/js/site_details/index");

		$this->page_title = $data['site']->name();

		return $this->view("site_details/index", $data);
	}


	public function site_details_config()
	{
		$site_id = $this->EE->input->get("site_id");
		
		Requirejs::load("third_party/site_manager_client/js/site_details/config");



		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_config");
		

	

		$this->page_title = $data['site']->name();

		return $this->view("site_details/config", $data);
	}




	public function site_details_channels()
	{
		$site_id = $this->EE->input->get("site_id");
		
		Requirejs::load("third_party/site_manager_client/js/site_details/channels");



		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_channels");
		

	

		$this->page_title = $data['site']->name();

		return $this->view("site_details/channels", $data);
	}



	public function site_details_addons()
	{
		$site_id = $this->EE->input->get("site_id");
		
		Requirejs::load("third_party/site_manager_client/js/site_details/addons");



		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_addons");
		

	

		$this->page_title = $data['site']->name();

		return $this->view("site_details/addons", $data);
	}




	public function delete_site()
	{
		$site_id = $this->EE->input->get("site_id");

		if(!$site_id) show_404();

		$this->EE->site_data->delete_site($site_id);

		redirectToMethod("index");
	}



	/**
	 * Ajax Endpoint
	 */
	public function ajax()
	{
		$method = $this->EE->input->get("js_method");

		if(!method_exists($this->ajax, $method)) show_404();

		$data = $this->ajax->$method();

		$this->EE->load->library('javascript');
		$this->EE->output->set_status_header($this->ajax->response_code);
		if ($this->EE->config->item('send_headers') == 'y')
		{
			@header('Content-Type: application/json');
		
		}
		
		exit($this->EE->javascript->generate_json($data, TRUE));
	}




	/**
	 * Validation Method
	 */
	public function _valid_settings($str='')
	{
		return $this->EE->site_data->verify_settings_payload($str);
	}


	protected function view($name='', $data=array())
	{
		$this->EE->cp->set_variable('cp_page_title', $this->page_title);
		
		if($this->page_title != "Site Manager") {
			$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=site_manager_client', $this->module_label);
		}

		$data['cp_page_title'] = $this->page_title;

		return $this->EE->load->view("pages/".$name, $data, TRUE);	
	}




	private function _site_detail_navigation($site_id, $current='')
	{
		return array(
			"site_details" 			=> array(
										"active"=> $current == "site_details",
										"label" => "Site Overview",
										"url" 	=> methodUrl("site_details", array("site_id" => $site_id))
									),
			"site_details_config" 	=> array(
										"active"=> $current == "site_details_config",
										"label" => "Configuration",
										"url" 	=> methodUrl("site_details_config", array("site_id" => $site_id))
									),
			"site_details_channels" 	=> array(
										"active"=> $current == "site_details_channels",
										"label" => "Channels",
										"url" 	=> methodUrl("site_details_channels", array("site_id" => $site_id))
									),
			"site_details_addons" 	=> array(
										"active"=> $current == "site_details_addons",
										"label" => "Addons",
										"url" 	=> methodUrl("site_details_addons", array("site_id" => $site_id))
									)
		);
	}
}