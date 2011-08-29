<?php
/**
 * 云验证码
 */
!defined('P_W') && exit('Forbidden');

class PW_CloudCaptcha {
	
	var $captchaGetInterface = 'http://pin.aliyun.com/get_img?';
	var $captchaCheckInterface = 'http://pin.aliyun.com/check_code?';
	var $identity = '';
	
	function PW_CloudCaptcha() {
		$this->identity = $GLOBALS['db_siteid'] ? md5($GLOBALS['db_siteid']) : 'default';
	}
	
	/**
	 * 获取验证码
	 * @param string $sessionid
	 * @param string $identity
	 * @param string $kjtype
	 * @return string
	 */
	function getCaptchaUrl($kjtype = 'default') {
		$interface = $this->captchaGetInterface . 'identity=' . $this->identity . '&kjtype=' . $kjtype;
		return $interface;
	}
	
	/**
	 * 校验
	 * @param string $sessionid
	 * @param string $code
	 * @param mixed $delflag
	 * @param string $identity
	 * @return bool
	 */
	function checkCode($sessionid, $code, $delflag = null) {
		if (!$sessionid || !isset($code)) return false;
		$del = is_null($delflag) ? '' : '&delflag=0';
		$interface = $this->captchaCheckInterface . 'sessionid=' . $sessionid . '&code=' . $code . $del . '&identity=' . $this->identity;
		$result = $this->request($interface);
		return (strtolower(trim($result)) == 'success.') ? true : false;
	}
	
	/**
	 * 获取唯一的sessionid
	 * @param string $onlineip
	 * @return string
	 */
	function generateSessionid($onlineip = '') {
		list($microtime, $time) = explode(' ', microtime());
		return md5($onlineip . $time . $microtime . randstr(8));
	}
	
	/**
	 * 请求数据
	 * @param $host
	 * @param $data
	 * @param $method
	 * @param $timeout
	 */
	function request($host, $data = '', $method = 'GET', $timeout = 5) {
		$parse = parse_url($host);
		$method = strtoupper($method);
		if (empty($parse) || !in_array($method, array('POST', 'GET'))) return null;
		if (!isset($parse['port']) || !$parse['port']) $parse['port'] = '80';
		
		$parse['host'] = str_replace(array('http://', 'https://'), array('', 'ssl://'), $parse['scheme'] . "://") . $parse['host'];
		if (!$fp = @fsockopen($parse['host'], $parse['port'], $errnum, $errstr, $timeout)) return null;
		
		$contentLength = $postContent = '';
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
?>