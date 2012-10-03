<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Site_data extends CI_Model 
{
	var $EE;

	//Config
	private $_db_name = "site_manager_sites";

	//Runtime Vars
	private $_sites;

	//Static
	static private $_instance;

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
		Site_data::$_instance =& $this; 

		//Fetch installed sites
		$this->get_all();
	}



	static function get_instance()
	{
		return Site_data::$_instance;
	}



	public function newSite($post=array())
	{
		$c =& CI_Controller::get_instance();

		//Sanitize everything first
		foreach ($post as $key => $row) {
			$post[$key] = $c->security->xss_clean($row);
		}

	
		$site_name = $post['site_name'];
		$base_url = $post['base_url'];
		unset($post['site_name']);
		unset($post['base_url']);

		$data = array(
			"site_name" => $site_name,
			"base_url" => $base_url,
			"settings" 	=> $this->encode_settings_payload($post),
			"added_by"	=> $c->session->userdata("member_id"),
			"date_added" => $this->EE->localize->now,
		);

		$this->EE->db->insert($this->_db_name, $data);
	
		
		return $this->EE->db->insert_id();
	}



	public function get_all()
	{
		//Fetch installed sites
		$q = $this->EE->db->get($this->_db_name)->result();

		$sites = new Site_Collection();
		
		foreach ($q as $key => $row) {
			$sites->add($row->id, $row);
		}

		//Cache installed sites
		$this->_sites = $sites;


		return $this->_sites;
	}



	public function get($site_id)
	{
		$site = $this->_sites->get($site_id);
		if($site) return $site;

		//Fetch installed sites
		$q = $this->EE->db->get_where($this->_db_name, array("id" => $site_id))->result();
		
		foreach ($q as $key => $row) {
			$this->_sites->add($row->id, $row);
		}

		return $this->_sites->get($site_id);
	}


	public function delete_site($site_id='')
	{
		if(!$site_id) return;
		
		$this->EE->db->where("id", $site_id);
		$this->EE->db->delete("site_manager_sites");
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

	public function encode_settings_payload($data=array())
	{
		return base64_encode(serialize($data));
	}

}