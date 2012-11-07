<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
*
*/
class Base
{
	var $EE;

	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/remote_object.php");
		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/site.php");
		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/site_collection.php");
	}
}