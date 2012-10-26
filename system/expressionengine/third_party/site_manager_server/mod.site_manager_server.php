<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Installer
*/
class Site_manager_server
{

	var $EE;
	var $version	= "0.2.2";

	var $response_code = 200;
	static $private_key;
	static $public_key;

	function __construct($initialise = TRUE)
	{
		global $_POST;

		$this->EE =& get_instance();


		//TODO
		//
		//Validate authentication....


		//Set Site ID
		$this->EE->load->helper("encryption");
		$this->EE->load->model("local_data");
		$this->EE->local_data->site_id = $this->EE->input->get("site_id");


		//Public key correct?
		if($this->EE->input->get("k") != self::$public_key){
			$this->EE->output->set_status_header(500);
			$this->output(array("success" => FALSE, "error" => "Invalid encryption key"));
			exit;
		}

		//Ok, from here on, we want errors to show so we can debug, so lets add our CORS header here
		@header("Access-Control-Allow-Origin: *");

		//Is there post data to decrypt?
		if($this->EE->input->post("payload")) {
			$data = decrypt_payload(base64_decode($this->EE->input->post("payload")), self::$private_key);

			$data = json_decode($data, TRUE);


			if(is_array($data)) {
				$_POST = $data;
			}
		}
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
		$this->output(array(
			"success" => TRUE,
			"app_version" => $this->EE->config->item("app_version")
		));
	}


    public function login()
    {
        $user_id = $this->EE->input->get("user_id");

        $q = $this->EE->db->where("member_id", $user_id)->get("members");

        if($q->num_rows() == 0) {
        	show_error("Member no longer exists.  Please reinstall this site in Site Manager Client in order to regain the single login feature.");
        }

        $member = $q->row();

        //Define these constants in case any extensions are listening for the login hooks (eg: Structure)
        //Unfortunately we cant set the session ID at this point, so unless they're on the new hawtness EE login
        //they may need to login again anyway...
		define('BASE', $base = $this->EE->config->item("cp_url").'?S=0&amp;D=cp');
		define('PATH_CP_THEME', PATH_THEMES.'cp_themes/');

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


	public function fieldgroups()
	{
		$this->output($this->EE->local_data->fieldgroup_data());
	}

	public function fields()
	{
		$group_id = $this->EE->input->get("group_id");
		$this->output($this->EE->local_data->fieldgroup_field_data($group_id));
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
	 * Create Channel
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function create_channel()
	{
		$data = array();

		foreach ($_POST as $key => $value) {
			$data[$key] = $this->EE->input->post($key);
		}

		//This method creates a new CI Controller instance which will
		//detatch all our loaded resources.  Hence further down we rerun
		//the constructor in order to reattach them
		$a = $this->EE->local_data->create_channel($data);

		//Reattach resources to this controller
		$this->__construct();
		$this->output($a);
	}


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


	public function create_fieldgroup()
	{
		$data = array();
		foreach ($_POST as $key => $value) {
			$data[$key] = $this->EE->input->post($key);
		}

		//This method creates a new CI Controller instance which will
		//detatch all our loaded resources.  Hence further down we rerun
		//the constructor in order to reattach them
		$a = $this->EE->local_data->create_fieldgroup($data);

		//Reattach resources to this controller
		$this->__construct(FALSE);
		$this->output($a);
	}


	public function create_field()
	{
		$data = array();

		foreach ($_POST as $key => $value) {
			$data[$key] = $this->EE->input->post($key);
		}
		$group_id = $this->EE->input->get("group_id");

		//This method creates a new CI Controller instance which will
		//detatch all our loaded resources.  Hence further down we rerun
		//the constructor in order to reattach them
		$a = $this->EE->local_data->create_field($group_id, $data);

		//Reattach resources to this controller
		$this->__construct();
		$this->output($a);
	}


	/**-----------------------------------------------------
	 * Utilities
	 * -----------------------------------------------------
	 */


	public function output($data=array())
	{

		$this->EE->load->model('local_data');
		$this->EE->load->library('javascript');
		$this->EE->output->set_status_header($this->response_code);

		@header('Content-Type: text/html');

		$output = $this->EE->javascript->generate_json($data, TRUE);

		exit(base64_encode(encrypt_payload($output, self::$private_key)));
	}
}