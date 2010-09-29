<?php
!function_exists('readover') && exit('Forbidden');
require_once R_P . 'u/lib/invite/base.inv.php';

/**
 * gmail邮箱登录，导出联系人列表
 * @author papa
 * 2010-04-22
 */

class INV_Gmail extends INV_Base {
	var $loginUrl = "https://www.google.com/accounts/ServiceLoginAuth?service=mail";
	var $listUrl = "http://mail.google.com/mail/contacts/data/contacts?thumb=true&groups=true&show=ALL&enums=true&psort=Name&max=300&out=js&rf=&jsx=true";

	/**
	 * 根据用户名密码获得 gmail 邮箱 联系人email地址列表
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	function getEmailAddressList($username, $password) {
		$this->username = $username;
		$this->password = $password;
		if (!$this->_validateUserAndPasswd()) {
			return 0;
		}
		if (!$this->_login()) {
			return 0;
		}
		$this->_getEmailAddressList();
		$this->_deleteCookieFile();
		return $this->addressList;
	}

	/**
	 * 获得好友列表
	 */
	function _getEmailAddressList() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->listUrl);
		curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR2);
		curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
		
		ob_start();
		curl_exec($ch);
		$contents = ob_get_contents();
		ob_end_clean();
		curl_close($ch);
		
		$contents = pwConvert($contents, 'GBK', 'UTF-8');
		preg_match_all("/\"DisplayName\":\"([^\"]*)\"/is", $contents, $names);
		preg_match_all("/\"Address\":\"([^\"]*)\"/is", $contents, $emails);
		
		foreach ($names[1] as $k => $user) {
			$this->addressList[$emails[1][$k]] = $user;
		}
		
	}

	/**
	 * 用户登录邮箱
	 * @param string $username
	 * @param string $password
	 */
	function _login() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
		curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR1);
		curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
		
		ob_start();
		curl_exec($ch);
		$result = ob_get_contents();
		ob_end_clean();
		curl_close($ch);
		
		$contents = file_get_contents(COOKIEJAR1);
		$galk = trim(substr($contents, -13));
		
		$fileds = "continue=http://mail.google.com/mail?&Email=" . $this->username . "&hl=en&Passwd={$this->password}&GALX=$galk";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fileds);
		curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR2);
		
		ob_start();
		curl_exec($ch);
		$result = ob_get_contents();
		ob_end_clean();
		curl_close($ch);
		if (preg_match("/The username or password you entered is incorrect/", $result)) {
			return 0;
		}
		return 1;
	}

}
?>