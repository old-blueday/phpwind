<?php
!defined('P_W') && exit('Forbidden');

/**
 *	type
 *		topic
 *		photo
 *		write
 *		diary
 */
class PW_UsercacheDB extends BaseDB {

	var $_tableName = "pw_usercache";
	var $now = 0;

	function PW_UsercacheDB() {
		parent::BaseDB();
		$this->now = $GLOBALS['timestamp'];
	}

	function update($uid, $type, $typeid, $value) {
		$data = array(
			'uid'	=> $uid,
			'type'	=> $type,
			'typeid'=> $typeid,
			'expire'=> $this->now + 608400,
			'value'	=> $this->_serialize($value)
		);

		$this->_db->update("REPLACE INTO " . $this->_tableName . " SET " . pwSqlSingle($data,false));
	}

	/**
	 * 保存用户模块缓存数据
	 * @param $uid int 用户id
	 * @param $modes array 获取模块数据及数量 array('article' => 1, 'write' => 2, ...)
	 */
	function saveModesData($uid, $data, $conf) {
		$array = array();
		foreach ($data as $key => $value) {
			$array[] = array(
				'uid'	=> $uid,
				'type'	=> $key,
				'expire'=> $this->now + 608400,
				'num'	=> $conf[$key],
				'value'	=> $this->_serialize($value)
			);
		}
		if ($array) {
			$this->_db->update("REPLACE INTO " . $this->_tableName . " (uid,type,expire,num,value) VALUES ". pwSqlMulti($array, false));
		}
	}

	function delete($uid, $type = null) {
		$this->_db->update('DELETE FROM ' . $this->_tableName . ' WHERE uid' . $this->_sqlIn($uid) . ($type ? ' AND type' . $this->_sqlIn($type) : ''));
	}
	
	/**
	 * 获取用户模块缓存数据
	 * @param $uid int 用户id
	 * @param $modes array 获取模块数据及数量 array('article' => 1, 'write' => 2, ...)
	 * return array
	 */
	function getByModes($uid, $modes) {
		$array = array();
		$query = $this->_db->query("SELECT type,num,value FROM " . $this->_tableName . " WHERE uid=" . pwEscape($uid) . ' AND type IN(' . pwImplode(array_keys($modes)) . ') AND expire>' . pwEscape($this->now, false));
		while ($rt = $this->_db->fetch_array($query)) {
			if ($modes[$rt['type']] < $rt['num']) {
				$array[$rt['type']] = array_slice($this->_unserialize($rt['value']), 0, $modes[$rt['type']], true);
			} elseif ($modes[$rt['type']] == $rt['num']) {
				$array[$rt['type']] = $this->_unserialize($rt['value']);
			}
		}
		return $array;
	}

	function get($uid,$type) {
		$rt = $this->_db->get_one("SELECT value,typeid FROM ".$this->_tableName." WHERE uid=".pwEscape($uid,false)."AND type=".pwEscape($type,false));
		$value = $this->_unserialize($rt['value']);
		return array('value'=>$value,'id'=>$rt['typeid']);
	}

	function getStruct() {
		return array('uid','type','typeid','expire','value');
	}

	function _checkData($data) {
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}

	function _sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . pwImplode($ids) . ')' : '=' . pwEscape($ids);
	}
}
?>