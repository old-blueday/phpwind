<?php
!function_exists('readover') && exit('Forbidden');
/**
 * @author papa
 * 2010-04-22
 */
define("TIMEOUT", 100);
define("COOKIETMP", D_P . "data/tmp");
define("COOKIEJAR1", tempnam(COOKIETMP, "c1_"));
define("COOKIEJAR2", tempnam(COOKIETMP, "c2_"));
define("COOKIEJAR3", tempnam(COOKIETMP, "c3_"));

class INV_Base {
	
	var $username;
	var $password;
	var $headurl;
	var $header = array();
	var $addressList = array();

	/**
	 * 校验用户名以及密码
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	function _validateUserAndPasswd() {
		if ($this->username == "" || $this->password == "") {
			return 0;
		}
		return 1;
	}

	/**
	 * 从头部信息中获取sid,host,refer
	 * @param string $username
	 * @return Array 
	 */
	function _setHeader() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->headurl);
		curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR2);
		curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR3);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		$content = curl_exec($ch);
		preg_match_all('/Location:\s*(.*?)\r\n/i', $content, $regs);
		$refer = $regs[1][0];
		preg_match_all('/http\:\/\/(.*?)\//i', $refer, $regs);
		$host = $regs[1][0];
		preg_match_all("/sid=(.*)/i", $refer, $regs);
		$sid = $regs[1][0];
		curl_close($ch);
		$this->header = array('sid' => $sid, 'refer' => $refer, 'host' => $host);
	}

	/**
	 * 删除临时cookie文件
	 */
	function _deleteCookieFile() {
		if (file_exists(COOKIEJAR1)) {
			unlink(COOKIEJAR1);
		}
		if (file_exists(COOKIEJAR2)) {
			unlink(COOKIEJAR2);
		}
		if (file_exists(COOKIEJAR3)) {
			unlink(COOKIEJAR3);
		}
	}

}
?>