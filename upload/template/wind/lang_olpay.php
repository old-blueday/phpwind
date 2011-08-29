<?php
!function_exists('readover') && exit('Forbidden');

$lang['olpay'] = array(
	
	'olpay_0_title'		=> '积分充值(订单号：$L[order_no])',
	'olpay_0_content'	=> '购买论坛{$GLOBALS[creditName]}(论坛UID：$GLOBALS[winduid])',

	'olpay_1_title'		=> '道具购买(订单号：$L[order_no])',
	'olpay_1_content'	=> '购买论坛道具{$GLOBALS[toolinfo][name]}(论坛UID：$GLOBALS[winduid])',

	'olpay_2_title'		=> '版块访问权限购买(订单号：$L[order_no])',
	'olpay_2_content'	=> '购买版块（{$GLOBALS[fname]}）的访问权限(论坛UID：$GLOBALS[winduid])',

	'olpay_3_title'		=> '特殊组购买(订单号：$L[order_no])',
	'olpay_3_content'	=> '购买论坛特殊用户组（{$GLOBALS[grouptitle]}）身份(论坛UID：$GLOBALS[winduid])',

	'olpay_4_title'		=> '注册码购买(订单号：$L[order_no])',
	'olpay_4_content'	=> '购买论坛注册码',
	
	// 孔明灯 by chenyun 2011-07-8
	'olpay_5_title'		=> '孔明灯购买(订单号：$L[order_no])',
	'olpay_5_content'	=> '购买论坛孔明灯',
);
?>