<?php

error_reporting(0);
define('P_W','admincp');
define('R_P',strpos(__FILE__, DIRECTORY_SEPARATOR) !== FALSE ? substr(__FILE__, 0, strrpos(__FILE__,DIRECTORY_SEPARATOR)).'/' : './');
define('D_P',R_P);
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');

require_once(R_P.'require/common.php');
S::filter();

//* require_once pwCache::getPath(D_P.'data/bbscache/config.php');
require_once (D_P.'data/sql_config.php');
pwCache::getData(D_P.'data/bbscache/config.php');
$timestamp = time();
$db_cvtime!=0 && $timestamp += $db_cvtime*60;
$onlineip = pwGetIp();

require_once(R_P.'api/class_base.php');
if ($database == 'mysqli' && Pwloaddl('mysqli') === false) {
	$database = 'mysql';
}
require_once S::escapePath(R_P."require/db_$database.php");

$dirstrpos = strpos($pwServer['PHP_SELF'],$db_dir);
if ($dirstrpos !== false) {
	$tmp = substr($pwServer['PHP_SELF'],0,$dirstrpos);
	$pwServer['PHP_SELF'] = "$tmp.php";
} else {
	$tmp = $pwServer['PHP_SELF'];
}
$db_bbsurl = S::escapeChar("http://".$pwServer['HTTP_HOST'].substr($tmp,0,strrpos($tmp,'/')));

if ($db_http != 'N') {
	$imgpath = $db_http;
} else {
	$imgpath = $db_bbsurl . '/' . $db_picpath;
}
$attachpath = $db_attachurl != 'N' ? $db_attachurl : $db_bbsurl . '/' . $db_attachname;
$imgdir		= R_P.$db_picpath;
$attachdir	= R_P.$db_attachname;
$pw_posts   = 'pw_posts';
$pw_tmsgs   = 'pw_tmsgs';
$tdtime		= PwStrtoTime(get_date($timestamp,'Y-m-d'));
$montime	= PwStrtoTime(get_date($timestamp,'Y-m').'-1');

$db  = new DB($dbhost,$dbuser,$dbpw,$dbname,$PW,$charset,$pconnect);

$api = new api_client();
$response = $api->run($_POST + $_GET);

if ($response) {
	echo $api->dataFormat($response);
}

function GetLang($lang,$EXT='php'){//No use
	return R_P."template/wind/lang_$lang.$EXT";
}
function Pwloaddl($module, $checkFunction = 'mysqli_get_client_info') {
	return extension_loaded($module) && $checkFunction && function_exists($checkFunction) ? true : false;
}
?>