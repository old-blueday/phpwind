<?php

class usermodel {

	var $base;
	var $db;
	var $user;

	function __construct(&$base) {
		$this->usermodel($base);
	}

	function usermodel(&$base) {
		$this->base =& $base;
		$this->db =& $base->db;
	}

	function get_by_uid($uid) {
		$arr = $this->db->get_one("SELECT uid,username,password,safecv,email,regdate FROM pw_members WHERE uid=" . UC::escape($uid));
		return $arr;
	}

	function get_by_username($username) {
		$arr = $this->db->get_one("SELECT uid,username,password,safecv,email,regdate FROM pw_members WHERE username=" . UC::escape($username));
		return $arr;
	}

	function get_by_email($email, $unique = true) {
		$query = $this->db->query("SELECT uid,username,password,safecv,email,regdate FROM pw_members WHERE email=" . UC::escape($email));
		$num = $this->db->num_rows($query);
		if ($unique) {
			$arr = $this->db->fetch_array($query);
		} else {
			$arr = array();
			while ($rt = $this->db->fetch_array($query)) {
				$arr[] = $rt;
			}
		}
		return array($arr, $num);
	}

	function check_email($email,$username) {
		$ucsql = $username ? " AND username<>" . UC::escape($username) : '';
		$count = $this->db->get_value("SELECT COUNT(*) AS sum FROM pw_members WHERE email=" . UC::escape($email) . $ucsql);
		return $count;
	}

	function add($username, $pwd, $email) {
		$this->db->update("INSERT INTO pw_members SET " . UC::sqlSingle(array(
			'username'	=> $username,
			'password'	=> $pwd,
			'email'		=> $email,
			'groupid'	=> 0,
			'regdate'	=> $this->base->time
		)));
		$uid = $this->db->insert_id();
	
		/**
		$this->db->update("INSERT INTO pw_memberdata SET " . $this->base->sqlSingle(array(
			'uid'		=> $uid,
			'lastvisit'	=> $this->base->time,
			'thisvisit'	=> $this->base->time,
			'onlineip'	=> $this->base->onlineip
		)));
		**/
		pwQuery::insert('pw_memberdata', array(
			'uid'		=> $uid,
			'lastvisit'	=> $this->base->time,
			'thisvisit'	=> $this->base->time,
			'onlineip'	=> $this->base->onlineip
		));
		
		return $uid;
	}

	function delete($uids) {
		if ($uids) {
			$this->db->update("DELETE FROM pw_members WHERE uid IN (" . UC::implode($uids) . ')');
			return $this->db->affected_rows();
		}
		return 0;
	}

	function edit($uid, $username, $pwd, $email) {
		$user  = $this->get_by_uid($uid);
		$ucsql = array();
		$retv  = 0;
		if ($username && $user['username'] != $username) {
			$ucsql['username'] = $username;
			$retv++;
		}
		if ($pwd && $user['password'] != $pwd) {
			$ucsql['password'] = $pwd;
		}
		if ($email && $user['email'] != $email) {
			$ucsql['email'] = $email;
		}
		if ($ucsql) {
			$retv++;
			$this->db->update("UPDATE pw_members SET " . UC::sqlSingle($ucsql) . ' WHERE uid=' . UC::escape($uid));
		}
		return $retv;
	}
}
?>