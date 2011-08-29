<?php
!function_exists('readover') && exit('Forbidden');

$ret_url = 'index.php';

$f = $db->get_one("SELECT f.fid,f.name,f.forumsell,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=" . S::sqlEscape($rt['paycredit']));

if ($f) {

	$date = $rt['extra_1'];
	$overdate = $timestamp + $date * 86400;
	$db->update("INSERT INTO pw_forumsell SET " . S::sqlSingle(array(
		'fid'		=> $f['fid'],
		'uid'		=> $rt['uid'],
		'buydate'	=> $timestamp,
		'overdate'	=> $overdate,
		'credit'	=> 'RMB',
		'cost'		=> $rt['price']
	),false));
	
	M::sendNotice(
		array($rt['username']),
		array(
			'title' => getLangInfo('writemsg','forumbuy_title'),
			'content' => getLangInfo('writemsg','forumbuy_content',array(
				'fee'		=> $fee,
				'fname'		=> $f['name'],
				'number'	=> $date
			)),
		)
	);
	$ret_url = 'thread.php?fid=' . $f['fid'];
}
?>