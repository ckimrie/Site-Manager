<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CP Class
 */

class Site_manager_client_mcp
{
	var $EE;
	var $return_data;

	var $module_label = "Site Manager";
	var $page_title = "Site Manager";
	
	
	function __construct()
	{
		$this->EE =& get_instance();

		//Ensure RequireJS is installed
		if(!property_exists($this->EE, "requirejs")){
			show_error("The Site Manager module needs the <a href='https://github.com/ckimrie/RequireJS-for-EE'>RequireJS-for-EE</a> extension to be installed in order to function correctly.", 500, "Module Required");
		}

		

		//What resources do we need?
		//Requirejs::load("css!third_party/site_manager_client/css/site_manager_client.css");
		$this->EE->cp->add_to_head("<link href='".URL_THIRD_THEMES."site_manager_client/css/site_manager_client.css' rel='stylesheet'/>");

		//PHP Resources
		$this->EE->load->model("site_data");
		$this->EE->load->helper("navigation");
	}



	public function index()
	{
		$data['sites'] = $this->EE->site_data->get_all();
		$data['add_url'] = methodUrl("add_site");

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
}