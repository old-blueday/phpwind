<?php
/**
 *  用户缓存相关服务类文件
 *  
 *	@package UserCache
 */

!defined('P_W') && exit('Forbidden');

/**
 * 用户缓存数据服务对象
 * type
 *		topic
 *		photo
 *		write
 *		diary
 * @package UserCache
 */

class PW_UserCache {

	var $_allowModes;
	var $_cache;

	function PW_UserCache() {
		$this->_allowModes = array(
			'article', 'cardtopic',//帖子缓存
			'reply', //回复过的帖子
			'carddiary',//日志缓存
			'cardphoto',//照片缓存
			'friend',
			'weibo',
			'colony',
			//'share',
			'messageboard',
			'recommendUsers',//回复帖子
			'friendsBirthday',
			'tags'
		);
		$this->_cache = Perf::checkMemcache();
	}
	
	function get($uid, $modes) {
		if (!$modes = $this->_checkModes($modes)) {
			return array();
		}
		$userCacheDb = $this->_getUserCacheDB();
		$data = $userCacheDb->getByModes($uid, $modes);
		if (count($data) == count($modes)) {
			return $data;
		}
		$array = array();
		$userInfoServer = L::loadClass('UserModeData', 'user');
		foreach ($modes as $key => $value) {
			if (isset($data[$key]))
				continue;
			$method = 'get_' . $key;
			if (method_exists($userInfoServer, $method)) {
				$array[$key] = $userInfoServer->$method($uid, is_array($value) ? $value['num'] : $value);
			}
		}
		$userCacheDb->saveModesData($uid, $array, $modes);
		return array_merge($data, $array);
	}

	function _checkModes($modes) {
		$array = array();
		foreach ($modes as $key => $value) {
			if (in_array($key, $this->_allowModes)) {
				$array[$key] = $value;
			}
		}
		return $array;
	}

	/**
	 * 删除用户缓存模块
	 * @param mixed $uid (int 123 or array('123', '321'))
	 * @param mixed $type (string 'topic' or array('topic', 'article'))
	 * @param int $typeid
	 * return array
	 */
	function delete($uid, $type = null, $typeid = null) {
		$userCacheDb = $this->_getUserCacheDB();
		if ($this->_cache) $userCacheDb->setAllKeys($this->_allowModes);
		return $userCacheDb->delete($uid, $type);
	}
	
	/**
	 * Get PW_UsercacheDB
	 * 
	 * @access protected
	 * @return PW_UsercacheDB
	 */
	
	function _getUserCacheDB() {
		return $this->_cache ? Perf::gatherCache('pw_usercache') : L::loadDB('UserCache', 'user');
	}
}
?>