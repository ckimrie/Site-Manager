<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');



/**
* Mock Session Library
*
* @author  Christopher Imrie
*/
class Mock_session
{
	public function set_flashdata($a="", $b=""){}
	public function userdata($key='')
	{
		if($key == "group_id") {
			return 1;
		}
	}
}