<?php
!defined('P_W') && exit('Forbidden');
/**
 * 群组相册数据库操作对象
 * @package CnAlbumDB
 * @author 	suqian
 */
class PW_CnAlbumDB extends BaseDB {
	var $_tableName = "pw_cnalbum";
	var $_primaryKey = 'aid';
	
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$aid){
		return $this->_update($fieldData,$aid);
	}
	function delete($aid){
		return $this->_delete($aid);
	}
	function get($aid){
		return $this->_get($aid);
	}
	function count(){
		return $this->_count();
	}
	
	function getAlbumNumByUid($uid,$atype = 0,$priacy=array()){
		$uid = intval($uid);
		$atype = intval($atype);
		if(0 >= $uid ){
			return 0;
		}
		$priacy = is_array($priacy) ? $priacy : array($priacy);
		$sqlAdd = ' AND ownerid='.$this->_addSlashes($uid);
		$sqlAdd .= 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sqlAdd .= empty($priacy) ? ' ' : ' AND private in ('.$this->_getImplodeString($priacy).')';
		$sql = 'SELECT COUNT(*) AS sum FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		$result = $this->_db->get_one($sql);
		return $result;
	}
	
	function getAlbumsByUid($uid,$atype = 0,$priacy=array()){
		$uid = intval($uid);
		$atype = intval($atype);
		if(0 >= $uid ){
			return array();
		}
		$priacy = is_array($priacy) ? $priacy : array($priacy);
		$sqlAdd = ' AND ownerid='.$this->_addSlashes($uid);
		$sqlAdd .= 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sqlAdd .= empty($priacy) ? ' ' : ' AND private in ('.$this->_getImplodeString($priacy).')';
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd.' ORDER BY aid DESC';
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);	
	}
	
	function getPageAlbumsByUid($uid,$page = 1,$perpage = 20,$atype = 0,$priacy=array()){
		$uid = intval($uid);
		$atype = intval($atype);
		if ($page <= 0 || $perpage <= 0  || $uid <= 0){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$priacy = is_array($priacy) ? $priacy : array($priacy);
		$sqlAdd = ' AND ownerid='.$this->_addSlashes($uid);
		$sqlAdd .= 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sqlAdd .= empty($priacy) ? ' ' : ' AND private in ('.$this->_getImplodeString($priacy).')';
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd.' ORDER BY aid DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);	
	}
	
	function albumExist($uid,$aname,$atype = 0){
		$uid = intval($uid);
		$atype = intval($atype);
		if(0 >= $uid || empty($aname) || 0 > $atype){
			return false;
		}
		$sqlAdd = ' AND ownerid='.$this->_addSlashes($uid);
		$sqlAdd .= 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sqlAdd .= empty($aname) ? ' ' : ' AND aname = '.$this->_addSlashes($aname);
		$sql = 'SELECT aid FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		$result = $this->_db->get_one($sql);
		if(empty($result)){
			return false;
		}
		return true;
	}
	
	function getAlbumsInfo($aid,$atype = 0){
		if(empty($aid)){
			return array();
		}
		$aid = is_array($aid) ? $aid : array($aid);
		$sqlAdd = 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd.' AND  aid IN (' . $this->_getImplodeString($aid).') ORDER BY aid DESC';
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getAlbumsByUids($uids,$page=1,$perpage = 20,$atype = 0,$priacy = array()){
		if ($page <= 0 || $perpage <= 0 || empty($uids)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$uids = is_array($uids) ? $uids : array($uids);
		$priacy = is_array($priacy) ? $priacy : array($priacy);
		$sqlAdd = ' AND ownerid in ('.$this->_getImplodeString($uids).')';
		$sqlAdd .= 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sqlAdd .= empty($priacy) ? ' ' : ' AND private in ('.$this->_getImplodeString($priacy).')';
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd.' ORDER BY aid DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return $result = $this->_getAllResultFromQuery($query);
	}
	function getAlbumsNumByUids($uids,$atype = 0,$priacy = array()){
		if(empty($uids)){
				return 0;
		}
		$uids = is_array($uids) ? $uids : array($uids);
		$priacy = is_array($priacy) ? $priacy : array($priacy);
		$sqlAdd = ' AND ownerid in ('.$this->_getImplodeString($uids).')';
		$sqlAdd .= 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$sqlAdd .= empty($priacy) ? ' ' : ' AND private in ('.$this->_getImplodeString($priacy).')';
		$sql = 'SELECT	count(*) as total FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		return  $this->_db->get_one($sql);
	}
	function getAlbumInfo($aid,$atype = 0){
		if(0 >= intval($aid)){
			return array();
		}
		$sqlAdd = 0 > $atype ? ' ' : ' AND atype = '.$this->_addSlashes($atype);
		$photoinfo = $this->_db->get_one('SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd.' AND aid=' . $this->_addSlashes($aid));
		return $photoinfo;
	}
	
	function getAlbumByUids ($uids) {
		if(!$uids) return false;
		$uids = is_array($uids) ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		$sql = "SELECT * FROM " .$this->_tableName. " WHERE ownerid IN( " .$uids." )";
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
}
