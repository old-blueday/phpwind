<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_AnnounceDB extends BaseDB {
	var $_tableName = "pw_announce";

	/**
	 * 获得最新公告
	 * @param int $num
	 */
	function getNewData($num) {
		global $timestamp;
		$num = (int) $num;
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE ifopen = '1' AND 
			startdate <= " . S::sqlEscape($timestamp) . " AND enddate=0 OR enddate>". S::sqlEscape($timestamp) ." ORDER BY aid DESC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}

}
?>