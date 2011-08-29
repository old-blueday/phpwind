<?php
!function_exists('readover') && exit('Forbidden');
S::gp(array('step','username'));
$username = urldecode($username);
if(!$_G['allowmessege']) Showmsg ( '你所在的用户组不能发送消息' );
//if(!($messageServer->checkUserMessageLevle('sms',1))) Showmsg ( '你已超过每日发送消息数或你的消息总数已满' );
$normalUrl = $baseUrl."?type=post";
include_once 'ms_header.php';

$uploadfiletype = ($db_uploadfiletype) ? unserialize($db_uploadfiletype) : array();
$attachAllow = pwJsonEncode($uploadfiletype);
$imageAllow = pwJsonEncode(getAllowKeysFromArray($uploadfiletype, array('jpg','jpeg','gif','png','bmp')));

require messageEot('post');
pwOutPut();
?>