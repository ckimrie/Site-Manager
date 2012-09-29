<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Site extends Remote_Object
{
	var $site_data;


	//Runtime vars
	protected 	$_id;
	protected	$_db_object;


	//Class Vars
	static 		$_db_cache = array();

	function __construct($id, $db_object=FALSE)
	{
		parent::__construct();


		if(!$db_object) {
			$db_object = $this->_get($id);
		}


		$this->_init($db_object);
	}





	public function id()
	{
		return $this->_id;
	}


	public function name()
	{
		return $this->_db_object->site_name;
	}


	public function base_url()
	{
		return $this->_db_object->base_url;
	}

	public function cp_url()
	{
		return $this->setting('cp_url');
	}


	public function local_url()
	{
		$this->EE->load->helper("navigation");
		return methodUrl('site_details')."&site_id=".$this->id();
	}

	public function setting($key='')
	{
		return isset($this->_db_object->settings[$key]) ? $this->_db_object->settings[$key] : FALSE;
	}



	public function config()
	{
		$this->new_connection("config");

		$a = $this->curl->execute();

		return json_decode($a, TRUE);
	}


	public function js_config()
	{
		$a = array(
			"site_id" => $this->id(),
			"site_name" => $this->name(),
			"base_url" => $this->base_url(),
			"cp_url" => $this->cp_url(),
			"local_url" => $this->local_url(),
			"api_url" => $this->buildUrl()
		);

		return json_encode($a);
	}



	public function thumbnail($width=120)
	{
		return "http://zenithwebtechnologies.com.au/thumbnail01.php?type=png&width=".$width."&imageSpecs=absolute&url=".$this->base_url();
	}




	/**
	 * Initialise the class
	 * @param  object $db_object Database row object
	 * @return null            
	 */
	private function _init($db_object)
	{
		$this->site_data = Site_data::get_instance();


		$this->_db_object = $db_object;
		$this->_id = $db_object->id;

		//Unpackage settings
		$this->_db_object->settings = $this->site_data->decode_settings_payload($this->_db_object->settings);

		$this->api_url = $this->base_url().$this->setting("index_page")."?ACT=".$this->setting("action_id");
	}

	/**
	 * Fetch Site DB Record
	 * @param  string $id The DB row ID of the site
	 * @return object     DB Record
	 */
	private function _get($id='')
	{
		//Exists in cache?
		if(isset(Site::$_db_cache[$id])){
			return Site::$_db_cache[$id];
		}


		$this->db->where("id", $id);
		$q = $this->db->get($this->db_name);

		if($q->num_rows() == 0){
			throw new Exception("Site DB record does not exist: ".$id);
			return;
		}

		$row = $q->row();

		//Cache the result for future
		Site::$_db_cache[$row->id] = $row;

		return $row;

	}


}