<?php
!function_exists('readover') && exit('Forbidden');

/**
 * msn 登录，导出联系人列表
 * @author papa
 * 2010-04-22
 */
define("PORT", '1863');
define("SERVER", 'messenger.hotmail.com');
define("MSNPROTOCOL", 'MSNP12');
define("TIMEOUT", 30);
define("TOTALEMAIL", 20);
define("MD5STRING", 'Q1P7W2E4J9R8U3S5');

class INV_msn {
	var $ssh_login = 'login.live.com/login2.srf';
	var $nexus = 'https://nexus.passport.com/rdr/pprdr.asp';
	var $trID = 0;

	/**
	 * 根据用户名密码获得 man 联系人email地址列表
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	function getEmailAddressList($username, $password) {
		if (!$this->_login($username, $password)) {
			return 0;
		}
		
		$_emails = array();
		$_count = 0;
		//获得联系人列表
		$this->_put("SYN $this->trID 0 0");
		$this->_get();
		
		$this->_put("CHG $this->trID NLN");
		$stream_info = stream_get_meta_data($this->fp);
		
		while ((!feof($this->fp)) && (!$stream_info['timed_out']) && ($_count <= 1)/* && (count($_emails) < TOTALEMAIL)*/) {
			$data = $this->_get();
			$stream_info = stream_get_meta_data($this->fp);
			if ($data) {
				switch ($code = substr($data, 0, 3)) {
					default :
						break;
					case 'MSG' :
						$_count++;
						break;
					case 'LST' :
						$_emails[] = $data;
						break;
					case 'SYN' :
						break;
					case 'CHL' :
						$bits = explode(' ', trim($data));
						$return = md5($bits[2] . MD5STRING);
						$this->_put("QRY $this->trID msmsgs@msnmsgr.com 32$return");
						break;
				}
			}
		}
		
		$_addressList = array();
		foreach ($_emails as $key => $value) {
			if (strpos($value, 'C=')) {
				$value = pwConvert($value,'GBK','UTF-8');
				$_friends = explode(' ', $value);
				$_addressList[substr($_friends[1], 2)] = substr($_friends[2], 2);
			}
		}
		return $_addressList;
	}

	/**
	 * MSN连接登录
	 * @param string $username
	 * @param string $password
	 */
	function _login($username, $password) {
		$this->trID = 1;
		if (!$this->fp = @fsockopen(SERVER, PORT, $errno, $errstr, 2)) {
			return 0;
		} else {
			stream_set_timeout($this->fp, TIMEOUT);
			$this->_put("VER $this->trID " . MSNPROTOCOL . " CVR0");
			
			while (!feof($this->fp)) {
				$data = $this->_get();
				switch ($code = substr($data, 0, 3)) {
					default :
						return 0;
						break;
					case 'VER' :
						$this->_put("CVR $this->trID 0x0804 winnt 6.1 i386 MSNMSGR 14.0.8089.0726 msmsgs $username");
						break;
					case 'CVR' :
						$this->_put("USR $this->trID TWN I $username");
						break;
					case 'XFR' :
						list(, , , $ip) = explode(' ', $data);
						list($ip, $port) = explode(':', $ip);
						if ($this->fp = @fsockopen($ip, $port, $errno, $errstr, 2)) {
							$this->trID = 1;
							$this->_put("VER $this->trID " . MSNPROTOCOL . " CVR0");
						} else {
							return 0;
						}
						break;
					case 'USR' :
						if (isset($this->authed)) {
							return 1;
						} else {
							$this->passport = $username;
							$this->password = urlencode($password);
							list(, , , , $code) = explode(' ', trim($data));
							if ($auth = $this->_ssl_auth($code)) {
								$this->_put("USR $this->trID TWN S $auth");
								$this->authed = 1;
							} else {
								return 0;
							}
						}
						break;
				}
			}
		}
	}

	/**
	 * @param string $auth_string
	 * @return array
	 */
	function _ssl_auth($auth_string) {
		if (empty($this->ssh_login)) {
			$ch = curl_init($this->nexus);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$header = curl_exec($ch);
			curl_close($ch);
			preg_match('/DALogin=(.*?),/', $header, $out);
			if (isset($out[1])) {
				$slogin = $out[1];
			} else {
				return 0;
			}
		} else {
			$slogin = $this->ssh_login;
		}
		
		$ch = curl_init('https://' . $slogin);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Passport1.4 OrgVerb=GET,OrgURL=http%3A%2F%2Fmessenger%2Emsn%2Ecom,sign-in=' . $this->passport . ',pwd=' . $this->password . ',' . $auth_string, 
			'Host: login.passport.com'));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$header = curl_exec($ch);
		curl_close($ch);
		preg_match("/from-PP='(.*?)'/", $header, $out);
		return (isset($out[1])) ? $out[1] : 0;
	}

	/**
	 * 获得一次请求的返回数据
	 * @return string
	 */
	function _get() {
		if ($data = @fgets($this->fp, 4096)) {
			return $data;
		} else {
			return 0;
		}
	}

	/**
	 * 发送一次请求
	 * @param string $data
	 */
	function _put($data) {
		fwrite($this->fp, $data."\r\n"); 
		$this->trID++;
	}

}
?>