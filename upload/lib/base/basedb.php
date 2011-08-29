<?php
!defined('P_W') && exit('Forbidden');

/**
 * 数据库操作基类
 * 
 * @package DB
 */
class BaseDB {
	/**
	 * 数据库连接对象
	 * 
	 * @var DB
	 */
	var $_db = null;
	/**
	 * @var primary ID
	 */
	var $_primaryKey = '';
	var $_tableName = '';
	
	/**
	 * 构造器
	 */
	function BaseDB() {
		if (!$GLOBALS['db']) PwNewDB();
		$this->_db = $GLOBALS['db'];
	}
	
	function _getConnection() {
		return $GLOBALS['db']; //global
	}
	
	/**
	 * 构造更新的sql
	 * 
	 * @see S::sqlSingle
	 * @access protected
	 * @param array $arr 更新数据数组
	 * @return string
	 */
	function _getUpdateSqlString($arr) {
		return S::sqlSingle($arr);
	}
	
	/**
	 * 获取查询结果
	 * 
	 * @access protected
	 * @param resource $query 数据库结果集资源符
	 * @param string|null 数据结果数组的索引key，为null则为自增key
	 * @return array
	 */
	function _getAllResultFromQuery($query, $resultIndexKey = null) {
		$result = array();
		
		if ($resultIndexKey) {
			while ($rt = $this->_db->fetch_array($query)) {
				$result[$rt[$resultIndexKey]] = $rt;
			}
		} else {
			while ($rt = $this->_db->fetch_array($query)) {
				$result[] = $rt;
			}
		}
		return $result;
	}
	
	/**
	 * 检查数组key是否为合法字段
	 * 
	 * @access protected
	 * @param array $fieldData 数据数组
	 * @param array $allowFields 允许的字段
	 * @return array 过滤后的数据
	 */
	function _checkAllowField($fieldData, $allowFields) {
		foreach ($fieldData as $key => $value) {
			if (!in_array($key, $allowFields)) {
				unset($fieldData[$key]);
			}
		}
		return $fieldData;
	}
	
	/**
	 * 反斜杠过滤
	 * 
	 * @see S::sqlEscape
	 * @access protected
	 * @param mixed $var 数据
	 * @return mixed 过滤后的数据
	 */
	function _addSlashes($var) {
		return S::sqlEscape($var);
	}
	
	/**
	 * implode组装数组为sql
	 * 
	 * @see S::sqlImplode
	 * @access protected
	 * @param $arr 数据数组
	 * @param bool $strip 是否经过stripslashes处理
	 */
	function _getImplodeString($arr, $strip = true) {
		return S::sqlImplode($arr, $strip);
	}
	
	/**
	 * 序列化数据
	 * 
	 * @access protected
	 * @param mixed $value
	 * @return string
	 */
	function _serialize($value) {
		if (is_array($value)) {
			return serialize($value);
		}
		if (is_string($value) && is_array(unserialize($value))) {
			return $value;
		}
		return '';
	}
	
	/**
	 * 反序列化数据
	 * 
	 * @access protected
	 * @param string $value
	 * @return mixed
	 */
	function _unserialize($value) {
		if ($value && is_array($tmpValue = unserialize($value))) {
			$value = $tmpValue;
		}
		return $value;
	}
	/**
	 * 基础增加数据查询语句
	 * @param $fieldData
	 * @return unknown_type
	 */
	function _insert($fieldData) {
		if (!$this->_check() || !$fieldData) return false;
		//* $this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData));
		return pwQuery::insert($this->_tableName, $fieldData);
	}
	/**
	 * 基础更新数据查询语句
	 * @param $fieldData
	 * @param $id
	 * @return unknown_type
	 */
	function _update($fieldData, $id) {
		if (!$this->_check() || !$fieldData || $id < 1) return false;
		//* $this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes($id) . " LIMIT 1");
		return pwQuery::update($this->_tableName, "{$this->_primaryKey}=:{$this->_primaryKey}", array($id), $fieldData);
	}
	/**
	 * 基础删除一条数据查询语句
	 * @param $id
	 * @return unknown_type
	 */
	function _delete($id) {
		if (!$this->_check() || $id < 1) return false;
		//* $this->_db->update("DELETE FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes($id) . " LIMIT 1");
		return pwQuery::delete($this->_tableName, "{$this->_primaryKey}=:{$this->_primaryKey}", array($id));
	}
	/**
	 * 基础获取一条数据查询语句
	 * @param $id
	 * @param $fields 返回的字段名，多个用','隔开，全部为'*'
	 * @return unknown_type
	 */
	function _get($id, $fields = '*') {
		if (!$this->_check() || $id < 1) return false;
		return $this->_db->get_one("SELECT $fields FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes($id) . " LIMIT 1");
	}
	/**
	 * 基础统计全部数据查询语句
	 * @return unknown_type
	 */
	function _count() {
		if (!$this->_check()) return false;
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName);
		return $result['total'];
	}
	/**
	 * 私用检查表名称与主键是否定义函数
	 * @return unknown_type
	 */
	function _check() {
		return (!$this->_tableName || !$this->_primaryKey) ? false : true;
	}
	
	/**
	 * SQL查询中,构造LIMIT语句
	 *
	 * @param int $start 开始记录位置
	 * @param int $num 读取记录数目
	 * @return string SQL语句
	 */
	function _Limit($start, $num = false){
		return S::sqlLimit($start, $num);
	}
}

