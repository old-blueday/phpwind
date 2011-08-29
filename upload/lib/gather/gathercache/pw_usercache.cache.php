<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherCache_PW_UserCache_Cache extends GatherCache_Base_Cache {
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'usercache_';
	var $_allKeys = array();
	 
	function getByModes($uid, $modes) {
		$keys = $array = array();
		foreach ($modes as $key => $value) {
			$keys[] = $this->_getKeyForUserMode($uid, $key);
		}
		if ($result = $this->_cacheService->get($keys)) {
			foreach ($result as $key => $value) {
				$type = $this->_getTypeFromKey($key);
				$num = (S::isArray($modes[$type])) ? S::int($modes[$type]['num']) : $modes[$type];
				if ($num < $value['num']) {
					$array[$type] = array_slice($value['value'], 0, $num, true);
				} elseif ($num == $value['num']) {
					$array[$type] = $value['value'];
				}
			}
			return $array;
		}
		return array();
	}

	function saveModesData($uid, $data, $conf) {
		$array = array();
		foreach ($data as $key => $value) {
			$this->_cacheService->set(
				$this->_getKeyForUserMode($uid, $key),
				array(
					'num' => (S::isArray($conf[$key])) ? S::int($conf[$key]['num']) : $conf[$key],
					'value' => $value
				),
				(S::isArray($conf[$key]) && isset($conf[$key]['expire'])) ? S::int($conf[$key]['expire']) : 608400
			);
		}
	}

	function delete($uid, $type = null) {
		!$type && $type = $this->_allKeys;
		!is_array($type) && $type = array($type);
		!is_array($uid) && $uid = array($uid);
		foreach ($uid as $k) {
			foreach ($type as $v) {
				$this->_cacheService->delete($this->_getKeyForUserMode($k, $v));
			}
		}
	}

	function setAllKeys($keys) {
		$this->_allKeys = $keys;
	}

	function _getKeyForUserMode($uid, $mode) {
		return $this->_prefix . 'uid_' . $uid . '_mode_' . $mode;
	}
	
	function _getTypeFromKey($key) {
		return substr($key, strrpos($key, '_')+1);
	}
}