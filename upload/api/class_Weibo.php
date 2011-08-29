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
		$extra = $photos ? array('sinaPhotos' => $photos) : array();
		$forwardWeiboId = isset($weiboData['forwardWeiboId']) ? intval($weiboData['forwardWeiboId']) : 0;
		
		$weiboService = $this->_getWeiboService();
		$translator = L::loadClass('SiteWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SiteWeiboContentTranslator */
		if ($forwardWeiboId) {
			$extra['isSinaForward'] = true;
			$weiboId = $weiboService->send($userId, $translator->translate($weiboData['content']), 'transmit', $forwardWeiboId, $extra);
			$weiboService->updateCountNum(array('transmit' => 1), $forwardWeiboId);
		} else {
			$weiboId = $weiboService->send($userId, $translator->translate($weiboData['content']), $weiboType, 0, $extra);
		}
		return new ApiResponse($weiboId);
	}
	
	function setAppStatus($status) {
		$status = $status ? 1 : 0;
		
		require_once (R_P . 'admin/cache.php');
		setConfig('db_sinaweibo_status', $status);
		updatecache_c();
		return new ApiResponse(true);
	}
	
	function sendComment($userId, $weiboCommentData) {
		if ($userId <= 0 || !isset($weiboCommentData['content']) || '' == $weiboCommentData['content'] || !isset($weiboCommentData['commentWeiboId']) || $weiboCommentData['commentWeiboId'] <= 0) return new ApiResponse(false);
		
		$translator = L::loadClass('SiteWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SiteWeiboContentTranslator */
		$weiboCommentService = $this->_getWeiboCommentService();
		$weiboCommentId = $weiboCommentService->comment($userId, $weiboCommentData['commentWeiboId'], $translator->translate($weiboCommentData['content']), array('isSinaComment' => true));
		
		$weiboService = $this->_getWeiboService();
		$weiboService->updateCountNum(array('replies' => 1), $weiboCommentData['commentWeiboId']);
		
		return new ApiResponse($weiboCommentId);
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
	
	/**
	 * @return PW_Comment
	 */
	function _getWeiboCommentService() {
		return L::loadClass('comment', 'sns');
	}
}
