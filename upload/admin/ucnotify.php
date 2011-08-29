<?php
!function_exists('adminmsg') && exit('Forbidden');

if ($uc_server != 1) {
	$db_adminrecord = 0;
	$basename = "javascript:parent.closeAdminTab(window);";
	adminmsg('uc_server_set');
}

if (empty($action)) {
	
	require_once(R_P . 'uc_client/class_core.php');
	$uc = new UC();
	$myApp = $uc->load('app');
	$applist = $myApp->applist();
	
	$page < 1 && $page = 1;
	$total = $db->get_one("SELECT COUNT(*) AS sum FROM pw_ucnotify");
	$limit = S::sqlLimit($page - 1, 20);

	$notifydb = array();
	$query = $db->query("SELECT * FROM pw_ucnotify ORDER BY nid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['timestamp'] = get_date($rt['timestamp']);
		$notifydb[] = $rt;
	}

	include PrintEot('ucnotify');exit;

} elseif ($action == 'send') {

	S::gp(array('nid', 'appid'));
	
	if ($nid && $appid) {
		require_once(R_P . 'uc_client/class_core.php');
		$uc = new UC();
		$notify = $uc->load('notify');
		$notify->send($nid, $appid);
	}

	adminmsg('operate_success');

} elseif ($action == 'del') {

	S::gp(array('selid'));

	if ($selid) {
		$db->update("DELETE FROM pw_ucnotify WHERE nid IN(" . S::sqlImplode($selid) . ')');
	}
	adminmsg('operate_success');

} elseif ($action == 'syncredit') {

	if (empty($_POST['step'])) {
		
		require_once(R_P . 'uc_client/class_core.php');
		$uc = new UC();
		$myApp = $uc->load('app');
		$applist = $myApp->applist();

		$page < 1 && $page = 1;
		$total = $db->get_one("SELECT COUNT(*) AS sum FROM pw_ucsyncredit");
		$limit = S::sqlLimit($page - 1, 20);

		$creditdb = array();
		$query = $db->query("SELECT u.*,m.username FROM pw_ucsyncredit u LEFT JOIN pw_members m ON u.uid=m.uid $limit");
		while ($rt = $db->fetch_array($query)) {
			$creditdb[] = $rt;
		}

		include PrintEot('ucnotify');exit;

	} else {

		S::gp(array('selid'));
		
		$basename .= '&action=syncredit';
		if ($selid) {
			$db->update("DELETE FROM pw_ucsyncredit WHERE uid IN(" . S::sqlImplode($selid) . ')');
		}
		adminmsg('operate_success');

	}
} elseif ($action == 'synupdate') {

	S::gp(array('uid', 'appid'));

	if ($uid && $appid) {

		require_once(R_P . 'uc_client/class_core.php');
		$uc = new UC();
		$myCredit = $uc->load('credit');
		$myCredit->synupdate($appid, array($uid));

	}
	$basename .= '&action=syncredit';

	adminmsg('operate_success');
}
?>