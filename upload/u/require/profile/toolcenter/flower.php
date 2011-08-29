<?php
!function_exists('readover') && exit('Forbidden');

/****

@name:鲜花道具
@type:帖子类
@effect:增加帖子的推荐数

****/

if($tooldb['type']!=1){
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}
//$db->update("UPDATE pw_threads SET dig=dig+1 WHERE tid=".S::sqlEscape($tid));
pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('dig=dig+1')));
$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
$logdata = array(
	'type'		=>	'use',
	'nums'		=>	'',
	'money'		=>	'',
	'descrip'	=>	"tool_{$toolid}_descrip",
	'uid'		=>	$winduid,
	'username'	=>	$windid,
	'ip'		=>	$onlineip,
	'time'		=>	$timestamp,
	'toolname'	=>	$tooldb['name'],
	'from'		=>	'',
);
writetoollog($logdata);
Showmsg('toolmsg_success');
?>