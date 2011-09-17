<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 帖子管理操作类
 * 
 * @package Thread
 */
class PW_Threads {

	/**
	 * 删除pw_threads表的一条记录
	 *
	 * @param int $threadId 帖子id
	 * @return int
	 */
	function deleteByThreadId($threadId) {
		$threadId = S::int($threadId);
		if($threadId < 1){
			return false;
		}
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->deleteByThreadId($threadId);
	}
	
	/**
	 * 获取pw_threads表的一条记录
	 *
	 * @param int $threadId 帖子id
	 * @return array
	 */
	function getByThreadId($threadId) {
		$threadId = S::int($threadId);
		if($threadId < 1){
			return false;
		}
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->get($threadId);
	}
	
	/**
	 * 
	 * 更新tpc状态
	 * @param int $threadId
	 * @param int $b 位
	 * @param int $v 0|1
	 */
	function setTpcStatusByThreadId($threadId,$b,$v = '1') {
		$b = intval($b);
		$threadId = intval($threadId);
		if (!$threadId) return false;
		$v != 1 && $v = '0';
		$threadInfo = $this->getByThreadId($threadId);
		if (!S::isArray($threadInfo)) return false;
		$tpcstatus = $threadInfo['tpcstatus'];
		setstatus($tpcstatus, $b ,$v);
		$_dbService = L::loadDB('threads', 'forum');
		$_dbService->update(array('tpcstatus' => $tpcstatus) ,$threadId);
		return true;
	}
	
	//** oxFFEF for tucool status
	function setTpcStatusByThreadIds($tids,$mask=0xFFEF){
		$_dbService = L::loadDB('threads', 'forum');
		$_dbService->setTpcStatusByThreadIds($tids,$mask=0xFFEF);
	}
	
	/**
	 * 删除pw_threads表里一组记录
	 *
	 * @param array $threadIds 帖子id （数组格式）
	 * @return int
	 */	
	function deleteByThreadIds($threadIds) {
		$threadIds = (array) $threadIds;
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->deleteByThreadIds($threadIds);
	}
	
	/**
	 * 设置帖子@信息
	 * @param int $tid
	 * @param int $pid
	 * @param array $usernames
	 */
	function setAtUsers($tid,$pid,$usernames){
		$tid = intval($tid);
		$pid = intval($pid);
		if (!$tid || !S::isArray($usernames)) {
			return false;
		}
		$userService = L::loadClass('userservice','user');
		$users = $userService->getByUserNames($usernames);
		if ($users) {
			$userids = array();
			foreach ($users as $v) {
				$userids[] = $v['uid'];
			}
			$threadsAtDb = $this->_getThreadsAtDB();
			$threadsAtDb->adds($tid,$pid,$userids);
			return true;
		}
		return false;
	}
	
	function updateAtUsers($tid,$pid,$usernames){
		$tid = intval($tid);
		$pid = intval($pid);
		if (!$tid) return false;
		if (!S::isArray($usernames)){
			return $this->deleteAtUsers($tid,array($pid));
		} else {
			$userService = L::loadClass('userservice','user');
			$users = $userService->getByUserNames($usernames);
			if ($users) {
				$deleteUserIds = $userids = array();
				foreach ($users as $v) {
					$userids[] = $v['uid'];
				}
				$threadsAtDb = $this->_getThreadsAtDB();
				$threadAt = $threadsAtDb->gets($tid,array($pid));
				foreach ($threadAt as $v) {
					$k = array_search($v['uid'], $userids);
					if ($k !== false){
						unset($userids[$k]);
						continue;
					} else {
						$deleteUserIds[] = $v['uid'];
					}
				}
				$userids && $threadsAtDb->adds($tid,$pid,$userids);
				$deleteUserIds && $threadsAtDb->deleteByUids($tid,$pid,$deleteUserIds);
				return true;
			}
		}
	}
	
	function getAtUsers($tid,$pids) {
		$tid = intval($tid);
		if (!$tid || !S::isArray($pids)) {
			return false;
		}
		$data = $tmpData = $uids = array();
		$threadsAtDb = $this->_getThreadsAtDB();
		$threadAt = $threadsAtDb->gets($tid,$pids);
		if (!$threadAt) return $data;
		foreach ($threadAt as $v){
			$uids[] = $v['uid'];
			$tmpData[] = $v;
		}
		$uids = array_unique($uids);
		if ($uids) {
			$userService = L::loadClass('userservice','user');
			$userNames = $userService->getUserNamesByUserIds($uids);
		}
		if($userNames && $tmpData){
			foreach ($tmpData as $v) {
				$data[$v['pid']][] = $userNames[$v['uid']];
			}
		}
		return $data;
	}
	
	function deleteAtUsers($tid,$pids){
		$threadsAtDb = $this->_getThreadsAtDB();
		return $threadsAtDb->delete($tid,$pids);
	}
	/**
	 * 根据板块id删除帖子
	 *
	 * @param int $forumId 板块id
	 * @return int
	 */
	function deleteByForumId($forumId) {
		$forumId = S::int($forumId);
		if($forumId < 1){
			return false;
		}
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->deleteByForumId($forumId);
	}
	
	/**
	 * 根据作者id 删除帖子
	 *
	 * @param int $authorId 作者id
	 * @return int
	 */
	function deleteByAuthorId($authorId) {
		$authorId = S::int($authorId);
		if($authorId < 1){
			return false;
		}
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->deleteByAuthorId($authorId);
	}	
	
	function getLatestImageThreads($num){
		$num = intval($num);
		if (!$num) return array();
		$_dbService = L::loadDB('threads', 'forum');
		$threads = $_dbService->getLatestImageThreads($num);
		if ($threads) {
			$_attachService = L::loadDB('attachs', 'forum');
			$attaches = $_attachService->getByTid(array_keys($threads),null,null,'img');
			if (!$attaches) return $threads;
			krsort($attaches);
			foreach ($attaches as $k=>$v) {
				if (isset($threads[$v['tid']]['attachurl']) ) continue;
				$threads[$v['tid']]['attachurl'] = $v['attachurl'];
				$threads[$v['tid']]['ifthumb'] = $v['ifthumb'];
			}
		}
		return $threads;
	}
	
	function deleteTucoolThreadsByTids($tids){
		if(!S::isArray($tids)) return false;
		$_dbService = L::loadDB('threads', 'forum');
		$_dbService->deleteTucoolThreadsByTids($tids);
	}
	function getLatestThreads($forumIds, $starttime, $endtime, $offset, $limit){
		$limit = intval($limit);
		if ($limit<=0) return array();
		if (!is_array($forumIds) || !count($forumIds)) return array();
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getLatestThreads($forumIds, $starttime, $endtime, $offset, $limit);
	}
	/**
	 * 获取单条帖子的tmsgs表信息  by chenyun 2011-07-13
	 * 
	 * @param int $tid
	 * @return array
	 */
	function getTmsgByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return array(); 
		$_dbService = L::loadDB('threads', 'forum');
		return $_dbService->getTmsgByThreadId($tid);
	}
	
	/**
	 * 获取单条帖子信息
	 * 
	 * @param int $tid
	 * @return array
	 */
	function getByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return array(); 
		$threadsDb = $this->_getThreadsDB();
		return $threadsDb->get($tid);
	}

	/**
	 * @return PW_ThreasdDB
	 */
	function _getThreadsDB() {
		return L::loadDB('threads', 'forum');
	}
	
	function _getThreadsAtDB() {
		return L::loadDB('threadsat', 'forum');
	}
}

?>
