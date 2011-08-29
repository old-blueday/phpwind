<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 数据库操作基类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class YUN_BaseDB {
	var $_db = null;
	var $_primaryKey = '';
	var $_tableName = '';
	function YUN_BaseDB() {
		if (! $GLOBALS ['db'])
			PwNewDB ();
		$this->_db = $GLOBALS ['db'];
	}
	function _getConnection() {
		return $GLOBALS ['db'];
	}
	
	function _getUpdateSqlString($arr) {
		return pwSqlSingle ( $arr );
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
		return pwEscape ( $var );
	}
	function _getImplodeString($arr, $strip = true) {
		return pwImplode ( $arr, $strip );
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
		return pwLimit ( $start, $num );
	}
}

