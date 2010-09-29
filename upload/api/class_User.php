<?php

!defined('P_W') && exit('Forbidden');
//api mode 1

define('API_USER_USERNAME_NOT_UNIQUE', 100);

class User {

	var $base;
	var $db;

	function User($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function getInfo($uids, $fields = array()) {
		if (!$uids) {
			return new ApiResponse(false);
		}
		require_once(R_P.'require/showimg.php');

		$uids = is_numeric($uids) ? array($uids) : explode(",",$uids);

		if (!$fields) $fields = array('uid', 'username', 'icon', 'gender', 'location', 'bday');
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$users = array();
		foreach ($userService->getByUserIds($uids) as $rt) {
			list($rt['icon']) = showfacedesign($rt['icon'], 1, 'm');
			$rt_a = array();
			foreach ($fields as $field) {
				if (isset($rt[$field])) {
					$rt_a[$field] = $rt[$field];
				}
			}
			$users[$rt['uid']] = $rt_a;
		}
		return new ApiResponse($users);
	}

	function alterName($uid, $newname, $oldname) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userName = $userService->getUserNameByUserId($uid);
		if (!$userName || $userName == $newname) {
			return new ApiResponse(1);
		}
		$existUserId = $userService->getUserIdByUserName($newname);
		if ($existUserId) {
			return new ApiResponse(API_USER_USERNAME_NOT_UNIQUE);
		}
		$userService->update($uid, array('username' => $newname));

		$user = L::loadClass('ucuser', 'user');
		$user->alterName($uid, $userName, $newname);

		return new ApiResponse(1);
	}

	function deluser($uids) {
		$user = L::loadClass('ucuser', 'user');
		$user->delUserByIds($uids);

		return new ApiResponse(1);
	}

	function synlogin($user) {
		global $timestamp,$uc_key;
		list($winduid, $windid, $windpwd) = explode("\t", $this->base->strcode($user, false));
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$safecv = '';
		$cktime = 31536000;
		$cktime != 0 && $cktime += $timestamp;
		Cookie("winduser",StrCode($winduid."\t".PwdCode($windpwd)."\t".$safecv),$cktime);
		Cookie("ucuser",StrCode($winduid."\t".md5($uc_key.$windpwd)),$cktime);
		Cookie('lastvisit','',0);

		return '';
	}

	function synlogout() {
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		Cookie('winduser',' ',0);
		Cookie('hideid','',0);
		Cookie('lastvisit','',0);
		Cookie('ck_info','',0);
		Cookie('msghide','',0,false);
		Cookie("ucuser",'',0);
		return '';
	}
    function getusergroup() {
        $usergroup = array();
        $query = $this->db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups ");
        while($rt= $this->db->fetch_array($query)) {
            $usergroup[$rt['gid']] = $rt;
        }
        return new ApiResponse($usergroup);
    }
}
?>