<?php
define('AJAX', '1');
require_once ('global.php');
require_once (R_P . 'require/functions.php');
S::gp(array(
	'action'
));

if (!$windid && !in_array($action, array(
	'tag',
	'login',
	'showface',
	'showsmile',
	'getverify',
	'showcard',
	'asearch',
	'poplogin',
	'pingpage',
	'jobpop',
	'showrandcolonys',
	'activity',
	'showsearcherimg',
	'changeuskin',
	'showsearcherimg',
	'pwschools'
))) {
	Showmsg('not_login');
}

$whiteActions = array(
	'leaveword',
	'favor',
	'tag',
	'relatetag',
	'deldownfile',
	'setcover',
	'draft',
	'msg',
	'usetool',
	'usertool',
	'dig',
	'extend',
	'sharelink',
	'showface',
	'newrp',
	'delnewrp',
	'addfriend',
	'deletefriend',
	'showsmile',
	'honor',
	'readlog',
	'threadlog',
	'mgmode',
	'setstyle',
	'mforder',
	'mfsetname',
	'shortcut',
	'pushto',
	'debate',
	'mutiatt',
	'delmutiatt',
	'delmutiattone',
	'playatt',
	'getverify',
	'changestate',
	'changeeditor',
	'pwb_friend',
	'pwb_message',
	'readmessage',
	'unread',
	'showbottom',
	'report',
	'showfriends',
	'showcard',
	'addfriendtype',
	'eidtfriendtype',
	'delfriendtype',
	'delfriend',
	'setfriendtype',
	'addattention',
	'delattention',
	'asearch',
	'pcjoin',
	'pcmember',
	'pcdel',
	'pcshow',
	'pcpay',
	'pcmodify',
	'pcalipay',
	'pcsendmsg',
	'pcifalipay',
	'pcdelimg',
	'poplogin',
	'pingpage',
	'delpinglog',
	'clearpinglog',
	'savepinglog',
	'doclearpinglog',
	'showuserbinding',
	'switchuser',
	'jobpop',
	'delspacelogo',
	'spacelayout',
	'shareurl',
	'uploadwritepic',
	'friendinvite',
	'showrandcolonys',
	'activity',
	'commend',
	'changewidthcfg',
	'changesidebar',
	'readfloor',
	'collectiontype',
	'deltag',
	'auth',
	'showsearcherimg',
	'pwschools',
	'changeuskin'
);
if (in_array($action, $whiteActions)) {
	require S::escapePath(R_P . 'actions/ajax/' . $action . '.php');
} else {
	Showmsg('undefined_action');
}




function getfavor($tids) {
	$tids = explode('|', $tids);
	$tiddb = array();
	foreach ($tids as $key => $t) {
		if ($t) {
			$v = explode(',', $t);
			foreach ($v as $k => $v1) {
				$tiddb[$key][$v1] = $v1;
			}
		}
	}
	return $tiddb;
}
function makefavor($tiddb) {
	$newtids = $ex = '';
	$k = 0;
	ksort($tiddb);
	foreach ($tiddb as $key => $val) {
		$new_tids = '';
		rsort($val);
		if ($key != $k) {
			$s = $key - $k;
			for ($i = 0; $i < $s; $i++) {
				$newtids .= '|';
			}
		}
		foreach ($val as $k => $v) {
			is_numeric($v) && $new_tids .= $new_tids ? ',' . $v : $v;
		}
		$newtids .= $ex . $new_tids;
		$k = $key + 1;
		$ex = '|';
	}
	return $newtids;
}

function getNum($fid) {
	if ($forum[$fid]['type'] = 'category') {
		$fidnum = 1;
	} elseif ($forum[$fid]['type'] = 'forum') {
		$fidnum = 2;
	} elseif ($forum[$fid]['type'] = 'sub') {
		$fup = $forum[$fid]['fup'];
		if ($forum[$fup]['type'] = 'forum') {
			$fidnum = 3;
		} else {
			$fidnum = 4;
		}
	}
	return $fidnum;
}

function addSingleFriend($updatemem, $winduid, $frienduid, $timestamp, $status, $friendtype = 0, $checkmsg = '') {
	global $db;	
	$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */
	if ($isAttention = $attentionService->isFollow($winduid, $frienduid)) {
		$db->update("UPDATE pw_friends SET status = ".S::sqlEscape($attentionService->_s_new_friend)." WHERE uid=".S::sqlEscape($winduid)." AND friendid=".S::sqlEscape($frienduid));
	} else {
		$pwSQL = S::sqlSingle(array(
						'uid' => $winduid,
						'friendid' => $frienduid,
						'joindate' => $timestamp,
						'status' => $status,
						'descrip' => $checkmsg,
						'ftid' => $friendtype
		));
		$db->update("INSERT INTO pw_friends SET $pwSQL");
	}
	if ($updatemem) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->updateByIncrement($winduid, array(), array('f_num' => 1));
	}
}
?>