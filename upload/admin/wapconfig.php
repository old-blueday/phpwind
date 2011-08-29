<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=wapconfig";
if (!$_POST['step']) {
	$showforums = ''; $num = 0;
	$query = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND allowvisit='' AND f_type!='hidden' AND cms='0'");
	while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
		$num++;
		$htm_tr = $num%2==0 ? '' : '';
		$forumscked = strpos(','.$db_wapfids.',',','.$rt[0].',')!==false ? 'CHECKED' : '';
		$forumName = trim(preg_replace('([\s| ]+)', ' ', $rt[1]));
		$showforums .= '<li><input type="checkbox" name="wapfids[]" value="'.$rt[0].'" '.$forumscked.'>'. $forumName .'</li>'.$htm_tr;
	}
	$showforums && $showforums = '<ul class="list_A list_120 cc">'.$showforums.'</ul>';

	$db_waplimit = (int)$db_waplimit;
	ifcheck($db_wapifopen,'wapifopen');
	ifcheck($db_wapcharset,'wapcharset');
//	ifcheck($db_waprecord,'waprecord');
	ifcheck($db_wapregist,'wapregist');
	ifcheck($db_waphottopicweek,'waphottopicweek');
	ifcheck($db_waphottopictoday,'waphottopictoday');
	ifcheck($db_waprecommend,'waprecommend');
	ifcheck($db_wapifathumb,'wapifathumb');
	ifcheck($db_wapifathumbgif,'wapifathumbgif');
	list($db_wapathumbwidth, $db_wapathumbheight) = explode("\t", $db_wapathumbsize);
	include PrintEot('wapconfig');exit;
} else {
	
	InitGP(array('config','wapathumbsize','wapfids'));
	$config['wapathumbsize'] = $wapathumbsize['wapathumbwidth'] . "\t" . $wapathumbsize['wapathumbheight'];
	$config['wapfids'] = implode(',',$wapfids);
	$configdb = array();
	$temppre = array('config' => 'db_');
	foreach ($config as $k => $value) {
		$var = 'db_' . $k;
		$vtype = 'string';
		if (is_array($value)) {
			$vtype = 'array';
			$value = serialize($value);
		}
		$configdb[$var] = array($var, $vtype, $value);
	}
	if (!$configdb) {
		adminmsg('undefine_action');
	}
	$names = array_keys($configdb);
	$query = $db->query('SELECT db_name,vtype,db_value FROM pw_config WHERE db_name IN ('.pwImplode($names,false).')');
	while ($rt = $db->fetch_array($query)) {
		if (isset($configdb[$rt['db_name']])) {
			if ($rt['db_value'] != $configdb[$rt['db_name']]) {
				$db->update("UPDATE pw_config SET " . pwSqlSingle(array('db_value' => $configdb[$rt['db_name']][2], 'vtype' => $configdb[$rt['db_name']][1])) . ' WHERE db_name=' . pwEscape($rt['db_name']));
			}
			$configdb[$rt['db_name']] = '';
		}
	}
	$db->free_result($query);
	$pwSqlMulti = pwSqlMulti($configdb);
	$pwSqlMulti && $db->update('INSERT INTO pw_config (db_name,vtype,db_value) VALUES' . $pwSqlMulti);
	updatecache_c();
	adminmsg('operate_success');
}
?>