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
						$this->db->pw_update(
							"SELECT uid FROM pw_membercredit WHERE uid=" . pwEscape($uid) . ' AND cid=' . pwEscape($cid),
							"UPDATE pw_membercredit SET value=" . pwEscape($value) .  ' WHERE uid=' . pwEscape($uid) . ' AND cid=' . pwEscape($cid),
							"INSERT INTO pw_membercredit SET " . pwSqlSingle(array('uid' => $uid, 'cid' => $cid, 'value' => $value))
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