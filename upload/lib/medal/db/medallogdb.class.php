<?php
defined('P_W') || exit('Forbidden');
class PW_MedalLogDB extends BaseDB {
	var $_tableName = 'pw_medal_log';
	var $_primaryKey = 'log_id';
	function get($logId) {
		return $this->_get($logId);
	}
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	function update($fieldData,$logId){
		return $this->_update($fieldData,$logId);
	}
	function delete($logId){
		return $this->_delete($logId);
	}
}