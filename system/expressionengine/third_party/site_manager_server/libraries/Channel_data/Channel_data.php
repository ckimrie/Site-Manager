<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Data
 *
 * Channel Data is a convenience class designed to easily retrieve
 * channel data from plugins. By using this one class, you don't 
 * have to worry about loading models.
 *
 * Channel Data is also a third party add-on API framework that
 * allows developers to interact with other add-ons programatically.
 *
 * Channel Data is modeled off CodeIgniter's driver object, but does
 * not actually extend the native driver class due to the way
 * ExpressionEngine handles loading drivers.
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


include_once('Channel_data_lib.php');

if(!class_exists('Channel_data'))
{
	class Channel_data extends Channel_data_lib {
		
		// Available Drivers
			
		public $drivers = array('channel_data_api', 'channel_data_utility', 'channel_data_tmpl');	
		public $debug	= FALSE;
		
		/**
		 * Construct
		 *
		 * Constructs the object and loads all the available drivers
		 *
		 * @param	array	Additional parameters used to instatiate the object
		 * @return	void
		 */	
		
		public function __construct($params = array())
		{
			parent::__construct($params);	
			
			$drivers = isset($params['drivers']) ? $params['drivers'] : $this->drivers;
			
			$this->load_drivers($drivers);	
		}
		
		/**
		 * Load Drivers
		 *
		 * Loads the Channel Data driver
		 *
		 * @param	mixed	You can override the default drivers by passing
		 					an array of drivers to load.
		 * @return	void
		 */	
		
		public function load_drivers($drivers = FALSE)
		{	
			$this->drivers = $drivers ? $drivers : $this->drivers;
			
			foreach($this->drivers as $driver)
			{
				$driver	= ucfirst($driver);
				$obj	= str_replace(array(__CLASS__.'_',strtolower(__CLASS__.'_')),'', $driver);
				$path 	= 'drivers/' . ucfirst($driver) . '.php';
				
				include_once($path);				
					
				if(class_exists($driver))
				{
					$this->$obj = new $driver();
				}
			}
		}
	}
}