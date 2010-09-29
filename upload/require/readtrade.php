<?php
!function_exists('readover') && exit('Forbidden');

$tradedb = $db->get_one("SELECT * FROM pw_trade WHERE tid=".pwEscape($tid));

if ($tradedb) {
	$tradedb['hits']  = $read['hits'];
	$tradedb['aliww'] = $read['aliww'];
	$pic = geturl($tradedb['icon'],'show',1);
	if(is_array($pic)){
		$tradedb['icon'] = $pic[0];
	} else {
		$tradedb['icon'] = 'images/noproduct.gif';
	}
	$special = 'read_trade';
}
?>