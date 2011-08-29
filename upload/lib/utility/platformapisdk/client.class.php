<?php
/**
 * Warning: should be coded in php4
 */
!function_exists('readover') && exit('Forbidden');

class PlatformApiProtocol
{
	/**
	 * sitehash
	 * @var string
	 */
	var $_appKey = '';
	/**
	 * siteownerid
	 * @var string
	 */
	var $_appSecret = '';
	
	/**
	 * 签名算法函数
	 * @var string
	 */
	var $_signMethod;
	/**
	 * 输出格式
	 * @var string
	 */
	var $_format;
	/**
	 * 本地字符编码
	 * @var string
	 */
	var $_localCharset;
	/**
	 * 平台字符编码
	 * @var string
	 */
	var $_platformCharset = 'UTF-8';
	/**
	 * 平台的基础URL地址
	 * @var string
	 */
	var $_platformApiBaseUrl; //static
	/**
	 * 客户端版本号
	 * @var string
	 */
	var $_version = '2.0'; //static
	
	/**
	 * 构造函数
	 * 
	 * @param string $appKey 客户端公钥，站点的sitehash
	 * @param string $appSecret 客户端密钥，站点的siteownerid
	 * @param string $localCharset 本地字符的编码
	 * @return PlatformApiProtocol
	 * @access public
	 */
	function PlatformApiProtocol($appKey, $appSecret, $localCharset = null)
	{
		if ('' == $appKey || '' == $appSecret) {
			$this->_throwError('appKey or appSecret should not be empty');
		}
		if ($localCharset) {
			$this->_localCharset = $localCharset;
		}
		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;
		return $this;
	}
	
	/**
	 * 获取客户端版本号
	 * @return string
	 */
	function getVersion()
	{
		return $this->_version;
	}
	
	/**
	 * 获取签名算法函数名
	 * @return string
	 */
	function getSignMethod()
	{
		return $this->_signMethod ? $this->_signMethod : 'md5';
	}
	
	/**
	 * 设置签名算法函数名
	 * @param string $signMethod
	 * @return boolean
	 */
	function setSignMethod($signMethod)
	{
		if (function_exists($signMethod)) {
			$this->_signMethod = $signMethod;
			return $this;
		}
		$this->_throwError('invalid sign method');
	}
	
	/**
	 * 获取输出格式
	 * @return string
	 */
	function getFormat()
	{
		return $this->_format;
	}
	
	/**
	 * 设置输出格式
	 * @param string $format
	 * @return PlatformApiProtocol
	 */
	function setFormat($format)
	{
		if (in_array($format, array('', 'json', 'xml', 'html'))) {
			$this->_format = $format;
			return $this;
		}
		$this->_throwError('invalid format');
	}

	/**
	 * 设置本地字符的编码
	 * @param string $charset
	 * @return PlatformApiProtocol
	 */
	function setLocalCharset($charset)
	{
		$this->_localCharset = $charset;
		return $this;
	}

	/**
	 * 获取本地字符的编码
	 * @return string|NULL
	 */
	function getLocalCharset()
	{
		if ($this->_localCharset) {
			return $this->_getSanitizedEncodingString($this->_localCharset);
		}
		//读取全局变量
		global $charset;
		if ($charset) {
			return $this->_getSanitizedEncodingString($charset);
		}
		return null;
	}
	
	/**
	 * 获取净化后的字符编码名称
	 * @param string $encoding 字符编码名称
	 * @return string
	 */
	function _getSanitizedEncodingString($encoding)
	{
		$encoding = strtoupper($encoding);
		switch ($encoding) {
			case 'UTF8':
				$encoding = 'UTF-8';
				break;
			case 'UTF16':
				$encoding = 'UTF-16';
				break;
			case 'UTF-32':
				$encoding = 'UTF-32';
				break;
			case 'GB2312':
				$encoding = 'GBK';
				break;
			case 'BIG-5':
				$encoding = 'BIG5';
				break;
			default:
				break;
		}
		return $encoding;
	}
	
	/**
	 * 获取平台的字符编码
	 * @return string
	 */
	function getPlatformCharset()
	{
		return $this->_getSanitizedEncodingString($this->_platformCharset);
	}
	
	/**
	 * 获取平台的基础URL地址，末尾带/
	 * @return string
	 */
	function getPlatformApiBaseUrl()
	{
		if (!$this->_platformApiBaseUrl) {
			$path = dirname(__FILE__) . '/config_platformurl.php';
			if (file_exists($path)) {
				$config = include S::escapePath((realpath($path)));
			}
			$this->_platformApiBaseUrl = $config ? $config : 'http://apps.phpwind.net/';
		}
		
		return rtrim($this->_platformApiBaseUrl, '/') . '/';
	}
	
	/**
	 * 获取请求URL地址（不包含参数）
	 * @param string $method 如module.controller.action
	 * @return string
	 */
	function _buildRequestUrl($method)
	{
		return $this->getPlatformApiBaseUrl() . str_replace('.', '/', trim($method, './'));
	}
	
	/**
	 * 获取带签名的请求参数
	 * @param string $method 请求的方法，如module.controller.action
	 * @param array $params 参数
	 * @param bool $convertCharset 是否将参数转为平台的编码
	 * @return string
	 */
	function _buildSignedQueryString($method, $params, $convertCharset = true)
	{
		$queryString = $this->_buildQueryString($params, $convertCharset);
		$signature = $this->_buildSignature($method, $queryString);
		return $queryString . '&sign=' . $signature;
	}
	
	/**
	 * 获取签名
	 * @param string $method
	 * @param string $stringToSign
	 * @return string
	 */
	function _buildSignature($method, $stringToSign)
	{
		$signMethod = $this->getSignMethod();
		if (function_exists($signMethod)) {
			return $signMethod($this->_appSecret . $method . $stringToSign);
		}
		$this->_throwError('invalid sign method');
	}
	
	/**
	 * 获取请求参数
	 * @param array $params 参数
	 * @param bool $convertCharset 是否将参数转为平台的编码
	 * @return string
	 */
	function _buildQueryString($params, $convertCharset = true)
	{
		$allParams = array_merge($this->_checkAppParams($params), $this->_getSystemParams());
		return implode('&', $this->_mapToQuery($allParams, '', $convertCharset));
	}
	
	function _buildPublicQueryString($params, $convertCharset = true)
	{
		$allParams = array_merge($this->_checkAppParams($params), $this->_getPublicSystemParams());
		return implode('&', $this->_mapToQuery($allParams, '', $convertCharset));
	}
	
	function _getPublicSystemParams()
	{
		if ($this->getFormat()) $params['format'] = $this->getFormat();
		$params['v'] = $this->getVersion();
		return $params;
	}
	
	function _mapToQuery($map, $prefix = '', $convertCharset = true)
	{
		$pairs = array();
		
		//转字符编码
		if ($convertCharset) {
			$localCharset = $this->getLocalCharset();
			$platformCharset = $this->getPlatformCharset();
			if ($localCharset != $platformCharset) {
				$map = PlatformApiClientUtility::convertCharset($localCharset, $platformCharset, $map);
			}
		}
		
		ksort($map);
		reset($map);
		foreach ($map as $key => $value) {
			$key = '' != $prefix ? $prefix . "[" . urlencode($key) . "]" : urlencode($key);
			if (!is_array($value)) {
				$pairs[] = $key . '=' . urlencode($value);
			} else {
				$pairs = array_merge($pairs, $this->_mapToQuery($value, $key, $convertCharset));
			}
		}
		return $pairs;
	}
	
	function _checkAppParams($params)
	{
		if (!is_array($params)) return array();
		if (isset($params['sign'])) unset($params['sign']);
		return $params;
	}
	
	function _getSystemParams()
	{
		$params = array();
		$params['app_key'] = $this->_appKey;
		$params['timestamp'] = array_sum(explode(' ', microtime()));
		if ($this->getFormat()) $params['format'] = $this->getFormat();
		if ($this->getSignMethod()) $params['sign_method'] = $this->getSignMethod();
		$params['v'] = $this->getVersion();
		$params['site_v'] = $this->_getSiteVersion();
		return $params;
	}
	
	function _checkMethod($method, $convertCharset = true)
	{
		//转字符编码
		if ($convertCharset) {
			$localCharset = $this->getLocalCharset();
			$platformCharset = $this->getPlatformCharset();
			if ($localCharset != $platformCharset) {
				$method = PlatformApiClientUtility::convertCharset($localCharset, $platformCharset, $method);
			}
		}
		$method = trim($method);
		
		if ('' == $method) $this->_throwError('method should not be empty');
		return $method;
	}

	function _getSiteVersion()
	{
		return defined('WIND_VERSION') ? WIND_VERSION : '';
	}
	
	function _throwError($msg)
	{
		die($msg . '');
	}
}

define('PW_PLATFORM_CLIENT_DEFAULT_ADMIN_USER_ID', '-1');
define('PW_PLATFORM_CLIENT_DEFAULT_GUEST_USER_ID', '-99');

class PlatformApiClient extends PlatformApiProtocol {

	/**
	 * 通过GET请求平台标准api接口
	 * 
	 * @param string $method 接口名，如：weibo.site.bind
	 * @param array $params 接口参数
	 * @param bool $convertCharset 是否将参数转为平台的编码
	 * @return string
	 */
	function get($method, $params = array(), $convertCharset = false) {
		$method = $this->_checkMethod($method, $convertCharset);
		$params['authtype'] = 'PhpwindUrlApiGet';
		
		return HttpClient::get($this->_buildRequestUrl($method), $this->_buildSignedQueryString($method, $params, $convertCharset));
	}
	
	/**
	 * 通过POST请求平台标准api接口
	 * 
	 * @param string $method 接口名，如：weibo.site.bind
	 * @param array $params 接口参数
	 * @param bool $convertCharset 是否将参数转为平台的编码
	 * @return string
	 */
	function post($method, $params = array(), $convertCharset = false) {
		$method = $this->_checkMethod($method, $convertCharset);
		$params['authtype'] = 'PhpwindUrlApiPost';
		
		return HttpClient::post($this->_buildRequestUrl($method), $this->_buildSignedQueryString($method, $params, $convertCharset));
	}
	
	/**
	 * 生成平台对站点开放的入口页面的URL
	 * 
	 * @param int $siteUserId 站点用户id，如无填0
	 * @param string $method 接口名，如：weibo.site.bind
	 * @param array $params 接口参数
	 * @param bool $convertCharset 是否将参数转为平台的编码
	 * @return string URL
	 */
	function buildPageUrl($siteUserId, $method, $params = array(), $convertCharset = false) {
		$method = $this->_checkMethod($method, $convertCharset);
		$params['site_uid'] = intval($siteUserId);
		$params['authtype'] = 'PhpwindUrlPageEntry';
		
		return $this->_buildRequestUrl($method) . "?" . $this->_buildSignedQueryString($method, $params, $convertCharset);
	}
	
	/**
	 * 生成平台对站点公共页面（不需要身份验证）的URL
	 * 
	 * @param string $method 接口名，如：openim.bind.intro
	 * @param array $params 接口参数
	 * @param bool $convertCharset 是否将参数转为平台的编码
	 * @return string URL
	 */
	function buildPublicPageUrl($method, $params = array(), $convertCharset) {
		$method = $this->_checkMethod($method, $convertCharset);
		return $this->_buildRequestUrl($method) . "?" . $this->_buildPublicQueryString($params, $convertCharset);
	}
	
	/**
	 * 将JSON数据转成本地php数据
	 * @param string $jsonString
	 * @return Ambigous
	 */
	function jsonToLocalPhpValue($jsonString)
	{
		$utf8PhpValue = PlatformApiClientUtility::decodeJson($jsonString);
		if (is_null($utf8PhpValue)) {
			return null;
		}
		
		//转字符编码
		$localCharset = $this->getLocalCharset();
		$platformCharset = $this->getPlatformCharset();
		if ($localCharset != $platformCharset) {
			return PlatformApiClientUtility::convertCharset($platformCharset, $localCharset, $utf8PhpValue);
		}
		return $utf8PhpValue;
	}
}

class PlatformApiClientUtility {
	function convertCharset($inCharset, $outCharset, $data) {
		if (is_array($data)) {
			$newData = array();
			foreach ($data as $key => $value) {
				$newKey = PlatformApiClientUtility::convertCharset($inCharset, $outCharset, $key);
				$newValue = PlatformApiClientUtility::convertCharset($inCharset, $outCharset, $value);
				$newData[$newKey] = $newValue;
			}
			return $newData;
		}
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


