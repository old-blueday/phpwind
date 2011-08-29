<?php
!defined('P_W') && exit('Forbidden');

class PW_MemberCreditDB extends BaseDB {
	var $_tableName = "pw_membercredit";

	function gets($userIds) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid IN(" . S::sqlImplode($userIds) . ")");
		return $this->_getAllResultFromQuery($query);
	}
}