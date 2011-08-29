<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 通用聚合服务
 */
class GatherInfo_General_Service {

	/**
	 * 该帖子的内容改变了，需要更新帖子缓存
	 *
	 * @param array $information 格式array('tid'=>$tids)
	 * @return boolean
	 */
	function changeThreadWithThreadIds($information) {
		if (!Perf::checkMemcache() || !isset($information['tid'])) return true;
		$threadIds = is_array ( $information ['tid'] ) ? $information ['tid'] : array ($information ['tid'] );
		$_cacheService = Perf::gathercache ( 'pw_threads' );
		$_cacheService->clearCacheForTmsgByThreadIds ( $threadIds );
		return $_cacheService->clearCacheForThreadByThreadIds ( $threadIds );
	}
	
	/**
	 * 有些查询语句无法改造成结构化查询，所以通过gatherinfo收集
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeThreads($information) {
		return Perf::gatherQuery('update', array('pw_threads'), $information);
	}
	
	/**
	 * 有些查询语句无法改造成结构化查询，所以通过gatherinfo收集
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changePosts($information) {
		return Perf::gatherQuery('update', array($information['_tablename']), $information);
	}
	
	function deletePosts($information){
		return Perf::gatherQuery('delete', array($information['_tablename']), $information);
	}

	/**
	 * 帖子详细信息改变时，需要更新缓存
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeTmsgWithThreadIds($information){
		if (!Perf::checkMemcache() || !isset($information['tid'])) return true;
		$threadIds = is_array ( $information ['tid'] ) ? $information ['tid'] : array ($information ['tid'] );
		$_cacheService = Perf::gathercache ( 'pw_threads' );
		return $_cacheService->clearCacheForTmsgByThreadIds ( $threadIds );
	}

	/**
	 * 该板块改变， 需要更新帖子列表缓存, 格式array('fid'=>$fids)
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeThreadWithForumIds($information){
		if (!Perf::checkMemcache() || !isset($information['fid'])) return true;
		$forumIds = is_array ( $information ['fid'] ) ? $information ['fid'] : array ($information ['fid'] );
		$_cacheService = Perf::gathercache ( 'pw_threads' );
		return $_cacheService->clearCacheForThreadListByForumIds ( $forumIds );
	}

	/**
	 * 帖子更新时，需要清理帖子列表 ， 在lib/forum/postmodify.class.php(363)调用
	 *
	 * @param array $information  
	 * @return boolean
	 */
	function changeThreadListWithThreadIds($information){
		if (!Perf::checkMemcache() || !isset($information['tid'])) return true;
		$threadIds = is_array ( $information ['tid'] ) ? $information ['tid'] : array ($information ['tid'] );
		$_cacheService = Perf::gathercache ( 'pw_threads' );
		$threads = $_cacheService->getThreadsByThreadIds($threadIds);
		if (is_array($threads)){
			$fid = array();
			foreach ($threads as $thread){
				$fid[] = $thread['fid'];
			}
			$fid && $_cacheService->clearCacheForThreadListByForumIds ( $fid );
		}
		return true;
	}

	/**
	 * 更新用户基本信息时，清除相应缓存
	 *
	 * @param array $information 格式array('uid'=>$uids)
	 * @return boolean
	 */
	function changeMembersWithUserIds($information){
		if (!isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		if (Perf::checkMemcache()){
			$_cacheService = Perf::gathercache ( 'pw_members' );
			return $_cacheService->clearCacheForMembersByUserIds( $userIds );
			//$_cacheService->clearCacheForMemberDataByUserIds( $userIds );
			//return $_cacheService->clearCacheForMemberInfoByUserIds( $userIds );
		}else {
			$_cacheService = Perf::gatherCache('pw_membersdbcache');
			return $_cacheService->clearMembersDbCacheByUserIds( $userIds );			
		}
	}

	/**
	 * 更新用户Data信息时，清除相应缓存
	 *
	 * @param array $information 格式array('uid'=>$uids)
	 * @return boolean
	 */
	function changeMemberDataWithUserIds($information){
		if (!Perf::checkMemcache() || !isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		$_cacheService = Perf::gathercache ( 'pw_members' );
		return $_cacheService->clearCacheForMemberDataByUserIds( $userIds );
	}

	/**
	 * 更新用户Info信息时，清除相应缓存
	 *
	 * @param array $information 格式array('uid'=>$uids)
	 * @return boolean
	 */
	function changeMemberInfoWithUserIds($information){
		if (!Perf::checkMemcache() || !isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		$_cacheService = Perf::gathercache ( 'pw_members' );
		return $_cacheService->clearCacheForMemberInfoByUserIds( $userIds );
	}

	/**
	 * 更新用户SingleRight信息时，清除相应缓存
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeSingleRightWithUserIds($information){
		if (!Perf::checkMemcache() || !isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		$_cacheService = Perf::gathercache ( 'pw_members' );
		return $_cacheService->clearCacheForSingleRightByUserIds( $userIds );
	}

	/**
	 * 更新用户MemberCredit信息时，清除相应缓存
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeMemberCreditWithUserIds($information){
		if (!isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		if (Perf::checkMemcache()){
			$_cacheService = Perf::gathercache ( 'pw_members' );
			return $_cacheService->clearCacheForMemberCreditByUserIds( $userIds );			
		}else{
			$_cacheService = Perf::gatherCache('pw_membersdbcache');
			return $_cacheService->clearCreditDbCacheByUserIds( $userIds );				
		}		
	}

	/**
	 * 更新用户群组信息（CmemberAndColony）时，清除相应缓存 
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeCmemberAndColonyWithUserIds($information){
		if (!isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		if (Perf::checkMemcache()){
			$_cacheService = Perf::gathercache ( 'pw_members' );
			return $_cacheService->clearCacheForCmemberAndColonyByUserIds( $userIds );			
		}else{
			$_cacheService = Perf::gatherCache('pw_membersdbcache');
			return $_cacheService->clearColonyDbCacheByUserIds( $userIds );				
		}
	}
	
	/**
	 * 更新用户群组信息（CmemberAndColony）时，清除相应缓存 
	 *
	 * @param array $information
	 * @return boolean
	 */
	/**
	function changeALLMembers($information = null){
		if (!Perf::checkMemcache() || !isset($information['uid'])) return true;
		$userIds = is_array ( $information ['uid'] ) ? $information ['uid'] : array ($information ['uid'] );
		$_cacheService = Perf::gathercache ( 'pw_members' );
		return $_cacheService->clearCacheForCmemberAndColonyByUserIds( $userIds );
	}
	**/
	
	/**
	 * 有些查询语句无法改造成结构化查询，所以通过gatherinfo收集
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeForumData($information){
		return Perf::gatherQuery('update', array('pw_forumdata'), $information);
	}
	
	/**
	 * 当发帖和发回复时，从memcache读取缓存数据然后更新它，而不是直接删除缓存
	 *
	 * @param array $information
	 * @return boolean
	 */
	function changeForumDataWithForumId($information = null){
		if (!Perf::checkMemcache()) return true;
		if (!S::isArray($information) || !($information = current($information)) || !isset($information['fid'])) return false;
		$fid = intval($information['fid']);
		$_cacheService = Perf::getCacheService();
		$_cacheInfo = $_cacheService->get(array('all_forums_info', 'forumdata_announce_' . $fid));
		$_unique = $GLOBALS['db_memcache']['hash'];
		// 更新index页面里版块缓存
		if (isset($_cacheInfo[$_unique . 'all_forums_info'])){
			$allForums = $_cacheInfo[$_unique . 'all_forums_info'];
			foreach ($information as $key => $value){
				if (in_array($key, array('article', 'topic','tpost','subtopic'))){
					$allForums[$fid][$key] = $allForums[$fid][$key] + $value;
				}else {
					$allForums[$fid][$key] = $value;
				}
			}
			$_cacheService->set('all_forums_info', $allForums, 300);
		}
		// 更新thread页面里版块和通告缓存
		if (isset($_cacheInfo[$_unique . 'forumdata_announce_' . $fid])){
			$forums = $_cacheInfo[$_unique . 'forumdata_announce_' . $fid];
			foreach ($information as $key => $value){
				if (in_array($key, array('article', 'topic','tpost','subtopic'))){
					$forums[$key] = $forums[$key] + $value;
				}else {
					$forums[$key] = $value;
				}
			}
			$_cacheService->set('forumdata_announce_' . $fid, $forums, 300);			
		}
		return true;
	}
}