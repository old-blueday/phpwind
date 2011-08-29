<?php
!defined('P_W') && exit('Forbidden');

/**
 * 用户注册、添加操作类
 * fix by sky_hold@163.com
 *
 */
class PW_Register {
	
	var $db;
	var $data;
	var $memberinfo;
	var $illegalChar;
	
	var $inv = array();
	var $uid;
	
	function PW_Register() {
		global $db;
		$this->db = & $db;
		$this->data = array(
			'username' => '',
			'password' => '',
			'email' => '',
			'safecv' => '',
			'yz' => 0,
			'groupid' => 0,
			'memberid' => 0,
			'userstatus' => 0
		);
		$this->memberinfo = array();
		$this->illegalChar = array(
			"\\",
			'&',
			' ',
			"'",
			'"',
			'/',
			'*',
			',',
			'<',
			'>',
			"\r",
			"\t",
			"\n",
			'#',
			'%',
			'?',
			'　'
		);
	}
	
	function setName($regname) {
		PW_Register::checkName($regname);
		$this->data['username'] = $regname;
	}
	
	function setPwd($regpwd, $regpwdrepeat) {
		PW_Register::checkPwd($regpwd, $regpwdrepeat);
		$this->data['password'] = md5($regpwd);
	}
	
	function setEmail($regemail) {
		PW_Register::checkEmail($regemail);
		$this->data['email'] = $regemail;
	}
	
	function setCustomfield($customfield) {
		global $value;
		$srcValue = $value;
		if (empty($customfield) || !is_array($customfield)) return;
		
		foreach ($customfield as $key => $value) {
			$field = "field_" . (int) $value['id'];
			$v = S::escapeChar(S::getGP($field, 'P'));
			if ($value['required'] && !$v) {
				Showmsg('field_empty');
			}
			if ($value['maxlen'] && strlen($v) > $value['maxlen']) {
				Showmsg('field_lenlimit');
			}
			$v && $this->memberinfo[$field] = $v;
		}
		$value = $srcValue;
	}
	
	function setCustomdata($customdata) {
		global $db_union;
		if (!is_array($db_union)) {
			$db_union = explode("\t", stripslashes($db_union));
		}
		$custominfo = unserialize($db_union[7]);
		if ($custominfo && $customdata) {
			foreach ($customdata as $key => $val) {
				$key = S::escapeChar($key);
				$customdata[stripslashes($key)] = stripslashes($val);
			}
			$this->memberinfo['customdata'] = serialize($customdata);
		}
	}
	
	function setReason($regreason) {
		if (L::reg('rg_ifcheck')) {
			!$regreason && Showmsg('reg_reason');
			$this->memberinfo['regreason'] = $regreason;
		}
	}
	
	function setSafecv($question, $customquest, $answer) {
		global $db_ifsafecv;
		if ($db_ifsafecv) {
			require_once (R_P . 'require/checkpass.php');
			$this->data['safecv'] = questcode($question, $customquest, $answer);
		}
	}
	
	function setField($field, $value) {
		$this->data[$field] = $value;
	}
	
	function setStatus($pos, $value = '1') {
		setstatus($this->data['userstatus'], $pos, $value);
	}
	
	function execute() {
		require_once (R_P . 'uc_client/uc_client.php');
		if (($winduid = uc_user_register($this->data['username'], $this->data['password'], $this->data['email'])) < 0) {
			switch ($winduid) {
				case -1:
					$errmsg = 'illegal_username';
					break;
				case -2:
					$errmsg = 'username_same';
					break;
				case -3:
					$errmsg = 'illegal_email';
					break;
				case -4:
					$errmsg = 'reg_email_have_same';
					break;
				default:
					$errmsg = 'undefined_action';
			}
			Showmsg($errmsg);
		}
		$this->uid = $winduid;
		
		$this->_setMemberid();
		$this->_setGroupid();
		$this->_setEmailCheck();
		$this->_insertUser();
	}
	
	function appendUser($uid, $username, $md5_pwd, $email) {
		$this->uid = $uid;
		$this->data['username'] = $username;
		$this->data['password'] = $md5_pwd;
		$this->data['email'] = $email;
		//$this->data['safecv']	= $safecv;

		$this->_setMemberid();
		$this->_setGroupid();
		$this->_setEmailCheck();
		$this->_insertUser();
	}
	
	function changeEmail($uid,$newEmail){
		$uid = intval($uid);
		$newEmail = trim($newEmail);
		if(!$uid || !$newEmail) return false;
		PW_Register::checkEmail($newEmail);
		return pwQuery::update('pw_members' , "uid=:uid", array($uid), array('email'=>$newEmail));
	}
	
	function _insertUser() {
		global $timestamp, $onlineip, $credit;
		/**
		$pwSQL = S::sqlSingle(array(
			'uid' => $this->uid,
			'username' => $this->data['username'],
			'password' => $this->data['password'],
			'safecv' => $this->data['safecv'],
			'email' => $this->data['email'],
			'groupid' => $this->data['groupid'],
			'memberid' => $this->data['memberid'],
			'regdate' => $timestamp,
			'yz' => $this->data['yz'],
			'userstatus' => $this->data['userstatus'],
			'newpm' => 0
		));
		$this->db->update("REPLACE INTO pw_members SET $pwSQL");
		**/
		$pwSQL = array(
			'uid' => $this->uid,
			'username' => $this->data['username'],
			'password' => $this->data['password'],
			'safecv' => $this->data['safecv'],
			'email' => $this->data['email'],
			'groupid' => $this->data['groupid'],
			'memberid' => $this->data['memberid'],
			'regdate' => $timestamp,
			'yz' => $this->data['yz'],
			'userstatus' => $this->data['userstatus'],
			'newpm' => 0
		);
		pwQuery::replace('pw_members', $pwSQL);
		/**
		$pwSQL = S::sqlSingle(array(
			'uid' => $this->uid,
			'postnum' => 0,
			'lastvisit' => $timestamp,
			'thisvisit' => $timestamp,
			'onlineip' => $onlineip
		));
		$this->db->pw_update(
			'SELECT uid FROM pw_memberdata WHERE uid=' . S::sqlEscape($this->uid),
			'UPDATE pw_memberdata SET ' . $pwSQL . ' WHERE uid=' . S::sqlEscape($this->uid),
			'INSERT INTO pw_memberdata SET ' . $pwSQL
		);
		**/
		$pwSQL = array(
			'uid' => $this->uid,
			'postnum' => 0,
			'lastvisit' => $timestamp,
			'thisvisit' => $timestamp,
			'onlineip' => $onlineip
		);
		$this->db->pw_update(
			'SELECT uid FROM pw_memberdata WHERE uid=' . S::sqlEscape($this->uid),
			pwQuery::updateClause('pw_memberdata', 'uid =:uid', array($this->uid), $pwSQL),
			pwQuery::insertClause('pw_memberdata', $pwSQL)
		);
		
		//$this->db->update("REPLACE INTO pw_memberdata SET $pwSQL");

		require_once (R_P . 'require/credit.php');
		$credit->addLog('reg_register', L::reg('rg_regcredit'), array(
			'uid' => $this->uid,
			'username' => stripslashes($this->data['username']),
			'ip' => $onlineip
		));
		$credit->sets($this->uid, L::reg('rg_regcredit'), false);
		$credit->runsql();
		
		//* $this->db->update("UPDATE pw_bbsinfo SET newmember=" . S::sqlEscape($this->data['username']) . ",totalmember=totalmember+1 WHERE id='1'");
		$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET newmember=:newmember,totalmember=totalmember+1 WHERE id=:id", array('pw_bbsinfo',$this->data['username'],1)));
		$this->memberinfo or $this->memberinfo['uid'] = $this->uid;
		if ($this->memberinfo) {
			/**
			$this->db->update("REPLACE INTO pw_memberinfo SET uid=" . S::sqlEscape($this->uid) . ',' . S::sqlSingle($this->memberinfo));
			**/
			$_temp = array('uid' => $this->uid) + $this->memberinfo;
			pwQuery::replace('pw_memberinfo', $_temp);
		}

		$statistics = L::loadClass('Statistics', 'datanalyse');
		$statistics->register();
	}
	
	function getRegUser() {
		return array(
			$this->uid,
			$this->data['yz'],
			$this->data['safecv']
		);
	}
	
	function _setMemberid() {
		/*
		$lneed = L::config('lneed', 'level');
		@asort($lneed);
		$this->data['memberid'] = key($lneed);
		*/
		global $db_upgrade;
		is_array($upgradeset = unserialize($db_upgrade)) || $upgradeset = array();
		$usercredit = L::reg('rg_regcredit');
		isset($usercredit['rvrc']) && $usercredit['rvrc'] *= 10;
		require_once(R_P.'require/functions.php');
		$this->data['memberid'] = getmemberid(CalculateCredit($usercredit, $upgradeset));
	}
	
	function _setGroupid() {
		if ($this->data['groupid'] == 0) {
			$this->data['groupid'] = L::reg('rg_ifcheck') ? 7 : -1;
		}
	}
	
	function _setEmailCheck() {
		if ($this->data['yz'] == 0) {
			$this->data['yz'] = L::reg('rg_emailcheck') ? num_rand(9) : 1;
		}
	}
	
	function checkInv($invcode) {
		global $timestamp;
		if (empty($invcode)) {
			Showmsg('invcode_empty');
		}
		$inv_days = L::config('inv_days', 'inv_config') * 86400;
		$this->inv = $this->db->get_one("SELECT id,uid FROM pw_invitecode WHERE invcode=" . S::sqlEscape($invcode) . " AND ifused<'1' AND createtime>" . S::sqlEscape($timestamp - $inv_days));
		!$this->inv && Showmsg('illegal_invcode');
	}
	
	function disposeInv() {
		global $timestamp;
		$this->db->update("UPDATE pw_invitecode SET " . S::sqlSingle(array(
			'receiver' => $this->data['username'],
			'usetime' => $timestamp,
			'ifused' => 1
		)) . ' WHERE id=' . S::sqlEscape($this->inv['id']));
		if ($this->inv['uid'] == 0) {
			$this->db->update("UPDATE pw_clientorder SET uid=" . S::sqlEscape($this->uid) . " WHERE type='4' AND uid='0' AND paycredit=" . S::sqlEscape($this->inv['id']));
		}
	}
	
	/**
	 * @static
	 */
	function checkName($regname) {
		if (!PW_Register::checkNameLen(strlen($regname))) {
			Showmsg('reg_username_limit');
		}
		if (str_replace($this->illegalChar, '', $regname) != $regname) {
			Showmsg('illegal_username');
		}
		if (!L::reg('rg_rglower') && !PW_Register::checkRglower($regname)) {
			Showmsg('username_limit');
		}
		if (S::inArray(strtolower($regname), array(
			'guest',
			'system'
		))) {
			Showmsg('illegal_username');
		}
		$banname = explode(',', L::reg('rg_banname'));
		foreach ($banname as $key => $value) {
			if ($value !== '' && strpos($regname, $value) !== false) {
				Showmsg('illegal_username');
			}
		}
		require_once (R_P . 'require/functions.php');
		if ($regname !== Sql_cv($regname)) {
			Showmsg('illegal_username');
		}
	}
	
	/**
	 * @static
	 */
	function checkNameLen($len) {
		list($regminname, $regmaxname) = explode("\t", L::reg('rg_namelen'));
		return ($len >= $regminname && $len <= $regmaxname);
	}
	
	/**
	 * @static
	 */
	function checkPwd($regpwd, $regpwdrepeat) {
		list($regminpwd, $regmaxpwd) = explode("\t", L::reg('rg_pwdlen'));
		if (strlen($regpwd) < $regminpwd) {
			Showmsg('reg_password_minlimit',$GLOBALS['showPwdLogin']);
		} elseif ($regmaxpwd && strlen($regpwd) > $regmaxpwd) {
			Showmsg('reg_password_maxlimit',$GLOBALS['showPwdLogin']);
		}
		if (str_replace($this->illegalChar, '', $regpwd) != $regpwd) {
			Showmsg('illegal_password',$GLOBALS['showPwdLogin']);
		}
		if ($regpwd != $regpwdrepeat) {
			Showmsg('password_confirm',$GLOBALS['showPwdLogin']);
		}
		$this->checkPwdComplex($regpwd);
	}
	
	/**
	 * @static
	 */
	function checkPwdComplex($regpwd) {
		if (!L::reg('rg_pwdcomplex')) return;
		foreach (explode(',', L::reg('rg_pwdcomplex')) as $key => $value) {
			switch (intval($value)) {
				case 1:
					if (!preg_match('/[a-z]/', $regpwd)) {
						Showmsg('reg_password_lowstring',$GLOBALS['showPwdLogin']);
					}
					break;
				case 2:
					if (!preg_match('/[A-Z]/', $regpwd)) {
						Showmsg('reg_password_upstring',$GLOBALS['showPwdLogin']);
					}
					break;
				case 3:
					if (!preg_match('/[0-9]/', $regpwd)) {
						Showmsg('reg_password_num',$GLOBALS['showPwdLogin']);
					}
					break;
				case 4:
					if (!preg_match('/[^a-zA-Z0-9]/', $regpwd)) {
						Showmsg('reg_password_specialstring',$GLOBALS['showPwdLogin']);
					}
					break;
			}
		}
	}
	
	/**
	 * @static
	 */
	function checkSameNP($regpwd, $regname) {
		if (L::reg('rg_npdifferf') && $regpwd == $regname) {
			Showmsg('reg_nameuptopwd');
		}
	}
	
	/**
	 * @static
	 */
	function checkEmail($regemail) {
		if (empty($regemail) || !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$", $regemail)) {
			Showmsg('illegal_email');
		}
		if (L::reg('rg_emailtype') == 1 && L::reg('rg_email')) {
			
			$e_check = 0;
			$e_limit = explode(',', L::reg('rg_email'));
			foreach ($e_limit as $key => $val) {
				if (strpos($regemail, "@" . $val) !== false) {
					$e_check = 1;
					break;
				}
			}
			$e_check == 0 && Showmsg('email_check');
		} elseif (L::reg('rg_emailtype') == 2 && L::reg('rg_banemail')) {
			$e_check = 0;
			$e_limit = explode(',', L::reg('rg_banemail'));
			foreach ($e_limit as $key => $val) {
				if (strpos($regemail, "@" . $val) !== false) {
					$e_check = 1;
					break;
				}
			}
			$e_check == 1 && Showmsg('email_bancheck');
		}
		$email_check = $this->db->get_one('SELECT COUNT(*) AS count FROM pw_members WHERE email=' . S::sqlEscape($regemail));
		if ($email_check['count']) {
			Showmsg('reg_email_have_same');
		}
	}
	
	/**
	 * @static
	 */
	function checkRglower($username) {
		global $db_charset;
		
		$namelen = strlen($username);
		for ($i = 0; $i < $namelen; $i++) {
			if (ord($username[$i]) > 127) {
				$i += 'utf-8' != $db_charset ? 1 : 2;
			} else {
				if (ord($username[$i]) >= 65 && ord($username[$i]) <= 90) {
					return false;
				}
			}
		}
		return true;
	}
}
?>