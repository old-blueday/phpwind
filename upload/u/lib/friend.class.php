<?php
!defined('P_W') && exit('Forbidden');

/**
 * 个人空间里的朋友模板服务层
 * @author  luomingqu @2010-4-27
 *
 */
class PwFriend {
	
	var $_db;

	function PwFriend() {
		global $db;
		$this->_db =& $db;
	}
	
	function getFriends($uid) {
		$_array = array();

		$_sql = "SELECT m.uid,m.username,f.ftid,f.iffeed FROM ".
			" pw_friends f".
			" LEFT JOIN pw_members m".
			" ON f.friendid=m.uid".
			" WHERE f.status=0 AND f.uid= " .S::sqlEscape($uid) .
			"ORDER BY f.joindate";
		$_query = $this->_db->query($_sql);
		while($rt = $this->_db->fetch_array($_query)) {
			$_array[$rt['uid']] = $rt;
		}
		
		return $_array;
	}


	function getAttentions($uid) {
		$_array = array();

		$_sql = "SELECT m.uid,m.username,a.touid FROM ".
			" pw_attention a".
			" LEFT JOIN pw_members m".
			" ON a.touid=m.uid".
			" WHERE a.uid= " .S::sqlEscape($uid) .
			"ORDER BY a.date";
		$_query = $this->_db->query($_sql);
		while($rt = $this->_db->fetch_array($_query)) {
			$_array[$rt['uid']] = $rt;
		}

		return $_array;
	}
	
}
?>