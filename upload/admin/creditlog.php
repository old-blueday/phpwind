<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=creditlog";

if (empty($action)) {

	require_once GetLang('logtype');
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/credit.php');

	S::gp(array('page','username','uid','ctype','stime','etime','optype','clg'));

	$pw_creditlog = 'pw_creditlog';
	$clgtb = $logdb = array();
	$query = $db->query("SHOW TABLE STATUS LIKE 'pw_creditlog%'");
	while ($rt = $db->fetch_array($query)) {
		$clgtb[] = str_replace($PW,'',$rt['Name']);
	}

	if ($clg && in_array($clg,$clgtb)) {
		$pw_creditlog = 'pw_'.$clg;
	}
	$sqladd = "WHERE 1";
	$urladd = '';
	if ($username) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$member = $userService->getByUserName($username);
		$uid = $member['uid'];
	}
	$uid && $sqladd .= " AND uid=".S::sqlEscape($uid);
	$ctype && $sqladd .= " AND ctype=".S::sqlEscape($ctype);
	if ($stime) {
		!is_numeric($stime) && $stime = PwStrtoTime($stime);
		$sqladd .= " AND adddate>".S::sqlEscape($stime);
	}
	if ($etime) {
		!is_numeric($etime) && $etime = PwStrtoTime($etime);
		$sqladd .= " AND adddate<".S::sqlEscape($etime);
	}
	if ($optype) {
		if (is_array($optype)) {
			$sqladd .= " AND logtype IN(".S::sqlImplode($optype).")";
			foreach ($optype as $key => $value) {
				$urladd .= "&optype[$key]=$value";
			}
		} else {
			$sqladd .= " AND logtype".(strpos($optype,'_') !== false ? "=".S::sqlEscape($optype) : " LIKE ".S::sqlEscape("$optype%"));
			$urladd .= "&optype=$optype";
		}
	}

	$db_perpage = 25;
	(int)$page < 1 && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_creditlog $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "$basename&uid=$uid&ctype=$ctype&stime=$stime&etime=$etime{$urladd}&");
	$query = $db->query("SELECT * FROM $pw_creditlog $sqladd ORDER BY adddate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['adddate'] = get_date($rt['adddate']);
		$rt['descrip'] = descriplog($rt['descrip']);
		$logdb[] = $rt;
	}
	require_once PrintEot('creditlog');

} elseif ($action == 'sort') {

	require_once GetLang('logtype');
	require_once(R_P.'require/credit.php');

	$clgtb = array();
	$query = $db->query("SHOW TABLE STATUS LIKE 'pw_creditlog%'");
	while ($rt = $db->fetch_array($query)) {
		$clgtb[] = str_replace($PW,'',$rt['Name']);
	}

	if (!empty($_POST['step'])) {

		S::gp(array('username','ctype','stime','etime','optype','clg'));

		$sqladd = "WHERE 1";
		$urladd = '';
		$pw_creditlog = 'pw_creditlog';
		if ($clg && in_array($clg,$clgtb)) {
			$pw_creditlog = 'pw_'.$clg;
			$urladd .= "&clg=$clg";
		}
		if ($username) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$member = $userService->getByUserName($username);
			$uid = $member['uid'];
			if ($uid) {
				$sqladd .= " AND uid=".S::sqlEscape($uid);
				$urladd .= "&uid=$uid";
			}
		}
		if ($ctype) {
			$sqladd .= " AND ctype=".S::sqlEscape($ctype);
		}
		if ($stime) {
			!is_numeric($stime) && $stime = PwStrtoTime($stime);
			$sqladd .= " AND adddate>".S::sqlEscape($stime);
			$urladd .= "&stime=$stime";
		}
		if ($etime) {
			!is_numeric($etime) && $etime = PwStrtoTime($etime);
			$sqladd .= " AND adddate<".S::sqlEscape($etime);
			$urladd .= "&etime=$etime";
		}
		if ($optype && is_array($optype) && !in_array('all',$optype)) {
			$sqladd .= " AND logtype IN(".S::sqlImplode($optype).")";
			foreach ($optype as $key => $value) {
				$urladd .= "&optype[$key]=$value";
			}
		}
		$sordb = array();
		$query = $db->query("SELECT SUM(affect) AS sum,ctype,affect>0 AS isget FROM $pw_creditlog $sqladd GROUP BY ctype,affect>0");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['isget']) {
				$sordb[$rt['ctype']]['add'] = $rt['sum'];
			} else {
				$sordb[$rt['ctype']]['reduce'] = $rt['sum'];
			}
			$sordb[$rt['ctype']]['sum'] += $rt['sum'];
		}
	}

	require_once PrintEot('creditlog');

} elseif ($action == 'backup' && (If_manager || $admin_gid == 3)) {

	if (empty($_POST['step'])) {

		$maxlg = 1000000;
		$clgtb = $maindb = array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_creditlog%'");
		while ($rt = $db->fetch_array($query)) {
			$rt['Data_length'] = round(($rt['Data_length']+$rt['Index_length'])/1048576,2);
			$key = str_replace($PW,'',$rt['Name']);
			$rs	 = $db->get_one("SELECT MAX(adddate) AS etime,MIN(adddate) AS stime FROM pw_{$key}");
			$rt['stime'] = get_date($rs['stime'],'Y-m-d');
			$rt['etime'] = get_date($rs['etime'],'Y-m-d');
			if ($key == 'creditlog') {
				$maindb = $rt;
			} else {
				$clgtb[$key] = $rt;
			}
		}
		require_once PrintEot('creditlog');

	} elseif ($_POST['step'] == '3') {

		$db->query("TRUNCATE TABLE pw_creditlog");

		adminmsg('operate_success');

	} elseif ($_POST['step'] == '5') {

		$t_a = array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_creditlog%'");
		while ($rt = $db->fetch_array($query)) {
			$key = (int)str_replace($PW.'creditlog','',$rt['Name']);
			$t_a[] = $key;
		}
		$t_k = 1;
		while (in_array($t_k,$t_a)) {
			$t_k++;
		}
		$CreatTable = $db->get_one("SHOW CREATE TABLE pw_creditlog");
		$sql = str_replace($CreatTable['Table'],'pw_creditlog',$CreatTable['Create Table']);
		$db->query("ALTER TABLE pw_creditlog RENAME pw_creditlog{$t_k}");
		$db->query($sql);

		adminmsg('operate_success');

	} elseif ($_POST['step'] == '7') {

		S::gp(array('selid'));

		if (empty($selid) || !is_array($selid)) {
			adminmsg('operate_error');
		}
		$clgtb = array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_creditlog%'");
		while ($rt = $db->fetch_array($query)) {
			$clgtb[] = str_replace($PW,'',$rt['Name']);
		}

		foreach ($selid as $key => $value) {
			if ($value <> 'creditlog' && in_array($value,$clgtb)) {
				$db->query("DROP TABLE pw_{$value}");
			}
		}

		adminmsg('operate_success');
	}
}
?>