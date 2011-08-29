<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=urlcheck";

if ($action != "unsubmit") {

	$urlcheck=$db->get_one("SELECT db_value FROM pw_config WHERE db_name='db_urlcheck'");
	$urlinfo=str_replace(",","\n",$urlcheck['db_value']);
	$urlcheck=$db->get_one("SELECT db_value FROM pw_config WHERE db_name='db_urlblacklist'");
	$urlblackinfo=str_replace(",","\n",$urlcheck['db_value']);
	include PrintEot('urlcheck');exit;

} elseif ($_POST['action'] == "unsubmit") {
	S::gp(array('urlinfo', 'ischeckurl', 'urlchecklimit', 'urlcheckstrategy', 'urlblackinfo', 'blurlcheckstrategy'),'P');
	
	$urlinfo = urlcheck_collectUrls($urlinfo);
	setConfig('db_urlcheck', $urlinfo);
	
	$ischeckurl = intval($ischeckurl);
	$windpost = $db->get_one("SELECT db_value FROM pw_config WHERE db_name='db_windpost'");
	$windpost = unserialize($windpost['db_value']);
	$windpost['checkurl'] = $ischeckurl;
	setConfig('db_windpost', $windpost);
	
	$urlchecklimit = intval($urlchecklimit);
	setConfig('db_urlchecklimit', $urlchecklimit < 0 ? 0 : $urlchecklimit);
	
	setConfig('db_urlcheckstrategy', $urlcheckstrategy);
	setConfig('db_blurlcheckstrategy', $blurlcheckstrategy);
	
	$urlblackinfo = urlcheck_collectUrls($urlblackinfo);
	setConfig('db_urlblacklist', $urlblackinfo);
	
	updatecache_c();
	adminmsg('operate_success');
}

function urlcheck_collectUrls($urlConfigString) {
	$urlConfigString = strtolower($urlConfigString);
	$urls = array();
	foreach (explode("\n", $urlConfigString) as $url) {
		$url = trim($url);
		if (false !== strpos($url, ",")) {
			foreach (explode(",",$url) as $v) {
				$v = trim($v);
				$urls[$v] = $v;
			}
		} else {
			$urls[$url] = $url;
		}
	}
	unset($urls['']);
	return implode(",", $urls);
}
?>