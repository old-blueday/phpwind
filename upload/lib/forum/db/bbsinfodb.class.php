<?php
!defined('P_W') && exit('Forbidden');
class PW_BbsinfoDB extends BaseDB {
	var $_tableName  = 'pw_bbsinfo';
	var $_primaryKey = 'id';
	
	function get($id){
		return $this->_get($id);
	}
}