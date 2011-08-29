<?php
!defined('P_W') && exit('Forbidden');

class PW_RobBuildFloorDb extends BaseDB {
	var $_tableName  = 'pw_robbuildfloor';

	function get($tid){
		$tid = intval($tid);
		if ($tid < 1) return false;
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " WHERE tid = " . S::sqlEscape($tid));
	}

	function setRobPostFloor($tid,$floor,$pid) {
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET tid = " . S::sqlEscape($tid) . ", floor = " . S::sqlEscape($floor) . ", pid = " . S::sqlEscape($pid));
		return $this->_db->insert_id();
	}
	
	function getFloorsByPids($pids){
		$query = $this->_db->query ( "SELECT floor FROM " . $this->_tableName . " WHERE pid IN(".S::sqlImplode($pids).")" );
		while ($rt = $this->_db->fetch_array($query)) {
			$robFloors[] = $rt['floor'];
		}
		return $robFloors;
	}
}
?>