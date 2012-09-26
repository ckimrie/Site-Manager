<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CP Class
 */

class Site_manager_client_ajax {


	var $EE;

	var $response_code = 200;

	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->model("site_data");
	}

	public function ping_site()
	{
		$site_id = $this->EE->input->get("site_id");

		if(!$site_id) show_404();

		$site = $this->EE->site_data->get($site_id);

		$response = $site->ping();

	
		$this->response_code = $response['http_code'];
		return $response;
	
	}
}