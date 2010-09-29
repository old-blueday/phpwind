<?php
!defined('P_W') && exit('Forbidden');
class PW_ForumsDB extends BaseDB {
	var $_tableName  = 'pw_forums';
	var $_primaryKey = 'fid';
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$id){
		return $this->_update($fieldData,$id);
	}
	function delete($id){
		return $this->_delete($id);
	}
	function get($id){
		return $this->_get($id);
	}
	function count(){
		return $this->_count();
	}
	
	function getsNotCategory(){
		$query = $this->_db->query ( "SELECT fid,allowvisit,password,name,f_type FROM " . $this->_tableName . " WHERE type<>'category'" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 注意只提供搜索服务
	 */
	function countSearch($keywords){
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE name like ".pwEscape("%$keywords%")." LIMIT 1" );
		return ($result) ? $result['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 */
	function getSearch($keywords,$offset,$limit){
		$query = $this->_db->query ("SELECT * FROM " . $this->_tableName . " WHERE name like ".pwEscape("%$keywords%")." LIMIT ".$offset.",".$limit);
		return $this->_getAllResultFromQuery ( $query );
	}
}