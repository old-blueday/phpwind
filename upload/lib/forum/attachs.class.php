<?php
!defined('P_W') && exit('Forbidden');
/**
 * 附件服务层
 * @author liuhui @2010-4-27
 * @version phpwind 8.0
 */
class PW_Attachs {
	
	function countMultiUpload($userId){
		$userId = intval($userId);
		if( $userId < 1 ){
			return false;
		}
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->countMultiUpload($userId);
	}
	
	function getDiaryAttachsBydid($id) {
		if(!$id) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getDiaryAttachsBydid($id);
	}
	
	function delByids($ids) {
		if(!$ids) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->delete($ids);
	}
	
	function getByUids($uids) {
		if(!$uids) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getByUids($uids);
	}
	
	function getAttachsDao(){
		static $sAttachsDao;
		if(!$sAttachsDao){
			$sAttachsDao = L::loadDB('attachs', 'forum');
		}
		return $sAttachsDao;
	}
}