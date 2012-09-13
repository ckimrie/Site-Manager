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


		//What resources do we need?
		Requirejs::load("css!third_party/site_manager_client/css/site_manager_client.css");

		//PHP Resources
		$this->EE->load->library("base");
	}



	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', "Site Manager");
		

		

		return $this->EE->load->view("pages/index/index", array(), TRUE);
	}
}