<?php

!defined('P_W') && exit('Forbidden');
//api mode 6

class Credit {
	
	var $base;
	var $db;

	function Credit($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get() {
		return new ApiResponse(pwCreditNames());
	}

	function syncredit($arr) {
		if (is_array($arr)) {
			foreach ($arr as $uid => $setv) {
				$updateMemberData = array();
				foreach ($setv as $cid => $value) {
					if (is_numeric($cid)) {
						$value = intval($value);
						/**
						$this->db->pw_update(
							"SELECT uid FROM pw_membercredit WHERE uid=" . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cid),
							"UPDATE pw_membercredit SET value=" . S::sqlEscape($value) .  ' WHERE uid=' . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cid),
							"INSERT INTO pw_membercredit SET " . S::sqlSingle(array('uid' => $uid, 'cid' => $cid, 'value' => $value))
						);
						**/
						
						$this->db->pw_update(
							"SELECT uid FROM pw_membercredit WHERE uid=" . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cid),
							pwQuery::updateClause('pw_membercredit', 'uid=:uid AND cid=:cid', array($uid,$cid), array('value'=>$value)),
							pwQuery::insertClause('pw_membercredit', array('uid' => $uid, 'cid' => $cid, 'value' => $value))
						);						
					} elseif (in_array($cid, array('money','rvrc','credit','currency'))) {
						$cid == 'rvrc' && $value *= 10;
						$updateMemberData[$cid] = intval($value);
					}
				}
				if ($updateMemberData) {
					$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
					$userService->update($uid, array(), $updateMemberData);
				}
			}
		}
		return new ApiResponse(1);
	}

	function set($uid, $ctype, $value) {
		require_once(R_P . 'require/credit.php');
		$credit->set($uid, $ctype, $value);
		return new ApiResponse(true);
	}

	function getvalue($uid) {
		require_once(R_P.'require/credit.php');
		$getv = $credit->get($uid);
		$retv = array();
		foreach ($credit->cType as $key => $value) {
			$retv[$key] = array('title' => $value, 'value' => isset($getv[$key]) ? $getv[$key] : 0);
		}
		return new ApiResponse($retv);
	}
}
?>