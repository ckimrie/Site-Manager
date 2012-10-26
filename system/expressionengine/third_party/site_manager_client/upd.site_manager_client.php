<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Installer
*/
class Site_manager_client_upd
{

	var $EE;
	var $version		= "0.2.2";
	var $module_name 	= "Site_manager_client";
	var $db_name		= "site_manager_sites";

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



		$this->EE->load->dbforge();
		$fields = array(
			"id" 		=> array(
							"type" => "INT",
							"auto_increment" => TRUE,
						),
			"site_name" =>  array(
							"type" => "VARCHAR",
							"constraint" => "128"
						),
			"base_url" 	=>  array(
							"type" => "VARCHAR",
							"constraint" => "255"
						),
			"settings" 	=> array(
							"type" => "TEXT",
							"null" => TRUE
						),
			"added_by" 	=> array(
							"type" => "INT"
						),
			"date_added" => array(
							"type" => "INT"
						)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->db_name);

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


		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table($this->db_name);

		return TRUE;
	}

}
