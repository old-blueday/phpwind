<?php
!defined('P_W') && exit('Forbidden');

class PW_SinaWeiboContentTranslator {
	var $_allowedTypes = array('article', 'diary', 'group_article', 'group_active', 'photos', 'group_photos', 'cms');
	
	function translate($siteWeiboType, $siteWeiboData, $siteWeiboExtra = array()) {
		if (!in_array($siteWeiboType, $this->_allowedTypes)) return $this->commonTranslate($siteWeiboData['content']);
		
		$translate = $this->_loadTranslate($siteWeiboType);
		return $this->commonTranslate($translate->translate($siteWeiboData, $siteWeiboExtra));
	}
	
	function commonTranslate($content) {
		$content = SinaWeiboSmileTranslator::translate($content);
		$content = SinaWeiboWincodeTranslator::translate($content);
		$content = str_replace(array('&gt;', '&lt;', '&amp;', '&quot;', '&#39;', '&#60;', '&#61;', '&#46;'), array('>', '<', '&', '"', "'", '<', '=', '.'), $content);
		$content = str_replace(array("\\\"", "\\'"), array('"', "'"), $content);
		$content = preg_replace(array('/(&nbsp;){1,}/', '/( ){1,}/', '/&#173;/'), array(' ', ' ', ' '), $content);
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
		return SinaWeiboContentTemplate::generateContent("article", $siteWeiboExtra['title'], $siteWeiboData['content'], $this->_generateThreadUrl($siteWeiboData['objectid']));
	}
	
	function _generateThreadUrl($threadId) {
		global $db;
		$threadInfo = $db->get_one("SELECT * FROM pw_threads WHERE tid=" . intval($threadId));
		$forumInfo = $db->get_one("SELECT * FROM pw_forums WHERE fid=" . intval($threadInfo['fid']));
		
		$htmlUrl = '';
		if ($forumInfo['allowhtm'] == 1) {
			global $db_readdir; //TODO HARD-CODED
			$htmlUrl = $db_readdir . '/' . $threadInfo['fid'] . '/' . date('ym', $threadInfo['postdate']) . '/' . $threadInfo['tid'] . '.html';
			if (!$forumInfo['cms'] && file_exists(R_P . $htmlUrl)) return $this->_getSiteBaseUrl() . trim($htmlUrl, '/\\');
		}
		return $this->_getSiteBaseUrl() . "read.php?tid=" . $threadId;
	}
}

class SinaWeiboContentTranslate_Diary extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=diary&a=detail&did=" . $siteWeiboData['objectid'] . "&uid=" . $siteWeiboData['uid'];
		return SinaWeiboContentTemplate::generateContent("diary", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_GroupArticle extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=group&a=read&cyid=" . $siteWeiboExtra['cyid'] . "&tid=" . $siteWeiboData['objectid'];
		return SinaWeiboContentTemplate::generateContent("group_article", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_GroupActive extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=group&a=active&job=view&cyid=" . $siteWeiboExtra['cyid'] . "&id=" . $siteWeiboData['objectid'];
		return SinaWeiboContentTemplate::generateContent("group_active", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_Cms extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "mode.php?m=cms&q=view&id=" . $siteWeiboData['objectid'];
		return SinaWeiboContentTemplate::generateContent("cms", $siteWeiboExtra['title'], $siteWeiboData['content'], $url);
	}
}

class SinaWeiboContentTranslate_Photos extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=photos&a=album&aid=" . $siteWeiboExtra['aid'];
		$photoCount = count($siteWeiboExtra['photos']);
		$photoCount = $photoCount > 0 ? $photoCount : 1;
		return SinaWeiboContentTemplate::generatePhotoContent('photos', $photoCount, $url);
	}
}

class SinaWeiboContentTranslate_GroupPhotos extends BaseSinaWeiboContentTranslate {
	function translate($siteWeiboData, $siteWeiboExtra = array()) {
		$url = $this->_getSiteBaseUrl() . "apps.php?q=galbum&a=album&cyid=" . $siteWeiboExtra['cyid'] . "&aid=" . $siteWeiboExtra['aid'];
		$photoCount = count($siteWeiboExtra['photos']);
		$photoCount = $photoCount > 0 ? $photoCount : 1;
		return SinaWeiboContentTemplate::generatePhotoContent('group_photos', $photoCount, $url);
	}
}

class SinaWeiboContentTemplate {
	function generateContent($category, $title, $content, $url) {
		$content = SinaWeiboContentTemplate::_cutString($content, 100);
		
		return SinaWeiboContentTemplate::_replaceTemplate($category, array('{title}', '{content}', '{url}'), array($title, $content, $url. ' '));
	}
	
	function generatePhotoContent($category, $photoCount, $url) {
		return SinaWeiboContentTemplate::_replaceTemplate($category, array('{photo_count}', '{url}'), array($photoCount, $url. ' '));
	}
	
	function _cutString($content, $bytes) {
		return substrs($content, $bytes); //TODO substrs is from common.php
	}
	
	function _replaceTemplate($type, $searchs, $replaces) {
		$siteBindInfoService = SinaWeiboContentTemplate::_getSiteBindInfoService();
		$template = $siteBindInfoService->getWeiboTemplateByType($type);
		
		return str_replace($searchs, $replaces, $template);
	}
	
	/**
	 * @return PW_WeiboSiteBindInfoService
	 */
	function _getSiteBindInfoService() {
		return L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service');
	}
}
