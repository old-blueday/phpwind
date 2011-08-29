<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=optimize";

if (!$action) {

	${'size_'.$db_size} = 'checked';
	${'func_'.$db_func} = 'checked';
	include PrintEot('optimize');exit;

} elseif ($action == 'size_detail') {

	if (!$_POST['step']) {

		if (file_exists(D_P.'data/bbscache/optimize_size.php')) {
			//* include pwCache::getPath(D_P.'data/bbscache/optimize_size.php');
			pwCache::getData(D_P.'data/bbscache/optimize_size.php');
			!$optimize_conf['size'][$type] && include(R_P.'admin/optimize_conf.php');
		} else {
			include(R_P.'admin/optimize_conf.php');
		}
		@extract($optimize_conf['size'][$type]);
		$db_hithour && $hithour_sel[$db_hithour]='selected';
		${'ads_'.$db_ads}='checked';
		$safegroup = "<ul class=\"list_A list_120 cc\">";
		$db_onlinetime = $db_onlinetime/60;
		foreach($ltitle as $key => $value){
			if($key==1||$key==2)continue;
			$num++;
			$htm_tr = $num % 3 == 0 ? '' : '';
			if(strpos($db_safegroup,",$key,")!==false){
				$s_checked = 'checked';
			} else {
				$s_checked = '';
			}
			$safegroup .= "<li><input type='checkbox' name='safegroup[]' value='$key' $s_checked>$value</li>$htm_tr";
		}
		$safegroup .= "</ul>";
		list($db_opensch,$db_schstart,$db_schend)=explode("\t",$db_opensch);
		ifcheck($db_opensch,'opensch');
		ifcheck($db_lp,'lp');
		ifcheck($db_obstart_tmp,'obstart');
		ifcheck($db_ifonlinetime,'ifonlinetime');
		ifcheck($db_ifselfshare,'ifselfshare');
		ifcheck($db_indexshowbirth,'indexshowbirth');
		ifcheck($db_indexonline,'indexonline');
		ifcheck($db_showguest,'showguest');
		ifcheck($db_today,'today');;
		ifcheck($db_threadonline,'threadonline');
		ifcheck($db_showcolony,'showcolony');
	//	ifcheck($db_iffthumb,'iffthumb');
		ifcheck($db_wapifopen,'wapifopen');
		ifcheck($db_jsifopen,'jsifopen');
		ifcheck($db_ifsafecv,'ifsafecv');
		ifcheck($db_htmifopen,'htmifopen');
		ifcheck($db_ipstates,'ipstates');
		ifcheck($db_ads,'ads');
		ifcheck($db_watermark,'watermark');
		$db_watermark == 1 ? $watermark_1 = 'checked' : ($db_watermark == 2 ? $watermark_2 = 'checked' : $watermark_0 = 'checked');
		include PrintEot('optimize');exit;

	} elseif ($_POST['recovery']) {

		include(R_P.'admin/optimize_conf.php');
		$configdb = array();
		$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%'");
		while ($rt = $db->fetch_array($query)) {
			$configdb[$rt['db_name']] = $rt['db_value'];
		}
		foreach ($optimize_conf['size'][$type] as $key => $value) {
			$c_key = $$key;
			if ($c_key != $value || $configdb[$key] != $value) {
				setConfig($key, $value);
			}
		}
		updatecache_c();
		updateoptimize($optimize_conf['size'][$type],$type,'size');
		adminmsg('operate_success');

	} else {

		S::gp(array('config','safegroup','schcontrol', 'showcustom'),'P');

		$config['opensch'] = $schcontrol['opensch']."\t".$schcontrol['schstart']."\t".$schcontrol['schend'];
		if ($safegroup) {
			$config['safegroup'] = ",".implode(",",$safegroup).",";
		} else{
			$config['safegroup'] = '';
		}
		$config['onlinetime'] *= 60;
		$config['size'] = $type;

		$configdb = array();
		$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%'");
		while ($rt = $db->fetch_array($query)){
			$configdb[$rt['db_name']] = $rt['db_value'];
		}
		foreach ($config as $key => $value) {
			$c_key = ${'db_'.$key};
			if ($c_key != $value || $configdb["db_$key"] != $value) {
				setConfig("db_$key", $value);
			}
		}
		$config['showcustom'] = $showcustom ? (array) $showcustom : array();
		setConfig("db_showcustom", $config['showcustom']);
		updatecache_c();
		updateoptimize($config,$type,'size');
		adminmsg('operate_success');
	}
} elseif ($action == 'func_detail') {

	if (!$_POST['step']) {

		if (file_exists(D_P.'data/bbscache/optimize_func.php')) {
			//* include pwCache::getPath(D_P.'data/bbscache/optimize_func.php');
			pwCache::getData(D_P.'data/bbscache/optimize_func.php');
			!$optimize_conf['func'][$type] && include(R_P.'admin/optimize_conf.php');
		} else {
			include(R_P.'admin/optimize_conf.php');
		}
		@extract($optimize_conf['func'][$type]);
		${'columns_'.$db_columns} = 'checked';
		list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize) = explode("\t",$db_upload);
		$db_imgsize = ceil($db_imgsize/1024);

		ifcheck($db_upload,'upload');
		ifcheck($db_msgsound,'msgsound');
		ifcheck($db_shield,'shield');
		ifcheck($db_tcheck,'tcheck');
		ifcheck($db_adminset,'adminset');
		ifcheck($db_allowupload,'allowupload');
		ifcheck($db_replysendmail,'replysendmail');
		ifcheck($db_replysitemail,'replysitemail');
		ifcheck($db_pwcode,'pwcode');
		ifcheck($db_setform,'setform');
		ifcheck($db_autoimg,'autoimg');
		ifcheck($db_menu,'menu');
	//	ifcheck($db_iffthumb,'iffthumb');
		ifcheck($db_ifathumb,'ifathumb');
		ifcheck($db_ifselfshare,'ifselfshare');
		$db_watermark == 1 ? $watermark_1 = 'checked' : ($db_watermark == 2 ? $watermark_2 = 'checked' : $watermark_0 = 'checked');
		include PrintEot('optimize');exit;

	} elseif ($_POST['recovery']) {

		include(R_P.'admin/optimize_conf.php');
		$configdb = array();
		$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%'");
		while ($rt = $db->fetch_array($query)) {
			$configdb[$rt['db_name']] = $rt['db_value'];
		}
		foreach ($optimize_conf['func'][$type] as $key => $value) {
			$c_key = $$key;
			if ($c_key != $value || $configdb[$key] != $value) {
				setConfig($key, $value);
			}
		}
		updatecache_c();
		updateoptimize($optimize_conf['func'][$type],$type,'func');
		adminmsg('operate_success');

	} elseif ($_POST['step'] == '2') {

		S::gp(array('config','upload'),'P');
		$upload['imgsize']=!is_numeric($upload['imgsize']) ? 20480 : $upload['imgsize']*1024;
		!is_numeric($config['imglen'])   && $config['imglen']=200;
		!is_numeric($config['imgwidth']) && $config['imgwidth']=180;
		$config['upload']="$upload[upload]\t$upload[imglen]\t$upload[imgwidth]\t$upload[imgsize]";

		$config['func'] = $type;

		$configdb = array();
		$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%'");
		while ($rt = $db->fetch_array($query)) {
			$configdb[$rt['db_name']] = $rt['db_value'];
		}
		foreach ($config as $key => $value) {
			$c_key = ${'db_'.$key};
			if ($c_key != $value || $configdb["db_$key"] != $value) {
				setConfig('db_'.$key, $value);
			}
		}
		updatecache_c();
		updateoptimize($config,$type,'func');
		adminmsg('operate_success');
	}
} elseif ($_POST['action'] == 'size') {

	if (file_exists(D_P.'data/bbscache/optimize_size.php')) {
		//* include pwCache::getPath(D_P.'data/bbscache/optimize_size.php');
		pwCache::getData(D_P.'data/bbscache/optimize_size.php');
		!$optimize_conf['size'][$type] && include(R_P.'admin/optimize_conf.php');
	} else {
		include(R_P.'admin/optimize_conf.php');
	}
	$configdb=array();
	$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%'");
	while ($rt = $db->fetch_array($query)){
		$configdb[$rt['db_name']]=$rt['db_value'];
	}
	foreach ($optimize_conf['size'][$type] as $key => $value) {
		$c_key = $$key;
		if ($c_key != $value || $configdb[$key] != $value) {
			setConfig($key, $value);
		}
	}
	updatecache_c();
	adminmsg('operate_success');

} elseif ($_POST['action'] == 'func') {

	if (file_exists(D_P.'data/bbscache/optimize_func.php')) {
		//* include pwCache::getPath(D_P.'data/bbscache/optimize_func.php');
		pwCache::getData(D_P.'data/bbscache/optimize_func.php');
		!$optimize_conf['func'][$type] && include(R_P.'admin/optimize_conf.php');
	} else {
		include(R_P.'admin/optimize_conf.php');
	}
	$configdb=array();
	$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%'");
	while ($rt = $db->fetch_array($query)){
		$configdb[$rt['db_name']]=$rt['db_value'];
	}
	foreach ($optimize_conf['func'][$type] as $key => $value) {
		$c_key = $$key;
		if ($c_key != $value || $configdb[$key] != $value){
			setConfig($key, $value);
		}
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>