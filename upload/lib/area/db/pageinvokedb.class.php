<?php
!defined('P_W') && exit('Forbidden');
class PW_PageInvokeDB extends BaseDB {
	var $_tableName = 'pw_pageinvoke';
	var $_joinTable = 'pw_invoke';

	function searchPageInvokes($array,$page,$prePage = 20) {
		$page = (int)$page-1 < 0 ? 0 : (int) $page-1;
		$temp = array();
		$_sql = "SELECT p.*,i.title,i.name FROM ".$this->_tableName." p LEFT JOIN ".$this->_joinTable." i ON p.invokename=i.name".$this->_getSearchSql($array,1).' ORDER by p.state ASC, p.id ASC '.S::sqlLimit($page*$prePage,$prePage);
		$query = $this->_db->query($_sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$rt = $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}
	function searchCount($array) {
		$_sql = "SELECT COUNT(*) as count FROM ".$this->_tableName." p LEFT JOIN ".$this->_joinTable." i ON p.invokename=i.name".$this->_getSearchSql($array);
		return $this->_db->get_value($_sql);
	}
	function _getSearchSql($array,$ifJoin=0) {
		$array = $this->_checkAllowField($array,$this->getStruct());
		$_sql_where = ' WHERE 1 ';
		foreach ($array as $key=>$value) {
			if ($key == 'invokename') {
				$_sql_where .= " AND ".($ifJoin ? "p.":'')."invokename LIKE ".S::sqlEscape("%$value%");
			} elseif ($key == 'title') {
				$_sql_where .= " AND ".($ifJoin ? "i.":'')."title LIKE ".S::sqlEscape("%$value%");
			}  elseif ($key == 'sign' && $value) {
				$_sql_where .= " AND ".($ifJoin ? "p.":'')."sign=".S::sqlEscape($value);
			} else {
				$_sql_where .= " AND ".($ifJoin ? "p.":'')."$key=".S::sqlEscape($value);
			}
		}
		return $_sql_where;
	}
	function get($id) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
		return $this->_unserializeData($temp);
	}
	function delete($id) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
	}
	function deleteByScrAndSign($scr,$sign) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign));
	}
	function getEffectPageInvokes($scr,$sign,$ifverify = 0) {
		$temp = array();

		$ifverify = (int) $ifverify;
		$_sql_add = $ifverify ? " AND p.ifverify=".S::sqlEscape($ifverify): '';
		$query = $this->_db->query("SELECT p.*,i.title FROM ".$this->_tableName." p LEFT JOIN ".$this->_joinTable." i ON p.invokename=i.name WHERE p.scr=".S::sqlEscape($scr)." AND p.sign=".S::sqlEscape($sign)." AND p.state=0 $_sql_add");
		while ($rt = $this->_db->fetch_array($query)) {
			$rt = $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		
		return $temp;
	}
	function getPageInvokes($scr,$sign) {
		$temp = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt = $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}
	function updatePageInvokesState($scr,$sign,$invokeNames,$state) {
		if ($invokeNames && is_array($invokeNames)) {
			$this->_db->update("UPDATE ".$this->_tableName." SET state=".S::sqlEscape($state)." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign)." AND invokename IN(".S::sqlImplode($invokeNames).')');
		} elseif ($invokeNames) {
			$this->_db->update("UPDATE ".$this->_tableName." SET state=".S::sqlEscape($state)." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign)." AND invokename=".S::sqlEscape($invokeNames));
		} else {
			$this->_db->update("UPDATE ".$this->_tableName." SET state=".S::sqlEscape($state)." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign));
		}
	}
	//在模块被重复使用时会有问题
	function getSignByInvokeName($name) {
		return $this->_db->get_value("SELECT sign FROM ".$this->_tableName." WHERE invokename=".S::sqlEscape($name));
	}
	
	function getByUnique($scr,$sign,$invokeName){
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign)." AND invokename=".S::sqlEscape($invokeName));
		return $this->_unserializeData($temp);
	}
	function update($id,$array) {
		$array = $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update("UPDATE ".$this->_tableName." SET ".S::sqlSingle($array,false)." WHERE id=".S::sqlEscape($id));
	}
	function insertData($array){
		$array	= $this->_checkData($array);
		if (!$array || !$array['scr'] || !$array['sign'] || !$array['invokename']) {
			return null;
		}
		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}

	function getStruct(){
		return array('id','scr','sign','invokename','pieces','state','ifverify','title');
	}
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		return $data;
	}
	function _serializeData($data) {
		if (isset($data['pieces']) && is_array($data['pieces'])) $data['pieces'] = serialize($data['pieces']);
		return $data;
	}

	function _unserializeData($data) {
		if ($data['pieces']) $data['pieces'] = unserialize($data['pieces']);
		return $data;
	}

}
?>