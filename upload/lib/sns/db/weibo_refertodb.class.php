<?php
/**
 * 新鲜事关联表数据库DAO服务
 * 
 * @package PW_Weibo_RefertoDB
 * @author suqian
 * @access private
 */

!defined('P_W') && exit('Forbidden');

class PW_Weibo_RefertoDB extends BaseDB {

	var $_tableName = 'pw_weibo_referto';	
	var $_foreignTableName = 'pw_weibo_content';
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

	function addRefer($fieldDatas) {
		$this->_db->update("INSERT INTO " . $this->_tableName . " (uid,mid) VALUES  " . S::sqlMulti($fieldDatas,FALSE));
		return $this->_db->insert_id();
	}

	function getRefersToMe($uid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_tableName.'  a LEFT JOIN '.$this->_foreignTableName.' b ON a.mid = b.mid WHERE a.uid = '.$this->_addSlashes($uid).'  ORDER BY a.mid DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getRefersToMeCount($uid){
		if(!$this->_isLegalNumeric($uid)){
			return 0;
		}
		$sql = 'SELECT count(*) FROM '.$this->_tableName.'  a LEFT JOIN '.$this->_foreignTableName.' b ON a.mid = b.mid WHERE  a.uid = '.$this->_addSlashes($uid);
		return $this->_db->get_value($sql);
	}
	
	function deleteRefersByMid($mids){
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