<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Local_data extends CI_model
{
	var 	$EE;
	var 	$site_id; 		//Sent with every API request

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






	public function channel_data()
	{
		$this->EE->load->driver("Channel_data");
		$fields = $this->EE->channel_data->get_fields();
		$channels = $this->EE->channel_data->get_channels();

		$data = array();



		foreach ($channels->result_array() as $key => $channel) {
			if($channel['site_id'] != $this->site_id) continue;

			$d = array(
				"channel_id"			=> $channel["channel_id"],
				"site_id"				=> $channel["site_id"],
				"channel_name"			=> $channel["channel_name"],
				"channel_title"			=> $channel["channel_title"],
				"channel_url"			=> $channel["channel_url"],
				"total_entries"			=> $channel["total_entries"],
				"total_comments"		=> $channel["total_comments"],
				"field_group"			=> $channel['field_group'],
				"fields"				=> array()
			);

			foreach ($fields->result_array() as $key2 => $field) {
				if($field['group_id'] == $channel['field_group']) {
					$d['fields'][] = array(		
						"field_id"				=> $field["field_id"],
						"field_name"			=> $field["field_name"],
						"field_label"			=> $field["field_label"],
						"field_type"			=> $field["field_type"]
					);
				}
			}

			$data[] = $d;
		}

		return $data;
	}


	public function channel_fields()
	{
		$this->EE->load->driver("Channel_data");
		return $this->EE->channel_data->get_fields();
	}



	public function addon_data()
	{
		$this->EE->load->helper('file');
		$this->EE->load->library("addons");
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$data = array(
			"native" => array(
				"modules" 		=> array(),
				"extensions" 	=> array(),
				"fieldtypes" 	=> array(),
				"accessories" 	=> array(),
				"plugins"		=> array()
			),
			"third_party" => array(
				"modules" 		=> array(),
				"extensions" 	=> array(),
				"fieldtypes" 	=> array(),
				"accessories" 	=> array(),
				"plugins"		=> array()
			)
		);


		/**
		 * Modules
		 */

		//  Fetch all module names from "modules" folder
		$installed_modules = $this->addons->get_installed();
		$modules = $this->addons->get_files();


		foreach($modules as $module => $module_info)
		{
			$m = array();
			$type = "third_party";

			if($module_info['type'] == "native") {
				$type = "native";
			}

			$this->lang->loadfile( $module);

			$m['label'] = (lang(strtolower($module).'_module_name') != FALSE) ? lang(strtolower($module).'_module_name') : $module_info['name'];
			$m['name'] = $module;
			$m['version'] = "---";
			$m['installed'] = FALSE;
			$m['has_cp_backend'] = FALSE;

			if (isset($installed_modules[$module]))
			{
				$m['version'] = $installed_modules[$module]['module_version'];
				$m['installed'] = TRUE;

				if($installed_modules[$module]['has_cp_backend'] == 'y') {
					$m['has_cp_backend'] = TRUE;
				}
			}

			//If uninstalled, then always set this to false to avoid unecessary UI logic
			if(!$m['installed']) {
				$m['has_cp_backend'] = FALSE;
			}

			$data[$type]['modules'][] = $m;
		}


		/**
		 * Extensions
		 */
		$extension_files = $this->addons->get_files('extensions');
		$installed_ext_q = $this->addons_model->get_installed_extensions();
		$installed_extensions = array();

		foreach ($installed_ext_q->result_array() as $row)
		{
			// Check the meta data
			$installed_extensions[$row['class']] = $row;
		}

		foreach ($extension_files as $ext_name => $ext) {
			
			$this->EE->load->add_package_path($ext['path']);

			$class_name = $ext['class'];

			//Load extension file if possible
			if ( ! class_exists($class_name))
			{
				@include($ext['path'].$ext['file']);
				
				if ( ! class_exists($class_name))
				{
					continue;
				}
			}

			$OBJ = new $class_name();

			$e = array();
			$type = "third_party";

			if($ext['type'] == "native") {
				$type = "native";
			}

			$e['label'] = (isset($OBJ->name)) ? $OBJ->name : $extension_files[$ext_name]['name'];
			$e['name'] = $ext_name;
			$e['version'] = $OBJ->version;
			$e['installed'] = ( isset($installed_extensions[$ext['class']]) ) ? TRUE : FALSE;
			$e['has_cp_backend'] = $OBJ->settings_exist == 'y' ? TRUE : FALSE;

			//If uninstalled, then always set this to false to avoid unecessary UI logic
			if(!$m['installed']) {
				$m['has_cp_backend'] = FALSE;
			}

			$data[$type]['extensions'][] = $e;
		}



		/**
		 * Fieldtypes
		 */
		$fieldtypes = $this->EE->api_channel_fields->fetch_all_fieldtypes();
		$installed_fts = $this->EE->addons->get_installed('fieldtypes');

	

		foreach ($fieldtypes as $fieldtype => $ft_info)
		{
			if ($fieldtype == 'hidden')
			{
				continue;
			}
			
			$f = array();
			$type = "third_party";

			if($ft_info['type'] == "native") {
				$type = "native";
			}

			if(!isset($installed_fts[$fieldtype]['has_global_settings'])) {
				$installed_fts[$fieldtype]['has_global_settings'] = "n";
			}

			$f['label'] = $ft_info['name'];
			$f['name'] = $fieldtype;
			$f['version'] = $ft_info['version'];
			$f['installed'] = (isset($installed_fts[$fieldtype]));
			$f['has_cp_backend'] = $f['installed'] && $installed_fts[$fieldtype]['has_global_settings'] == "y" ? TRUE : FALSE;


			$data[$type]['fieldtypes'][] = $f;
		}


		/**
		 * Plugins
		 */
		$plugins = $this->_get_installed_plugins();


		foreach ($plugins as $name => $plugin) {
			
			$p = array();
			$type = "third_party";

			if(in_array($name, array("magpie", "xml_encode"))) {
				$type = "native";
			}

			$p['label'] = $plugin['pi_name'];
			$p['name'] = $name;
			$p['version'] = $plugin['pi_version'];
			$p['installed'] = TRUE;
			$p['has_cp_backend'] = TRUE;

			$data[$type]['plugins'][] = $p;
		}


		/**
		 * Accessories
		 */

		$accessories = $this->addons->get_files('accessories');
		$installed = $this->addons->get_installed('accessories');


		foreach ($accessories as $name => $info)
		{
			// Grab the version and description
			if ( ! class_exists($accessories[$name]['class']))
			{
				include $accessories[$name]['path'].$accessories[$name]['file'];
			}

			// add the package and view paths
			$path = PATH_THIRD.strtolower($name).'/';

			$this->EE->load->add_package_path($path, FALSE);
			
			$ACC = new $accessories[$name]['class']();

			$this->EE->load->remove_package_path($path);

			$a = array();
			$type = "third_party";

			if(in_array($name, array("expressionengine_info", "news_and_stats", "learning", "quick_tips"))) {
				$type = "native";
			}
			$a['label'] = $ACC->name;
			$a['name'] = $name;
			$a['version'] = $ACC->version;
			$a['installed'] = isset($installed[$name]);
			$a['has_cp_backend'] = isset($installed[$name]);

			$data[$type]['accessories'][] = $a;
		}


		return $data;
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


	private function _get_installed_plugins()
	{
		$this->EE->load->helper('file');

		$ext_len = strlen('.php');

		$plugin_files = array();
		$plugins = array();

		// Get a list of all plugins
		// first party first!
		if (($list = get_filenames(PATH_PI)) !== FALSE)
		{
			foreach ($list as $file)
			{
				if (strncasecmp($file, 'pi.', 3) == 0 && 
					substr($file, -$ext_len) == '.php' && 
					strlen($file) > 7 && 
					in_array(substr($file, 3, -$ext_len), $this->core->native_plugins))
				{
					$plugin_files[$file] = PATH_PI.$file;
				}
			}
		}


		// third party, in packages
		if (($map = directory_map(PATH_THIRD, 2)) !== FALSE)
		{
			foreach ($map as $pkg_name => $files)
			{
				if ( ! is_array($files))
				{
					$files = array($files);
				}

				foreach ($files as $file)
				{
					if (is_array($file))
					{
						// we're only interested in the top level files for the addon
						continue;
					}

					// we gots a plugin?
					if (strncasecmp($file, 'pi.', 3) == 0 && 
						substr($file, -$ext_len) == '.php' && 
						strlen($file) > strlen('pi.'.'.php'))
					{
						if (substr($file, 3, -$ext_len) == $pkg_name)
						{
							$plugin_files[$file] = PATH_THIRD.$pkg_name.'/'.$file;
						}
					}
				}
			}
		}

		ksort($plugin_files);

		// Grab the plugin data
		foreach ($plugin_files as $file => $path)
		{
			// Used as a fallback name and url identifier
			$filename = substr($file, 3, -$ext_len);

			// Magpie maight already be in use for an accessory or other function
			// If so, we still need the $plugin_info, so we'll open it up and
			// harvest what we need. This is a special exception for Magpie.
			if ($file == 'pi.magpie.php' && 
				in_array($path, get_included_files()) && 
				class_exists('Magpie'))
			{
				$contents = file_get_contents($path);
				$start = strpos($contents, '$plugin_info');
				$length = strpos($contents, 'Class Magpie') - $start;
				eval(substr($contents, $start, $length));
			}

			@include_once($path);

			if (isset($plugin_info) && is_array($plugin_info))
			{
				// Third party?
				$plugin_info['installed_path'] = $path;

				// fallback on the filename if no name is given
				if ( ! isset($plugin_info['pi_name']) OR $plugin_info['pi_name'] == '')
				{
					$plugin_info['pi_name'] = $filename;
				}

				if ( ! isset($plugin_info['pi_version']))
				{
					$plugin_info['pi_version'] = '--';
				}
				$plugins[$filename] = $plugin_info;
			}
			else
			{
				//log_message('error', "Invalid Plugin Data: {$filename}");
			}

			unset($plugin_info);
		}

		return $plugins;
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