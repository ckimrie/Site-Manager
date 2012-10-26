<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Installer
*/
class Site_manager_server_upd
{

	var $EE;
	var $version	= "0.2.2";
	var $db_name	= "site_manager_server_config";
	var $module_name = "Site_manager_server";
	var $action_name = "request";

	function __construct()
	{

		$this->EE =& get_instance();
	}

	/**
	 * Installer function
	 *
	 * @return bool
	 * @author Christopher Imrie
	 **/
	function install()
	{
		$data = array(
			'module_name'			=>	$this->module_name,
			'module_version'		=>	$this->version,
			'has_cp_backend'		=>	'y',
			'has_publish_fields'	=>	'n'
		);
		$this->EE->db->insert('modules', $data);

		//Action
		$data = array(
			'class'			=>	$this->module_name,
			'method'		=>	$this->action_name
		);
		$this->EE->db->insert('actions', $data);

		$this->EE->load->dbforge();
		$fields = array(
			"site_id" 	=> array(
							"type" => "INT",
							"auto_increment" => TRUE,
						),
			"public_key"=>  array(
							"type" => "VARCHAR",
							"constraint" => "32"
						),
			"private_key"=>  array(
							"type" => "VARCHAR",
							"constraint" => "32"
						),
			"settings" => array(
							"type" => "TEXT",
							"null" => TRUE
						)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('site_id', TRUE);
		$this->EE->dbforge->create_table("site_manager_server_config");

		return TRUE;
	}



	/**
	 * Updates the module from previous version
	 *
	 * @return void
	 * @author Christopher Imrie
	 **/
	function update($current = '')
	{

		return TRUE;
	}

	/**
	 * Uninstalls the module
	 *
	 * @return bool
	 * @author Christopher Imrie
	 **/
	function uninstall()
	{
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');

		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table("site_manager_server_config");

		return TRUE;
	}

}
