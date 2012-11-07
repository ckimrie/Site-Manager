<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Theme Loader
 *
 * A helper class that allows developers to easily add CSS and JS 
 * packages from the associating third party theme directory.
 *
 * @package		Theme Loader
 * @subpackage	Libraries
 * @category	Library
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2011, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/libraries/channel_data
 * @version		1.3.0
 * @build		20121106
 */
 
if(!class_exists('Theme_loader'))
{
	class Theme_loader {
		
		/*
		 * Module name
		*/
		
		public $module_name;
		
		/*
		 * Loaded files
		*/
		
		public $loaded_files = array();
		
		/*
		 * Javascript directory name
		*/
		 
		public $js_directory = 'javascript';
		
		/*
		 * Javascript file extension
		*/
		
		public $js_ext = '.js';
		
		/*
		 * CSS directory name
		*/
		
		public $css_directory = 'css';
		
		/*
		 * CSS file extension
		*/
		
		public $css_ext  = '.css';
		
		/*
		 * Localhost URL
		*/
		
		public $localhost = 'http://localhost';
		
		/*
		 * Regex pattern that validates URL's
		*/
		
		public $url_format;
		
		/*
		 * Use the RequieJS for EE extension?
		*/
		
		public $requirejs = TRUE;
		
		/**
		 * Construct
		 *
		 * @access	public
		 * @param	array	Pass a module name to define 
		 * @return	void
		 */
		
		public function __construct($data = array())
		{
			$this->EE =& get_instance();
			
			if(isset($data['module_name']))
			{
				$this->module_name = $data['module_name'];
			}
			else if(isset($data[0]))
			{
				$this->module_name = strtolower(str_replace(array('_mcp', '_upd'), '', $data[0]));
			}
			
			/* Url Validation */
			$this->url_format = 

			'/^(https?):\/\/'.                                         // protocol
			'(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
			'(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
			'@)?(?#'.                                                  // auth requires @
			')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
			'[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
			'|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
			'(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
			')(:\d+)?'.                                                // port
			')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
			'(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
			'?)?)?'.                                                   // path and query string optional
			'(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
			'$/i';
			
		}
		
		/**
		 * Get the theme file path
		 *
		 * @access	public
		 * @return	string
		 */
		
		public function theme_path($addon_path = TRUE, $append = NULL)
		{
			if($addon_path)
			{
				if($config = config_item('path_third_themes'))
				{
					return $config . $append;
				}
				else if(defined('PATH_THIRD_THEMES'))
				{
					return PATH_THIRD_THEMES . $append;
				}
				else
				{
					return rtrim($this->EE->config->item('theme_folder_path'), '/').'/third_party/' . $append;;
				}
			}
			else
			{
				return config_item('theme_folder_path') . $append;
			}
		}
		
		/**
		 * Get the theme URL
		 *
		 * @access	public
		 * @return	string
		 */
		
		public function theme_url($addon_theme = TRUE, $append = NULL)
		{			
			if($addon_theme)
			{
				if($config = config_item('url_third_themes'))
				{
					return $config . $append;
				}
				else if(defined('URL_THIRD_THEMES'))
				{
					return URL_THIRD_THEMES . $append;
				}
				else
				{
					return rtrim($this->EE->config->item('theme_folder_url'), '/').'/third_party/' . $append;
				}
			}
			else
			{
				return config_item('theme_folder_url') . $append;
			}
		}	
		
		/**
		 * Add a javascript file to the document head
		 *
		 * @access	public
		 * @param   string	A valid file name, no ext necessary
		 * @return	string
		 */
		
		public function javascript($file)
		{
			$file = $this->prep_url($this->js_directory, $file, $this->js_ext);
			
			if(!in_array($file, $this->loaded_files))
			{
				$this->loaded_files[] = $file;
			
				if($this->requirejs && isset($this->EE->requirejs))
				{
					$this->EE->requirejs->add($file);
				}
				else
				{					
					$this->EE->cp->add_to_head('<script type="text/javascript" src="'.$file.'"></script>');
				}
			}
		}
		
		/**
		 * Add a css file to the document head
		 *
		 * @access	public
		 * @param   string	A valid file name, no ext necessary
		 * @return	string
		 */
		
		public function css($file)
		{	
			$file = $this->prep_url($this->css_directory, $file, $this->css_ext);
			
			if(!in_array($file, $this->loaded_files))
			{
				$this->loaded_files[] = $file;
			
				$this->EE->cp->add_to_head('<link type="text/css" href="'.$file.'" rel="stylesheet" media="screen" />');
			}
		}
		
		/**
		 * Add a javascript file to the document head
		 *
		 * @access	private
		 * @param   string	A valid directory name, no ext necessary
		 * @param   string	A valid file name, no ext necessary
		 * @param   string	A valid file extension (.css|.js)
		 * @return	string
		 */
		
		private function prep_url($directory, $file, $ext)
		{
			if(!$this->is_valid_url($file))
			{
				$file 	= str_replace(array($this->js_ext, $this->css_ext), '', $file);
				$file 	= $this->theme_url() . $this->module_name . '/' . $directory . '/' . $file . $ext;
			}
			
			return $file;	
		}
		
		/**
		 * Verify the syntax of the given URL. 
		 * 
		 * @access public
		 * @param $url The URL to verify.
		 * @return boolean
		 */
		private function is_valid_url($url) {
		  if ($this->str_starts_with(strtolower($url), $this->localhost)) {
		    return true;
		  }
		  return preg_match($this->url_format, $url);
		}
	
		/**
		 * String starts with something
		 * 
		 * This function will return true only if input string starts with
		 * niddle
		 * 
		 * @param string $string Input string
		 * @param string $niddle Needle string
		 * @return boolean
		 */
		private function str_starts_with($string, $niddle) {
		      return substr($string, 0, strlen($niddle)) == $niddle;
		}		
	}
}