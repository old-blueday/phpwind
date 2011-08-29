<?php
!defined('P_W') && exit('Forbidden');

/**
 * 帖子内容连接检查工具
 * 
 * @package Tool
 */
class PW_LinkChecker {
	var $whiteList = array();
	var $blackList = array();
	var $linkNumberLimit = 0;
	
	var $unwhiteDomains = array();
	var $caughtBlackUrls = array();
	
	function setConfig($whiteList, $blackList, $linkNumberLimit) {
		$this->whiteList = is_array($whiteList) ? $whiteList : array();
		$this->blackList = is_array($blackList) ? $blackList : array();
		$this->linkNumberLimit = $linkNumberLimit > 0 ? intval($linkNumberLimit) : 0;
	}
	
	/**
	 * check content
	 *
	 * @param $content
	 * @return null
	 */
	function checkContent($content) {
		$domains = $this->_findLinkDomains($content);
		unset($content);
		
		$this->unwhiteDomains = $this->_stripWhiteDomains($domains);
	}
	
	/**
	 * Is unwhite domains number reach the limit
	 *
	 * @return bool reach or not
	 */
	function isReachLimit() {
		$unwhiteDomainsCount = count($this->unwhiteDomains);
		return $this->linkNumberLimit ? $unwhiteDomainsCount > $this->linkNumberLimit : false;
	}
	
	/**
	 * Is unwhite domains have black one
	 *
	 * @return bool have or not
	 */
	function haveBlackDomains() {
		if (empty($this->blackList)) return false;
		
		$caught = false;
		foreach ($this->unwhiteDomains as $key => $domain) {
			foreach ($this->blackList as $blackDomain) {
				$pos = strpos($domain, $blackDomain);
				/* && $pos == (strlen($domain) - strlen($blackDomain))*/
				if (false !== $pos) {
					$this->_caughtBlackUrl($blackDomain);
					$caught = true;
				}
			}
		}
		
		return $caught;
	}
	
	function getBlackUrls() {
		return $this->caughtBlackUrls;
	}
	
	function _caughtBlackUrl($blackDomain) {
		$this->caughtBlackUrls[$blackDomain] = $blackDomain;
	}
	
	function _stripWhiteDomains($domains) {
		if (empty($this->whiteList)) return $domains;
		
		foreach ($domains as $key => $domain) {
			foreach ($this->whiteList as $whiteDomain) {
				$pos = strpos($domain, $whiteDomain);
				if (false !== $pos && $pos == (strlen($domain) - strlen($whiteDomain))) {
					unset($domains[$key]);
				}
			}
		}
		return $domains;
	}
	
	function _findLinkDomains($content) {
		$pattern = '/\[url(=(.+?))?\](.+?)\[\/url\]/eis';
		$matches = array();
		preg_match_all($pattern, $content, $matches);
		if (empty($matches)) return array();
		
		$domains = array();
		foreach ($matches[0] as $index => $string) {
			$urlPostion = 2;
			if ('' == $matches[$urlPostion][$index]) $urlPostion = 3;
			
			$parseInfo = @parse_url($matches[$urlPostion][$index]);
			
			if (isset($parseInfo['host'])) {
				$domains[] = $parseInfo['host'];
			} elseif (isset($parseInfo['path']) && $this->_isPathADomain($parseInfo['path'])) {
				$domains[] = $parseInfo['path'];
			}
		}
		return $domains;
	}
	
	function _isPathADomain($string) {
		$pattern = '/^([\w-]+\.)?[\w-]+\.(com|cn|mobi|tel|asia|net|org|name|me|tv|cc|hk|biz|info)(\.cn)?$/ei';
		return (bool) preg_match($pattern, $string, $out);
	}
}

?>