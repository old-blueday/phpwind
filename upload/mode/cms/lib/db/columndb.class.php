<?php
!defined('P_W') && exit('Forbidden');

/**
 * 栏目管理CRUD操作实现
 * @author yishuo
 */
class PW_ColumnDB extends BaseDB {
	var $_tableName = "pw_cms_column";

	/**
	 * 根据栏目ID删除一个栏目
	 * @param $id
	 * @return boolean
	 */
	function deleteColumnById($id) {
		if (empty($id)) return false;
		$_sql = "DELETE FROM " . $this->_tableName . " WHERE column_id = " . S::sqlEscape($id);
		return $this->_db->update($_sql);
	}

	/**
	 * 根据栏目ID批量删除一组栏目
	 * @param array $ids
	 * @return boolean
	 */
	function deleteColumnByIds($ids) {
		if (!is_array($ids)) return false;
		$_sql = "DELETE FROM " . $this->_tableName . " WHERE column_id IN (" . S::sqlImplode($ids) . ")";
		$this->_db->update($_sql);
		$_sql = "DELETE FROM " . $this->_tableName . " WHERE parent_id IN (" . S::sqlImplode($ids) . ")";
		$this->_db->update($_sql);
		return true;
	}

	/**
	 * 批量添加文章栏目
	 * (parent_id,name,order,allowoffer,seotitle,seodesc,seokeywords)
	 * @return string
	 */
	function insertColumn($datas) {
		$_sql = "INSERT INTO " . $this->_tableName . " (`parent_id`,`name`,`order`,`allowoffer`,`seotitle`,`seodesc`,`seokeywords`) VALUES " . S::sqlMulti($datas, false);
		return $this->_db->update($_sql);
	}

	/**
	 * 更新文章栏目
	 * @param unknown_type $cid
	 * @param unknown_type $data
	 */
	function updateColumn($cid, $data) {
		$_sql = "UPDATE " . $this->_tableName . " SET " . S::sqlSingle($data) . " WHERE column_id = " . S::sqlEscape($cid);
		return $this->_db->update($_sql);
	}

	/**
	 * 更新文章排序
	 * @param unknown_type $cid
	 * @param unknown_type $order
	 */
	function updateColumnOrder($cid, $order) {
		$_sql = "UPDATE " . $this->_tableName . " SET `order` = " . S::sqlEscape($order) . " WHERE column_id = " . S::sqlEscape($cid);
		return $this->_db->update($_sql);
	}

	function updateColumnSEO($cid, $title, $desc, $keyword) {
		$_sql = "UPDATE " . $this->_tableName . " SET seotitle = " . S::sqlEscape($title) . ", seodesc = " . S::sqlEscape($desc) . ", seokeywords = " . S::sqlEscape($keyword) . " WHERE column_id = " . S::sqlEscape($cid);
		return $this->_db->update($_sql);
	}

	/**
	 * 获得所有文章栏目列表
	 * @return 
	 */
	function getColumns() {
		$_sql = "SELECT c.* FROM " . $this->_tableName . " c ORDER BY c.order ASC";
		$query = $this->_db->query($_sql);
		$result = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$result[$rt['column_id']] = $rt;
		}
		return $result;
	}

	function getColumnByName($name) {
		$_sql = "SELECT c.* FROM " . $this->_tableName . " c WHERE c.name = " . S::sqlEscape($name);
		return $this->_db->get_value($_sql);
	}

	/**
	 * 根据栏目ID获得栏目
	 * @param array/int $id
	 * @return array
	 */
	function getColumn($id) {
		if (!is_array($id)) $id = array($id);
		$_sql = "SELECT * FROM " . $this->_tableName . " WHERE column_id IN ( " . S::sqlImplode($id) . " )";
		return $this->_getAllResultFromQuery($this->_db->query($_sql));
	}

}
?>