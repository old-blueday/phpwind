<?php
!function_exists('readover') && exit('Forbidden');

/**
 *
 * phpwind 网上支付统一入口
 * author:chenjm@phpwind.net / sky_hold@163.com
 *
 */
Class OnlinePay {

	var $charset;
	var $seller_email;
	var $baseurl;

	var $alipay_url	= 'https://www.alipay.com/cooperate/gateway.do?';
	var $alipay_key;
	var $alipay_partnerID;

	var $pwpay_url	= 'http://pay.phpwind.net/pay/create_payurl.php?';
	var $pwpay_key;
	var $pwpay_partnerID;

	function OnlinePay($email) {
		$this->seller_email = $email;
		$this->charset = $GLOBALS['db_charset'];
		$this->baseurl = $GLOBALS['db_bbsurl'];

		$this->alipay_key = $GLOBALS['ol_alipaykey'];
		$this->alipay_partnerID = $GLOBALS['ol_alipaypartnerID'];

		$this->pwpay_key = $GLOBALS['db_siteid'];
		$this->pwpay_partnerID = $GLOBALS['db_sitehash'];
	}

	//$extra给额外参数用,中文无效
	function alipayurl($order_no, $fee, $paytype,$extra='') {

		$param = array(
			'_input_charset'	=> $this->charset,
			'service'			=> 'create_direct_pay_by_user',
			'notify_url'		=> $this->baseurl.'/alipay.php',
			'return_url'		=> $this->baseurl.'/alipay.php',
			'payment_type'		=> '1',
			'subject'			=> getLangInfo('olpay', "olpay_{$paytype}_title", array('order_no' => $order_no)),
			'body'				=> getLangInfo('olpay', "olpay_{$paytype}_content"),
			'out_trade_no'		=> $order_no,
			'total_fee'			=> $fee,
			'extra_common_param' => $this->formatExtra($extra),
			'seller_email'		=> $this->seller_email
		);
		if ($this->alipay_key && $this->alipay_partnerID) {
			$url = $this->urlCompound($this->alipay_url, $this->alipay_partnerID, $this->alipay_key, $param);
		} else {
			Showmsg('支付失败，本站点尚未填写支付宝商户信息(partnerID和key)，请登录后台->网上支付填写!');
			$url = $this->urlCompound($this->pwpay_url, $this->pwpay_partnerID, $this->pwpay_key, $param);
		}
		return $url;
	}

	function formatExtra($extra) {
		$extraLen = strlen($extra);
		if ($extraLen > 100) $extra = '';
		if ($extra == '') return '';
		$param = array();
		for($i = 0,$cnt = strlen($extra);$i<$cnt;$i++){
			$param[] = ord($extra{$i});
		} 
		return implode('.',$param); 
	}
	function alipay2url($param) {
		$param['service']			= 'trade_create_by_buyer';
		$param['_input_charset']	= $this->charset;
		$param['seller_email']		= $this->seller_email;

		if (0 && $this->alipay_key && $this->alipay_partnerID) {
			$url = $this->urlCompound($this->alipay_url, $this->alipay_partnerID, $this->alipay_key, $param);
		} else {
			$url = $this->urlCompound($this->pwpay_url, $this->pwpay_partnerID, $this->pwpay_key, $param);
		}
		return $url;
	}

	function urlCompound($url, $partnerID, $partnerKey, $param) {
		$param['partner'] = $partnerID;
		ksort($param);
		reset($param);
		$arg = '';
		foreach ($param as $key => $value) {
			if ($value) {
				$url .= "$key=".urlencode($value)."&";
				$arg .= "$key=$value&";
			}
		}
		$url .= 'sign='.md5(substr($arg,0,-1).$partnerKey).'&sign_type=MD5';
		return $url;
	}
}
?>