<?php
!defined('P_W') && exit('Forbidden');

/**
 * PW数据HASH缓存机制,类memcached接口
 * 通过$_cache = getDatastore();实例化
 *
 * @package Cache
 */
class PW_DBCache {
	var $table = 'pw_datastore';
	var $cache = null;
	var $now = null;
	var $isExpire = false;
	function PW_DBCache() {
		$this->cache = $GLOBALS['db'];
		$this->now = $GLOBALS['timestamp'];
	}
	function flush() {
		$this->cache->update("TRUNCATE TABLE " . $this->table, false);
	}
	function delete($keys) {
		if (!empty($keys)) {
			if (is_array($keys)) {
				$this->cache->update("DELETE FROM " . $this->table . " WHERE skey IN (" . S::sqlImplode($keys, false) . ")");
			} else {
				$this->cache->update("DELETE FROM " . $this->table . " WHERE skey=" . S::sqlEscape($keys, false));
			}
		}
	}
	/**
	 * 批量更新缓存数据
	 *
	 * @param array $data 缓存数据,array('KEY'=>'VALUE')
	 * @param int $expire 缓存数据自动过期时间(秒)
	 * @return bool
	 */
	function update($data, $expire = 180) {
		$tmpvhash = $dcache = $kcache = array();
		$expire = $this->now + $expire;
		$keys = array_keys($data);
		if ($keys) {
			$query = $this->cache->query("SELECT skey,vhash FROM " . $this->table . " WHERE skey IN (" . S::sqlImplode($keys, false) . ")");
			while ($rt = $this->cache->fetch_array($query)) {
				$tmpvhash[$rt['skey']] = $rt['vhash'];
			}
		}
		foreach($data as $key => $value) {
			$v = $this->_serialize($value);
			$vhash = md5($v);
			if (!isset($tmpvhash[$key]) || $tmpvhash[$key] != $vhash) {
				$dcache[] = array(
					$key,
					$expire,
					$vhash,
					$v
				);
			} else {
				$kcache[] = $key;
			}
		}
		if ($dcache) {
			$this->cache->update("REPLACE INTO " . $this->table . " (skey,expire,vhash,value) VALUES " . S::sqlMulti($dcache, false));
		}
		if ($kcache) {
			$this->cache->update("UPDATE " . $this->table . " SET expire=" . S::sqlEscape($expire, false) . "WHERE skey IN (" . S::sqlImplode($kcache, false) . ")");
		}
		$this->_expire();
	}
	/**
	 * 更新指定KEY的缓存数据
	 *
	 * @param string $key 缓存KEY
	 * @param string $value
	 * @param int $expire
	 * @return bool
	 */
	function set($key, $value, $expire = 180) {
		if ($expire > 0) {
			$expire = $this->now + $expire;
			$v = $this->_serialize($value);
			$vhash = md5($v);
			$tmpvhash = $this->cache->get_value("SELECT vhash FROM " . $this->table . " WHERE skey=" . S::sqlEscape($key, false));
			if ($vhash != $tmpvhash) {
				$dcache = array(
					'skey' => $key,
					'expire' => $expire,
					'vhash' => $vhash,
					'value' => $v
				);
				$this->cache->update("REPLACE INTO " . $this->table . " SET " . S::sqlSingle($dcache, false));
			} else {
				$this->cache->update("UPDATE " . $this->table . " SET expire=" . S::sqlEscape($expire, false) . "WHERE skey=" . S::sqlEscape($key, false));
			}
		}
		$this->_expire();
	}
	/**
	 * 获取指定KEY的数据
	 *
	 * @param string|array $keys
	 * @return string|array
	 */
	function get($keys) {
		if (empty($keys)) return array();
		if (is_array($keys)) {
			$data = array();
			$query = $this->cache->query("SELECT skey,value FROM " . $this->table . " WHERE skey IN (" . S::sqlImplode($keys, false) . ") AND expire > " . S::sqlEscape($this->now, false));
			while ($rt = $this->cache->fetch_array($query)) {
				$data[$rt['skey']] = $this->_unserialize($rt['value']);
			}
		} else {
			$data = $this->cache->get_value("SELECT value FROM " . $this->table . " WHERE skey=" . S::sqlEscape($keys, false) . "AND expire > " . S::sqlEscape($this->now, false));
			$data = $this->_unserialize($data);
		}
		return $data;
	}
	function _serialize($value) {
		$value = serialize($value);
		return $value;
	}
	function _unserialize($value) {
		if ($value) {
			$tmpValue = unserialize($value);
			$tmpValue !== false && $value = $tmpValue;
		}
		return $value;
	}
	function _expire() {
		if (!$this->isExpire) {
			$expire = $this->now - 86400;
			$this->cache->update("DELETE FROM " . $this->table . " WHERE expire<" . S::sqlEscape($expire, false));
			$this->isExpire = true;
		}
	}
}
?>