<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 帖子列表
 * 
 * @package Thread
 */
class PW_ThreadList {
	var $_db;
	var $_connect = FALSE; //缓存连接标识
	var $_tableName = "pw_threads"; //数据表名
	var $_number = 1000; // 获取数据个数
	var $_prefix = "threadlist_"; // 缓存KEY前缀
	var $_expire = 300; // 缓存失效时间
	var $_prePage = 20; // 帖子列表页每页个数
	var $_exist = FALSE; // 检查Memcache是否安装
	var $_baseTop = - 100;
	var $_baseForward = - 1;
	var $_topDay = 1000;
	function PW_ThreadList() {
		$this->_init();
		$this->_db = $GLOBALS['db'];
	}
	function _init() {
		if ($this->_isMemecacheOpen()) {
			$this->_exist = TRUE;
		}
	}
	
	/**
	 * 获取帖子列表
	 * @param <type> $forumId
	 * @param <type> $offset
	 * @param <type> $limit
	 * @return <type>
	 */
	function getThreads($forumId, $offset = 0, $limit = 20) {
		if (intval($forumId) < 1) {
			return null;
		}
		if ($this->_exist == FALSE) {
			return $this->_getThreadsByFroumId($forumId, $offset, $limit);
		}
		$threadIds = $this->_getIdsByForumId($forumId, $offset, $limit);
		return $this->_getThreadsByThreadIds($threadIds);
	}
	
	/**
	 *  获取版块某页的帖子ID列表
	 * @param <int> $forumId
	 * @param <int> $page
	 * @return <array>
	 */
	function _getIdsByForumId($forumId, $offset = 0, $limit = 20) {
		if ($this->_exist == FALSE) {
			return null;
		}
		$result = $this->_getThreadIdsByForumId($forumId);
		if (!$result) {
			return null;
		}
		//$result = array_flip($result);
		//return array_slice($result,$offset,$limit);
		$result = $this->PW_Array_Slice($result, $offset, $limit);
		return ($result) ? array_keys($result) : array();
	}
	
	/**
	 * 重新排序缓存中的帖子列表
	 * 如果已经存在则排序，否则弹出最后一个值，增加一个新值
	 * @param <type> $forumId
	 * @param <type> $threadId
	 * @return <type>
	 */
	function updateThreadIdsByForumId($forumId, $threadId, $t = 0) {
		if (intval($forumId) < 1 || intval($threadId) < 1) {
			return null;
		}
		if ($this->_exist == FALSE) {
			return null;
		}
		$result = $this->_getThreadIdsByForumId($forumId);
		if (!$result) {
			return null;
		}
		$result = $this->sort($result, $threadId, $t);
		if ($result) {
			$key = $this->_getKey($forumId);
			$memcacheConnection = $this->_getMemcacheConnection();
			$memcacheConnection->set($key, $result, $this->_expire);
		}
		return $result;
	}
	
	/**
	 * 从帖子列表中去掉帖子ID
	 * @param <type> $forumId
	 * @param <type> $threadId
	 * @return <type>
	 */
	function removeThreadIdsByForumId($forumId, $threadId) {
		if (intval($forumId) < 1 || intval($threadId) < 1) {
			return null;
		}
		if ($this->_exist == FALSE) {
			return null;
		}
		$result = $this->_getThreadIdsByForumId($forumId);
		if (!$result) {
			return null;
		}
		if (isset($result[$threadId])) {
			unset($result[$threadId]);
		}
		$key = $this->_getKey($forumId);
		$memcacheConnection = $this->_getMemcacheConnection();
		if ($result) {
			$memcacheConnection->set($key, $result, $this->_expire);
		} else {
			$memcacheConnection->delete($key);
		}
		return $result;
	}
	
	/**
	 *  清空某个版块的缓存
	 * @param <type> $forumId
	 * @return <type>
	 */
	function clearThreadIdsByForumId($forumId) {
		if (intval($forumId) < 1) {
			return null;
		}
		if ($this->_exist == FALSE) {
			return null;
		}
		$key = $this->_getKey($forumId);
		$memcacheConnection = $this->_getMemcacheConnection();
		return $memcacheConnection->delete($key);
	}
	
	/**
	 * 刷新某个版块的缓存
	 * @param <type> $forumId
	 */
	function refreshThreadIdsByForumId($forumId) {
		if (intval($forumId) < 1) {
			return null;
		}
		if ($this->_exist == FALSE) {
			return null;
		}
		$this->clearThreadIdsByForumId($forumId);
		return $this->_getThreadIdsByForumId($forumId);
	}
	
	function _getThreadIdsByForumId($forumId) {
		$key = $this->_getKey($forumId);
		$memcacheConnection = $this->_getMemcacheConnection();
		$result = $memcacheConnection->get($key);
		if ($result === FALSE) {
			$result = $this->_getThreadIdsByForumIdNoCache($forumId);
			if ($result) {
				$memcacheConnection->set($key, $result, $this->_expire);
			}
		}
		arsort($result);
		return $result;
	}
	
	function _getKey($forumId) {
		return $this->_prefix . $forumId;
	}
	
	function _getThreadIdsByForumIdNoCache($forumId) {
		$query = $this->_db->query("SELECT tid,topped,lastpost FROM " . $this->_tableName . " WHERE fid=" . pwEscape($forumId) . "AND ifcheck=1 AND topped=0 ORDER BY lastpost DESC LIMIT " . $this->_number);
		$result = array();
		$t = 1;
		while ($rt = $this->_db->fetch_array($query)) {
			$k = ($rt['topped']) ? ($this->_number - $t) + 24 * 60 * 60 * $this->_topDay : 0;
			$l = $k + $rt['lastpost'];
			$result[$rt['tid']] = $l;
			$t++;
		}
		return $result;
	}
	
	function _getThreadsByFroumId($forumId, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE fid=" . pwEscape($forumId) . "AND ifcheck=1 AND topped=0 ORDER BY lastpost DESC LIMIT $offset,$limit");
		$result = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
	
	function _getThreadsByThreadIds($threadIds) {
		if (!$threadIds || !is_array($threadIds)) {
			return null;
		}
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE tid IN (" . pwImplode($threadIds, false) . ") ORDER BY lastpost DESC");
		$result = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
	
	function _getMemcacheConnection() {
		if ($this->_connect === FALSE) {
			$this->_connect = L::loadClass('Memcache', 'utility');
		}
		return $this->_connect;
	}
	
	function _getConnection() {
		return $GLOBALS['db'];
	}
	
	function _isMemecacheOpen() {
		return class_exists("Memcache") && strtolower($GLOBALS['db_datastore']) == 'memcache';
	}
	
	//时间排序
	function sort($threadIds, $threadId, $t = 0) {
		//$threadId = (string)$threadId;
		if (isset($threadIds[$threadId])) {
			unset($threadIds[$threadId]);
		} else {
			(count($threadIds) >= $this->_number) && array_pop($threadIds);
		}
		$threadIds[$threadId] = $GLOBALS['timestamp'] + intval($t);
		arsort($threadIds);
		return $threadIds;
	}
	
	//类似于array_Slice，注意 array_slice() 默认将重置数组的键。自 PHP 5.0.2 起，可以通过将 preserve_keys 设为 TRUE 来改变此行为。
	function PW_Array_Slice($array, $offset, $length) {
		if (!is_array($array)) {
			return false;
		}
		$offset = ($offset <= 0) ? 0 : $offset;
		$length = ($length <= 0) ? count($array) : $length;
		$tmp = array();
		$count = 0;
		foreach($array as $k => $v) {
			if ($count >= $offset && $count < $length + $offset) {
				$tmp[$k] = $v;
			}
			if (count($tmp) == $length) {
				break;
			}
			$count++;
		}
		return ($tmp) ? $tmp : false;
	}
}
