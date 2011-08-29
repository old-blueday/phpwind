<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:沉淀卡
@type:帖子类
@effect:帖子中使用，让帖子丢到12小时前,回复也不再上来．

****/

if($tooldb['type']!=1){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}

//$db->update("UPDATE pw_threads SET toolinfo=".S::sqlEscape($tooldb['name'],false).",locked='3',lastpost=lastpost-43200 WHERE tid='$tid'");
$db->update(pwQuery::buildClause("UPDATE :pw_table SET toolinfo=:toolinfo,locked='3',lastpost=lastpost-43200 WHERE tid=:tid", array('pw_threads', $tooldb['name'], $tid)));
# memcache refresh
$fid = $db->get_value("SELECT fid FROM pw_threads WHERE tid=".S::sqlEscape($tid));
//* $threadList = L::loadClass("threadlist", 'forum');
//* $threadList->refreshThreadIdsByForumId($fid);
//* $threads = L::loadClass('Threads', 'forum');
//* $threads->delThreads($tid);
Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
require_once (R_P . 'require/updateforum.php');
delfcache($fid, $db_fcachenum);

$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
$logdata = array(
	'type'		=>	'use',
	'descrip'	=>	'tool_17_descrip',
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