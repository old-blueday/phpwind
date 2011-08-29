<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=ptable";
$db_tlist = (array)$db_tlist;
if (!$action) {

	if (!$_POST['step']) {

		$tmsgdb  = $postdb = array();
		$tlistdb = $db_tlist;
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_tmsgs%'");
		while($rs=$db->fetch_array($query)){
			if($GLOBALS['PW']){
				$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			}else{
				$key = substr($rs['Name'],5);
			}
			if ($key && !is_numeric($key)) continue;
			$pw_tmsgs = 'pw_tmsgs'.$key;
			@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_tmsgs"));
			$rs['tmin'] = $tmin;
			$rs['tmax'] = $tmax;
			list($rs['tidmin'],$rs['tidmax'])=maxmin($key);
			$rs['Data_length'] = round(($rs['Data_length']+$rs['Index_length'])/1048576,2);
			$tmsgdb[$key] = $rs;
		}

		$plistdb = $db_plist ? $db_plist : array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rs=$db->fetch_array($query)){
			if($GLOBALS['PW']){
				$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			}else{
				$key = substr($rs['Name'],5);
			}
			if ($key && !is_numeric($key)) continue;
			$rs['sel'] = $key==$db_ptable ? 'checked' : '';
			$pw_posts  = GetPtable($key);
			@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_posts"));
			$rs['tmin'] = $tmin;
			$rs['tmax'] = $tmax;
			$rs['Data_length'] = round(($rs['Data_length']+$rs['Index_length'])/1048576,2);
			count($plistdb) > 1 && $rs['name'] = $plistdb[$key];
			$postdb[$key] = $rs;
		}
		require_once PrintEot('ptable');

	} elseif ($_POST['step'] == '3') {

		S::gp(array('ttable'),'P');
		if (is_array($ttable)) {
			$ttable = arraySort($ttable,1);
			foreach ($ttable as $key => $value) {
				$key != 0 && !is_numeric($value[1]) && adminmsg('numerics_checkfailed');
			}
		} else {
			$ttable = '';
		}
		setConfig('db_tlist', $ttable);
		updatecache_c();
		adminmsg('operate_success');

	} elseif ($_POST['step'] == '5') {

		S::gp(array('ktable','plistdb'),'P');
		setConfig('db_ptable', $ktable);

		$plist = array();
		$plist[0] = $plistdb[0];
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while ($rs = $db->fetch_array($query)) {
			$j = str_replace($PW.'posts','',$rs['Name']);
			if ($j && !is_numeric($j)) continue;
			if ($j) {
				$plist[$j] = $plistdb[$j] ? $plistdb[$j] : '';
			}
		}
		$plist = is_array($plist) && (count($plist) > 1) ? $plist : '';
		setConfig('db_plist', $plist);
		updatecache_c();
		adminmsg('operate_success');
	}
} elseif ($action == 'create') {

	$num_a = array();
	if ($type == 1) {
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_tmsgs%'");
		while ($rs = $db->fetch_array($query)) {
			$j = str_replace($PW.'tmsgs','',$rs['Name']);
			$num_a[] = (int)$j;
		}
		$num = max($num_a)+1;
		$table = 'pw_tmsgs'.$num;
		$CreatTable = $db->get_one("SHOW CREATE TABLE pw_tmsgs");
		$sql = str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$db->query($sql);
		if ($db_tlist) {
			$tlistdb = $db_tlist;
			$current_tlist = current($tlistdb);
			$tidmax = $current_tlist[1];
		} else {
			$tlistdb = array();
			$tidmax  = 0;
		}
		$tlistdb = (array)$tlistdb;
		@extract($db->get_one("SELECT MAX(tid) AS tid FROM pw_threads"));
		$tidmax = max($tidmax,$tid);
		$tlistdb[$num] = array(1=>($tidmax + 100),2=>'');
		if (count($tlistdb) == 1) {
			$tlistdb[0] = array(1=>'',2=>'');
		}
		$tlistdb = arraySort($tlistdb,1);
		$db_tlist = $tlistdb;
		setConfig('db_tlist', $db_tlist);
	} else {
		$i = 0;
		$plistdb = is_array($db_plist) ? $db_plist : array();
		$plistdb['0'] = $plistdb['0'] ? $plistdb['0'] : '';
		$plist = array(0=>$plistdb['0']);
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while ($rs = $db->fetch_array($query)) {
			$j = str_replace($PW.'posts','',$rs['Name']);
			if ($j && !is_numeric($j)) continue;
			$i++;
			if ($j) {
				$plist[$j] = $plistdb[$j] ? $plistdb[$j] : '';
			}
			$num_a[]=$j;
		}
		if ($i == 1) {
			extract($db->get_one("SELECT MAX(pid) AS pid FROM pw_posts"));
			$db->update("REPLACE INTO pw_pidtmp SET pid=".S::sqlEscape($pid,false));
			$num = 1;
		} else{
			$num = max($num_a)+1;
		}
		$table = 'pw_posts'.$num;
		$CreatTable = $db->get_one("SHOW CREATE TABLE pw_posts");
		$sql = str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$db->query($sql);
		$plist[$num] = '';
		setConfig('db_ptable', $num);
		setConfig('db_plist', $plist);
	}
	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'movedata') {

	S::gp(array('step'));

	if (!$step) {

		$table_sel = '';
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while ($rs = $db->fetch_array($query)) {
			$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			if ($key && !is_numeric($key)) continue;
			$table_sel .= "<option value=\"$key\">$rs[Name]</option>";
		}
		require_once PrintEot('ptable');

	} else {

		$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
		set_time_limit(0);
		$db_bbsifopen && adminmsg('bbs_open');
		S::gp(array('tstart','tend','tfrom','tto','lines'));

		$tfrom = (int) $tfrom;
		$tto   = (int) $tto;
		if ($tfrom == $tto) {
			adminmsg('table_same');
		}
		!$lines && $lines=200;
		!$tstart && $tstart=0;

		$ftable = $tfrom ? 'pw_posts'.$tfrom : 'pw_posts';
		$ttable = $tto   ? 'pw_posts'.$tto   : 'pw_posts';
		if (!$tend) {
			@extract($db->get_one("SELECT MAX(tid) AS tend FROM $ftable"));
		}
		$end = $tstart + $lines;
		$end > $tend && $end = $tend;
		$db->update("INSERT INTO $ttable SELECT * FROM $ftable WHERE tid>".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));
		//$db->update("DELETE FROM $ftable WHERE tid>".S::sqlEscape($tstart)."AND tid<=".S::sqlEscape($end));
		pwQuery::delete($ftable, 'tid>:tid1 AND tid<=:tid2', array($tstart, $end));
		//$db->update("UPDATE pw_threads SET ptable=".S::sqlEscape($tto)."WHERE tid>".S::sqlEscape($tstart)."AND tid<=".S::sqlEscape($end)."AND ptable=".S::sqlEscape($tfrom));
		pwQuery::update('pw_threads', 'tid>:tid AND tid<=:end AND ptable=:ptable', array($tstart, $end, $tfrom), array('ptable'=>$tto));
		Perf::gatherInfo('changeThreadListWithThreadIds', array('tid'=>$tstart+1));
		if ($end < $tend) {
			$step++;
			$j_url="$basename&action=$action&step=$step&tstart=$end&tend=$tend&tfrom=$tfrom&tto=$tto&lines=$lines";
			adminmsg('table_change',EncodeUrl($j_url),2);
		} else {

			//* $_cache = getDatastore();
			//* $_cache->flush();
			$_cacheService = perf::gatherCache('pw_membersdbcache');
			$_cacheService->flush();
			if (Perf::checkMemcache()){
				$_cacheService = L::loadClass('cacheservice', 'utility');
				$_cacheService->flush(PW_CACHE_MEMCACHE);			
			}
			
			adminmsg('operate_success');
		}
	}
} elseif ($action == 'movetmsg') {

	S::gp(array('step','id'));
	$tlistdb = $db_tlist;

	if (!$step) {

		$id < 1 && $id = '';
		$pw_tmsgs = 'pw_tmsgs'.($id > 0 ? intval($id) : '');
		@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_tmsgs"));
		list($tidmin,$tidmax) = maxmin($id);
		$tiderror = '';
		$tmin<=$tidmin && $tiderror .= "$tmin - ".($tmax > $tidmin ? $tidmin : $tmax)." &nbsp;&nbsp;";
		$tidmax && $tmax > $tidmax && $tiderror .= ($tidmax+1)." - $tmax";
		$tiderror=='' && adminmsg('operate_undefined');
		require_once PrintEot('ptable');

	} else {

		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		set_time_limit(0);
		$db_bbsifopen && adminmsg('bbs_open');
		S::gp(array('tstart','lines','tmax','tmin'));
		list($tidmin,$tidmax) = maxmin($id);
		!$lines && $lines=5000;

		if ($tmin <= $tidmin && $step < 3) {
			!$tstart && $tstart = $tmin-1;
			$end  = $tstart + $lines;
			$tend = $tmax > $tidmin ? $tidmin : $tmax;
			$end > $tend && $end = $tend;
			$ttable = GetTtable($end);
			$step = 2;
		} else {
			!$tstart && $tstart = $tidmax;
			$end  = $tstart + $lines;
			$tend = $tmax;
			$end > $tend && $end = $tend;
			$ttable = GetTtable($tstart+1);
			$step = 3;
		}
		$ftable = 'pw_tmsgs'.($id > 0 ? intval($id) : '');
		$ftable == $ttable && adminmsg('table_same');

		$db->update("INSERT INTO $ttable SELECT * FROM $ftable WHERE tid>".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));
		$db->update("DELETE FROM $ftable WHERE tid>".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));

		if ($end < $tend) {
			$j_url = "$basename&action=$action&step=$step&tstart=$end&lines=$lines&tmax=$tmax&tmin=$tmin&id=$id";
			adminmsg('table_change',EncodeUrl($j_url),2);
		} elseif ($step == 2 && $tidmax && $tmax > $tidmax) {
			$step  = 3;
			$j_url = "$basename&action=$action&step=$step&lines=$lines&tmax=$tmax&tmin=$tmin&id=$id";
			adminmsg('table_change',EncodeUrl($j_url),2);
		} else {
			adminmsg('operate_success');
		}
	}
} elseif ($action == 'delttable') {

	S::gp('id','GP',2);
	$rt = $db->get_one("SHOW TABLE STATUS LIKE 'pw_tmsgs$id'");
	if ($rt && $rt['Rows']) {
		adminmsg('deltable_error2');
	}
	$rt && $db->update("DROP TABLE pw_tmsgs$id",0);
	$tlistdb = $db_tlist;
	unset($tlistdb[$id]);
	$db_tlist = count($tlistdb)>1 ? $tlistdb : '';
	setConfig('db_tlist', $db_tlist);
	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'delptable') {

	S::gp('id','GP',2);
	if ($id == $db_ptable) {
		adminmsg('delptable_error');
	}
	$rt = $db->get_one("SHOW TABLE STATUS LIKE 'pw_posts$id'");
	if ($rt && $rt['Rows']) {
		adminmsg('deltable_error2');
	}
	$rt && $db->update("DROP TABLE pw_posts$id",0);
	$plistdb = is_array($db_plist) ? $db_plist : array();
	$plistdb['0'] = $plistdb['0'] ? $plistdb['0'] : '';
	$plist = array(0=>$plistdb['0']);
	$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
	while ($rs = $db->fetch_array($query)) {
		$j = str_replace($PW.'posts','',$rs['Name']);
		if ($j && !is_numeric($j)) continue;
		if ($j) {
			$plist[$j] = $plistdb[$j] ? $plistdb[$j] : '';
		}
	}
	$plist = is_array($plist) && (count($plist) > 1) ? $plist : '';
	setConfig('db_plist', $plist);
	updatecache_c();
	adminmsg('operate_success');
}

function arraySort ($array,$sortkey) {
	foreach ($array as $key => $value) {
		$keyValue[$key] = $value[$sortkey];
	}
	arsort($keyValue);
	foreach ($keyValue as $key2 => $value2) {
		$keySort[] = $key2;
	}
	for($i=0; $i<count($keySort); $i++){
		$newArray[$keySort[$i]] = $array[$keySort[$i]]; 
	}
	return $newArray;
}
?>