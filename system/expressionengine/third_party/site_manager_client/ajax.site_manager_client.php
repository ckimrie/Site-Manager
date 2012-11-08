<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Site Manager Ajax Sub Controller
 * 
 * This class is instantiated by the main mcp.site_manager.php controller and all
 * ajax requested to the main controller are routed into this sub controller
 * 
 * This controller does not output anything.  The main mcp controller handles all
 * JSON output so these methods need only return arrays/objects
 * 
 * @author  Christopher Imrie
 */
class Site_manager_client_ajax {


	var $EE;
	var $response_code = 200;

	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->model("site_data");
	}


	/**
	 * Ping Remote Site [Deprecated]
	 * 
	 * Accepts site_id GET param and pings the remote site to check if
	 * the server module is available for communication.
	 * 
	 * This method is deprecated now that all communication happens via JS, nonetheless
	 * it could come in handy at some point
	 * 
	 * @author Christopher Imrie
	 * @return array      
	 */
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