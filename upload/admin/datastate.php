<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = $baseurl = "$admin_file?adminjob=datastate";
S::gp(array('type'));
!in_array($type,array('reply','regmen','postmen')) && $type = 'topic';

${'cls_'.$type} = 'class="current"';
foreach ($_GET as $key => $value) {
	!in_array($key,array('adminjob','type')) && $baseurl .= "&$key=$value";
}
$basename .= "&type=".$type;

if (empty($action)) {

	S::gp(array('c_year'));
	empty($c_year) && $c_year = get_date($timestamp,'Y');
	$p_year = $c_year - 1;
	$n_year = $c_year + 1;
	$sortdb = array();
	$Ntotal = 1;

	$query = $db->query("SELECT month,SUM($type) as $type FROM pw_datastate WHERE year=".S::sqlEscape($c_year)."GROUP BY month");

	while ($rt = $db->fetch_array($query)) {
		if ($type == 'postmen') {
			$rt[$type] = round($rt[$type]/get_date(PwStrtoTime($c_year.'-'.$rt['month'].'-1'),'t'));
		}
		$rt[$type] > $Ntotal && $Ntotal = $rt[$type];

		$sortdb[$rt['month']] = $rt;
	}
	for ($i = 1; $i <= 12; $i++) {
		if (!isset($sortdb[$i])) {
			$sortdb[$i] = array($type=>0,'w'=>0);
		} else {
			$sortdb[$i]['w'] = 560*$sortdb[$i][$type]/$Ntotal;
		}
	}
	ksort($sortdb);

	include PrintEot('datastate');exit;

} elseif ($action == 'month') {

	S::gp(array('year','month'));

	$p_year	 = $n_year = $year;
	$p_month = $month - 1;
	$n_month = $month + 1;
	if ($p_month < 1) {
		$p_month = 12;
		$p_year--;
	}
	if ($n_month > 12) {
		$n_month = 1;
		$n_year++;
	}
	$sortdb = array();
	$Ntotal = 1;

	$query = $db->query("SELECT day,$type FROM pw_datastate WHERE year=".S::sqlEscape($year)."AND month=".S::sqlEscape($month));
	while ($rt = $db->fetch_array($query)) {
		$rt[$type] > $Ntotal && $Ntotal = $rt[$type];
		$sortdb[$rt['day']] = $rt;
	}
	for ($i = 1; $i <= get_date(PwStrtoTime($year.'-'.$month.'-1'),'t'); $i++) {
		if (!isset($sortdb[$i])) {
			$sortdb[$i] = array($type=>0,'w'=>0);
		} else {
			$sortdb[$i]['w'] = 560*$sortdb[$i][$type]/$Ntotal;
		}
	}
	ksort($sortdb);

	include PrintEot('datastate');exit;

} elseif ($action == 'msort') {

	@set_time_limit(1000);
	S::gp(array('year','month'));

	$timestart	= PwStrtoTime($year.'-'.$month.'-1');
	$timeend	= $timestart + (get_date($timestart,'t') * 86400);

	$sortdb = array();

	switch ($type) {
		case 'topic':
			$query = $db->query("SELECT COUNT(*) AS topic,DAYOFMONTH(FROM_UNIXTIME(postdate)) AS day FROM pw_threads WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend).'GROUP BY day');
			while ($rt = $db->fetch_array($query)) {
				$sortdb[$rt['day']] = $rt['topic'];
			}
			break;
		case 'reply':
			$sql_1 = "SELECT COUNT(*) AS replies,DAYOFMONTH(FROM_UNIXTIME(postdate)) AS day FROM pw_posts WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend).'GROUP BY day';

			if ($db_plist && count($db_plist)>1) {
				foreach ($db_plist as $key=>$value) {
					if ($key == 0) continue;
					$sql_1 .= " UNION ALL SELECT COUNT(*) AS replies,DAYOFMONTH(FROM_UNIXTIME(postdate)) AS day FROM pw_posts{$key} WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend).'GROUP BY day';
				}
			}
			$query = $db->query($sql_1);
			while ($rt = $db->fetch_array($query)) {
				$sortdb[$rt['day']] += $rt['replies'];
			}
			break;
		case 'regmen':
			$query = $db->query("SELECT COUNT(*) AS regmen,DAYOFMONTH(FROM_UNIXTIME(regdate)) AS day FROM pw_members WHERE regdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend).'GROUP BY day');
			while ($rt = $db->fetch_array($query)) {
				$sortdb[$rt['day']] = $rt['regmen'];
			}
			break;
		case 'postmen':
			$sql_2 = "SELECT DAYOFMONTH(FROM_UNIXTIME(postdate)) AS day,authorid FROM pw_threads WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend)."GROUP BY day,authorid UNION SELECT DAYOFMONTH(FROM_UNIXTIME(postdate)) AS day,authorid FROM pw_posts WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend)."GROUP BY day,authorid";

			if ($db_plist && count($db_plist)>1) {
				foreach ($db_plist as $key=>$value) {
					if ($key == 0) continue;
					$sql_2 .= " UNION SELECT DAYOFMONTH(FROM_UNIXTIME(postdate)) AS day,authorid FROM pw_posts{$key} WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend)."GROUP BY day,authorid";
				}
			}
			if ($db->server_info() > '4.1') {
				$query = $db->query("SELECT COUNT(*) AS postmen,day FROM (".$sql_2.") t1 GROUP BY day");
			} else {
				$db->query("CREATE TEMPORARY TABLE tmp_datastate $sql_2");
				$query = $db->query("SELECT COUNT(*) AS postmen,day FROM tmp_datastate GROUP BY day");
			}
			while ($rt = $db->fetch_array($query)) {
				$sortdb[$rt['day']] = $rt['postmen'];
			}
			break;
	}
	foreach ($sortdb as $day => $value) {
		$db->pw_update(
			"SELECT * FROM pw_datastate WHERE year=".S::sqlEscape($year).'AND month='.S::sqlEscape($month).'AND day='.S::sqlEscape($day),
			"UPDATE pw_datastate SET {$type}=".S::sqlEscape($value).'WHERE year='.S::sqlEscape($year).'AND month='.S::sqlEscape($month).'AND day='.S::sqlEscape($day),
			"INSERT INTO pw_datastate SET ".S::sqlSingle(array('year'=>$year,'month'=>$month,'day'=>$day,$type=>$value))
		);
	}

	echo "<?xml version=\"1.0\" encoding=\"$db_charset\"?><ajax>success</ajax>";exit;

} elseif ($action == 'dsort') {

	@set_time_limit(1000);
	S::gp(array('year','month','day'));

	$timestart	= PwStrtoTime($year.'-'.$month.'-'.$day);
	$timeend	= $timestart + 86400;

	$total = 0;

	switch ($type) {
		case 'topic':
			$rt = $db->get_one("SELECT COUNT(*) AS topic FROM pw_threads WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend));
			$rt && $total = $rt['topic'];
			break;
		case 'reply':
			$sql_1 = "SELECT COUNT(*) AS replies FROM pw_posts WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend);
			if ($db_plist && count($db_plist)>1) {
				foreach ($db_plist as $key=>$value) {
					if ($key == 0) continue;
					$sql_1 .= " UNION ALL SELECT COUNT(*) AS replies FROM pw_posts{$key} WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend);
				}
			}
			$query = $db->query($sql_1);
			while ($rt = $db->fetch_array($query)) {
				$total += $rt['replies'];
			}
			break;
		case 'regmen':
			$rt = $db->get_one("SELECT COUNT(*) AS regmen FROM pw_members WHERE regdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend));
			$rt && $total = $rt['regmen'];
			break;
		case 'postmen':
			$sql_2 = "SELECT authorid FROM pw_threads WHERE postdate BETWEEN '$timestart' AND '$timeend' GROUP BY authorid UNION SELECT authorid FROM pw_posts WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend)."GROUP BY authorid";
			if ($db_plist && count($db_plist)>1) {
				foreach ($db_plist as $key=>$value) {
					if ($key == 0) continue;
					$sql_2 .= " UNION SELECT authorid FROM pw_posts{$key} WHERE postdate BETWEEN ".S::sqlEscape($timestart).' AND '.S::sqlEscape($timeend)." GROUP BY authorid";
				}
			}
			if ($db->server_info() > '4.1') {
				$rt = $db->get_one("SELECT COUNT(*) AS postmen FROM (".$sql_2.") t1");
			} else {
				$db->query("CREATE TEMPORARY TABLE temp $sql_2");
				$rt = $db->get_one("SELECT COUNT(*) AS postmen FROM temp");
			}
			$rt && $total = $rt['postmen'];
			break;
	}
	if ($total > 0) {
		$db->pw_update(
			"SELECT * FROM pw_datastate WHERE year=".S::sqlEscape($year).' AND month='.S::sqlEscape($month).' AND day='.S::sqlEscape($day),
			"UPDATE pw_datastate SET {$type}=".S::sqlEscape($total)."WHERE year=".S::sqlEscape($year).'AND month='.S::sqlEscape($month).'AND day='.S::sqlEscape($day),
			"INSERT INTO pw_datastate SET ".S::sqlSingle(array('year'=>$year,'month'=>$month,'day'=>$day,$type=>$total))
		);
	}

	echo "<?xml version=\"1.0\" encoding=\"$db_charset\"?><ajax>success</ajax>";exit;
}
?>