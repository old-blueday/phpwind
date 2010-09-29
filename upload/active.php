<?php
require_once('global.php');

!$winduid && Showmsg('not_login');
InitGP(array('action'));
(!$action || !$tid) && Showmsg('undefined_action');

if ($action == 'apply') {

	$sql = $sqlsel = '';
	if (!$_POST['step']) {
		$pw_tmsgs = GetTtable($tid);
		$sql      = " LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid";
		$sqlsel   = ",tm.ifconvert,tm.content";
	}
	$act = $db->get_one('SELECT a.*,t.authorid,t.subject as tsubject ' . $sqlsel
		. ' FROM pw_activity a LEFT JOIN pw_threads t ON t.tid=a.tid ' . $sql
		. ' WHERE a.tid=' . pwEscape($tid));
	!$act && Showmsg('data_error');
	$act['deadline']<$timestamp && Showmsg('time_out');
	if ($act['num']) {
		@extract($db->get_one('SELECT COUNT(*) AS count FROM pw_actmember WHERE actid='.pwEscape($tid).' AND state=1'));
		$count >= $act['num'] && Showmsg('num_full');
	}
	if ($act['sexneed']) {
		$member = $db->get_one('SELECT gender FROM pw_members WHERE uid='.pwEscape($winduid));
		$member['gender'] != $act['sexneed'] && Showmsg('apply_gender_error');
	}
	$rt = $db->get_one('SELECT state FROM pw_actmember WHERE winduid='.pwEscape($winduid).' AND actid='.pwEscape($tid));
	$rt && Showmsg('have_act');

	if (empty($_POST['step'])) {

		require_once(R_P.'require/header.php');
		require_once(R_P.'require/bbscode.php');
		$read = array();
		$act['strendtime'] = $act['endtime'];
		$act['starttime'] = get_date($act['starttime'],'Y-m-d');
		$act['endtime']   = get_date($act['endtime'],'Y-m-d');
		$act['deadline']  = get_date($act['deadline'],'Y-m-d');
		$act['content']   = str_replace("\n","<br />",$act['content']);
		$act['ifconvert']==2 && $act['content'] = convert($act['content'],$db_windpost,2);

		$query = $db->query('SELECT COUNT(*) AS count,state FROM pw_actmember WHERE actid='.pwEscape($tid).' GROUP BY state');
		$act_total = $act_y = 0;
		while ($rt = $db->fetch_array($query)) {
			$act_total += $rt['count'];
			$rt['state'] == 1 && $act_y += $rt['count'];
		}
		require_once PrintEot('active');footer();

	} else {

		PostCheck();
		InitGP(array('contact','message'),'P');
		!$contact && Showmsg('contact_empty');
		$state = $act['admin']==$winduid ? 1 : 0;
		$pwSQL = pwSqlSingle(array(
			'actid'		=> $tid,
			'winduid'	=> $winduid,
			'state'		=> $state,
			'applydate'	=> $timestamp,
			'contact'	=> $contact,
			'message'	=> $message
		));
		$db->update('INSERT INTO pw_actmember SET '.$pwSQL);
		refreshto("read.php?tid=$tid&fpage=$fpage",'operate_success');
	}

} elseif ($action == 'view') {

	require_once(R_P.'require/header.php');
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/bbscode.php');
	InitGP(array('page'),'GP',2);
	$pw_tmsgs = GetTtable($tid);
	$act = $db->get_one('SELECT a.*,t.authorid,t.subject as tsubject,tm.ifconvert,tm.content FROM pw_activity a LEFT JOIN pw_threads t ON a.tid=t.tid LEFT JOIN ' . $pw_tmsgs . ' tm ON a.tid=tm.tid WHERE a.tid='.pwEscape($tid));
	!$act && Showmsg('data_error');
	$admincheck = $act['admin'] == $winduid ? 1 : 0;

	if (!$admincheck) {
		$ifact = $db->get_one('SELECT * FROM pw_actmember WHERE actid='.pwEscape($tid).' AND winduid='.pwEscape($winduid).' AND state=1');
		!$ifact && Showmsg("actid_view_error");
	}
	$act['strendtime'] = $act['endtime'];
	$act['starttime'] = get_date($act['starttime'],'Y-m-d H:i');
	$act['endtime']   = get_date($act['endtime'],'Y-m-d H:i');
	$act['deadline']  = get_date($act['deadline'],'Y-m-d H:i');
	$act['content']   = str_replace("\n","<br />",$act['content']);
	$act['ifconvert']==2 && $act['content'] = convert($act['content'],$db_windpost,2);

	if ($admincheck) {
		$sql = '';
	} else {
		$sql = "AND a.state='1'";
	}
	$query = $db->query('SELECT COUNT(*) AS count,state FROM pw_actmember WHERE actid='.pwEscape($tid).' GROUP BY state');
	$act_total = $act_y = 0;
	while ($rt = $db->fetch_array($query)) {
		$act_total += $rt['count'];
		$rt['state'] == 1 && $act_y += $rt['count'];
	}
	$act_show = $admincheck ? $act_total : $act_y;
	$db_showperpage = 30;
	$page<1 && $page = 1;
	$limit = pwLimit(($page-1)*$db_showperpage,$db_showperpage);
	$pages = numofpage($act_show,$page,ceil($act_show/$db_showperpage),"active.php?action=view&tid=$tid&");

	$actdb = array();
	$query = $db->query("SELECT a.*,m.username,m.gender FROM pw_actmember a LEFT JOIN pw_members m ON a.winduid=m.uid WHERE a.actid=".pwEscape($tid)." $sql ORDER BY applydate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['applydate'] = get_date($rt['applydate']);
		$actdb[] = $rt;
	}
	require_once PrintEot('active');footer();

} elseif ($action == 'pass' || $action == 'unpass') {

	PostCheck();
	InitGP(array('selid'),'P');
	$selids = array();
	foreach ($selid as $key => $val) {
		if (is_numeric($val)) {
			$selids[] = $val;
		}
	}
	!$selids && Showmsg('selid_illegal');

	$read = $db->get_one('SELECT admin FROM pw_activity WHERE tid='.pwEscape($tid));
	!$read && Showmsg('data_error');
	$read['admin'] != $winduid && Showmsg('active_manager_right');
	$state = $action=='pass' ? 1 : 2;
	$db->update('UPDATE pw_actmember'
			. ' SET state='.pwEscape($state)
			. ' WHERE actid='.pwEscape($tid)
			. ' AND id IN('.pwImplode($selids).')');
	refreshto("active.php?action=view&tid=$tid",'operate_success');

} elseif ($action == 'exit') {

	PostCheck();
	$db->update('DELETE FROM pw_actmember WHERE actid='.pwEscape($tid).' AND winduid='.pwEscape($winduid));
	refreshto("read.php?tid=$tid",'operate_success');

} elseif ($action == 'cancle') {

	PostCheck();
	$read = $db->get_one('SELECT admin FROM pw_activity WHERE tid='.pwEscape($tid));
	!$read && Showmsg('data_error');
	$read['admin'] != $winduid && Showmsg('active_manager_right');
	$db->update('DELETE FROM pw_activity WHERE tid='.pwEscape($tid));
	$db->update('DELETE FROM pw_actmember WHERE actid='.pwEscape($tid));
	$db->update('UPDATE pw_threads SET special=0 WHERE tid='.pwEscape($tid));
	refreshto("read.php?tid=$tid",'operate_success');
}
?>