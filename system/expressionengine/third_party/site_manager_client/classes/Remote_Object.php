<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



/**
* 
*/
class Remote_Object
{
	var $EE;

	protected $curl;

	protected $api_url;
	protected $site_id = 1;


	public function __construct()
	{
		$this->EE =& get_instance();

	}


	public function ping()
	{
		$this->new_connection("ping");

		$a = json_decode($this->curl->execute(), TRUE);
		$a['total_time'] = $this->curl->info['total_time'];
		$a['http_code'] = $this->curl->info['http_code'];

		return $a;
	}

	public function ping_url()
	{
		return $this->buildUrl("ping");
	}



	protected function new_connection($method="", $fragments=array())
	{
		if(!$method) {
			throw new Exception("Remote method not specified");
		}

		if(!$this->curl) {
			$this->EE->load->library("curl");
			$this->curl = new Curl($this->buildUrl($method, $fragments));
		} else {
			$this->curl->create($this->buildUrl($method, $fragments));
		}
	}


	protected function buildUrl($method="", $fragments=array())
	{
		$url = $this->api_url;

		if($method) {
			$url .= "&method=".$method;
		}

		
		$url .= "&site_id=".$this->site_id;

		foreach ($fragments as $key => $value) {
			$url .= "&".$key."=".urlencode($value);
		}
		return $url;
	}



}