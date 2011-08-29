<?php
!function_exists('adminmsg') && exit('Forbidden');

if ($uc_server != 1) {
	$db_adminrecord = 0;
	$basename = "javascript:parent.closeAdminTab(window);";
	adminmsg('uc_server_set');
}
require_once(R_P . 'uc_client/class_core.php');
$uc = new UC();
$credittype	= pwCreditNames();

if (empty($action)) {
	
	$ucApp		= $uc->load('app');
	$applist	= $ucApp->applist();

	include PrintEot('uccredit');exit;

} elseif ($action == 'create') {
	
	$ucApp		= $uc->load('app');
	$applist	= $ucApp->applist();

	if (empty($_POST['step'])) {
		
		$setv = '';
		include PrintEot('uccredit');exit;

	} else {

		S::gp(array('cid','ctype'));
		$basename .= '&action=create';
		
		if (!$cid || !isset($credittype[$cid])) {
			adminmsg('uc_cname_empty');
		}
		!$uc_syncredit && $uc_syncredit = array();

		if (isset($uc_syncredit[$cid])) {
			adminmsg('uc_syncredit_is_exists');
		}
		foreach ($ctype as $key => $value) {
			if ($value && isset($applist[$key])) {
				if (isCreditSet($uc_syncredit, $key, $value)) {
					$appName = $applist[$key]['name'];
					$cName = $uc_credits[$key][$value];
					adminmsg('uc_appcredit_is_exists');
				}
				$uc_syncredit[$cid][$key] = $value;
			}
		}
		setConfig('uc_syncredit', $uc_syncredit);
		updatecache_c();

		$notify = $uc->load('notify');
		$nid = $notify->add('updatesyncredit', array('syncredit' => $uc_syncredit));
		$notify->send_by_id($nid);

		adminmsg('operate_success');
	}
} elseif ($action == 'edit') {

	S::gp(array('cid'));
	if (!$cid || !isset($credittype[$cid]) || !isset($uc_syncredit[$cid])) {
		adminmsg('undefined_action');
	}
	$myApp   = $uc->load('app');
	$applist = $myApp->applist();

	if (empty($_POST['step'])) {
		
		$credittype = array($cid => $credittype[$cid]);
		$setv = "'cid' : '$cid'";
		foreach ($uc_syncredit[$cid] as $key => $value) {
			$setv .= ",'cid$key' : '$value'";
		}
		include PrintEot('uccredit');exit;

	} else {
		
		S::gp(array('cid','ctype'));
	
		$uc_syncredit[$cid] = array();
		foreach ($ctype as $key => $value) {
			if ($value && isset($applist[$key])) {
				if (isCreditSet($uc_syncredit, $key, $value)) {
					$appName = $applist[$key]['name'];
					$cName = $uc_credits[$key][$value];
					adminmsg('uc_appcredit_is_exists');
				}
				$uc_syncredit[$cid][$key] = $value;
			}
		}
		setConfig('uc_syncredit', $uc_syncredit);
		updatecache_c();
		
		$notify = $uc->load('notify');
		$nid = $notify->add('updatesyncredit', array('syncredit' => $uc_syncredit));
		$notify->send_by_id($nid);

		adminmsg('operate_success');
	}
} elseif ($action == 'setcredit') {
	
	$basename .= '&action=create';
	$ucApp = $uc->load('app');
	$applist = $ucApp->applist();
	$credits = array();
	foreach ($applist as $key => $value) {
		if ($value['id'] == $uc_appid)
			continue;
		$resp = $ucApp->ucfopen($value['siteurl'], $value['interface'], $value['secretkey'], 'Credit', 'get');
		if (isset($resp['result'])) {
			$credits[$key] = $resp['result'];
		}
	}
	setConfig('uc_credits', $credits, 'array');
	updatecache_c();

	adminmsg('operate_success');

} elseif ($action == 'del') {
	
	S::gp(array('cid'));
	if (!$cid || !isset($credittype[$cid]) || !isset($uc_syncredit[$cid])) {
		adminmsg('undefined_action');
	}
	$ucApp		= $uc->load('app');
	$applist	= $ucApp->applist();

	if (empty($_POST['step'])) {
		
		include PrintEot('uccredit');exit;

	} else {
		
		unset($uc_syncredit[$cid]);
		setConfig('uc_syncredit', $uc_syncredit);
		updatecache_c();

		$notify = $uc->load('notify');
		$nid = $notify->add('updatesyncredit', array('syncredit' => $uc_syncredit));
		$notify->send_by_id($nid);

		adminmsg('operate_success');
	}
}

function isCreditSet($syncredit, $appid, $credit) {
	foreach ($syncredit as $key => $value) {
		if (isset($value[$appid]) && $value[$appid] == $credit) {
			return true;
		}
	}
	return false;
}
?>