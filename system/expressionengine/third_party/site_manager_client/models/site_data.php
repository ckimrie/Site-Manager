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
		$this->getAll();
	}



	public function getAll()
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



}