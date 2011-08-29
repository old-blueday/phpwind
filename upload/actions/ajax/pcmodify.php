<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'pcid',
	'pcmid',
	'tid',
	'jointype',
	'ifpay',
	'payway'
), GP, 2);

$pcvaluetable = GetPcatetable($pcid);
$fieldvalue = $db->get_one("SELECT endtime,limitnum,deposit,price FROM $pcvaluetable WHERE tid=" . S::sqlEscape($tid));
if ($fieldvalue['endtime'] < $timestamp) {
	Showmsg('joinpc_error');
}
$rt = $db->get_one("SELECT nums,phone,mobile,address,extra,name,zip,message,ifpay FROM pw_pcmember WHERE pcmid=" . S::sqlEscape($pcmid) . " AND uid=" . S::sqlEscape($winduid));
!$ifpay && $ifpay = $rt['ifpay'];

if (empty($_POST['step'])) {
	
	//$sign = $db->get_value("SELECT sign FROM pw_postcate WHERE pcid=" . S::sqlEscape($pcid));
	$rt['extra'] && $checked = 'checked';
	
	require_once PrintEot('ajax');
	ajax_footer();
} elseif ($_POST['step'] == 2) {
	
	PostCheck();
	S::gp(array(
		'nums',
		'phone',
		'mobile',
		'address',
		'extra',
		'name',
		'zip',
		'message'
	));
	
	$nums = (int) $nums;
	if ($ifpay) {
		$nums = $rt['nums'];
	}
	if ($nums <= 0 && !$ifpay) {
		echo "numserror\t";
		ajax_footer();
	}
	
	$membernum = $db->get_value("SELECT SUM(nums) FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid));
	if ($fieldvalue['limitnum'] && $fieldvalue['limitnum'] + $rt['nums'] - $membernum < $nums) {
		
		if ($pcid == 1) {
			echo "pcjoin_pcid_more\t";
		} elseif ($pcid == 2) {
			echo "pcjoin_more\t";
		}
		ajax_footer();
	}
	
	$fieldvalue['deposit'] = number_format($fieldvalue['deposit'], 2, '.', '');
	$fieldvalue['price'] = number_format($fieldvalue['price'], 2, '.', '');

	$deposit = $fieldvalue['deposit'] > 0 ? $fieldvalue['deposit'] : $fieldvalue['price'];
	$totalcash = $deposit * $nums;
	$sqlarray = array(
		'phone' => $phone,
		'mobile' => $mobile,
		'address' => $address,
		'extra' => $extra,
		'totalcash' => $totalcash,
		'name' => $name,
		'zip' => $zip,
		'message' => $message
	);
	$nums && $sqlarray['nums'] = $nums;
	
	$db->update("UPDATE pw_pcmember SET " . S::sqlSingle($sqlarray) . " WHERE pcmid=" . S::sqlEscape($pcmid) . " AND uid=" . S::sqlEscape($winduid));
	echo "success\t$jointype\t$tid\t$payway";
	ajax_footer();
}
