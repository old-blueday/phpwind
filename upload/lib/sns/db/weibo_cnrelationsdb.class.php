<?php
/**
 * 新鲜事关联表数据库DAO服务
 * 
 * @package PW_Weibo_CnrelationsDB
 * @author suqian
 * @access private
 */

!defined('P_W') && exit('Forbidden');

class PW_Weibo_CnrelationsDB extends BaseDB {

	var $_tableName = 'pw_weibo_cnrelations';	
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

	function addCnRelations($fieldDatas) {
		$this->_db->update("INSERT INTO " . $this->_tableName . " (cyid,mid) VALUES  " . S::sqlMulti($fieldDatas,FALSE));
		return $this->_db->insert_id();
	}

	function getConloysWeibos($cyids,$page = 1,$perpage = 20){
		$offset = ($page - 1) * $perpage;
		if ($cyids == 'nocyids') {
			$sql = 'SELECT * FROM '.$this->_tableName.'  a LEFT JOIN '.$this->_foreignTableName.' b ON a.mid = b.mid ORDER BY a.mid DESC '.$this->_Limit($offset,$perpage);
		} else {
			$cyids = is_array($cyids) ? $cyids : array($cyids);
			if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || empty($cyids)){
				return array();
			} 
			$sql = 'SELECT * FROM '.$this->_tableName.'  a LEFT JOIN '.$this->_foreignTableName.' b ON a.mid = b.mid WHERE a.cyid IN ( '.S::sqlImplode($cyids).' ) ORDER BY a.mid DESC '.$this->_Limit($offset,$perpage);
		}
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getConloysWeibosCount($cyids){
		$cyids = is_array($cyids) ? $cyids : array($cyids);
		if(empty($cyids)){
			return 0;
		}
		$sql = 'SELECT count(*) FROM '.$this->_tableName.'  a LEFT JOIN '.$this->_foreignTableName.' b ON a.mid = b.mid WHERE  a.cyid IN ( '.S::sqlImplode($cyids).')';
		return (int)$this->_db->get_value($sql);
	}
	
	function deleteCnrelationsByMid($mids){
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