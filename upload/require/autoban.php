<?php
!function_exists('readover') && exit('Forbidden');

function autoban($uid) {
	global $db,$db_banby,$db_banmax,$db_bantype,$db_banlimit,$timestamp;
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->get($uid, true, true);
	if ($rt['groupid'] == '-1' || $rt['groupid'] == '6') {
		switch ($db_banby) {
			case 1:$banby = $rt['postnum']; break;
			case 2:$banby = $rt['rvrc']/10;break;
			case 3:$banby = $rt['money'];break;
			default:$banby = $rt['postnum'];
		}
		if ($rt['groupid'] == '-1') {
			if ($banby < $db_banmax) {
				$userService->update($uid, array('groupid' => 6));
				$pwSQL = S::sqlSingle(array(
					'uid'		=> $uid,
					'fid'		=> 0,
					'type'		=> $db_bantype,
					'startdate'	=> $timestamp,
					'days'		=> $db_banlimit,
					'admin'		=> 'autoban',
					'reason'	=> ''
				));
				$db->update("REPLACE INTO pw_banuser SET $pwSQL");
			}
		} elseif ($banby >= $db_banmax) {
			$bandb = $db->get_one("SELECT id FROM pw_banuser WHERE uid=".S::sqlEscape($uid)." AND fid='0'");
			if (!$bandb) {
				$userService->update($uid, array('groupid' => -1));
			} elseif ($bandb['type'] == 1 && $timestamp-$bandb['startdate']>$bandb['days']*86400) {
				$userService->update($uid, array('groupid' => -1));
				$db->update("DELETE FROM pw_banuser WHERE id=".S::sqlEscape($bandb['id']));
			}
		}
		//* $_cache = getDatastore();
		//* $_cache->delete('UID_'.$uid);
	}
}
?>