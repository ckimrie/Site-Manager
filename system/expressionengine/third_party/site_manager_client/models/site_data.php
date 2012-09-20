<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Site_data extends CI_Model 
{
	var $EE;

	private $_sites;

	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/Remote_Object.php");
		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/Site.php");
		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/Site_Collection.php");

		$this->init();
	}



	public function init()
	{
		//Initialise
		

		//Fetch installed sites
		$this->get_all();
	}



	public function get_all()
	{
		//Fetch installed sites
		$q = $this->EE->db->get("site_manager_sites")->result();

		$sites = new Site_Collection();
		
		foreach ($q as $key => $row) {
			$sites->add($row->id, $row);
		}

		//Cache installed sites
		$this->_sites = $sites;


		return $this->_sites;
	}


	public function verify_settings_payload($str='')
	{
		$a = $this->decode_settings_payload($str);
		return is_array($a) && count($a) > 0;
	}

	public function decode_settings_payload($str='')
	{
		return @unserialize(base64_decode($str));
	}

}