<?php
!defined('P_W') && exit('Forbidden');

class PW_AuthCertificateDB extends BaseDB {
	var $_tableName  = 'pw_auth_certificate';
	var $_primaryKey = 'id';
	
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
	function getAuthCertificateByUid($uid){
		return $this->_db->get_one("SELECT * FROM $this->_tableName WHERE uid=". S::sqlEscape($uid));
	}
	
	function updateCertificateStateByIds($ids,$state){
		return $this->_db->update("UPDATE $this->_tableName SET state=" .S::sqlEscape($state). " WHERE id IN (".S::sqlImplode($ids).")");
	}
	
	function deleteCertificateByIds($ids){
		return $this->_db->update("DELETE FROM $this->_tableName  WHERE id IN (".S::sqlImplode($ids).")");
	}
	
	function getCertificateInfo($start,$limit,$state){
		if ($state) {
			$where = 'WHERE state='. S::sqlEscape($state);
		} else {
			$where = 'WHERE state>0';
		}
		$query = $this->_db->query("SELECT * FROM $this->_tableName $where ORDER BY id DESC".S::sqlLimit($start,$limit));
		return $this->_getAllResultFromQuery($query,'uid');
	}
	
	function countCertificateInfo($state){
		if ($state) {
			$where = 'WHERE state='. S::sqlEscape($state);
		} else {
			$where = 'WHERE state>0';
		}
		return $this->_db->get_value("SELECT COUNT(*) FROM $this->_tableName $where");
	}
}