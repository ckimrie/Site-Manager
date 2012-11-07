<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$e =& get_instance();
$e->load->file(PATH_THIRD."site_manager_client/helpers/phpseclib/Crypt/AES.php");

function decrypt_payload($val,$key)
{
    $aes = new Crypt_AES();
    $aes->setKey($key);
    return $aes->decrypt($val);
}


function encrypt_payload($val,$key)
{
    $aes = new Crypt_AES();
    $aes->setKey($key);
    return $aes->encrypt($val);
}