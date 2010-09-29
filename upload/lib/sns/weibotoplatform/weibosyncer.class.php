<?php
!defined('P_W') && exit('Forbidden');

class PW_WeiboSyncer {
	function send($weiboId, $type, $message, $extra = array()) {
		$translator = L::loadClass('SinaWeiboContentTranslator', 'sns/weibotoplatform'); /* @var $translator PW_SinaWeiboContentTranslator */
		
		$data = array();
		$data['weiboId'] = $weiboId;
		$data['siteUserId'] = $message['uid'];
		$data['content'] = $translator->translate($type, $message, $extra);
		$data['timestamp'] = $message['postdate'];
		$data['photos'] = $this->_getPhotosFromExtra($extra);

		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->post('weibo.sync.send', $data);
	}
	
	function _getPhotosFromExtra($extra) {
		if (!isset($extra['photos'])) return array();
		
		$photos = array();
		foreach ($extra['photos'] as $photoId => $photoInfo) {
			$photoUrl = getphotourl($photoInfo['path']);
			if (strpos($photoUrl, 'http://') === false) $photoUrl = $this->_getSiteUrl() . $photoUrl;
			$photos[] = array(
				'url' => $photoUrl,
				'title' => $photoInfo['pintro'],
			);
		}
		return $photos;
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
}

