<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Site 
{
	var $EE;

	function __construct()
	{
		$this->EE =& get_instance();

		//$this->EE->load->file(PATH_THIRD."site_manager_client/classes/Site");
	}
}