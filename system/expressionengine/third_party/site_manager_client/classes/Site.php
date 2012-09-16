<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* 
*/
class Site extends Remote_Object
{
	var $EE;


	//Runtime vars
	protected 	$_id;
	protected	$_db_object;


	//Class Vars
	static 		$_db_cache = array();

	function __construct($id, $db_object=FALSE)
	{
		$this->EE =& get_instance();


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
		return $this->_db_object->name;
	}




	/**
	 * Initialise the class
	 * @param  object $db_object Database row object
	 * @return null            
	 */
	private function _init($db_object)
	{
		$this->_db_object = $db_object;
		$this->_id = $db_object->id;
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