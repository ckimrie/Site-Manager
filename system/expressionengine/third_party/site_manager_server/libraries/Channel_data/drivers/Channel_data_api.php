<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base API
 *
 * This is an abstract class that all third party API's will be
 * extended from.
 *
 * @package		Channel Data
 * @subpackage	Libraries
 * @category	Library
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/libraries/channel_data
 * @version		0.7.0
 * @build		20120711
 */
 
abstract class Base_API extends Channel_data {
	
	public $version;
	
	public abstract function usage();
}

/**
 * Channel Data API
 *
 * This class does performs all the loading and instantiating of the
 * third party API's.
 *
 * @package		Channel Data
 * @subpackage	Libraries
 * @category	Library
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/libraries/channel_data
 * @version		0.7.0
 * @build		20120711
 */

class Channel_data_api {

	public $name;
	public $author;
	public $version;
	
	/**
	 * Does API Exist
	 *
	 * Returns a true if the API exists
	 *
	 * @access	public
	 * @param	string	Name of the API to load
	 * @return	void
	 */

	public function does_api_exist($module)
	{
		extract($this->get_module_info($module));
		
		if(file_exists($path))
		{
			include_once($path);
			
			if(class_exists($module_api))
			{
				return TRUE;
			}		
			else
			{
				show_error('The class <i>'.$module_name.'</i> does not exist. Check that your files are named correctly, and that the module has a valid API (api.'.$module_name.'.php).');
			}
		}
		else
		{
			show_error('The <i>'.$module_name.'</i> API cannot be found. Double check that your module name is correct and that the module has a valid API (api.'.$module_name.'.php).');
		}
		
		return FALSE;
	}
	
	/**
	 * Get Module Info
	 *
	 * Creates and array with information about the module
	 *
	 * @access	public
	 * @param	type	description
	 * @return	void
	 */
		
	public function get_module_info($module)
	{
		return array(
			'module'			=> $modulue = strtolower($module),
			'module_api' 		=> $module_api = ucfirst($module).'_api',
			'package'			=> $package = PATH_THIRD . $module,
			'path'				=> $package . '/api.' . $module . '.php'
		);
	}
	
	/**
	 * Load
	 *
	 * Instantiates a new instance of the module class
	 *
	 * @access	public
	 * @param	string	Name of the module being loaded
	 * @param	array	An array of parameters that will get passed to the 
	 *					constructor method.
	 * @param	mixed	You can (optionally) load the instance to a custom
	 *					object.
	 * @return	object
	 */
	
	
	public function load($module, $params = array(), $object_name = false)
	{
		extract($this->get_module_info($module));
		
		if($this->does_api_exist($module))
		{
			$EE =& get_instance();
			
			$EE->load->add_package_path($package);	
						
			$obj = new $module_api($params);
			
			if($object_name === FALSE)
				$EE->channel_data->$module = $obj;
			else
				$EE->channel_data->$object_name->$module = $obj;
			
			return $obj;
		}
	
		show_error('An unknown error has occurred while loading the API.');
	}
	
	/**
	 * Usage
	 *
	 * Call the Usage method on a given module
	 *
	 * @access	public
	 * @param	string	Name of the module being loaded
	 * @param	array	An array of parameters that will get passed to the 
	 *					constructor method.
	 * @return	void
	 */
	
	
	public function usage($module, $params = array())
	{
		extract($this->get_module_info($module));
		
		if($this->does_api_exist($module))
		{
			$EE =& get_instance();

			$EE->load->add_package_path($package);	
						
			$obj = new $module_api($params);
			
			return $obj->usage();
		}
	}
}
