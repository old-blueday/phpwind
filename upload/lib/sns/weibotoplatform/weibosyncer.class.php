<?php
!defined('P_W') && exit('Forbidden');

class PW_WeiboSyncer {
	/**
	 * 同步微博
	 * 
	 * @param int $weiboId
	 * @param string $type
	 * @param array $message
	 * @param array $extra
	 * @return bool
	 */
	function send($weiboId, $type, $message, $extra = array()) {
		if ($weiboId <= 0 || !$message || !isset($message['content']) || '' == $message['content'] || !isset($message['uid'])) return false;
		if (!$this->isWeiboContentTypePermitSync($message['uid'], $type)) return false;
		
		$data = $this->_generateWeiboObject($weiboId, $type, $message, $extra);
		if ('transmit' == $type) $data['forward'] = $this->_getForwardWeiboData($message['objectid']);

		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.sync.send', $data));
		return isset($isSuccess->result) && $isSuccess->result;
	}
	
	
	function shareContent($userId, $content, $photo = null) {
		if ($userId < 1 || empty($content)) return false;
		$sinaWeiboContentTranslator = L::loadClass('SinaWeiboContentTranslator', 'sns/weibotoplatform/');
		$content = $sinaWeiboContentTranslator->commonTranslate($content);
		$data = array('siteUserId'=>$userId, 'content'=>$content, 'photo'=>$photo);
		//return true;
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.sync.share', $data));
		return isset($isSuccess->result) && $isSuccess->result;
	}
	
	/**
	 * 同步微博评论
	 * 
	 * @param int $commentId
	 * @param array $message
	 * @return bool
	 */
	function sendComment($commentId, $message) {
		if ($commentId <= 0 || !$message || !isset($message['content']) || '' == $message['content'] || !isset($message['uid'])) return false;
		if (!$this->isWeiboContentTypePermitSync($message['uid'], 'comment')) return false;
		
		$data = $this->_generateWeiboCommentObject($commentId, $message);
		
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.sync.sendcomment', $data));
		return isset($isSuccess->result) && $isSuccess->result;
	}
	
	/**
	 * 是否允许某个类型微博内容同步
	 * 
	 * @param int $userId
	 * @param string $weiboContentType
	 * @return bool 是否允许同步，默认为允许
	 */
	function isWeiboContentTypePermitSync($userId, $weiboContentType) {
		if (strpos($weiboContentType, '_') !== false) $weiboContentType = current(explode('_', $weiboContentType));
		
		$syncSetting = $this->getUserWeiboSyncSetting($userId);
		return isset($syncSetting[$weiboContentType]) ? $syncSetting[$weiboContentType] : true;
	}
	
	/**
	 * 获取用户微博同步设置
	 * 
	 * @param int $userId
	 * @return array (string)weiboContentType=>(bool)isAllowed
	 */
	function getUserWeiboSyncSetting($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return null;
		
		$userBindService = $this->_getUserBindService();
		$bindUser = $userBindService->getABind($userId);
		
		$syncSetting = isset($bindUser['info']['syncSetting']) ? $bindUser['info']['syncSetting'] : null;
		foreach ($this->_getUserWeiboSyncDefaultSetting() as $weiboContentType => $defaultRule) {
			$syncSetting[$weiboContentType] = isset($syncSetting[$weiboContentType]) ? $syncSetting[$weiboContentType] : $defaultRule;
		}
		return $syncSetting;
	}
	
	/**
	 * 更新用户微博同步设置
	 * 
	 * @param int $userId
	 * @param array $syncSetting
	 * @return bool
	 */
	function updateUserWeiboSyncSetting($userId, $syncSetting) {
		$userBindService = $this->_getUserBindService();
		$bindUser = $userBindService->getABind($userId);
		$bindInfo = $bindUser['info'];
		$bindInfo['syncSetting'] = $this->_checkUserWeiboSyncSettingItems($syncSetting);
		return $userBindService->updateBindInfo($userId, $bindUser['weibotype'], $bindInfo);
	}

	
	
	function _getUserWeiboSyncDefaultSetting() {
		return array(
			'article' => true,
			'diary' => true,
			'photos' => true,
			'group' => true,
			'transmit' => true,
			'comment' => true,
		);
	}
	
	function _checkUserWeiboSyncSettingItems($syncSetting) {
		$filter = array();
		foreach (array_keys($this->_getUserWeiboSyncDefaultSetting()) as $weiboContentType) {
			if (isset($syncSetting[$weiboContentType])) $filter[$weiboContentType] = (bool) $syncSetting[$weiboContentType];
		}
		return $filter;
	}
	
	
	function _getPhotosFromExtra($extra, $type = null) {
		if (!isset($extra['photos']) || !is_array($extra['photos']) || !count($extra['photos'])) return array();
		if (in_array($type, array('photos', 'group_photos'))) $extra['photos'] = array(current($extra['photos']));
		
		$photos = array();
		$isNotLocalPhoto = isset($extra['isNotLocalPhoto']) ? (bool) $extra['isNotLocalPhoto'] : false;
		foreach ($extra['photos'] as $photoId => $photoInfo) {
			if (!$isNotLocalPhoto) {
				$photoUrl = getphotourl($photoInfo['path']);
				if (strpos($photoUrl, 'http://') === false) $photoUrl = $this->_getSiteUrl() . $photoUrl;
			} else {
				$photoUrl = $photoInfo['path'];
			}
			$photos[] = array(
				'url' => $photoUrl,
				'title' => $photoInfo['pintro'],
			);
		}
		return $photos;
	}
	
	function _getForwardWeiboData($weiboId) {
		$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
		
		$message = $weiboService->getWeibosByMid($weiboId);
		$type = $weiboService->getType($type);
		$extra = $message['extra'] ? unserialize($message['extra']) : array();
		
		return $this->_generateWeiboObject($message['mid'], $type, $message, $extra);
	}
	
	function _generateWeiboObject($weiboId, $type, $message, $extra = array()) {
		$translator = L::loadClass('SinaWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SinaWeiboContentTranslator */
		
		$data = array();
		$data['weiboId'] = $weiboId;
		$data['siteUserId'] = $message['uid'];
		$data['content'] = $translator->translate($type, $message, $extra);
		$data['timestamp'] = $message['postdate'];
		$data['photos'] = $this->_getPhotosFromExtra($extra, $type);
		return $data;
	}
	
	function _generateWeiboCommentObject($commentId, $message) {
		$translator = L::loadClass('SinaWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SinaWeiboContentTranslator */
		
		$data = array();
		$data['commentId'] = $commentId;
		$data['weiboId'] = $message['mid'];
		$data['siteUserId'] = $message['uid'];
		$data['content'] = $translator->commonTranslate($message['content']);
		$data['timestamp'] = $message['postdate'];
		return $data;
	}
	
	function _getSiteUrl() {
		static $siteUrl = null;
		if (null === $siteUrl) {
			global $db_bbsurl;
			$siteUrl = trim($db_bbsurl, '/') . '/';
		}
		return $siteUrl;
	}
	
	/**
	 * @return PlatformApiClient
	 */
	function _getPlatformApiClient() {
		static $client = null;
		if (null === $client) {
			global $db_sitehash, $db_siteownerid;
			L::loadClass('client', 'utility/platformapisdk', false);
			$client = new PlatformApiClient($db_sitehash, $db_siteownerid);
		}
		return $client;
	}
	
	/**
	 * @return PW_WeiboUserBindService
	 */
	function _getUserBindService() {
		return L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service');
	}
}

