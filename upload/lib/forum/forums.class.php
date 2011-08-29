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
	
	/*
	 * 删除版块管理员
	 * */
	function deleteForumAdmin($username,$fid = 0) {
		$fid = intval($fid);
		$forumsDao = $this->getForumsDao();
		$f_admin = $forumsDao->getForumAdmin($fid);
		
		foreach($f_admin as $k=>$v){
			if(false !== $key = array_search($username,$v)){
				unset($v[$key]);
				$forumsDao->_update(array('forumadmin',implode(',',$v)), $k);
			}
		}
	}

	function getTucoolForums(){
		$tucoolForums = array();
		$fids = $this->getAllForumIds();
		$forumsDao = $this->getForumsDao();
		$forumSets = $forumsDao->getForumSetsByFids($fids);
		if ($forumSets) {
			foreach ($forumSets as $k=>$v) {
				$forumset = array();
				$v = @unserialize($v['forumset']);
				if(!$v['iftucool']) continue;
				$forumset['tucoolpic'] = intval($v['tucoolpic']);
				S::isArray($forumset) && $tucoolForums[$k] = $forumset;
			}
		}
		if ($tucoolForums) {
			$forums = $forumsDao->getFormusByFids(array_keys($tucoolForums),'fid,name');
			foreach ($forums as $k=>$v) {
				$tucoolForums[$k] = array_merge($tucoolForums[$k],$v);
			}
		}
		return $tucoolForums;
	}
	function getAllForumIds() {
		$forums = getForumCache();
		foreach ($forums as $v) {
			$fids[] = $v['fid'];
		}
		return $fids;
	}
}