<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Site Data Model
 *
 * Data model for interacting with site data stored within the EE DB
 *
 * @author  Christopher Imrie
 */
class Site_data extends CI_Model
{
	var $EE;

	//Config
	private $_db_name = "site_manager_sites";

	//Runtime Vars
	private $_sites;

	//Static
	static private $_instance;


	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/remote_object.php");
		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/site.php");
		$this->EE->load->file(PATH_THIRD."site_manager_client/classes/site_collection.php");

		$this->init();
	}


	/**
	 * Initialise Class
	 *
	 * @author Christopher Imrie
	 *
	 * @return null
	 */
	public function init()
	{
		//Initialise
		Site_data::$_instance =& $this;

		//Fetch installed sites
		$this->get_all();
	}


	/**
	 * Get class instance
	 *
	 * @author Christopher Imrie
	 *
	 * @return object
	 */
	static function get_instance()
	{
		return Site_data::$_instance;
	}


	/**
	 * Create a new remote site
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $post  Site settings
	 * @return integer            Newly created site id
	 */
	public function newSite($post=array())
	{
		$c =& CI_Controller::get_instance();

		//Sanitize everything first
		foreach ($post as $key => $row) {
			$post[$key] = $c->security->xss_clean($row);
		}


		$site_name = $post['site_name'];
		$base_url = $post['base_url'];
		unset($post['site_name']);
		unset($post['base_url']);

		$data = array(
			"site_name" => $site_name,
			"base_url" => $base_url,
			"settings" 	=> $this->encode_settings_payload($post),
			"added_by"	=> $c->session->userdata("member_id"),
			"date_added" => $this->EE->localize->now,
		);

		$this->EE->db->insert($this->_db_name, $data);


		return $this->EE->db->insert_id();
	}


	/**
	 * Update a local site
	 *
	 * @author Christopher Imrie
	 *
	 * @param  integer      $site_id The ID of the site to be updated
	 * @param  array       $post     Data to update the site with
	 * @return integer               The ID of the site updated
	 */
	public function updateSite($site_id='', $post=array())
	{
		$c =& CI_Controller::get_instance();

		//Sanitize everything first
		foreach ($post as $key => $row) {
			$post[$key] = $c->security->xss_clean($row);
		}


		$site_name = $post['site_name'];
		$base_url = $post['base_url'];
		unset($post['site_name']);
		unset($post['base_url']);

		$data = array(
			"site_name" => $site_name,
			"base_url" => $base_url,
			"settings" 	=> $this->encode_settings_payload($post)
		);

		$this->EE->db->where("id", $site_id);
		$this->EE->db->update($this->_db_name, $data);


		return $site_id;
	}


	/**
	 * Get all sites
	 *
	 * Instead of returning an array of sites, we return a SiteCollection
	 * instance. This iterable class has various convenience methods
	 * for working with multiple sites at a time.
	 *
	 * @author Christopher Imrie
	 *
	 * @return object      SiteCollection instance
	 */
	public function get_all()
	{
		//Fetch installed sites
		$q = $this->EE->db->get($this->_db_name)->result();

		$sites = new Site_Collection();

		foreach ($q as $key => $row) {
			$sites->add($row->id, $row);
		}

		//Cache installed sites
		$this->_sites = $sites;


		return $this->_sites;
	}


	/**
	 * Get a single site
	 *
	 * @author Christopher Imrie
	 *
	 * @param  integer      $site_id
	 * @return object                Site instance
	 */
	public function get($site_id)
	{
		$site = $this->_sites->get($site_id);
		if($site) return $site;

		//Fetch installed sites
		$q = $this->EE->db->get_where($this->_db_name, array("id" => $site_id))->result();

		foreach ($q as $key => $row) {
			$this->_sites->add($row->id, $row);
		}

		return $this->_sites->get($site_id);
	}


	/**
	 * Delete a site
	 *
	 * @author Christopher Imrie
	 *
	 * @param  integer      $site_id
	 * @return null
	 */
	public function delete_site($site_id='')
	{
		if(!$site_id) return;

		$this->EE->db->where("id", $site_id);
		$this->EE->db->delete("site_manager_sites");
	}


	/**
	 * Verify settings payload integrity
	 *
	 * Simply decodes the settings payload that has been provided and
	 * checks that it decodes into an array (meaning it is not corrupt)
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $str  Encoded settings payload
	 * @return bool
	 */
	public function verify_settings_payload($str='')
	{
		$a = $this->decode_settings_payload($str);
		return is_array($a) && count($a) > 0;
	}


	/**
	 * Decode settings payload
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $str  Encoded settings payload
	 * @return mixed             Array if valid payload, FALSE if not
	 */
	public function decode_settings_payload($str='')
	{
		return @unserialize(base64_decode($str));
	}


	/**
	 * Encode settings payload
	 *
	 * @author Christopher Imrie
	 *
	 * @param  array       $data Data to encode
	 * @return string
	 */
	public function encode_settings_payload($data=array())
	{
		return base64_encode(serialize($data));
	}

}