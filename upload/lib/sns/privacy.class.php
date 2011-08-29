<?php 
/**
 * 用户相关服务类文件
 * 
 * @package Ouserdata
 */
!defined('P_W') && exit('Forbidden');

/**
 * 用户隐私相关服务对象
 * @author sky_hold@163.com
 * @package Privacy
 */
class PW_Privacy {
	
	function getIsFollow($uid, $key = null) {
		$ouserdataDb = $this->_getOuserdataDB();
		if ($rt = $ouserdataDb->get($uid)) {
			$array = array(
				'self'		=> $rt['self_isfollow'],
				'friend'	=> $rt['friend_isfollow'],
				'cnlesp'	=> $rt['cnlesp_isfollow'],
				'article'	=> $rt['article_isfollow'],
				'diary'		=> $rt['diary_isfollow'],
				'photos'	=> $rt['photos_isfollow'],
				'group'		=> $rt['group_isfollow'],
			);
		} else {
			$array = array(
				'self'		=> 1,				'friend'	=> 1,
				'cnlesp'	=> 1,				'article'	=> 1,
				'diary'		=> 1,				'photos'	=> 1,
				'group'		=> 1,				
			);
		}
		
		/* platform weibo app */
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen()) {
			foreach ($siteBindService->getBindTypes() as $key => $config) {
				$array[$key] = 1;
			}
		}
		
		return $key ? $array[$key] : $array;
	}

	function getIsFeed($uid, $key = null) {
		$ouserdataDb = $this->_getOuserdataDB();
		if ($rt = $ouserdataDb->get($uid)) {
			$array = array(
				'article'	=> $rt['article_isfeed'],
				'diary'		=> $rt['diary_isfeed'],
				'photos'	=> $rt['photos_isfeed'],
				'group'		=> $rt['group_isfeed'],
			);
		} else {
			$array = array(
				'article'	=> 1,
				'diary'		=> 1,
				'photos'	=> 1,
				'group'		=> 1,
			);
		}
		
		/* platform weibo app */
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen()) {
			if ($key && $siteBindService->isBind($key)) return true;
		}
		
		return $key ? $array[$key] : $array;
	}
	
	function getIsPriacy($uid,$key = null){
		$ouserdataDb = $this->_getOuserdataDB();
		if ($rt = $ouserdataDb->get($uid)) {	 	 		 	
			$array = array(
				'index'		=> $rt['index_privacy'],
				'profile'	=> $rt['profile_privacy'],
				'info'		=> $rt['info_privacy'],
				'credit'	=> $rt['credit_privacy'],
		    	'owrite'	=> $rt['owrite_privacy'],
				'msgboard'	=> $rt['msgboard_privacy'],
				'photos'	=> $rt['photos_privacy'],
				'diary'		=> $rt['diary_privacy']
			);
		} else {
			$array = array(
				'index'		=> 1,				'msgboard'	=> 1,
				'profile'	=> 1,				'photos'	=> 1,
		    	'credit'    => 1,				'diary'	    => 1,
				'owrite'	=> 1,				'info'		=> 1,
			);
		}
		return $key ? $array[$key] : $array;
	}
	
	function getAtFeedByUserNames($usernames){
		$data = array();
		if (!S::isArray($usernames)) return $data;
		$userService = L::loadClass('userservice','user');
		$users = $userService->getByUserNames($usernames);
		if (S::isArray($users)) {
			$userIds = array();
			foreach ($users as $k=>$v) {
				$userIds[$v['uid']] = $v['username'];
			}
			$ouserdataDb = $this->_getOuserdataDB();
			$atFeeds = (array)$ouserdataDb->findUserAtPrivacy(array_keys($userIds));
			if (S::isArray($atFeeds)) {
				foreach ($atFeeds as $v) {
					$data[$v['uid']] = array('at_isfeed' => $v['at_isfeed'] ,'username' => $userIds[$v['uid']] );
				}
			}
			foreach ($userIds as $uid=>$username){
				$data[$uid] or $data[$uid] = array('at_isfeed' => 0 ,'username' => $username);
			}
		}
		return $data;
	}
	/**
	 * Get PW_OuserdataDB
	 * 
	 * @access protected
	 * @return PW_OuserdataDB
	 */
	function _getOuserdataDB() {
		return L::loadDB('Ouserdata', 'sns');
	}
}
?>