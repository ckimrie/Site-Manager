<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//Fetch Real Admin_content controller
$e =& get_instance();
$e->load->file(PATH_THIRD."site_manager_server/classes/mock_cp.php");
$e->load->file(PATH_THIRD."site_manager_server/classes/mock_lang.php");
$e->load->file(PATH_THIRD."site_manager_server/classes/mock_functions.php");
$e->load->file(PATH_THIRD."site_manager_server/classes/mock_session.php");
$e->load->file(PATH_THIRD."site_manager_server/classes/mock_logger.php");
$e->load->file(APPPATH."controllers/cp/admin_content.php");


/**
* Mock Admin_content controller for doing various DB interaction using EE's
* native methods.
*
* Since this controller is designed to load from within the CP, it expects to have certain
* resources already loaded.  In order to allow compatibility for it to run
* outside the CP, we need to setup up some mock resources so calls to external
* objects on the singleton dont cause errors.
*
* @author  Christopher Imrie
*/
class Mock_admin_content extends Admin_content
{
	var $cp;
	var $lang;
	var $logger;
	var $functions;
	var $addons;
	var $db;

	function __construct()
	{
		$EE = get_instance();

		//Setup fake EE resources
		$this->cp 			= new Mock_cp();
		$this->lang 		= new Mock_lang();
		$this->functions 	= new Mock_functions();
		$this->session 		= new Mock_session();
		$this->logger		= new Mock_logger();

		//Attach a pointer to the existing DB object and addons library
		$this->db =& $EE->db;
		$this->addons =& $EE->addons;

		//We need the real addons loaded


		//Define some fake constants used for navigation
		if(!defined("BASE")){
			define("BASE", "");
		}

		//Fire her up...
		parent::__construct();

		$this->load->model("local_data");
	}


}