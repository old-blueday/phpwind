<?php
!defined('P_W') && exit('Forbidden');
class PW_cacheDataDB extends BaseDB {
	var $_tableName = "pw_cachedata";

	function getDataByInvokepieceId($invokepieceid){
		$temp = $this->_db->get_one("SELECT invokepieceid,fid,loopid,data,cachetime,ifpushonly FROM ".$this->_tableName." WHERE invokepieceid=".S::sqlEscape($invokepieceid)."AND fid=".S::sqlEscape($fid)." AND loopid=".S::sqlEscape($loopid));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}
	function insertData($array){
		$array = $this->_checkData($array);
		if (!$array || !$array['invokepieceid']) {
			return null;
		}
		$this->_db->update("REPLACE INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function deleteData($invokepieceid){
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE invokepieceid=".S::sqlEscape($invokepieceid));
	}
	function deleteDatas($ids) {
		if(!$ids || !is_array($ids)){
			return false;
		}
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE invokepieceid IN(".S::sqlImplode($ids).')');
	}
	function truncate(){
		$this->_db->query("TRUNCATE ".$this->_tableName."");
	}
	function updates($array){
		foreach ($array as $key=>$value) {
			$array[$key] = $this->_serializeData($value);
		}
		$this->_db->update("REPLACE INTO ".$this->_tableName." (invokepieceid,data,cachetime) VALUES " . S::sqlMulti($array,false));
	}

	function getDatasByInvokepieceids($invokepieceids){
		return $this->commonGetDatas($invokepieceids);
	}
	/*
	 * 普通模块
	 */
	function commonGetDatas($invokepieceids){
		if (!is_array($invokepieceids) || !$invokepieceids) return array();
		$temp	= array();
		//print_r("SELECT * FROM ".$this->_tableName." WHERE invokepieceid IN(".S::sqlImplode($invokepieceids).")");exit;
		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE invokepieceid IN(".S::sqlImplode($invokepieceids).")");
		while ($rt = $this->_db->fetch_array($query)) {
			$rt	= $this->_unserializeData($rt);
			//$key = $rt['loopid'] ? $rt['invokepieceid']."_".$rt['loopid'] : $rt['invokepieceid'];
			$temp[$rt['invokepieceid']] = $rt;
		}
		return $temp;
	}

	/*
	 * private functions
	 */
	function getStruct(){
		return array('id','invokepieceid','data','cachetime','ifpushonly');
	}
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		return $data;
	}
	function _serializeData($data) {
		if (isset($data['data']) && is_array($data['data'])) $data['data'] = serialize($data['data']);
		return $data;
	}

	function _unserializeData($data) {
		if ($data['data']) $data['data'] = unserialize($data['data']);
		return $data;
	}
}
?>