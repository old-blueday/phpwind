<?php
!function_exists('readover') && exit('Forbidden');
$lang['message'] = array (

'colony_joinlimit'			=> '加入群组失败，你允许加入的群组个数已满。',
'colony_memberlimit'		=> '加入群组失败，该群组人数已满。',
'colony_joinrefuse'			=> '该群组拒绝新成员加入。',
'colony_joinfail'			=> '你的{$GLOBALS[moneyname]}不足，加入群组需要 {$GLOBALS[o_joinmoney]} {$GLOBALS[moneyname]}',
'colony_passfail'			=> '用户 <b>{$GLOBALS[rt][username]}</b> {$GLOBALS[moneyname]}不足，不能通过审核。',
'colony_alreadyjoin'		=> '你已经加入了该群组。',
'colony_joinsuccess'		=> '加入群组成功!',
'colony_joinsuccess_check'	=> '你的加入群组申请已提交，请等待群主审核',
'colony_joinsuccess_check2'	=> '你的加入群组申请已提交，请等待群主审核',
'colony_add_ignore'			=> '你已经忽略了对方的群组邀请',
'colony_request_agree'		=> '你已经同意了对方的群组邀请'	,
'colony_check_success'		=> '你已经同意了TA加入群组的请求'	,
'colony_check_fail'			=> '你已不具备审核资格'	,
'colony_check_ignore'		=> '你已经忽略了对方加入群组的请求'	,
'colony_check_agree'		=> '你已经同意了对方加入群组的请求'	,

'friend_add_success'		=> '从现在开始，你们俩就是好友了！<a href="{$GLOBALS[db_userurl]}{$GLOBALS[fid]}">马上去看TA</a>',
'friend_add_fail'			=> '添加好友失败',
'friend_add_ignore'			=> '你已经忽略了对方的请求',
'friend_request_agree'		=> '你已经同意了TA的好友请求'	,

'app_add_success'			=> '你同意安装了此应用',
'app_add_fail'				=> '应用安装失败',
'app_add_ignore'			=> '你已经忽略了对方的请求',
'app_request_agree'			=> '你已经同意了TA的应用请求'	,

'request_friend'			=> '好友请求',
'request_group'				=> '群组请求',
'request_app'				=> '应用请求',
'request_ignore'			=> '你已经忽略了对方的请求',
)

?>