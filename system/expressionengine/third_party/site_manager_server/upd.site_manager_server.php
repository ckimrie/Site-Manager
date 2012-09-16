<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Installer
*/
class Site_manager_server_upd
{

	var $EE;
	var $version	= 0.1;
	var $module_name = "Site_manager_server";
	
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
		
		return TRUE;
	}
	
}
