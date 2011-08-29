<?php
/**
 * 新鲜事评论关联表DAO
 * 
 * @package PW_Weibo_CmrelationsDB
 * @author suqian
 * @access private
 */

!defined('P_W') && exit('Forbidden');

class PW_Weibo_CmrelationsDB extends BaseDB {

	var $_tableName = 'pw_weibo_cmrelations';	
	var $_foreignTableName = 'pw_weibo_comment';
	var $_primaryKey = 'uid';

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

	function addCmRelations($fieldDatas){
		$this->_db->update("INSERT INTO " . $this->_tableName . " (uid,cid) VALUES  " . S::sqlMulti($fieldDatas,FALSE));
		return $this->_db->insert_id();
	}
	function removeCmRelation($uid,$cid){
		if(!$this->_isLegalNumeric($cid) || !$this->_isLegalNumeric($uid)){
			return false;
		}
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE uid = '.$this->_addSlashes($uid).' AND cid = '.$this->_addSlashes($cid);
		$this->_db->query($sql);
		return true;
	}
	
	function deleteCmRelationsByUid($uid){
		if(!$this->_isLegalNumeric($uid)){
			return false;
		}
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE uid = '.$this->_addSlashes($uid);
		$this->_db->query($sql);
		return true;
	}
	
	function deleteCmRelationsByCids($cids){
		if(empty($cids)){
			return false;
		}
		$cids = is_array($cids) ? $cids : array($cids);
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE cid  IN (' . S::sqlImplode($cids) . ') ';
		$this->_db->query($sql);
		return true;
	}
	
	function _isLegalNumeric($id){
		return intval($id) > 0;
	}
	
}
?>