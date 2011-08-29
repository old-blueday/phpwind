<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Colonys {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Colonys_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
	
	}
	
	function update($tableName, $fields, $expand = array()) {
	
	}
	
	function delete($tableName, $fields, $expand = array()) {
	
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Colonys_Impl {

}