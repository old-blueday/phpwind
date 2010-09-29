<?php
!defined('P_W') && exit('Forbidden');

class Weibo {
	var $base;
	var $db;

	function Weibo($base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	function bind($userId, $weiboType, $bindInfo) {
		if ($userId <= 0 || '' == $weiboType) return new ApiResponse(false);
		
		$bindService = $this->_getBindService();
		return new ApiResponse($bindService->localBind($userId, $weiboType, $bindInfo));
	}
	
	function unBind($userId, $weiboType) {
		if ($userId <= 0 || '' == $weiboType) return new ApiResponse(false);
		
		$bindService = $this->_getBindService();
		return new ApiResponse($bindService->localUnBind($userId, $weiboType));
	}
	
	function send($userId, $weiboType, $weiboData) {
		if ($userId <= 0 || '' == $weiboType || !isset($weiboData['content']) || '' == $weiboData['content']) return new ApiResponse(false);
		$photos = isset($weiboData['photos']) && $weiboData['photos'] ? $weiboData['photos'] : array();
		$extra = $photos ? array('sinaPhotos'=>$photos) : array();
		
		$weiboService = $this->_getWeiboService();
		$translator = L::loadClass('SiteWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SiteWeiboContentTranslator */
		$weiboId = $weiboService->send($userId, $translator->translate($weiboData['content']), $weiboType, 0, $extra);
		return new ApiResponse($weiboId);
	}
	
	function setAppStatus($status) {
		$status = $status ? 1 : 0;
		
		require_once(R_P.'admin/cache.php');
		setConfig('db_sinaweibo_status', $status);
		updatecache_c();
		return new ApiResponse(true);
	}
	
	/**
	 * @return PW_WeiboBindService
	 */
	function _getBindService() {
		return L::loadClass('WeiboBindService', 'sns/weibotoplatform');
	}
	
	/**
	 * @return PW_Weibo
	 */
	function _getWeiboService() {
		return L::loadClass('weibo', 'sns');
	}
}
