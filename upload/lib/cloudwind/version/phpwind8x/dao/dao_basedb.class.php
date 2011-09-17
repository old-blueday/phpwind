<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Base_Db {
	
	var $_db = null;
	var $_primaryKey = '';
	var $_tableName = '';
	
	function CloudWind_Base_Db() {
		$this->_db = $this->_getConnection();
	}
	
	function _getConnection() {
		return $GLOBALS['db'];
	}
	
	function _getUpdateSqlString($arr) {
		return CLOUDWIND_SECURITY_SERVICE::sqlSingle ( $arr );
	}
	
	function _getAllResultFromQuery($query, $resultIndexKey = null) {
		$result = array ();
		if ($resultIndexKey) {
			while ( $rt = $this->_db->fetch_array ( $query ) ) {
				$result [$rt [$resultIndexKey]] = $rt;
			}
		} else {
			while ( $rt = $this->_db->fetch_array ( $query ) ) {
				$result [] = $rt;
			}
		}
		return $result;
	}
	
	function _checkAllowField($fieldData, $allowFields) {
		foreach ( $fieldData as $key => $value ) {
			if (! in_array ( $key, $allowFields )) {
				unset ( $fieldData [$key] );
			}
		}
		return $fieldData;
	}
	
	function _addSlashes($var) {
		return CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $var );
	}
	
	function _getImplodeString($arr, $strip = true) {
		return CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $arr, $strip );
	}
	
	function _serialize($value) {
		if (is_array ( $value )) {
			return serialize ( $value );
		}
		if (is_string ( $value ) && is_array ( unserialize ( $value ) )) {
			return $value;
		}
		return '';
	}
	
	function _unserialize($value) {
		if ($value && is_array ( $tmpValue = unserialize ( $value ) )) {
			$value = $tmpValue;
		}
		return $value;
	}
	
	function _insert($fieldData) {
		if (! $this->_check () || ! $fieldData)
			return false;
		return $this->_db->update ( "INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) );
	}
	
	function _update($fieldData, $id) {
		if (! $this->_check () || ! $fieldData || $id < 1)
			return false;
		return $this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
	}
	
	function _delete($id) {
		if (! $this->_check () || $id < 1)
			return false;
		return $this->_db->update ( "DELETE FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
	}
	
	function _get($id, $fields = '*') {
		if (! $this->_check () || $id < 1)
			return false;
		return $this->_db->get_one ( "SELECT $fields FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
	}
	
	function _count() {
		if (! $this->_check ())
			return false;
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName );
		return $result ['total'];
	}
	
	function _check() {
		return (! $this->_tableName || ! $this->_primaryKey) ? false : true;
	}
	
	function _Limit($start, $num = false) {
		return CLOUDWIND_SECURITY_SERVICE::sqlLimit ( $start, $num );
	}
	
	function _getRange($page, $perpage) {
		$perpage = intval ( $perpage );
		$start = intval ( ($page - 1) * $perpage );
		return array ($start, $start + $perpage );
	}
}