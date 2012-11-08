<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Local Data Model
 *
 * Used by controllers to examine and extract data about site this
 * module has been installed in
 *
 * @author Christopher Imrie
 */
class Local_data extends CI_model
{
	//Public
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




	/**----------------------------------------------------
	 * Public
	 * ----------------------------------------------------
	 */


	public function public_key()
	{
		return $this->_public_key;
	}

	public function private_key()
	{
		return $this->_private_key;
	}


	/**
	 * Get local config payload
	 *
	 * Creates an encoded payload that contains all configurations
	 * needed in order to setup and communicate with the site this
	 * module is installed in.
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
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



	/**
	 * Category Group Data
	 *
	 * As well as the groups, it fetches the categories and fields and adds them
	 * as arrays to each category group
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
	public function categorygroup_data()
	{
		//Fetch all the data we need
		$categories 	= $this->EE->db->where("site_id", $this->site_id)
										->get("categories")
										->result_array();

		$fields 		= $this->EE->db->where("site_id", $this->site_id)
										->get("category_fields")
										->result_array();

		$categorygroups = $this->EE->db->where("site_id", $this->site_id)
										->get("category_groups")
										->result_array();

		foreach ($categorygroups as $key => $group) {

			//Attach Categories

			$categorygroups[$key]['categories'] = array();
			foreach ($categories as $c_key => $cat) {

				//Category for current group?
				if($cat['group_id'] == $group['group_id']) {

					//Yes - add to category array in group
					$categorygroups[$key]['categories'][] = $cat;

					//Unset category to speed things up when processing large numbers of categories
					unset($categories[$c_key]);
				}
			}

			//Attach Fields
			$categorygroups[$key]['fields'] = array();
			foreach ($fields as $f_key => $field) {

				//Category for current group?
				if($field['group_id'] == $group['group_id']) {

					//Yes - add to category array in group
					$categorygroups[$key]['fields'][] = $field;

					//Unset category to speed things up when processing large numbers of fields
					unset($field[$f_key]);
				}
			}

			$categorygroups[$key]['total_categories'] = count($categorygroups[$key]['categories']);
		}

		return $categorygroups;
	}





	/**
	 * Channel Data
	 *
	 * Instead of just fetching channel data, this also combines the
	 * channel fields in a sub array of each channel
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
	public function channel_data()
	{
		$this->EE->load->driver("Channel_data");
		$fieldgroups = $this->EE->channel_data->get_field_groups();
		$fields = $this->EE->channel_data->get_fields();
		$channels = $this->EE->channel_data->get_channels();

		$data = array();



		foreach ($channels->result_array() as $key => $channel) {
			if($channel['site_id'] != $this->site_id) continue;

			$d = $channel;
			$d["fields"] = array();

			//Fields
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

			//Fieldgroup Name for later identification
			$d['field_group_name'] = "";
			foreach ($fieldgroups->result_array() as $key => $fieldgroup) {
				if($fieldgroup['group_id'] == $channel['field_group']){
					$d['field_group_name'] = $fieldgroup['group_name'];
					break;
				}
			}

			$data[] = $d;
		}

		return $data;
	}



	/**
	 * Channel Field Data
	 *
	 * @author Christopher Imrie
	 *
	 * @return object 		CI DB Result
	 */
	public function channel_fields()
	{
		$this->EE->load->driver("Channel_data");
		return $this->EE->channel_data->get_fields();
	}


	/**
	 * Fieldgroup Data
	 *
	 * @author Christopher Imrie
	 *
	 * @return object      CI DB Result
	 */
	public function fieldgroup_data()
	{
		$this->EE->load->model("field_model");
		return $this->EE->field_model->get_field_groups();
	}


	/**
	 * Field Data
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $group_id
	 * @return object                	CI DB Result
	 */
	public function fieldgroup_field_data($group_id='')
	{
		$this->EE->load->model("field_model");
		$custom_fields = $this->EE->field_model->get_fields($group_id, array('site_id' => $this->site_id));

		/**
		 * Add extra data for special fields
		 */
		$custom_fields = $this->_include_special_field_data($custom_fields->result_array());

		return $custom_fields;
	}



	/**
	 * Addon Data
	 *
	 * Because of the non-standardised way that EE checks, loads and handles different addon
	 * types, this method is pretty complex and likely to need updating frequently.
	 *
	 * The logic here comes from examing (and copying frequently) the EE CP controllers
	 * that power the "addons" section of the CP.
	 *
	 * //TODO
	 * - Break up into multiple method fetches for each addon type
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
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

			if(@$module_info['type'] == "native") {
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

			if(@$ext['type'] == "native") {
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

			if(@$ft_info['type'] == "native") {
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
		$accessories = array();
		//$accessories = $this->addons->get_files('accessories');
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



	/**
	 * Create Channel
	 *
	 * Still quite a bit to do here, but crucially it verifies and preserves
	 * fieldgroup relationships.
	 *
	 * TODO:
	 * - Validate/Create Status group
	 * - Validate/Create category group
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $data
	 * @return array
	 */
	public function create_channel($data=array())
	{
		global $_POST;

		if(!$data) {
			return $this->error("No data provided for channel creation");
		}

		//Load admon content controller
		$this->EE->load->file(PATH_THIRD.strtolower($this->_module_name)."/classes/mock_admin_content.php");

		//Is there a field group?
		if(isset($data['field_group']) && $data['field_group'] != NULL) {

			//Match fieldgroup ID.  If there is no fieldgroup, then we cannot proceed
			$q = $this->EE->db->where("group_name", $data['field_group_name'])
								->limit(1)
								->where("site_id", $this->site_id)
								->get("field_groups");

			if($q->num_rows() == 0) {
				return $this->error("The field group '".$data['field_group_name']."' could not be found on destination site.  Please create this field group and then try again.");
			}
			$data['field_group'] = $q->row()->group_id;
		}


		//Filter the input fields
		$fields = $this->EE->db->list_fields("channels");
		foreach ($data as $key => $value) {
			if(!in_array($key, $fields)) {
				unset($data[$key]);
			}
		}





		$_POST = $data;

		$c = new Mock_admin_content();
		$c->channel_update();

		//Now lets update with group assignments etc
		$_POST = $data;
		$_POST['channel_id'] = $this->EE->db->insert_id();
		$c->channel_update_group_assignments();

		return array("success" => TRUE);
	}





	/**
	 * Create a new Category Group
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $data
	 * @return array
	 */
	public function create_categorygroup($data=array())
	{
		$categories = array();

		if(!$data) {
			return $this->error("No data provided for category creation");
		}


		$this->EE->load->model("category_model");

		//Remove category data
		if($data['categories']) {
			$categories = $data['categories'];
			unset($data['categories']);
		}

		//Legacy EE2 sites dont have the 'exclude_group' field
		if(!$this->EE->db->field_exists("exclude_group", "category_groups")) {
			unset($data['exclude_group']);
		}

		$this->EE->category_model->insert_category_group($data);
		$group_id = $this->EE->db->insert_id();

		$insert_ids = array();
		$id_map  =array();


		//Insert all Categories first (parent ids are fixed further down).  Track all id's new and old
		foreach ($categories as $key => $category) {
			$id = $category['cat_id'];

			unset($category['cat_id']);

			$category['site_id'] = $this->site_id;
			$category['group_id'] = $group_id;


			$this->EE->db->insert("categories", $category);

			$new_id = $this->EE->db->insert_id();

			$insert_ids[] = $new_id;
			$id_map[$id] = $new_id;
		}

		//Correct Parent Id's
		$c = $this->EE->db->where_in("cat_id", $insert_ids)->get("categories")->result_array();
		foreach ($c as $key => $category) {
			if($category['parent_id'] != 0) {
				$this->EE->db->where("cat_id", $category['cat_id'])->update("categories", array("parent_id" => $id_map[$category['parent_id']]));
			}
		}


		//Parents first


		//Return a complete record
		$data['site_id'] = $this->site_id;
		$data['group_id'] = $group_id;

		return array("success" => TRUE);
	}


	/**
	 * Create Field Group
	 *
	 * Use native EE Admin_content controller to trigger the creation of a new
	 * field group.  Since native methods are being used here, all native trigger
	 * points are fired, meaning EE is under the impression a valid, logged in user
	 * is creating this field group.
	 *
	 * In order for the Controller to be fooled, we need to build our own POST array
	 * since it will be accessing the raw post data submitted by the user
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array      $data
	 * @return array
	 */
	public function create_fieldgroup($data=array())
	{
		if(!$data) {
			return $this->error("No data provided for fieldgroup creation");
		}

		//Load admon content controller
		$this->EE->load->file(PATH_THIRD.strtolower($this->_module_name)."/classes/mock_admin_content.php");

		if(!isset($data['group_name'])) show_error("Group name must be specified");

		//Prep POST input
		$_POST['group_name'] = $data['group_name'];

		$c = new Mock_admin_content();
		$c->field_group_update();

		return array("success" => true);
	}



	/**
	 * Create Field
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $data
	 * @return array
	 */
	public function create_field($group_id, $data=array())
	{
		if(!$group_id) {
			return $this->error("Field group not specified");
		}

		if(!$data) {
			return $this->error("No data provided for field creation");
		}


		//Load admon content controller
		$this->EE->load->file(PATH_THIRD.strtolower($this->_module_name)."/classes/mock_admin_content.php");


		$extra = array();
		if(isset($data['extra'])) {
			$extra = $this->_decode_field_settings($data['extra']);
			unset($data['extra']);
		}



		//Verify the field exists and is installed in this site
		if(!$this->_is_fieldtype_installed($data['field_type'])){
			return $this->error("Fieldtype '".$data['field_type']."' is not installed on destination site.  Please install this fieldtype and try again.");
		}


		//OK, we create a dummy field via EE's APIs, so it does the heavy lifting with regards
		//to DB manipulation and key matching.  Once we have an insert ID, we'll then replace this
		//with the raw DB data from the remote site
		$_POST = array(
			"group_id" 						=> $group_id,
			"site_id" 						=> $this->site_id,
			"field_name" 					=> "temp".time(),
			"field_label" 					=> "Placeholder",
			"field_type" 					=> "text",
			"field_order" 					=> 0,
			"field_instructions" 			=> "",
			"field_required"				=> "n",
			"field_search"					=> "n",
			"field_is_hidden"				=> "n",
			"field_order"					=> 4,
			"field_maxl"					=> "128",
			"text_field_fmt"				=> "none",
			"text_field_show_fmt"			=> "n",
			"text_field_text_direction"		=> "ltr",
			"text_field_content_type"		=> "all",
			"text_field_show_smileys"		=> "n",
			"text_field_show_glossary"		=> "n",
			"text_field_show_spellcheck"	=> "n",
			"text_field_show_file_selector"	=> "n"
		);



		//Get EE to create the field officially....
		$c = new Mock_admin_content();
		$c->field_update();


		//What was the last field created ID?  We know there is one, so this is pretty safe...
		$field_id = $this->EE->db->from("channel_fields")->order_by("field_id", "desc")->get()->row()->field_id;



		//Prep data input
		unset($data['field_id']);
		$data['group_id'] = $group_id;
		$data['site_id'] = $this->site_id;

		//Make sure no DB errors by matching field names
		$fields = $this->EE->db->list_fields("channel_fields");

		foreach ($data as $key => $value) {
			if(!in_array($key, $fields)){
				unset($data[$key]);
			}
		}



		//Update the field with the DB data from the remote site
		$this->EE->db->where("field_id", $field_id);
		$this->EE->db->update("channel_fields", $data);


		//Any extra DB data sent with this field?
		if($extra) {
			$this->_rebuild_special_fields($field_id, $data['field_type'], $extra);
		}


		return array("success" => true);
	}







	/**----------------------------------------------------
	 * Private
	 * ----------------------------------------------------
	 */





	/**
	 * Standard Error response format
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $msg
	 * @return array
	 */
	public function error($msg='')
	{
		return array(
			"success" => FALSE,
			"error" => $msg
		);
	}



	/**
	 * Initialise the Model
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	private function _init()
	{
		//Fetch module specific data
		$this->_site_id = $this->EE->config->item("site_id");
		$q = $this->_fetch_site_db_data($this->_site_id);
		$this->_private_key = $q->private_key;
		$this->_public_key = $q->public_key;
		$this->_settings = $q->settings;

		if(!class_exists("Site_manager_server")) {
			$this->EE->load->file(PATH_THIRD."site_manager_server/mod.site_manager_server.php");
		}
		Site_manager_server::$public_key = $this->_public_key;
		Site_manager_server::$private_key = $this->_private_key;

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





	private function _include_special_field_data($custom_fields=array())
	{
		foreach ($custom_fields as $key => $field) {

			if($field['field_type'] == "matrix") {
				$custom_fields[$key]['extra'] = $this->_encode_field_settings($this->_include_matrix_field_data($field));
			}
		}

		return $custom_fields;
	}




	private function _rebuild_special_fields($field_id, $type, $data=array())
	{
		if($type == "matrix") {
			$this->_rebuild_matrix_data($field_id, $data);
		}
	}



	private function _include_matrix_field_data($field=array())
	{
		$extra = array();

		//DB Table Name as key
		$extra['matrix_cols'] = $this->EE->db->where("site_id", $this->site_id)
													->where("field_id", $field['field_id'])
													->get("matrix_cols")
													->result_array();

		//Matrix stores data in field columns for each matrix column.  The column type is
		//different for each matrix cell type.  Make a note of it here so we can copy
		//at the other end
		$extra['matrix_data'] = array();
		$fields = $this->EE->db->field_data("matrix_data");
		foreach ($fields as $key => $field) {
			if(strpos($field->name, "col_id_") === 0) {
				$id = str_replace("col_id_", "", $field->name);
				$extra['matrix_data'][$id] = array(
					"type" => $field->type,
					"length" => $field->max_length
				);
			}

		}
		return $extra;
	}



	private function _rebuild_matrix_data($field_id, $data=array())
	{
		$col_ids = array();

		//Sigh... DB Forge gets attached to CI instance, not EE
		$this->EE->load->dbforge();
		$c =& CI_Controller::get_instance();



		foreach ($data['matrix_cols'] as $key => $row) {
			$old_id = $row['col_id'];

			//Matrix Cols Table
			unset($row['col_id']);

			$row['field_id'] = $field_id;
			$row['site_id'] = $this->site_id;

			$this->EE->db->insert("matrix_cols", $row);
			$new_id = $this->EE->db->insert_id();



			//Matrix Data Table
			$cell = $data['matrix_data'][$old_id];
			$fields = array(
            	'col_id_'.$new_id => array(
					'type' => $cell['type'] == "int" ? "INT" : "TEXT"
				)
    		);
    		$c->dbforge->add_column("matrix_data", $fields);
		}


		foreach ($col_ids as $key => $col_id) {

		}
	}




	/**
	 * Verifies whether a fieldtype is installed
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $field_name
	 * @return boolean
	 */
	private function _is_fieldtype_installed($field_name='')
	{
		$this->EE->load->library("addons");
		$installed_fts = $this->EE->addons->get_installed('fieldtypes');

		if(isset($installed_fts[$field_name])) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get installed plugins
	 *
	 * Utility method used by addon_data method.  Scraped from
	 * the EE CP plugin page controller
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
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


	/**
	 * Fetch local settings from DB
	 *
	 * A little more complex that it first appears since we need to create a local
	 * settings config if there is not one already.
	 *
	 * Using a teeny bit of recursion we create a new if needed that includes public/private
	 * keys to be used for data transmission
	 *
	 * @author Christopher Imrie
	 *
	 * @param  integer      $site_id
	 * @return object
	 */
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


	/**
	 * Decode field settings
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $settings
	 * @return array
	 */
	public function _decode_field_settings($settings='')
	{
		return unserialize(base64_decode($settings));
	}


	/**
	 * Encode field settings
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $settings
	 * @return array
	 */
	public function _encode_field_settings($settings=array())
	{
		return base64_encode(serialize($settings));
	}


	/**
	 * Generate a unique 32 character key (for private/public key generation)
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	private function _generate_key()
	{
		$c =& CI_Controller::get_instance();
		return md5(rand(0,9999).$c->security->xss_hash().rand(0,9999));
	}


	/**
	 * Encode settings for transport (ie: a string that can be copied and pasted to site manager client)
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array      $data
	 * @return string
	 */
	private function _prep_settings_payload_for_transport($data=array())
	{
		return base64_encode(serialize($data));
	}


	/**
	 * Encode settings for DB storage
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $data
	 * @return string
	 */
	private function _prep_settings_for_db($data=array())
	{
		return base64_encode(serialize($data));
	}


	/**
	 * Decode settings from DB storage
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string       $data
	 * @return array
	 */
	private function _restore_settings_from_db($data="")
	{
		return unserialize(base64_decode($data));
	}


	/**
	 * Fetch this modules' installed action ID (there is only one)
	 *
	 * @author Christopher Imrie
	 *
	 * @return string
	 */
	private function _fetch_action_id()
	{
		return $this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id($this->_module_name, $this->_action_name));
	}
}