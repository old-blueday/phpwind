<?php
!defined('P_W') && exit('Forbidden');

class Weibo {
	var $base;
	var $db;
	
	function Weibo($base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	function setAppStatus($status, $bindTypes) {
		$siteBindService = $this->_getSiteBindService();
		return new ApiResponse($status ? $siteBindService->open($bindTypes) : $siteBindService->close());
	}
	
	function setSiteBindInfo($options) {
		$siteBindInfoService = $this->_getSiteBindInfoService();
		return new ApiResponse($siteBindInfoService->save($options));
	}
	
	function bind($userId, $bindType, $bindInfo) {
		if ($userId <= 0 || '' == $bindType) return new ApiResponse(false);
		
		$userBindService = $this->_getUserBindService();
		return new ApiResponse($userBindService->localBind($userId, $bindType, $bindInfo));
	}
	
	function unBind($userId, $bindType) {
		if ($userId <= 0 || '' == $bindType) return new ApiResponse(false);
		
		$userBindService = $this->_getUserBindService();
		return new ApiResponse($userBindService->localUnBind($userId, $bindType));
	}
	
	function send($userId, $weiboType, $weiboData) {
		if ($userId <= 0 || '' == $weiboType || !isset($weiboData['content']) || '' == $weiboData['content']) return new ApiResponse(false);
		if (!$this->_checkIsUserExist($userId)) return new ApiResponse(false);
		if (!$this->_isAllowSend($userId)) return new ApiResponse(false);
		
		$photos = isset($weiboData['photos']) && $weiboData['photos'] ? $weiboData['photos'] : array();
		$extra = $photos ? array('photos' => $photos) : array();
		$forwardWeiboId = isset($weiboData['forwardWeiboId']) ? intval($weiboData['forwardWeiboId']) : 0;
		
		$weiboService = $this->_getWeiboService();
		$translator = L::loadClass('SiteWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SiteWeiboContentTranslator */
		if ($forwardWeiboId) {
			$extra['noSync'] = true;
			$weiboId = $weiboService->send($userId, $translator->translate($weiboData['content']), 'transmit', $forwardWeiboId, $extra);
			$weiboService->updateCountNum(array('transmit' => 1), $forwardWeiboId);
		} else {
			$weiboId = $weiboService->send($userId, $translator->translate($weiboData['content']), $weiboType, 0, $extra);
		}
		return new ApiResponse($weiboId);
	}
	
	function sendComment($userId, $weiboCommentData) {
		if ($userId <= 0 || !isset($weiboCommentData['content']) || '' == $weiboCommentData['content'] || !isset($weiboCommentData['commentWeiboId']) || $weiboCommentData['commentWeiboId'] <= 0) return new ApiResponse(false);
		if (!$this->_checkIsUserExist($userId)) return new ApiResponse(false);
		if (!$this->_isAllowSend($userId)) return new ApiResponse(false);
		
		$translator = L::loadClass('SiteWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SiteWeiboContentTranslator */
		$weiboCommentService = $this->_getWeiboCommentService();
		$weiboCommentId = $weiboCommentService->comment($userId, $weiboCommentData['commentWeiboId'], $translator->translate($weiboCommentData['content']), array('noSync' => true));
		
		$weiboService = $this->_getWeiboService();
		$weiboService->updateCountNum(array('replies' => 1), $weiboCommentData['commentWeiboId']);
		
		return new ApiResponse($weiboCommentId);
	}
	
	function updateLoginSession($sessionId, $sessionData) {
		if ('' == $sessionId) return new ApiResponse(false);
		
		$loginService = $this->_getWeiboLoginService();
		return new ApiResponse($loginService->updateLoginSession($sessionId, $sessionData));
	}
	
	
	
	function _checkIsUserExist($userId) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$user = $userService->get($userId);
		if ($user) return true;
		
		$userBindService = $this->_getUserBindService();
		$siteBindService = $this->_getSiteBindService();
		foreach ($siteBindService->getBindTypes() as $bindType => $config) {
			$userBindService->unbind($userId, $bindType);
		}
		return false;
	}
	
	function _isAllowSend($userId) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$user = $userService->get($userId);
		if (!$user) return false;
		
		$groupId = $user['groupid'];
		$groupId == '-1' && $groupId = $user['memberid'];

		if ($groupId == 6 || getstatus($user['userstatus'], PW_USERSTATUS_BANUSER)) return false; //会员禁言

		if (file_exists(D_P."data/groupdb/group_$groupId.php")) {
			include Pcv(D_P."data/groupdb/group_$groupId.php");
		} else {
			include(D_P.'data/groupdb/group_1.php');
		}
		if (!$_G['allowvisit']) return false; //用户组没权限（包含注册未审核）
		
		return true;
	}
	
	/**
	 * @return PW_WeiboSiteBindService
	 */
	function _getSiteBindService() {
		return L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service');
	}
	
	/**
	 * @return PW_WeiboSiteBindInfoService
	 */
	function _getSiteBindInfoService() {
		return L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service');
	}
	
	/**
	 * @return PW_WeiboUserBindService
	 */
	function _getUserBindService() {
		return L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service');
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

	/**
	  * @return PW_WeiboLoginService
	  */
	function _getWeiboLoginService() {
		return L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service');
	}
}
