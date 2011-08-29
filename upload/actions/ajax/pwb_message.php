<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('u'), 'P', 2);
if (!$u)
	Showmsg('undefined_action');
if ($u != $winduid)
	Showmsg('undefined_action');
	
$msgdb = array();
$messageServer = L::loadClass("message", 'message');
$temp = array();
$msgdb = $messageServer->getAllNotRead($winduid, 1, 10);
foreach ($msgdb as $value) {
	$type = $messageServer->getReverseConst(substr($value['typeid'],0,1));
	$temp[] = array(
		'fromuid' => $value['uid'], 
		'from' => $value['username'], 
		'title' => substrs($value['title'], 30), 
		'rid' => $value['rid'], 
		'mid' => $value['mid'], 
		'typeid' => $value['typeid'],
		'type' => $type);
}
$str = '';
if ($temp) {
	$str = stripcslashes(pwJsonEncode($temp));
}
echo "success\t$str";
ajax_footer();
