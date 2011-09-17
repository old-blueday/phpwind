<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=sitemap";

if(!$action){
	//* @include_once pwCache::getPath(D_P.'data/bbscache/sm_config.php');
	pwCache::getData(D_P.'data/bbscache/sm_config.php');
	include PrintEot('sitemap');exit;
} elseif($action == 'create'){
	p_unlink(D_P.'sitemap.xml');
	adminmsg('operate_success');
} elseif($_POST['action'] == 'baidu'){
	S::gp(array('config'));
	foreach($config as $key=>$value){
		$hk_name = 'sm_'.$key;
		$db->pw_update(
			"SELECT hk_name FROM pw_hack WHERE hk_name=".S::sqlEscape($hk_name),
			"UPDATE pw_hack SET hk_value=".S::sqlEscape($value)."WHERE hk_name=".S::sqlEscape($hk_name),
			"INSERT INTO pw_hack SET hk_name=".S::sqlEscape($hk_name).",hk_value=".S::sqlEscape($value)
		);
	}
	updatecache_sm();
	adminmsg('operate_success');
}
function updatecache_sm() {
	global $db;
	$hk_name = $hk_value = '';
	$query    = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'sm_%'");
	$configdb = "<?php\r\n";
	while (@extract($db->fetch_array($query))) {
		$hk_name = key_cv($hk_name);
		$configdb.="\$$hk_name=".pw_var_export($hk_value).";\r\n";
	}
	$configdb.="?>";
	pwCache::setData(D_P.'data/bbscache/sm_config.php',$configdb);
}
?>