<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Installer
*/
class Site_manager_server
{

	var $EE;
	var $version	= 0.1;
	
	var $response_code = 200;

	function __construct()
	{
		$this->EE =& get_instance();


		//TODO
		//
		//Validate authentication....
		

		//Set Site ID
		$this->EE->load->model("local_data");
		$this->EE->local_data->site_id = $this->EE->input->get("site_id");
	}


	public function request()
	{
		$method = $this->EE->input->get("method");

		if(!method_exists($this, $method)){
			//TODO
			// Return json error
			show_404();
		}

		$this->$method();
	}



	public function ping()
	{
		$this->output(array("app_version" => $this->EE->config->item("app_version")));
	}


	


	public function config()
	{
		$this->output($this->EE->config->config);
	}


	public function channels()
	{
		
		$this->output($this->EE->local_data->channel_data());
	}


	public function output($data=array())
	{


		$this->EE->load->library('javascript');
		$this->EE->output->set_status_header($this->response_code);
		if ($this->EE->config->item('send_headers') == 'y')
		{
			@header('Content-Type: application/json');
		
		}
		
		exit($this->EE->javascript->generate_json($data, TRUE));
	}
}