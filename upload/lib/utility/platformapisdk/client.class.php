<?php
/**
 * Warning: should be coded in php4
 */
!function_exists('readover') && exit('Forbidden');
class PlatformApiProtocol {
	var $_appKey = '';
	var $_appSecret = '';
	
	var $_signMethod;
	var $_format;
	
	var $_platformApiBaseUrl; //static
	var $_version = '1.0'; //static
	
	/**
	 * 构造函数
	 * 
	 * @param string $appKey 客户端公钥，站点的sitehash
	 * @param string $appSecret 客户端密钥，站点的siteownerid
	 * @return
	 */
	function PlatformApiProtocol($appKey, $appSecret) {
		if ('' == $appKey || '' == $appSecret) $this->_throwError('appKey or appSecret should not be empty');
		
		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;
	}
	
	function getVersion() {
		return $this->_version;
	}
	function getSignMethod() {
		return $this->_signMethod;
	}
	function getFormat() {
		return $this->_format;
	}
	
	function setSignMethod($signMethod) {
		if (in_array($signMethod, array('md5'))) {
			$this->_signMethod = $signMethod;
			return true;
		}
		return false;
	}
	
	function setFormat($format) {
		if (in_array($format, array('json'))) {
			$this->_format = $format;
			return true;
		}
		return false;
	}
	
	function getPlatformApiBaseUrl() {
		if (!$this->_platformApiBaseUrl) {
			$path = dirname(__FILE__) . '/config_platformurl.php';
			$config = @include S::escapePath((realpath($path)));
			$this->_platformApiBaseUrl = $config ? $config : 'http://apps.phpwind.net/';
		}
		
		return $this->_platformApiBaseUrl;
	}
	
	function _buildRequestUrl($method) {
		return $this->getPlatformApiBaseUrl() . str_replace('.', '/', trim($method, './'));
	}
	
	function _buildSignedQueryString($method, $params) {
		$queryString = $this->_buildQueryString($params);
		$signature = $this->_buildSignature($method, $queryString);
		return $queryString . '&sign=' . $signature;
	}
	
	function _buildSignature($method, $stringToSign) {
		return md5($this->_appSecret . $method . $stringToSign);
	}
	function _buildQueryString($params) {
		$allParams = array_merge($this->_checkAppParams($params), $this->_getSystemParams());
		return implode('&', $this->_mapToQuery($allParams));
	}
	
	function _buildPublicQueryString($params) {
		$allParams = array_merge($this->_checkAppParams($params), $this->_getPublicSystemParams());
		return implode('&', $this->_mapToQuery($allParams));
	}
	
	function _getPublicSystemParams() {
		if ($this->getFormat()) $params['format'] = $this->getFormat();
		$params['v'] = $this->getVersion();
		return $params;
	}
	
	function _mapToQuery($map, $prefix = '') {
		$pairs = array();
		ksort($map);
		reset($map);
		foreach ($map as $key => $value) {
			$key = '' != $prefix ? $prefix . "[" . urlencode($key) . "]" : urlencode($key);
			if (!is_array($value)) {
				$pairs[] = $key . '=' . urlencode($value);
			} else {
				$pairs = array_merge($pairs, $this->_mapToQuery($value, $key));
			}
		}
		return $pairs;
	}
	
	function _checkAppParams($params) {
		if (!is_array($params)) return array();
		if (isset($params['sign'])) unset($params['sign']);
		return $params;
	}
	
	function _getSystemParams() {
		$params = array();
		$params['app_key'] = $this->_appKey;
		$params['timestamp'] = time();
		if ($this->getFormat()) $params['format'] = $this->getFormat();
		if ($this->getSignMethod()) $params['sign_method'] = $this->getSignMethod();
		$params['v'] = $this->getVersion();
		$params['site_v'] = $this->_getSiteVersion();
		return $params;
	}
	
	function _checkMethod($method) {
		$method = trim($method);
		if ('' == $method) $this->_throwError('method should not be empty');
		return $method;
	}

	function _getSiteVersion() {
		return defined('WIND_VERSION') ? WIND_VERSION : '';
	}
	
	function _throwError($msg) {
		die($msg . '');
	}
}


class PlatformApiClient extends PlatformApiProtocol {

	/**
	 * 通过GET请求平台标准api接口
	 * 
	 * @param string $method 接口名，如：weibo.site.bind
	 * @param array $params 接口参数
	 * @return string
	 */
	function get($method, $params = array()) {
		$method = $this->_checkMethod($method);
		return HttpClient::get($this->_buildRequestUrl($method), $this->_buildSignedQueryString($method, $params));
	}
	
	/**
	 * 通过POST请求平台标准api接口
	 * 
	 * @param string $method 接口名，如：weibo.site.bind
	 * @param array $params 接口参数
	 * @return string
	 */
	function post($method, $params = array()) {
		$method = $this->_checkMethod($method);
		return HttpClient::post($this->_buildRequestUrl($method), $this->_buildSignedQueryString($method, $params));
	}
	
	/**
	 * 生成平台对站点开放的入口页面的URL
	 * 
	 * @param int $siteUserId 站点用户id，如无填0
	 * @param string $method 接口名，如：weibo.site.bind
	 * @param array $params 接口参数
	 * @return string URL
	 */
	function buildPageUrl($siteUserId, $method, $params = array()) {
		$method = $this->_checkMethod($method);
		$params['site_uid'] = intval($siteUserId);
		return $this->_buildRequestUrl($method) . "?" . $this->_buildSignedQueryString($method, $params);
	}
	
	/**
	 * 生成平台对站点公共页面（不需要身份验证）的URL
	 * 
	 * @param string $method 接口名，如：openim.bind.intro
	 * @param array $params 接口参数
	 * @return string URL
	 */
	function buildPublicPageUrl($method, $params = array()) {
		$method = $this->_checkMethod($method);
		return $this->_buildRequestUrl($method) . "?" . $this->_buildPublicQueryString($params);
	}
}

class PlatformApiClientUtility {
	function convertCharset($inCharset, $outCharset, $data) {
		return pwConvert($data, $outCharset, $inCharset);
	}
	
	function decodeJson($jsonString) {
		L::loadClass('json', 'utility', false);
		$json = new Services_JSON();
		return $json->decode($jsonString);
	}
}

class HttpClient {
	
	function get($host, $data, $timeout = 5) {
		return HttpClient::request($host, $data, 'GET', $timeout);
	}
	
	function post($host, $data, $timeout = 5) {
		return HttpClient::request($host, $data, 'POST', $timeout);
	}
	
	function request($host, $data, $method = 'GET', $timeout = 5) {
		$parse = parse_url($host);
		$method = strtoupper($method);
		if (empty($parse)) return null;
		if (!isset($parse['port']) || !$parse['port']) $parse['port'] = '80';
		if (!in_array($method, array('POST', 'GET'))) return null;
		
		$parse['host'] = str_replace(array('http://', 'https://'), array('', 'ssl://'), $parse['scheme'] . "://") . $parse['host'];
		if (!$fp = @fsockopen($parse['host'], $parse['port'], $errnum, $errstr, $timeout)) return null;
		
		$contentLength = '';
		$postContent = '';
		$query = isset($parse['query']) ? $parse['query'] : '';
		$parse['path'] = str_replace(array('\\', '//'), '/', $parse['path']) . "?" . $query;
		if ($method == 'GET') {
			substr($data, 0, 1) == '&' && $data = substr($data, 1);
			$parse['path'] .= ($query ? '&' : '') . $data;
		} elseif ($method == 'POST') {
			$contentLength = "Content-length: " . strlen($data) . "\r\n";
			$postContent = $data;
		}
		$write = $method . " " . $parse['path'] . " HTTP/1.0\r\n";
		$write .= "Host: " . $parse['host'] . "\r\n";
		$write .= "Content-type: application/x-www-form-urlencoded\r\n";
		$write .= $contentLength;
		$write .= "Connection: close\r\n\r\n";
		$write .= $postContent;
		@fwrite($fp, $write);
		
		$responseText = '';
		while ($data = fread($fp, 4096)) {
			$responseText .= $data;
		}
		@fclose($fp);
		$responseText = trim(stristr($responseText, "\r\n\r\n"), "\r\n");
		return $responseText;
	}
}


