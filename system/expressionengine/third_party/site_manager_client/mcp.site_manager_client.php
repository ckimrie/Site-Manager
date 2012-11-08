<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Site Manager CP Controller
 *
 * Main EE CP Controller for Site Manager Module
 *
 * @author  Christopher Imrie
 */
class Site_manager_client_mcp
{
	//Config Vars
	var $module_label 	= "Site Manager";
	var $page_title 	= "Site Manager";

	//Runtime Vars
	var $EE;
	var $ajax;
	var $return_data;
	var $payload_error;


	/**
	 * Constructor
	 *
	 * @author Christopher Imrie
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		//Must be super admin to login (Recommended by EL)
		if($this->EE->session->userdata('group_id') == 2) {
			show_error("Site Manager can only be accessed by Super Admins.");
		}

		//Ensure RequireJS is installed
		if(!property_exists($this->EE, "requirejs")){
			show_error("The Site Manager module needs the <a href='https://github.com/ckimrie/RequireJS-for-EE'>RequireJS-for-EE</a> extension to be installed in order to function correctly.", 500, "Module Required");
		}


		//Site manager now depends on Theme loader, which is included in RequireJS-for-EE 1.4
		//Thankfully this version also includes the 'version' property, so we'll check that
		if (!property_exists($this->EE->requirejs, "version") || $this->EE->requirejs->version < "1.4") {
			show_error("The Site Manager module needs at least version 1.4 of <a href='https://github.com/ckimrie/RequireJS-for-EE'>RequireJS-for-EE</a>.  Please update RequireJS-for-EE to the latest version and try again.", 500, "Update Required");
		}



		// Define your module name so the file paths will be correct
		$this->EE->theme_loader->module_name  = 'site_manager_client';
		$this->EE->theme_loader->js_directory = 'js';

		$this->EE->load->file(PATH_THIRD."site_manager_client/ajax.site_manager_client.php");
		$this->ajax = new Site_manager_client_ajax();


		$this->EE->theme_loader->css('site_manager_client');


		//PHP Resources & view fragments
		$this->EE->load->model("site_data");
		$this->EE->load->helper("navigation");
	}





	/*-----------------------------------------------------------
	 * Pages
	 * ----------------------------------------------------------
	 */

	/**
	 * Module Index Page
	 *
	 * Displays a grid view of all sites currently configured for remote
	 * management
	 *
	 * @author Christopher Imrie
	 * @return null
	 */
	public function index()
	{
		//Page JS
		$this->EE->theme_loader->javascript('index/index');


		$data['sites'] = $this->EE->site_data->get_all();

		return $this->view("index/index", $data);
	}

	/**
	 * A multi stage page that presents a text box to accept encoded site config data
	 * and then on POST displays a form of the data decoded
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
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



	/**
	 * Create site function
	 *
	 * Accepts a POST array of site configuration data and then calls
	 * on the site_data model to create each one.
	 *
	 * This page has no UI, it simple accepts data from the "add_site" page
	 *
	 * TODO:
	 * - Add data validation
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function insert_site()
	{
		if(!$this->EE->input->post('sites')) show_error("No site data recieved");


		foreach ($this->EE->input->post('sites', TRUE) as $key => $site) {

			if(!$this->_validateSitePayload($site)) {
				show_error("Invalid settings provided: " .$this->payload_error);
			}

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


	/**
	 * Site details - Index Page
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function site_details()
	{
		$site_id = $this->EE->input->get("site_id");

		if(!$site_id) show_404();

		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details");

		//Site ID provided but doesnt exist in db?
		if(!$data['site']) show_404();

		//Page JS
		$this->EE->theme_loader->javascript('site_details/index');


		$this->page_title = $data['site']->name();

		return $this->view("site_details/index", $data);
	}



	/**
	 * Site details - Config Page
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function site_details_config()
	{
		$site_id = $this->EE->input->get("site_id");

		//Page JS
		$this->EE->theme_loader->javascript('site_details/config');


		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_config");

		$this->page_title = $data['site']->name();

		return $this->view("site_details/config", $data);
	}



	/**
	 * Site details - Channels Page
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function site_details_channels()
	{
		$site_id = $this->EE->input->get("site_id");

		//Page JS
		$this->EE->theme_loader->javascript('site_details/channels');


		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_channels");

		$this->page_title = $data['site']->name();

		return $this->view("site_details/channels", $data);
	}


	/**
	 * Site details - Addons Page
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function site_details_addons()
	{
		$site_id = $this->EE->input->get("site_id");

		//Page JS
		$this->EE->theme_loader->javascript('site_details/addons');


		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_addons");

		$this->page_title = $data['site']->name();

		return $this->view("site_details/addons", $data);
	}



	/**
	 * Site details - Settings
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function site_details_settings()
	{
		$site_id = $this->EE->input->get("site_id");

		//Page JS
		$this->EE->theme_loader->javascript('site_details/index');


		$data = array();
		$data['site'] = $this->EE->site_data->get($site_id);
		$data['update'] = $this->EE->input->get("update");
		$data['delete_url'] = methodUrl("delete_site", array("site_id" => $site_id));
		$data['navigation'] = $this->_site_detail_navigation($site_id, "site_details_settings");
		$data['form_declaration'] = $this->EE->functions->form_declaration(array(
			'action' => methodUrl("update_site_settings", array("site_id" => $site_id)),
			"hidden_fields" => array(
				'XID' => XID_SECURE_HASH
			)
		));


		$this->page_title = $data['site']->name();

		return $this->view("site_details/settings", $data);
	}



	/**
	 * Site Details - Update local settings
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function update_site_settings()
	{
		if(! $this->EE->input->post('site_id')) show_error("No site data recieved");


		$site_id = $this->EE->input->get("site_id");
		$data = array(
			"site_id"					=> $this->EE->input->post('site_id'),
			"public_key"				=> $this->EE->input->post('public_key'),
			"private_key"				=> $this->EE->input->post('private_key'),
			"cp_url"					=> $this->EE->input->post('cp_url'),
			"site_name"					=> $this->EE->input->post('site_name'),
			"base_url"					=> $this->EE->input->post('base_url'),
			"index_page"				=> $this->EE->input->post('index_page'),
			"user_id"					=> $this->EE->input->post('user_id'),
			"channel_nomenclature"		=> $this->EE->input->post('channel_nomenclature'),
			"action_id"					=> $this->EE->input->post('action_id')
		);

		//Validate that the updated fields are correct
		if(!$this->_validateSitePayload($data)){
			show_error("Invalid settings provided: " .$this->payload_error);
		}

		try {
			$this->EE->site_data->updateSite($site_id, $data);
		} catch (Exception $e) {
			show_error($e->getMessage());
		}



		redirectToMethod("site_details_settings", array("site_id" => $site_id, "update" => "success"));
	}



	/**
	 * License and EE version review page
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function license_review()
	{
		//Page JS
		$this->EE->theme_loader->javascript('index/license_review');


		$data['sites'] = $this->EE->site_data->get_all();

		$this->page_title = "License Review";

		return $this->view("index/license_review", $data);
	}



	/**
	 * Multi Site Sync Page
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	public function sync()
	{
		//Page JS
		$this->EE->theme_loader->javascript('sync/index');


		$data['sites'] = $this->EE->site_data->get_all();

		$this->page_title = "Sync Data";

		return $this->view("sync/index", $data);
	}



	/**
	 * Delete a site
	 *
	 * Reads site_id GET variable to specify what site to delete
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function delete_site()
	{
		$site_id = $this->EE->input->get("site_id");

		if(!$site_id) show_404();

		$this->EE->site_data->delete_site($site_id);

		redirectToMethod("index");
	}



	/**
	 * Ajax Controller Delegation
	 *
	 * All ajax requests are routed through here and dispatched
	 * to ajax.site_manager_clien.php sub controller
	 *
	 * Return data from subcontroller is output as JSON
	 * Status header can be set by subcontroller (default 200)
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function ajax()
	{
		$method = $this->EE->input->get("js_method");

		if(!method_exists($this->ajax, $method)) show_404();

		$data = $this->ajax->$method();

		$this->EE->load->library('javascript');
		$this->EE->output->set_status_header($this->ajax->response_code);
		if ($this->EE->config->item('send_headers') == 'y')
		{
			@header('Content-Type: application/json');

		}

		exit($this->EE->javascript->generate_json($data, TRUE));
	}



	/**
	 * Decryption Service
	 *
	 * Decrypts data sent by the remote site using the private encryption key
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function decrypt()
	{
		$this->EE->load->helper("encryption");
		$this->EE->load->library('javascript');

		$site_id = $this->EE->input->get("local_site_id");
		$site = $this->EE->site_data->get($site_id);

		$data = $this->EE->input->post("data");
		$data = decrypt_payload(base64_decode($data), $site->setting("private_key"));

		$data = json_decode($data, TRUE);

		@header('Content-Type: application/json');

		exit($this->EE->javascript->generate_json($data, TRUE));
	}


	/**
	 * Encryption Service
	 *
	 * Encrypts data to be sent to a remote site. Data is submitted via POST.
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function encrypt()
	{
		$this->EE->load->helper("encryption");
		$this->EE->load->library('javascript');

		$site_id = $this->EE->input->get("local_site_id");
		$site = $this->EE->site_data->get($site_id);


		$data = $_POST;

		$data = json_encode($data);

		$data = base64_encode(encrypt_payload($data, $site->setting("private_key")));

		@header('Content-Type: text/html');

		exit($data);


	}


	/*-----------------------------------------------------------
	 * Utilities
	 * ----------------------------------------------------------
	 */


	/**
	 * Site Config Validation
	 *
	 * Needs to be public since its being called by the CI Form Validation library
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string $str Encoded settings from remote site
	 * @return boolean
	 */
	public function _valid_settings($str='')
	{
		return $this->EE->site_data->verify_settings_payload($str);
	}


	/**
	 * Validate Site Payload
	 *
	 * Verifies that all settings are present and of the correct type.
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $site Decoded data containing settings from remote site
	 * @return bool
	 */
	public function _validateSitePayload($site=array())
	{
		//All Present and accounted for?
		$required = array(
			"site_id",
			"public_key",
			"private_key",
			"cp_url",
			"site_name",
			"base_url",
		 /* "index_page", */
			"user_id",
		 /* "channel_nomenclature", */
			"action_id"
		);

		foreach ($required as $key => $name) {
			if( ! isset($site[$name])) {
				$this->payload_error = $name . " field missing.";
				return FALSE;
			}

			if( ! $site[$name]) {
				$this->payload_error = $name . " field is empty.";
				return FALSE;
			}
		}


		//Encryption key lengths correct? (32)
		if(strlen($site['public_key']) != 32) {
			$this->payload_error = "Public encryption key must be 32 characters long";
			return FALSE;
		}
		if(strlen($site['private_key']) != 32) {
			$this->payload_error = "Private encryption key must be 32 characters long";
			return FALSE;
		}

		//URLs being with http ??
		if(strpos($site['base_url'], "http") !== 0) {
			$this->payload_error = "Base URL must be valid URL that begins with 'http'";
			return FALSE;
		}

		if(strpos($site['cp_url'], "http") !== 0) {
			$this->payload_error = "CP URL must be valid URL that begins with 'http'";
			return FALSE;
		}


		//
		//Integer checks
		//

		//Site ID
		if(!is_numeric($site['site_id'] )) {
			$this->payload_error = "Site ID must be integer";
			return FALSE;
		}

		//API Action ID
		if(!is_numeric($site['site_id'] )) {
			$this->payload_error = "API Action ID must be integer";
			return FALSE;
		}

		//User ID
		if(!is_numeric($site['site_id'] )) {
			$this->payload_error = "User ID must be integer";
			return FALSE;
		}

		return TRUE;
	}



	/**
	 * CP View Generator
	 *
	 * Convenience method that loads the requested view, sets page title
	 * and loads various useful template vars
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string $name Template name
	 * @param  array  $data
	 * @return string
	 */
	private function view($name='', $data=array())
	{
		$data['cp_page_title'] = $this->page_title;
		$this->EE->cp->set_variable('cp_page_title', $this->page_title);

		//Top Bar navigation variables
		$nav = array();
		$nav['add_url'] 			= methodUrl("add_site");
		$nav['license_review_url'] 	= methodUrl("license_review");
		$nav['all_sites_url'] 		= methodUrl("index");
		$nav['sync_url'] 			= methodUrl("sync");
		$nav['XID'] 				= XID_SECURE_HASH;


		//Add some useful vars to the main template data array
		$data["js_api"] 			= methodUrl("ajax");
		$data["js_decryption_api"]  = methodUrl("decrypt");
		$data["js_encryption_api"]  = methodUrl("encrypt");
		$data["navigation_top"]		= $this->EE->load->view("embeds/navigation-top", $nav, TRUE);

		//If title is not 'site manager' then add a breadcrumb, if not leave it as is
		if($this->page_title != "Site Manager") {
			$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=site_manager_client', $this->module_label);
		}


		return $this->EE->load->view("pages/".$name, $data, TRUE);
	}



	/**
	 * Top Bar Navigation
	 *
	 * @author Christopher Imrie
	 * @param  integer $site_id
	 * @param  string $current
	 * @return array
	 */
	private function _site_detail_navigation($site_id, $current='')
	{
		return array(
			"site_details" 			=> array(
										"active"=> $current == "site_details",
										"label" => "Site Overview",
										"url" 	=> methodUrl("site_details", array("site_id" => $site_id))
									),
			"site_details_config" 	=> array(
										"active"=> $current == "site_details_config",
										"label" => "Configuration",
										"url" 	=> methodUrl("site_details_config", array("site_id" => $site_id))
									),
			"site_details_channels" 	=> array(
										"active"=> $current == "site_details_channels",
										"label" => "Channels",
										"url" 	=> methodUrl("site_details_channels", array("site_id" => $site_id))
									),
			"site_details_addons" 	=> array(
										"active"=> $current == "site_details_addons",
										"label" => "Addons",
										"url" 	=> methodUrl("site_details_addons", array("site_id" => $site_id))
									),
			"site_details_settings" 	=> array(
										"active"=> $current == "site_details_settings",
										"label" => "Local Settings",
										"url" 	=> methodUrl("site_details_settings", array("site_id" => $site_id))
									)
		);
	}
}