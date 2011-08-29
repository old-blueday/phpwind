<?php
!defined('P_W') && exit('Forbidden');

/**
 * Ucuser
 * fix by sky_hold@163.com
 *
 * @package UserCenter
 */
class PW_Ucuser {
	
	var $db;
	
	function PW_Ucuser() {
		global $db;
		$this->db = & $db;
	}
	
	function edit($uid, $oldname, $info) {
		require_once (R_P . 'uc_client/uc_client.php');
		$errmsg = null;
		$errcode = array(
			'-1' => 'illegal_username',
			'-2' => 'username_same',
			'-3' => 'illegal_email',
			'-4' => 'reg_email_have_same'
		);
		$ucstatus = uc_user_edit($uid, $oldname, $info['username'], $info['password'], $info['email']);
		if ($ucstatus < 0) {
			$errmsg = $errcode[$ucstatus];
		}
		if ($ucstatus == 2) {
			$this->alterName($uid, $oldname, $info['username']);
		}
		return array(
			$ucstatus,
			$errmsg
		);
	}
	
	function delete($u_arr) {
		if (empty($u_arr)) return false;
		
		require_once (R_P . 'require/writelog.php');
		global $admin_name, $timestamp, $onlineip;
		$udb = array();
		$userService = $this->_getUserService();
		foreach ($userService->getByUserIds($u_arr) as $rt) {
			$log = array(
				'type' => 'deluser',
				'username1' => $rt['username'],
				'username2' => $admin_name,
				'field1' => 0,
				'field2' => $rt['groupid'],
				'field3' => '',
				'descrip' => 'deluser_descrip',
				'timestamp' => $timestamp,
				'ip' => $onlineip
			);
			writelog($log);
			
			$udb[] = $rt['uid'];
		}
		$this->delUserByIds($udb);
		
		require_once (R_P . 'uc_client/uc_client.php');
		uc_user_delete($u_arr);
	}
	
	function delUserByIds($uids) {
		if (!is_array($uids) || !count($uids)) {
			return;
		}
		
		$userService = $this->_getUserService();
		$userService->deletes($uids);
		
		$count = $userService->count();
		$lastestUser = $userService->getLatestNewUser();
		
		//* $this->db->update("UPDATE pw_bbsinfo SET newmember=" . S::sqlEscape($lastestUser['username']) . ',totalmember=' . S::sqlEscape($count) . " WHERE id='1'");
		$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET newmember=:newmember,totalmember=:totalmember WHERE id=:id", array('pw_bbsinfo',$lastestUser['username'], $count)));
	}
	
	function alterName($uid, $oldname, $username) {
		global $db_plist;
		//$this->db->update("UPDATE pw_threads SET author=" . S::sqlEscape($username) . " WHERE authorid=" . S::sqlEscape($uid));
		pwQuery::update('pw_threads', 'authorid=:authorid', array($uid), array('author'=>$username));
		$ptable_a = array(
			'pw_posts'
		);
		if ($db_plist && count($db_plist) > 1) {
			foreach ($db_plist as $key => $val) {
				if ($key == 0) continue;
				$ptable_a[] = 'pw_posts' . $key;
			}
		}
		foreach ($ptable_a as $val) {
			//$this->db->update("UPDATE $val SET author=" . S::sqlEscape($username) . " WHERE authorid=" . S::sqlEscape($uid));
			pwQuery::update($val, 'authorid=:authorid', array($uid), array('author' => $username));
		}
		//* $this->db->update("UPDATE pw_cmembers SET username=" . S::sqlEscape($username) . " WHERE uid=" . S::sqlEscape($uid));
		pwQuery::update('pw_cmembers', 'uid=:uid', array($uid), array('username'=>$username));
		
		$this->db->update("UPDATE pw_area_level SET username=" . S::sqlEscape($username) . " WHERE uid=" . S::sqlEscape($uid));
		//* $this->db->update("UPDATE pw_colonys SET admin=" . S::sqlEscape($username) . " WHERE admin=" . S::sqlEscape($oldname));
		pwQuery::update('pw_colonys', 'admin=:admin', array($oldname), array('admin'=>$username));
		//* $this->db->update("UPDATE pw_announce SET author=" . S::sqlEscape($username) . " WHERE author=" . S::sqlEscape($oldname));
		pwQuery::update('pw_announce','author=:author', array($oldname), array('author'=>$username));
		//$this->db->update("UPDATE pw_medalslogs SET awardee=" . S::sqlEscape($username) . " WHERE awardee=" . S::sqlEscape($oldname));

		$upfid = array();
		$query = $this->db->query("SELECT fid,forumadmin,fupadmin FROM pw_forums WHERE forumadmin LIKE " . S::sqlEscape("%,$oldname,%", false) . " OR fupadmin LIKE " . S::sqlEscape("%,$oldname,%", false));
		while ($rt = $this->db->fetch_array($query)) {
			$rt['forumadmin'] = str_replace(",$oldname,", ",$username,", $rt['forumadmin']);
			$rt['fupadmin'] = str_replace(",$oldname,", ",$username,", $rt['fupadmin']);
			//$this->db->update("UPDATE pw_forums SET forumadmin=" . S::sqlEscape($rt['forumadmin'], false) . ",fupadmin=" . S::sqlEscape($rt['fupadmin'], false) . " WHERE fid=" . S::sqlEscape($rt['fid'], false));
			pwQuery::update('pw_forums', 'fid=:fid', array($rt['fid']), array('forumadmin' => $rt['forumadmin'], 'fupadmin' => $rt['fupadmin']));
			$upfid[] = $rt['fid'];
		}
		if ($upfid) {
			require_once(R_P . 'admin/cache.php');
			updatecache_forums($upfid);
		}
	}
	
	/**
	 * @return PW_UserService
	 */
	function _getUserService() {
		return L::loadClass('UserService', 'user');
	}
}
?>