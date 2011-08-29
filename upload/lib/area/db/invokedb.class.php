<?php
!defined('P_W') && exit('Forbidden');
class PW_InvokeDB extends BaseDB {
	var $_tableName = "pw_invoke";
	
	function getInvokes($page,$prePage) {
		$page = (int) $page;
		$page<=0 && $page =1;
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." ".S::sqlLimit($page*$prePage,$prePage));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt	= $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}

	function getDataByName($name) {
		$temp	= $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE name=".S::sqlEscape($name));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}

	function getDataById($id) {
		$temp	= $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}

	function updateByName($name,$array) {
		$array	= $this->_checkData($array);
		if (!$array) {
			return null;
		}
		$this->_db->update("UPDATE ".$this->_tableName." SET ".S::sqlSingle($array,false)." WHERE name=".S::sqlEscape($name));
	}

	function count($type='') {
		$sqladd = '';
		if ($type) {
			$sqladd = $this->_getSqlAdd($type);
			if (!$sqladd) return 0;
		}
		return $this->_db->get_value("SELECT COUNT(*) AS count FROM ".$this->_tableName." $sqladd");
	}

	function insertData($array) {
		$array	= $this->_checkData($array);
		if (!$array || !$array['name']) {
			return null;
		}
		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function replaceData($array) {
		$array	= $this->_checkData($array);
		if (!$array || !$array['name']) {
			return null;
		}
		$this->_db->update("REPLACE INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function deleteByName($name){
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE name=".S::sqlEscape($name));
	}
	function deleteByNames($names){
		if (!is_array($names) || !$names) return null;
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE name IN(".S::sqlImplode($names).")");
	}

	function getDatesByNames($names) {
		if (!is_array($names) || !$names) return null;
		$temp	= array();
		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE name IN(".S::sqlImplode($names).")");
		while ($rt = $this->_db->fetch_array($query)) {
			$rt	= $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}
//begin
	function getEffectPageInvokes($scr,$sign,$ifverify = 0) {
		$temp = array();

		$ifverify = (int) $ifverify;
		$_sql_add = $ifverify ? " AND ifverify=".S::sqlEscape($ifverify): '';
		$query = $this->_db->query("SELECT id,name,title,ifapi,scr,sign,pieces,state,ifverify FROM ".$this->_tableName." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign)." AND state=0 $_sql_add");
		while ($rt = $this->_db->fetch_array($query)) {
			$rt = $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}
	function deleteByScrAndSign($scr,$sign) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign));
	}
	function updatePageInvokesState($scr,$sign,$invokeNames,$state) {
		if ($invokeNames && is_array($invokeNames)) {
			$this->_db->update("UPDATE ".$this->_tableName." SET state=".S::sqlEscape($state)." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign)." AND name IN(".S::sqlImplode($invokeNames).')');
		} elseif ($invokeNames) {
			$this->_db->update("UPDATE ".$this->_tableName." SET state=".S::sqlEscape($state)." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign)." AND name=".S::sqlEscape($invokeNames));
		} else {
			$this->_db->update("UPDATE ".$this->_tableName." SET state=".S::sqlEscape($state)." WHERE scr=".S::sqlEscape($scr)." AND sign=".S::sqlEscape($sign));
		}
	}
	
	function searchPageInvokes($array,$page,$prePage = 20) {
		$page = (int)$page-1 < 0 ? 0 : (int) $page-1;
		$temp = array();
		$_sql = "SELECT id,name,title,ifapi,scr,sign,pieces,state,ifverify FROM ".$this->_tableName." ".$this->_getSearchSql($array).' ORDER by state ASC, id ASC '.S::sqlLimit($page*$prePage,$prePage);
		$query = $this->_db->query($_sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$rt = $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}
	function searchCount($array) {
		$_sql = "SELECT COUNT(*) as count FROM ".$this->_tableName." ".$this->_getSearchSql($array);
		return $this->_db->get_value($_sql);
	}
	function _getSearchSql($array) {
		$array = $this->_checkAllowField($array,$this->getStruct());
		$_sql_where = ' WHERE 1 ';
		foreach ($array as $key=>$value) {
			if ($key == 'name') {
				$_sql_where .= " AND name LIKE ".S::sqlEscape("%$value%");
			} elseif ($key == 'title') {
				$_sql_where .= " AND title LIKE ".S::sqlEscape("%$value%");
			}  elseif ($key == 'sign' && $value) {
				$_sql_where .= " AND sign=".S::sqlEscape($value);
			} else {
				$_sql_where .= " AND $key=".S::sqlEscape($value);
			}
		}
		return $_sql_where;
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
//end	
	/*
	 * private functions
	 */
	function _getSqlAdd($type,$join=false) {
		$sqladd = '';
		$pw_tpl = L::loadDB('Tpl', 'area');
		if ($type) {
			$tplids = $pw_tpl->getTplIdsByType($type);
			if ($tplids) {
				$field 	= $join ? 'i.tplid':'tplid';
				$sqladd = " WHERE $field IN (".S::sqlImplode($tplids).")";
			}
		}
		return $sqladd;
	}
	function getStruct() {
		return array('id','name','tplid','tagcode','parsecode','title','ifapi','scr','sign','pieces','state','ifverify');
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