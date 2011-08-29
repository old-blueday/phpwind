<?php
!defined('P_W') && exit('Forbidden');
class PW_PostsDB extends BaseDB {
	var $_tableName  = 'pw_posts';
	var $_primaryKey = 'pid';
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
	
	function getsByPostIds($postIds,$table){
		if(!$table) return false;
		$postIds = (is_array($postIds)) ? S::sqlImplode($postIds) : $postIds;
		$query = $this->_db->query ( "SELECT * FROM ".$table." p  WHERE p.pid in(".$postIds.") ORDER BY p.postdate DESC" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 注意只提供搜索服务
	 * @param $sql
	 * @return unknown_type
	 */
	function countSearch($sql){
		$result = $this->_db->get_one ( $sql );
		return ($result) ? $result['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 * @param $sql
	 * @return unknown_type
	 */
	function getSearch($sql){
		$query = $this->_db->query ($sql);
		return $this->_getAllResultFromQuery ( $query );
	}

}