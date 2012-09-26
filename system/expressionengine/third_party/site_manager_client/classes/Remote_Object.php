<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



/**
* 
*/
class Remote_Object
{
	var $EE;

	private $curl;

	protected $api_url;


	public function __construct()
	{
		$this->EE =& get_instance();

	}


	public function ping()
	{
		$this->new_connection();

		$a = $this->curl->execute();

		return $this->curl->info;
	}



	private function new_connection()
	{
		if(!$this->curl) {
			$this->EE->load->library("curl");
			$this->curl = new Curl($this->api_url);
		} else {
			$this->curl->create($this->api_url);
		}
	}



}