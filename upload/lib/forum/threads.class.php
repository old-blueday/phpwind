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
	
}

?>
