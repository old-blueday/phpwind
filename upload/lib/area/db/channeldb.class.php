<?php
!defined('P_W') && exit('Forbidden');

class PW_ChannelDB extends BaseDB {
	var $_tableName = "pw_channel";
	

	function addChannel($channel_name,$channel_alias,$channel_theme,$channel_domain) {
		$isExistsChannel = $this->getChannelByAlias($channel_alias);
		if ($isExistsChannel) return false;
		
		$this->_db->update("INSERT INTO ".$this->_tableName."(name,alias,relate_theme,domain_band) VALUES (".pwEscape($channel_name).",".pwEscape($channel_alias).",".pwEscape($channel_theme).",".pwEscape($channel_domain).")");
		return $this->_db->insert_id();
	}
	
	function update($id,$array) {
		$array = $this->_checkData($array);
		if (!$array) return null;
		return $this->_db->update("UPDATE ".$this->_tableName." SET ".pwSqlSingle($array,false)." WHERE id=".pwEscape($id));
	}
	function updateByAlias($alias,$array) {
		$array = $this->_checkData($array);
		if (!$array) return null;
		if (isset($array['id'])) unset($array['id']); 
		return $this->_db->update("UPDATE ".$this->_tableName." SET ".pwSqlSingle($array,false)." WHERE alias=".pwEscape($alias));
	}
	function getSecendDomains() {
		$temp = array();
		$rs = $this->_db->query("SELECT alias,domain_band FROM ".$this->_tableName." WHERE domain_band<>''");
		while($rt = $this->_db->fetch_array($rs)) {
			$temp[$rt['alias']] = $rt['domain_band'];
		}
		return $temp;
	}

	//删除操作
	function delChannel($id) {
		$sql = "DELETE FROM ".$this->_tableName." WHERE id=".pwEscape($id);
		if ($this->_db->update($sql)) return true;
		return false;
	}

	//取数据操作
	function getChannels() {
		$sql = $this->_db->query("SELECT * FROM ".$this->_tableName." ORDER BY queue");
		while($rt = $this->_db->fetch_array($sql)) {
			$rt = $this->_unserializeData($rt);
			$channel_list[$rt['id']] = $rt;
		}
		return $channel_list;
	}

	function getAliasByChannelid($cid) {
		$temp = $this->_db->get_one("SELECT alias FROM ".$this->_tableName." WHERE id=".pwEscape($cid));
		return $temp['alias'];
	}
	function getChannelByChannelid($cid) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".pwEscape($cid));
		$temp = $this->_unserializeData($temp);
		return $temp;
	}
	
	//取某一条数据操作
	function getChannelByAlias($alias) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE alias=".pwEscape($alias));
		$temp = $this->_unserializeData($temp);
		return $temp;
	}
	//通过频道名称取记录
	function getChannelByChannelName($channel_name) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE name=".pwEscape($channel_name));
		$temp = $this->_unserializeData($temp);
		return $temp;
	}
	
	function getStruct() {
		return array('id','name','alias','queue','relate_theme','domain_band','metatitle','metadescrip','metakeywords','statictime');
	}
	
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		return $data;
	}
	function _serializeData($data) {
		return $data;
	}

	function _unserializeData($data) {
		return $data;
	}
}
?>