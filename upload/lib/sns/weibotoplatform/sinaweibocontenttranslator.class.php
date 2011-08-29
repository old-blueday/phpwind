<?php

class PW_SinaWeiboContentTranslator {
	var $_allowedTypes = array('article', 'diary', 'group_article', 'group_active', 'photos', 'group_photos');
	
	function translate($siteWeiboType, $siteWeiboData, $siteWeiboExtra = array()) {
		if (!in_array($siteWeiboType, $this->_allowedTypes)) return $this->commonTranslate($siteWeiboData['content']);
		
		$translate = $this->_loadTranslate($siteWeiboType);
		return $this->commonTranslate($translate->translate($siteWeiboData, $siteWeiboExtra));
	}
	
	function commonTranslate($content) {
		$content = SinaWeiboSmileTranslator::translate($content);
		$content = SinaWeiboWincodeTranslator::translate($content);
		$content = str_replace(array('&gt;', '&lt;', '&amp;', '&quot;', '&#39;', '&#60;'), array('>', '<', '&', '"', "'", '<'), $content);
		$content = str_replace(array("\\\"", "\\'"), array('"', "'"), $content);
		return $content;
	}
	
	function _loadTranslate($siteWeiboType) {
		$className = 'SinaWeiboContentTranslate_' . $this->_formatTranslateName($siteWeiboType);
		return new $className();
	}
	
	function _formatTranslateName($siteWeiboType) {
		$siteWeiboType = explode('_', $siteWeiboType);
		$translateName = '';
		foreach ($siteWeiboType as $v) {
			$translateName .= ucfirst(strtolower($v));
		}
		return $translateName;
	}
}

class SinaWeiboSmileTranslator {
	function translate($content) {
		$pattern = '|(\[s\:([^\[\]]+)\])|Uis';
		return preg_replace($pattern, "[\\2]", $content);
	}

}

class SinaWeiboWincodeTranslator {
	function translate($content) {
		$pattern = '|(\[url=([^\]]+)\])(.+)(\[/url\])|Uis';
		$content = preg_replace_callback($pattern, 'WincodeTranslatorCallBack', $content);
		
		return $content;
	}
}

function WincodeTranslatorCallBack($matches) {
	return trim($matches[3]) . ' ' . trim($matches[2]);
}

class BaseSinaWeiboContentTranslate {
	var $_siteBaseUrl = '';
	
	function _getSiteBaseUrl() {
		if ('' == $this->_siteBaseUrl) {
			global $db_bbsurl;
			$this->_siteBaseUrl = trim($db_bbsurl, ' /') . '/';
		}
		return $this->_siteBaseUrl;
	}
}

class SinaWeiboContentTranslate_Article extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "read.php?tid=" . $siteWeiboData['objectid'];
		return SinaWeiboContentTemplate::generateContent("帖子", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_Diary extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=diary&a=detail&did=" . $siteWeiboData['objectid'] . "&uid=" . $siteWeiboData['uid'];
		return SinaWeiboContentTemplate::generateContent("日志", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_GroupArticle extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=group&a=read&cyid=" . $siteWeiboExtra['cyid'] . "&tid=" . $siteWeiboData['objectid'];
		return SinaWeiboContentTemplate::generateContent("群组话题", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_GroupActive extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=group&a=active&job=view&cyid=" . $siteWeiboExtra['cyid'] . "&id=" . $siteWeiboData['objectid'];
		return SinaWeiboContentTemplate::generateContent("群组活动", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_Photos extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=photos&a=album&aid=" . $siteWeiboExtra['aid'];
		$photoCount = count($siteWeiboExtra['photos']);
		$photoCount = $photoCount > 0 ? $photoCount : 1;
		return "我刚上传了" . $photoCount . "张照片" . " " . $url;
	}
}

class SinaWeiboContentTranslate_GroupPhotos extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=galbum&a=album&cyid=" . $siteWeiboExtra['cyid'] . "&aid=" . $siteWeiboExtra['aid'];
		$photoCount = count($siteWeiboExtra['photos']);
		$photoCount = $photoCount > 0 ? $photoCount : 1;
		return "我刚上传了" . $photoCount . "张群组照片" . " " . $url;
	}
}

class SinaWeiboContentTemplate {
	function generateContent($category, $title, $content, $url) {
		$content = SinaWeiboContentTemplate::_cutString($content, 100);
		return "【" . $category . "：" . $title . "】" . $content . " " . $url;
	}
	
	function _cutString($content, $bytes) {
		return substrs($content, $bytes); //TODO substrs is from common.php
	}
}
