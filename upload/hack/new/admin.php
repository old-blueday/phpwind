<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$amind_file?adminjob=hack&hackset=new&id=new";

if($action){
	$code = S::getGP('code');
	$code  = str_replace('EOT','',$code);
	$code1 = htmlspecialchars(stripslashes($code));
	$code2 = stripslashes($code);
}
include PrintHack('admin');exit;
?>