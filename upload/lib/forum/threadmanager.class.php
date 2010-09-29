<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 帖子管理操作类
 * 
 * @package Thread
 */
class PW_ThreadManager {
	
	var $_tableName = 'pw_threads';
	var $_memcache = FALSE;
	
	function PW_ThreadManager() {
		$this->_db = $GLOBALS['db'];
		$this->_memcache = $GLOBALS['db_memcache'];
	}
	
	function deleteByThreadId($forumId, $threadId) {
		if ($threadId < 1) {
			return false;
		}
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE tid=" . pwEscape($threadId));
		$result = $this->_db->affected_rows();
		if ($result && $this->_memcache) {
			$threadList = $this->_getThreadList();
			$threadList->removeThreadIdsByForumId($forumId, $threadId);
		}
		$threads = L::loadClass('Threads', 'forum');
		$threads->delThreads($threadId);
		return $result;
	}
	
	function deleteByThreadIds($forumId, $threadIds) {
		if (empty($threadIds)) return null;
		if (is_array($threadIds)) {
			$threads = L::loadClass('Threads', 'forum');
			$threads->delThreads($threadIds);
			$threadIds = pwImplode($threadIds);
		}
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE tid in(" . $threadIds . ")");
		$result = $this->_db->affected_rows();
		if ($result && $this->_memcache) {
			$threadList = $this->_getThreadList();
			$threadList->refreshThreadIdsByForumId($forumId);
		}
		return $result;
	}
	
	function deleteByForumId($forumId) {
		if ($forumId < 1) {
			return false;
		}
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE fid=" . pwEscape($forumId));
		$result = $this->_db->affected_rows();
		if ($result && $this->_memcache) {
			$threadList = $this->_getThreadList();
			$threadList->clearThreadIdsByForumId($forumId);
		}
		return $result;
	}
	
	function deleteByAuthorId($authorId) {
		if ($authorId < 1) {
			return false;
		}
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE authorid=" . pwEscape($authorId, false));
		return $this->_db->affected_rows();
	}
	
	function _getThreadList() {
		L::loadClass('threadlist', 'forum', false);
		return new PW_ThreadList();
	}
	
	function _getConnection() {
		return $GLOBALS['db'];
	}
}

?>
