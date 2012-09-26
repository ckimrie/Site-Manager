<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* Site_Collection
*/
class Site_Collection implements Iterator
{
	private $EE;

	//Config
	var $db_name = "site_manager_sites";


	//Runtime vars
	private $_index = 0;
	private $_sites = array();
	private $_key_map = array();
	private $_db_cache = array();
	
	function __construct()
	{
		$this->EE =& get_instance();
	}


	public function add($id, $db_row = FALSE)
	{	
		try {
			$s = new Site($id, $db_row);	
		} catch (Exception $e) {
			show_error($e->getMessage());
		}

		//Add to local collection
		$this->_sites[] = $s;

		//Update the keymap
		$this->_key_map[$id] = count($this->_sites) -1;
	}



	public function get($site_id='')
	{
		//Has this ID been cached yet?
		if(!isset($this->_key_map[$site_id])) return FALSE;

		return $this->_sites[$this->_key_map[$site_id]];
	}



	/**
	 * Iterator Methods
	 */

	public function current ( ) {
		return $this->_sites[$this->_index];
	}
	public function key ( ) {
		return $this->_index;
	}
	public function next ( ) {
		$this->_index++;
	}
	public function rewind ( ) {
		$this->_index = 0;
	}
	public function valid ( ) {
		return isset($this->_sites[$this->_index]);
	}


	
}