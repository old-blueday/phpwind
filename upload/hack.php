<?php
define('SCR','hack');
if(isset($_GET['action']) && $_GET['action'] == 'ajax'){
	define('AJAX','1');
}
require_once('global.php');

S::gp(array('H_name'));
if (preg_match('/^http/i',$H_name)) {
	Showmsg($H_name);
} elseif(!$db_hackdb[$H_name] || !is_dir(R_P.'hack/'.$H_name) || !file_exists(R_P."hack/$H_name/index.php")){
	Showmsg('hack_error');
}
define('H_P',R_P."hack/$H_name/");
$basename	= "hack.php?H_name=$H_name";
$hkimg		= "hack/$H_name/image";
$webPageTitle = strip_tags($db_hackdb[$H_name][0]).' ';

if (!defined('AJAX')) {
	require_once(R_P.'require/header.php');
}
require_once(H_P.'index.php');

function PrintHack($template,$EXT="htm"){
	return H_P."template/".$template.".$EXT";
}
?>