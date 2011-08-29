<?php
!function_exists('readover') && exit('Forbidden');

$tradedb = $db->get_one("SELECT * FROM pw_trade WHERE tid=".S::sqlEscape($tid));

if ($tradedb) {
	$tradedb['hits']  = $read['hits'];
	$tradedb['aliww'] = $read['aliww'];
	//要求以拍下后就算售出
	$tradedb['salenum2'] = $db->get_value("SELECT SUM(`quantity`) FROM pw_tradeorder WHERE tid=".S::sqlEscape($tradedb['tid']));
	$attachService = L::loadClass('attachs','forum');
	$tradedb['icon'] = $attachService->getThreadAttachMini($tradedb['icon']);
	/*
	$pic = geturl($tradedb['icon'],'show',1);
	if(is_array($pic)){
		$tradedb['icon'] = $pic[0];
	} else {
		$tradedb['icon'] = 'images/noproduct.gif';
	}
	*/
	$special = 'read_trade';
}
?>