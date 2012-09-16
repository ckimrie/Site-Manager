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
	}



	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', "Site Manager");
		

		return $this->EE->load->view("pages/index/index", $data, TRUE);
	}
}