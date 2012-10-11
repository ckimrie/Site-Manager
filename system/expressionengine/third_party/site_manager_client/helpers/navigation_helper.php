<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function methodUrl($method = "", $get=array()) {

	$str = str_replace("&amp;", "&", BASE . "&C=addons_modules&M=show_module_cp&module=site_manager_client&method=".$method);

	foreach ($get as $key => $value) {
		$str .= "&".$key."=".urlencode($value);
	}

	return $str;
}


function redirectToMethod($method='', $get = array())
{
	header("Location: ".methodUrl($method, $get));
	exit;
}