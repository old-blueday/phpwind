<?php 
/**
 * 用户相关服务类文件
 * 
 * @package Ouserdata
 */
!defined('P_W') && exit('Forbidden');

/**
 * 用户相关服务对象
 * 
 * @package Ouserdata
 */
class PW_Ouserdata {
	
	function get($userId) {
		$ouserdataDb = $this->_getOuserdataDB();
		return 	$ouserdataDb->get($userId);	
		
	}

	/**
	 * 根据用户范围，筛选记录权限
	 * 
	 * @param array		$userIds		array(1,2,3,4,......n)	
	 * @return array 	
	 */ 
	function findUserOwritePrivacy($userIds) {
		$ouserdataDb = $this->_getOuserdataDB();
		return 	$ouserdataDb->findUserOwritePrivacy($userIds);	
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