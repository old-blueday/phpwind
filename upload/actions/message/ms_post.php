<?php
!function_exists('readover') && exit('Forbidden');
InitGP(array('step','username'));
if(!$_G['allowmessege']) Showmsg ( '你所在的用户组不能发送消息' );
//if(!($messageServer->checkUserMessageLevle('sms',1))) Showmsg ( '你已超过每日发送消息数或你的消息总数已满' );
$normalUrl = $baseUrl."?type=post";
include_once 'ms_header.php';
$uploadfiletype = ($db_uploadfiletype) ? unserialize($db_uploadfiletype) : '';
$filetypeinfo = $filetype = '';
if($uploadfiletype){
	foreach($uploadfiletype as $type=>$size){
		$filetype .= ' '.$type.' ';
		$filetypeinfo   .= $type.":".$size."KB; ";
	}
}
if ($db_allowupload && $_G['allowupload']) {
	$attachsService = L::loadClass('attachs', 'forum');
	$mutiupload = intval($attachsService->countMultiUpload($winduid));
}
require messageEot('post');
pwOutPut();
?>