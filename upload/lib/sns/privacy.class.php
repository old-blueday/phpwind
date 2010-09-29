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
				'sinaweibo'	=> $rt['sinaweibo_isfollow'],
			);
		} else {
			$array = array(
				'self'		=> 1,				'friend'	=> 1,
				'cnlesp'	=> 1,				'article'	=> 1,
				'diary'		=> 1,				'photos'	=> 1,
				'group'		=> 1,				'sinaweibo'	=> 1,
			);
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
				'sinaweibo'	=> 1,
			);
		} else {
			$array = array(
				'article'	=> 1,
				'diary'		=> 1,
				'photos'	=> 1,
				'group'		=> 1,
				'sinaweibo'	=> 1,
			);
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