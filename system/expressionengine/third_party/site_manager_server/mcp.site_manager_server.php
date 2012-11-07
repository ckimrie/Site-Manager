<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CP Class
 */

class Site_manager_server_mcp
{
	var $EE;
	var $return_data;
	
	
	function __construct()
	{
		$this->EE =& get_instance();


		//PHP Resources
		$this->EE->load->model("local_data");

		//CSS Resources
		$this->EE->cp->add_to_head("<link href='".$this->EE->config->item("theme_folder_url")."third_party/site_manager_server/css/site_manager_server.css' rel='stylesheet'/>");
	}



	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', "Site Manager Server");
	

		$data = array();
		$data['setup_payload'] = $this->EE->local_data->get_setup_payload();
		
		return $this->EE->load->view("pages/index/index", $data, TRUE);
	}
}