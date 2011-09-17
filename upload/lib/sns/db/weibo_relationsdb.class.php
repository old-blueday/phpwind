<?php
/**
 * 新鲜事关系体数据库DAO服务
 * 
 * @package PW_FeedDB
 * @author suqian
 */

!defined('P_W') && exit('Forbidden');

class PW_Weibo_RelationsDB extends BaseDB {

	var $_tableName = 'pw_weibo_relations';	
	var $_primaryKey = 'mid';

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

	function addRelation($fieldDatas){
		$this->_db->update("INSERT INTO " . $this->_tableName . " (uid,mid,authorid,type,postdate) VALUES " . S::sqlMulti($fieldDatas,FALSE));
		return $this->_db->insert_id ();
	}

	function removeRelation($uid,$authorid){
		if(!$this->_isLegalNumeric($uid) || !$this->_isLegalNumeric($authorid)){
			return 0;
		}
		$sql = 'DELETE FROM ' . $this->_tableName . ' WHERE uid=' . $this->_addSlashes($uid) . ' AND authorid=' . $this->_addSlashes($authorid);
		$this->_db->update($sql);
		return $this->_db->affected_rows();
	}

	function deleteAttentionRelation($uid, $num) {
		$num = intval($num);
		if ($num < 1) return 0;
		$sql = 'DELETE FROM ' . $this->_tableName . ' WHERE uid=' . $this->_addSlashes($uid) . " ORDER BY postdate ASC LIMIT $num";
		$this->_db->update($sql);
		return $this->_db->affected_rows();
	}
	
	function delRelationsByMid($mids){
		if(empty($mids)){
			return false;
		}
		$mids = is_array($mids) ? $mids : array($mids);
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE mid IN (' . S::sqlImplode($mids) . ') ';
		$this->_db->query($sql);
		return true;
	}

	function _isLegalNumeric($id){
		return intval($id) > 0;
	}
}
?>