<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$e =& get_instance();
$e->load->file(APPPATH."libraries/Cp.php");

/**
* Mock CP Library
*
* @author  Christopher Imrie
*/
class Mock_cp extends Cp
{
	var $EE;

	function __construct()
	{
		$this->EE =& get_instance();

		//Purposefully not calling CP __construct otherwise since it throws
		//a hissy fit if not running from within the CP
	}

	public function set_breadcrumb($a="", $b=""){}
	public function allowed_group($a="", $b="", $c=""){ return TRUE;}

}