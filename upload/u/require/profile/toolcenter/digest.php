<?php
!function_exists('readover') && exit('Forbidden');

/**
 * @name:精华I道具
 * @type:帖子类
 * @effect:可以将自己的帖子加为精华I
*/

if ($tooldb['type'] != 1){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}
if ($tpcdb['authorid'] != $winduid){
	Showmsg('tool_authorlimit');
}

//$db->update("UPDATE pw_threads SET digest='1',toolinfo=".S::sqlEscape($tooldb['name'],false)."WHERE tid=".S::sqlEscape($tid));
pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('digest'=>1, 'toolinfo'=>$tooldb['name']));
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$userService->updateByIncrement($winduid, array(), array('digests' => 1));
//* $threads = L::loadClass('Threads', 'forum');
//* $threads->delThreads($tid);

$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
$logdata = array(
	'type'		=>	'use',
	'descrip'	=>	'tool_9_descrip',
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