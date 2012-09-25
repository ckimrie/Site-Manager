<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Local_data extends CI_model
{
	var $EE;

	//Config
	private $_db_name 		= "site_manager_server_config";
	private $_module_name	= "Site_manager_server";
	private $_action_name	= "request";

	//Runtime Vars
	private $_site_id;
	private $_public_key;
	private $_private_key;
	private $_settings;
	private $_site;

	public function __construct()
	{
		parent::__construct();

		$this->EE =& get_instance();
		$this->_init();
	}





	public function get_setup_payload()
	{
		$c =& CI_Controller::get_instance();

		$payload = array(
			"site_id" 		=> $this->_site_id,
			"public_key" 	=> $this->_public_key,
			"private_key" 	=> $this->_private_key,
			"cp_url"		=> $this->_site["cp_url"],
			"site_name" 	=> $this->_site['site_name'],
			"base_url" 		=> $this->_site['base_url'],
			"index_page" 	=> $this->_site['index_page'],
			"user_id" 		=> $c->session->userdata("member_id"),
			"channel_nomenclature" 	=> $this->_site['channel_nomenclature'],
			"action_id"		=> $this->_fetch_action_id()
		);

		return $this->_prep_settings_payload_for_transport(array($payload));
	}





	private function _init()
	{
		//Fetch module specific data
		$this->_site_id = $this->EE->config->item("site_id");
		$q = $this->_fetch_site_db_data($this->_site_id);
		$this->_private_key = $q->private_key;
		$this->_public_key = $q->public_key;
		$this->_settings = $q->settings;
		
		//Site data
		$this->_site = array(
			"site_name" => $this->EE->config->item("site_name"),
			"cp_url"	=> $this->EE->config->item("cp_url"),
			"base_url" => $this->EE->config->item("base_url"),
			"cp_url"  => $this->EE->config->item("cp_url"),
			"index_page" => $this->EE->config->item("index_page"),
			"channel_nomenclature" => $this->EE->config->item("channel_nomenclature"),
		);
	}



	private function _fetch_site_db_data($site_id)
	{
		$q = $this->EE->db->get_where($this->_db_name, array("site_id" => $site_id));

		if($q->num_rows() == 0) {
			$data = array(
				"site_id" 		=> $site_id,
				"public_key" 	=> $this->_generate_key(),
				"private_key" 	=> $this->_generate_key(),
				"settings"		=> $this->_prep_settings_for_db(array())
			);
			
			$this->EE->db->insert($this->_db_name, $data);
			return $this->_fetch_site_db_data($site_id);
		}



		$r = $q->row();
		if($r->settings){
			//Settings are a serialised array
			$r->settings = $this->_restore_settings_from_db($r->settings);
		}else{
			$r->settings = array();
		}

		return $r;
	}


	private function _generate_key()
	{
		$c =& CI_Controller::get_instance();
		return md5(rand(0,9999).$c->security->xss_hash().rand(0,9999));
	}


	private function _prep_settings_payload_for_transport($data='')
	{
		return base64_encode(serialize($data));
	}


	private function _prep_settings_for_db($data=array())
	{
		return base64_encode(serialize($data));
	}

	private function _restore_settings_from_db($data=array())
	{
		return unserialize(base64_decode($data));
	}

	private function _fetch_action_id()
	{
		return $this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id($this->_module_name, $this->_action_name));
	}
}