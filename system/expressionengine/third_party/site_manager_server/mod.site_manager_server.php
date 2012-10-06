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
		$this->output(array(array(
			"app_version" => $this->EE->config->item("app_version")
		)));
	}


    public function login()
    {
    	error_reporting(E_ALL);
        $user_id = $this->EE->input->get("user_id");

        $q = $this->EE->db->where("member_id", $user_id)->get("members");

        if($q->num_rows() == 0) {
        	show_error("Member no longer exists.  Please reinstall this site in Site Manager Client in order to regain the single login feature.");
        }

        $member = $q->row();

        //Legacy EE
        if(!file_exists(APPPATH."libraries/Auth.php")){
        	$this->EE->load->library("session");
        	$this->EE->load->library("functions");

        	if ($this->EE->config->item('admin_session_type') != 's')
			{
				$this->EE->functions->set_cookie($this->EE->session->c_expire , time(), "0");
				$this->EE->functions->set_cookie($this->EE->session->c_uniqueid , $member->unique_id , "0");
				$this->EE->functions->set_cookie($this->EE->session->c_password , $member->password,  "0");
				$this->EE->functions->set_cookie($this->EE->session->c_anon , 1,  "0");
			}

			if ( $this->EE->input->get("site_id") && is_numeric($this->EE->input->get("site_id")))
			{
				$this->EE->functions->set_cookie('cp_last_site_id', $this->EE->input->post('site_id'), 0);
			}
        	$session_id = $this->EE->session->create_new_session($user_id , TRUE);
        	$index = "index.php";

        //New Hawtness
        }else{
        	$this->EE->load->library("auth");
	        $this->EE->load->library("logger");

	        $authed = new Auth_result($member);
	        $authed->start_session(TRUE);

	        $session_id = $authed->session_id();
	        $index = "";
        }

        $base = $this->EE->config->item("cp_url").$index;
        $base .= "?S=".$session_id."&D=cp&";

		if ($this->EE->config->item('admin_session_type') != 'c')
		{
			$base = preg_replace('/S=\d+/', 'S='.$session_id, $base);
		}

		$return_path = $base.AMP.'C=homepage';
		//echo $return_path;
		$this->EE->functions->redirect($return_path);
    }



	public function config()
	{
		$c = $this->EE->config->config;
		ksort($c);
		$this->output($c);
	}


	public function channels()
	{
		$this->output($this->EE->local_data->channel_data());
	}



	public function categorygroups()
	{
		$this->output($this->EE->local_data->categorygroup_data());
	}



	public function installation_details()
	{
		$this->output(array(
			"is_system_on"		=> $this->EE->config->item("is_system_on") == "y" ? TRUE : FALSE,
			"license_number"	=> $this->EE->config->item("license_number"),
			"app_version" 		=> $this->EE->config->item("app_version"),
			"site_id" 			=> $this->EE->config->item("site_id"),
			"module_version" 	=> $this->version.""
		));
	}



	public function addons()
	{
		$this->output($this->EE->local_data->addon_data());
	}






	/**-----------------------------------------------------
	 * Sync Methods
	 * -----------------------------------------------------
	 */




	/**
	 * Create a new Category Group
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function create_categorygroup()
	{
		$data = array();
		foreach ($_POST as $key => $value) {
			$data[$key] = $this->EE->input->post($key);
		}

		$this->output($this->EE->local_data->create_categorygroup($data));
	}


	/**-----------------------------------------------------
	 * Utilities
	 * -----------------------------------------------------
	 */


	public function output($data=array())
	{


		$this->EE->load->library('javascript');
		$this->EE->output->set_status_header($this->response_code);

		@header("Access-Control-Allow-Origin: *");
		@header('Content-Type: application/json');



		exit($this->EE->javascript->generate_json($data, TRUE));
	}
}