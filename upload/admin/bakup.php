<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('adminitem'));
empty($adminitem) && $adminitem = 'bakout';
@set_time_limit(1800);
require_once (R_P.'admin/table.php');
$job = S::getGP('job');
!$job && $job = 'bakout';
$bakupDir = D_P . 'data/sqlback/';
createFolder($bakupDir);
$basename = "$admin_file?adminjob=bakup";

if ($job == 'bakout') {
	if (empty($action)) {
		$type = S::getGP('t');
		list($pwdb, $otherdb) = N_getTabledb(true);
		$tables = $type == 'pw' ? $pwdb : ($type == 'other' ? $otherdb : array_merge($pwdb, $otherdb));
		if ($type == 'pw') {
			$creditLog = $db->query("SHOW TABLE STATUS LIKE 'pw_creditlog%'");
			while ($tmpCreditLog = $db->fetch_array($creditLog)) {
				$tables[] = $tmpCreditLog['Name'];
				$PW != 'pw_' && $tmpCreditLog['Name'] = str_replace($PW, 'pw_', $tmpCreditLog['Name']);
				$lang['dbtable'][$tmpCreditLog['Name']] = $lang['dbtable']['pw_creditlog'];
			}
		}
		$existTables = array();
		$query = $db->query("SHOW TABLES");
		while ($rt = $db->fetch_array($query, 2)) {
			$value = trim($rt[0]);
			$existTables[$value] = true;
		}
		$isZlibEnabled = (extension_loaded('zlib') && function_exists('gzcompress')) ? true : false;
		include PrintEot('bakup');exit;

	} elseif ($action == 'bak') {
		$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
		S::gp(array('sizelimit', 'compress', 'start', 'tableid', 'step'), 'gp', 2);
		S::gp(array('tabledb', 'insertmethod', 'dirname', 'tabledbname'));

		$insertmethod = $insertmethod == 'extend' ? 'extend' : 'common';
		$sizelimit = $sizelimit ? $sizelimit : 2048;
		(!S::isArray($tabledb) && !$step) && adminmsg('operate_error');
		
		if (!$tabledb && $step) {
			$cachedTable = pwCache::readover(S::escapePath(D_P . 'data/tmp/' . $tabledbname . '.tmp'));
			$tabledb = explode("|", $cachedTable);
		}
		!$tabledb && adminmsg('operate_error');
		
		$backupService = L::loadClass('backup', 'site');
		!$dirname && $dirname = $backupService->getDirectoryName();
		if (!$step) {
			$backupTable = $backupService->backupTable($tabledb, $dirname, $compress);
			$tabledbTmpSaveDir = D_P . 'data/tmp/';
			createFolder($tabledbTmpSaveDir);
			$tabledbname = 'cached_table_' . randstr(8);
			pwCache::writeover(S::escapePath(D_P . 'data/tmp/' . $tabledbname . '.tmp'), implode("|", $tabledb), 'wb');
		}
		$step = (!$step ? 1 : $step) + 1;
		$filename = $dirname . '/' . $dirname . '_' . ($step - 1) . '.sql';
		list($backupData, $tableid, $start, $totalRows)  = $backupService->backupData($tabledb, $tableid, $start, $sizelimit, $insertmethod, $filename);
		
		$continue = $tableid < count($tabledb) ? true : false;
		$backupService->saveData($filename, $backupData, $compress);
		
		if ($continue) {
			$currentTableName = $tabledb[$tableid];
			$currentPos = $start + 1;
			$createdFileNum = $step - 1;
			$j_url = "$basename&action=$action&start=$start&tableid=$tableid&sizelimit=$sizelimit&step=$step&dirname=$dirname&tabledbname=$tabledbname&insertmethod=$insertmethod&compress=$compress";
			adminmsg('bakup_step', EncodeUrl($j_url), 2);
		} else {
			$bakfile = '<a href="data/sqlback/' . $dirname . '" target="_blank">' . $dirname . '</a><br>';
			unlink(S::escapePath(D_P . 'data/tmp/' . $tabledbname . '.tmp'));
			adminmsg('bakup_out');
		}
	} elseif ($action == 'repair' || $action == 'optimize') {

		!$_POST['tabledb'] && adminmsg('db_empty_tables');
		$table = S::escapeChar(implode(', ',array_unique($_POST['tabledb'])));

		$db->dbpre = 'pw_';
		if ($action == 'repair') {
			$query = $db->query("REPAIR TABLE $table EXTENDED");
		} else {
			$query = $db->query("OPTIMIZE TABLE $table");
		}
		while ($rt = $db->fetch_array($query)) {
			$rt['Table']  = substr(strrchr($rt['Table'] ,'.'),1);
			$msgdb[] = $rt;
		}
		$db->free_result($query);
		$db->dbpre = $GLOBALS['PW'];

		include PrintEot('bakup');exit;
	}
} elseif ($job == 'bakin') {
	$basename .= "&job=bakin";

	if (empty($action)) {
		$filedb = array();
		$handle = opendir($bakupDir);
		while (($file = readdir($handle)) !== false) {
			if ((!$PW || preg_match('/^pw_/i', $file) || preg_match("/^$PW/i", $file)) && preg_match('/\.sql$/i', $file)) {
				$strlen = preg_match("/^$PW/i", $file) ? 16 + strlen($PW) : 19;
				$fp = fopen($bakupDir . $file, 'rb');
				$bakinfo = fread($fp, 200);
				fclose($fp);
				$detail = explode("\n", $bakinfo);
				$bk['name'] = $file;
				$bk['version'] = substr($detail[2], 10);
				$bk['time'] = substr($detail[3], 8);
				$bk['pre'] = substr($file, 0, $strlen);
				$bk['num'] = substr($file, $strlen, strrpos($file, '.') - $strlen);
				$bk['type'] = '备份文件';
				$filedb[] = $bk;
			} elseif (preg_match('/^pw_([^_]+)_(\d{14})_[a-z0-9]{5}/i', $file, $match)) {
				$bk['name'] = $file;
				$bk['version'] = str_replace('-', '.', $match[1]);
				$time = str_split($match[2], 2);
				$bk['time'] = $time[0] . $time[1] . '-' . $time[2] . '-' . $time[3] . ' ' . $time[4] . ':' . $time[5];
				$bk['pre'] = $bk['name'];
				$bk['num'] = '-';
				$bk['type'] = '目录';
				$bk['isdir'] = 1;
				$filedb[] = $bk;
			}
		}
		include PrintEot('bakup');exit;

	} elseif ($action == 'listsubcat') {
		S::gp(array('pre'));
		$pre = Pcv($pre);
		!$pre && adminmsg('operate_error');
		
		$handle = opendir($bakupDir . $pre);
		while (($file = readdir($handle)) !== false) {
			if (preg_match('/^(pw_([^_]+)_(\d{14})_[a-z0-9]{5})_(\d+)\.(sql|zip)$/i', $file, $match)) {
				$bk['name'] = $file;
				$bk['version'] = str_replace('-', '.', $match[2]);
				$time = str_split($match[3], 2);
				$bk['time'] = $time[0] . $time[1] . '-' . $time[2] . '-' . $time[3] . ' ' . $time[4] . ':' . $time[5];
				$bk['pre'] = $match[1];
				$bk['num'] = $match[4];
				$bk['type'] = $match[5] == 'sql' ? '备份文件' : '压缩文件';
				$bk['isdir'] = 1;
				$bk['nosub'] = 1;
				$tmpType = $match[5];
				$filedb[] = $bk;
			}
		}
		$tableStructure = $filedb[0];
		$tableStructure['name'] = 'table.' . $tmpType;
		$tableStructure['num'] = '-';
		$tableStructure['type'] = '数据表结构备份';
		$filedb[] = $tableStructure;
		file_exists($bakupDir . $pre . '/table.index') && $showRecover = true;
		
		include PrintEot('bakup');exit;
		
	} elseif ($action == 'bakincheck') {
		S::gp(array('pre', 'isdir'));
		!$pre && adminmsg('operate_error');
		include PrintEot('bakup');exit;

	} elseif ($action == 'singlerestore') {
		S::gp(array('tablename', 'pre' , 'step'));
		$tablename = trim($tablename);
		$pre = Pcv($pre);
		$step = (int) $step;
		(!$pre || !$tablename) && adminmsg('operate_error');
		
		$tableIndex = $bakupDir . $pre . '/table.index';
		!file_exists($tableIndex) && adminmsg('bakup_in_indexnot_exists');
		$data = pwCache::readover($tableIndex);
		preg_match_all("/^$tablename:([^\,]+\,\d+\,[\d\-]+)$/im", $data, $match, PREG_SET_ORDER);
		!$match && adminmsg('bakup_in_noindex');
		
		$index = array();
		foreach ($match as $value) {
			$index[] = $value[1];
		}
		$total = count($index);
		singleBakinData($index[$step], $pre);
		$step++;
		if ($step < $total) {
			$j_url = "$basename&action=singlerestore&step=$step&pre=$pre&tablename=$tablename";
			adminmsg('bakup_siglerestore', EncodeUrl($j_url), 2);
		}
		updatecache();
		extract(pwCache::getData(D_P . 'data/bbscache/config.php', false));
		adminmsg('operate_success');
		
	} elseif ($action == 'bakin') {
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('step','count','pre', 'isdir'));
		$pre = Pcv($pre);
		!$pre && adminmsg('operate_error');
		
		if (!$isdir && !$count) {
			$count = 0;
			$handle = opendir($bakupDir);
			while (($file = readdir($handle)) !== false) {
				if (preg_match("/^$pre/i", $file) && preg_match("/\.sql$/i", $file)) $count++;
			}
		} elseif ($isdir && !$count) {
			$count = 1;
			$handle = opendir($bakupDir . $pre);
			while (($file = readdir($handle)) !== false) {
				if(preg_match("/^$pre\_\d+\.(sql|zip)$/i", $file)) $count++;
			}
		}
		
		!$step && $step = 1;
		!$isdir ? bakindata($bakupDir . $pre . $step . '.sql') : newBakinData($pre, $step);
		
		$i = $step;
		$step++;
		if($count > 1 && $step <= $count){
			$j_url = "$basename&action=bakin&step=$step&count=$count&pre=$pre&isdir=$isdir";
			adminmsg('bakup_in', EncodeUrl($j_url), 2);
		}
		updatecache();
		extract(pwCache::getData(D_P . 'data/bbscache/config.php', false));
		adminmsg('operate_success');

	} elseif ($action == 'del') {
		S::gp(array('delfile', 'issub'), 'P');

		!S::isArray($delfile) && adminmsg('operate_error');
		foreach($delfile as $key => $value){
			$value = Pcv($value);
			if (!$value) continue;
			if(preg_match("/\.(sql|zip)$/i", $value)){
				$deletePath = $bakupDir . $value;
				if ($issub) {
					preg_match('/^(pw_[^_]+_\d{14}_[a-z0-9]{5})_\d+\.(sql|zip)$/i', $value, $match);
					$deletePath = $bakupDir . $match[1] . '/' . $value;
				}
				P_unlink($deletePath);
			} elseif (preg_match('/^pw_([^_]+)_(\d{14})_[a-z0-9]{5}/i', $value)) {
				$fp = opendir($bakupDir . $value);
				while (($file = readdir($fp)) !== false) {
					if ($file == '.' || $file == '..') continue;
					P_unlink($bakupDir . $value . '/' . $file);
				}
				closedir($fp);
				@rmdir($bakupDir . $value);
			}
		}
		adminmsg('operate_success');
	}
} elseif ($job =='ptable') {

	$basename .= "&job=ptable";
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
		if ($type == 1) {
			$GLOBALS['db_tlist'] = $db_tlist;
			updateMergeTmsgsTable();
		} else {
			$GLOBALS['db_tlist'] = $plist;
			updateMergePostsTable();
		}
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
			$db->update("INSERT INTO $ttable SELECT * FROM $ftable WHERE tid>=".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));
			//$db->update("DELETE FROM $ftable WHERE tid>".S::sqlEscape($tstart)."AND tid<=".S::sqlEscape($end));
			pwQuery::delete($ftable, 'tid>=:tid1 AND tid<=:tid2', array($tstart, $end));
			//$db->update("UPDATE pw_threads SET ptable=".S::sqlEscape($tto)."WHERE tid>".S::sqlEscape($tstart)."AND tid<=".S::sqlEscape($end)."AND ptable=".S::sqlEscape($tfrom));
			pwQuery::update('pw_threads', 'tid>=:tid AND tid<=:end AND ptable=:ptable', array($tstart, $end, $tfrom), array('ptable'=>$tto));
			Perf::gatherInfo('changeThreadListWithThreadIds', array('tid'=>$tstart+1));
			if ($end < $tend) {
				$step++;
				$end++;
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
				$db->query('OPTIMIZE TABLE ' . S::sqlMetadata($ftable));
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
			$ftable = 'pw_tmsgs'.$id;
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
		$GLOBALS['db_tlist'] = $db_tlist;
		updateMergeTmsgsTable();
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
		$GLOBALS['db_plist'] = $plist;
		updateMergePostsTable();
		adminmsg('operate_success');
	}
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

function bakindata($filename) {
	$data = pwCache::readover($filename);
	$sql = explode("\n", $data);
	return doBackIn($sql);
}

function newBakinData($dir, $step) {
	global $bakupDir;
	$dir = Pcv($dir);
	if (!$dir || !$step) return false;
	$step = intval($step) - 1;
	
	$tmpname = $bakupDir . $dir . '/';
	$extend = file_exists($tmpname . 'table.zip') ? 'zip' : 'sql';
	$filename = $tmpname . (!$step? 'table.' . $extend : $dir . '_' . $step . '.' . $extend);
	if ($extend == 'zip') {
		L::loadClass('zip', 'utility', false);
		$zipService = new Zip();
		list($data) = $zipService->extract($filename);
		$sql = explode("\n", $data['data']);
	} else {
		$data = pwCache::readover($filename);
		$sql = explode("\n", $data);
	}
	return doBackIn($sql);
}

function singleBakinData($index, $pre) {
	global $bakupDir;
	if (!$index) return false;
	
	$sql = array();
	$tmpname = $bakupDir . $pre . '/';
	$extend = file_exists($tmpname . 'table.zip') ? 'zip' : 'sql';
	$data = explode(',', $index);
	$filename = $tmpname . $data[0];
	if ($extend == 'zip') {
		L::loadClass('zip', 'utility', false);
		$zipService = new Zip();
		$filename = str_replace(substr($filename, strpos($filename, '.')), '.zip', $filename);
		list($tmpData) = $zipService->extract($filename);
		$tmpSql = explode("\n", $tmpData['data']);
	} else {
		$tmpData = pwCache::readover($filename);
		$tmpSql = explode("\n", $tmpData);
	}
	$sql = $data[2] == -1 ? array_slice($tmpSql, $data[1]) : array_slice($tmpSql, $data[1], $data[2] - $data[1]);
	return doBackIn($sql);
}

function doBackIn($sql) {
	global $db, $charset, $PW;
	if (!$sql) return false;
	$tablepre = substr($sql[4], 0, 11) == '# tablepre:' ? trim(substr($sql[4], 12)) : '';
	$query = '';
	$num = 0;
	foreach ($sql as $value) {
		$value = trim($value);
		if (!$value || $value[0] == '#') continue;
		if(preg_match("/\;$/i", $value)){
			$query .= $value;
			if(preg_match("/^CREATE/i", $query)){
				$extra = substr(strrchr($query, ')'), 1);
				$tabtype = substr(strchr($extra, '='), 1);
				$tabtype = substr($tabtype, 0, strpos($tabtype, strpos($tabtype,' ') ? ' ' : ';'));
				$query = str_replace($extra, '', $query);
				if( $db->server_info() > '4.1') {
					$extra = $charset ? "ENGINE=$tabtype DEFAULT CHARSET=$charset;" : "ENGINE=$tabtype;";
				} else {
					$extra = "TYPE=$tabtype;";
				}
				$query .= $extra;
			} elseif (preg_match("/^INSERT/i", $query)){
				$query = 'REPLACE ' . substr($query, 6);
			}
			if ($tablepre && $tablepre != $PW) {
				$query = str_replace(array(" $tablepre", "`$tablepre", " '$tablepre"), array(" $PW", "`$PW", " '$PW"), $query);
			}
			$db->query($query);
			$query = '';
		} else{
			$query .= $value;
		}
	}
	return true;
}

function createFolder($path) {
	if (!is_dir($path)) {
		createFolder(dirname($path));
		mkdir($path);
		chmod($path,0777);
		fclose(fopen($path.'/index.html','w'));
		chmod($path.'/index.html',0777);
	}
}

function updateMergeTmsgsTable() {
	global $db_tlist, $db, $PW;
	!$db_tlist && $threadTableNames = array("{$PW}tmsgs");
	if (S::isArray($db_tlist)) {
		foreach ($db_tlist as $key => $val) {
			$threadTableNames[$key] = $key == 0 ? "{$PW}tmsgs" : "{$PW}tmsgs" . $key;
		}
	}
	$engineType = $db->server_info() > '4.1' ? 'ENGINE=MERGE' : 'TYPE=MERGE';
	$creatTable = $db->get_one("SHOW CREATE TABLE `pw_tmsgs`");
	preg_match('/\(.+\)/is', $creatTable['Create Table'], $match);
	preg_match('/CHARSET=([^;\s]+)/is', $creatTable['Create Table'], $charsetMatch);
	$db->query("DROP TABLE IF EXISTS `pw_merge_tmsgs`");
	ksort($threadTableNames);
	$createTableSql = 'CREATE TABLE `pw_merge_tmsgs` ' . $match[0] . " $engineType UNION=(" . implode(',', $threadTableNames) . ') DEFAULT CHARSET=' . $charsetMatch[1] . ' INSERT_METHOD=LAST';
	$db->query($createTableSql);
	
	$success = $db->get_one("SHOW TABLE STATUS LIKE 'pw_merge_tmsgs'");
	$config = $success['Engine'] ? 1 : 0;
	setConfig("db_merge_tmsgs", $config);
	updatecache_c();
	return true;
}

function updateMergePostsTable() {
	global $db_plist, $db, $PW;
	!$db_plist && $postTableNames = array("{$PW}posts");
	if (S::isArray($db_plist)) {
		foreach ($db_plist as $key => $val) {
			$postTableNames[$key] = $key == 0 ? "{$PW}posts" : "{$PW}posts" . $key;
		}
	}
	$engineType = $db->server_info() > '4.1' ? 'ENGINE=MERGE' : 'TYPE=MERGE';
	$creatTable = $db->get_one("SHOW CREATE TABLE `pw_posts`");
	preg_match('/\(.+\)/is', $creatTable['Create Table'], $match);
	preg_match('/CHARSET=([^;\s]+)/is', $creatTable['Create Table'], $charsetMatch);
	$db->query("DROP TABLE IF EXISTS `pw_merge_posts`");
	ksort($postTableNames);
	$createTableSql = 'CREATE TABLE `pw_merge_posts` ' . $match[0] . " $engineType UNION=(" . implode(',', $postTableNames) . ') DEFAULT CHARSET=' . $charsetMatch[1] . ' INSERT_METHOD=LAST';
	$db->query($createTableSql);
	
	$success = $db->get_one("SHOW TABLE STATUS LIKE 'pw_merge_posts'");
	$config = $success['Engine'] ? 1 : 0;
	setConfig("db_merge_posts", $config);
	updatecache_c();
	return true;
}
?>