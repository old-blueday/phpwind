<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:置顶道具
@type:帖子类
@effect:可将自己发表的帖子在版块中置顶，置顶时间为6小时。

****/

if($tooldb['type']!=1){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}

if($tpcdb['authorid'] != $winduid){
	Showmsg('tool_authorlimit');
}
if($tpcdb['topped'] != 0){
	Showmsg('toolmsg_4_failed');
}
$toolfield = $timestamp + 3600*6;
$db->update("UPDATE pw_threads SET topped='1',toolinfo=".pwEscape($tooldb['name'],false).",toolfield=".pwEscape($toolfield)."WHERE tid=".pwEscape($tid));
$fid = $db->get_value("SELECT fid FROM pw_threads WHERE tid=".intval($tid));
$threadList = L::loadClass("threadlist", 'forum');
$threadList->refreshThreadIdsByForumId($fid);
require_once(R_P.'require/updateforum.php');
setForumsTopped($tid,$fid,1,$toolfield);
updatetop();

$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".pwEscape($winduid)."AND toolid=".pwEscape($toolid));
$logdata=array(
	'type'		=>	'use',
	'nums'		=>	'',
	'money'		=>	'',
	'descrip'	=>	'tool_4_descrip',
	'uid'		=>	$winduid,
	'username'	=>	$windid,
	'ip'		=>	$onlineip,
	'time'		=>	$timestamp,
	'toolname'	=>	$tooldb['name'],
	'subject'	=>	substrs($tpcdb['subject'],15),
	'tid'		=>	$tid,
);
writetoollog($logdata);
Showmsg('toolmsg_success');
?>