<?php
!defined('P_W') && exit('Forbidden');

/**
 *
 * PHPWind 活动AA支付宝接口统一调用
 * Author:shenqj@phpwind.net / future5985@gmail.com
 * Date:2010-4-2
 */
Class AlipayInterface {
	
	var $service;
	var $charset;
	var $baseurl;
	var $aa_create_url = 'http://pay.phpwind.net/pay/aa_create_url.php?';
	var $pwpay_key;
	var $pwpay_partnerID;

	function AlipayInterface($service) {
		
		$this->service			= $service;
		$this->charset			= $GLOBALS['db_charset'];
		$this->baseurl			= $GLOBALS['db_bbsurl'];
		$this->pwpay_key		= $GLOBALS['db_siteid'];
		$this->pwpay_partnerID	= $GLOBALS['db_sitehash'];
	}

	function alipayurl($param) {
		
		$param['_input_charset']	= $this->charset;
		$param['service']			= $this->service;

		$url = $this->urlCompound($this->aa_create_url, $this->pwpay_partnerID, $this->pwpay_key, $param);
		return $url;
	}

	function urlCompound($url, $partnerID, $partnerKey, $param) {
		
		$param['partner'] = $partnerID;
		krsort($param);
		reset($param);
		$arg = '';
		foreach ($param as $key => $value) {
			if ($value) {
				$url .= "$key=".urlencode($value)."&";
				$arg .= "$key=$value&";
			}
		}
		$url .= "bbsurl=".$this->baseurl."&";
		$url .= 'sign='.md5(substr($arg,0,-1).$partnerKey).'&sign_type=MD5';
		
		return $url;
	}
}
?>