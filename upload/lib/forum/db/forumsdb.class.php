<?php
!defined('P_W') && exit('Forbidden');
class PW_ForumsDB extends BaseDB {
	var $_tableName  = 'pw_forums';
	var $_extraTableName = 'pw_forumsextra';
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

	function getForumAdmin($fid= 0){
		$sqlWhere = 'WHERE 1';
		$sqlWhere .= $fid > 0?" AND fid=$fid":'';
		$f_admin = array();
		$query = $this->_db->query("SELECT fid,forumadmin FROM " . $this->_tableName . " $sqlWhere");
		while ($forum = $this->_db->fetch_array($query)) {
			$adminarray = explode(",",$forum['forumadmin']);
			foreach ($adminarray as $k => $v) {
				$v = trim($v);
				if ($v) {
					$f_admin[$v['fid']][] = $v;
				}
			}
		}
		return $f_admin;
	}
	
	function getsNotCategory(){
		$query = $this->_db->query ( "SELECT fid,allowvisit,password,name,f_type FROM " . $this->_tableName . " WHERE type<>'category'" );
		return $this->_getAllResultFromQuery ( $query );
	}

	function getsNotCategoryAllInfo(){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE type<>'category'" );
		return $this->_getAllResultFromQuery ( $query );
	}
	/**
	 * 注意只提供搜索服务
	 */
	function countSearch($sql){
		$result = $this->_db->get_one ( $sql );
		return ($result) ? $result['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 */
	function getSearch($sql){
		$query = $this->_db->query ($sql);
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getFormusByFids($fids,$fields = '*') {
		$fids = is_array($fids) ? $fids : array($fids);
		$query = $this->_db->query ("SELECT $fields FROM " . $this->_tableName. " WHERE fid in( ".S::sqlImplode($fids).")");
		return $this->_getAllResultFromQuery($query, 'fid');
	}
	
	function getForumSetsByFids($fids){ 
		$fids = is_array($fids) ? $fids : array($fids);
		$query = $this->_db->query ("SELECT fid,forumset FROM " . $this->_extraTableName. " WHERE fid in( ".S::sqlImplode($fids).")");
		return $this->_getAllResultFromQuery($query, 'fid');
	}
}