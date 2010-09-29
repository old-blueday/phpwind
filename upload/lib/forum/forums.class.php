<?php
!defined('P_W') && exit('Forbidden');
/**
 * 版块服务层
 * @author liuhui @2010-4-25
 * @version phpwind 8.0
 */
class PW_Forums {
	function getForum($forumId){
		$forumId = intval($forumId);
		if( 1 > $forumId) return false;
		$forumsDao = $this->getForumsDao();
		return $forumsDao->get($forumId);
	}
	
	function getsNotCategory(){
		$forumsDao = $this->getForumsDao();
		return $forumsDao->getsNotCategory();
	}
	
	function getForumsDao(){
		static $sForumsDao;
		if(!$sForumsDao){
			$sForumsDao = L::loadDB('forums', 'forum');
		}
		return $sForumsDao;
	}
}