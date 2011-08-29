<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = $basename;

if (!$_POST['step']) {
	
	ifcheck($db_msgsound,'db_msgsound');
	ifcheck($db_msgreplynotice,'db_msgreplynotice');
	
} else {
	
	S::gp(array('config'));
	$names = array_keys($config);

	if ($_POST['step'] == 3) {
		if($db_history && !$config['db_history']){
			adminmsg('你不能清空已经设置的历史时间');
		}
		if($config['db_history'] && ($setHistory = PwStrtoTime($config['db_history']))){
			if( $db_history && $setHistory < PwStrtoTime($db_history)){
				adminmsg('你设置的历史时间不能小于当前历史时间');
			}
			@set_time_limit(0);
			$messageServer = L::loadClass('message', 'message');
			$messageServer->setHistorys($setHistory);
		}
	}

	$query = $db->query('SELECT db_name,vtype,db_value FROM pw_config WHERE db_name IN ('.S::sqlImplode($names,false).')');
	while ($rt = $db->fetch_array($query)) {
		if (isset($config[$rt['db_name']])) {
			if ($rt['db_value'] != $config[$rt['db_name']]) {
				$db->update("UPDATE pw_config SET " . S::sqlSingle(array('db_value' => $config[$rt['db_name']], 'vtype' => 'string')) . ' WHERE db_name=' . S::sqlEscape($rt['db_name']));
			}
			$config[$rt['db_name']] = '';
		}
	}
	$db->free_result($query);
	foreach($config as $key => $value){
		if($config[$key]){
			$config[$key] = array();
			$config[$key]['name'] = $key;
			$config[$key]['type'] = 'string';
			$config[$key]['value']=	$value;
		}
	}
	$pwSqlMulti = S::sqlMulti($config);
	$pwSqlMulti && $db->update('INSERT INTO pw_config (db_name,vtype,db_value) VALUES' . $pwSqlMulti);
	updatecache_c();
	adminmsg('operate_success');
}

require_once PrintEot('messageset');
?>