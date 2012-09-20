<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CP Class
 */

class Site_manager_client_mcp
{
	var $EE;
	var $return_data;
	
	
	function __construct()
	{
		$this->EE =& get_instance();

		//Ensure RequireJS is installed
		if(!property_exists($this->EE, "requirejs")){
			show_error("The Site Manager module needs the <a href='https://github.com/ckimrie/RequireJS-for-EE'>RequireJS-for-EE</a> extension to be installed in order to function correctly.", 500, "Module Required");
		}

		$this->EE->cp->set_variable('cp_page_title', "Site Manager");

		//What resources do we need?
		Requirejs::load("css!third_party/site_manager_client/css/site_manager_client.css");

		//PHP Resources
		$this->EE->load->model("site_data");
		$this->EE->load->helper("navigation");
	}



	public function index()
	{
		
		

		$data['sites'] = $this->EE->site_data->get_all();
		$data['add_url'] = methodUrl("add_site");

		return $this->EE->load->view("pages/index/index", $data, TRUE);
	}


	public function add_site()
	{
		$this->EE->load->library("form_validation");
		$this->EE->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->EE->form_validation->set_rules("site_settings", "Site Settings", "required|callback__valid_settings");

		$this->EE->form_validation->set_message("_valid_settings", "The settings you have entered are invalid");
		

		if($this->EE->form_validation->run() !== FALSE) {
			//Success!
			
			
			return print_r($this->EE->site_data->decode_settings_payload($this->EE->input->post("site_settings")), TRUE);
		}

		//Initial load or invalid form submission
		$data = array();
		$data['add_site_form_declaration'] = $this->EE->functions->form_declaration(array(
			'action' => methodUrl("add_site"),
			"hidden_fields" => array(
				'XID' => XID_SECURE_HASH
			)
		));
		
		return $this->EE->load->view("pages/add_site/index.php", $data, TRUE);	
		
	}



	public function _valid_settings($str='')
	{
		return $this->EE->site_data->verify_settings_payload($str);
	}
}