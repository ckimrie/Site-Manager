<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function methodUrl($method = "") {
	
	return str_replace("&amp;", "&", BASE . "&C=addons_modules&M=show_module_cp&module=site_manager_client&method=".$method);
}


function redirectToMethod($method='')
{
	header("Location: ".methodUrl($method));
	exit;
}