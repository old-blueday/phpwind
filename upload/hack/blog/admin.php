<?php
!function_exists('adminmsg') && exit('Forbidden');
//* include_once pwCache::getPath(D_P."data/bbscache/bg_config.php");
pwCache::getData(D_P."data/bbscache/bg_config.php");

if (!$action){
	ifcheck($bg_ifopen,'ifopen');
	foreach($ltitle as $key=>$value){
		if($key != 1 && $key != 2){
			$checked = '';
			if(strpos($bg_groups,','.$key.',') !== false){
				$checked = 'checked';
			}
			$num++;
			$htm_tr = $num%4 == 0 ?  '</tr><tr>' : '';
			$usergroup .=" <td width='20%'><input type='checkbox' name='groups[]' value='$key' $checked>$value</td>$htm_tr";
		}
	}
	include PrintHack('admin');exit;
}else{
	S::gp(array('config','groups'));
	if (is_array($groups)){
		$config['bg_groups'] = ','.implode(',',$groups).',';
	} else {
		$config['bg_groups'] = '';
	}
	foreach($config as $key => $value){
		$db->pw_update(
			"SELECT hk_name FROM pw_hack WHERE hk_name=".S::sqlEscape($key),
			"UPDATE pw_hack SET hk_value=".S::sqlEscape($value)."WHERE hk_name=".S::sqlEscape($key),
			"INSERT INTO pw_hack SET hk_name=".S::sqlEscape($key).",hk_value=".S::sqlEscape($value)
		);
	}
	updatecache_bg();
	adminmsg("operate_success");
}

function updatecache_bg(){
	global $db;
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'bg_%'");
	$blogdb = "<?php\r\n";
	while (@extract($db->fetch_array($query))) {
		$hk_name = key_cv($hk_name);
		$blogdb .= "\$$hk_name=".pw_var_export($hk_value).";\r\n";
	}
	$blogdb .= "\n?>";
	pwCache::setData(D_P.'data/bbscache/bg_config.php', $blogdb);
}
?>