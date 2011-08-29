<?php
!function_exists('readover') && exit('Forbidden');

$lang['log'] = array (

'bk_save_descrip_1'		=> '[b]{$L[username1]}[/b]使用活期存款功能，存入金额：{$L[field1]}{$GLOBALS[db_moneyname]}',
'bk_save_descrip_2'		=> '[b]{$L[username1]}[/b]使用定期存款功能，存入金额：{$L[field1]}{$GLOBALS[db_moneyname]}',
'bk_draw_descrip_1'		=> '[b]{$L[username1]}[/b]使用活期取款功能，取出金额：{$L[field1]}{$GLOBALS[db_moneyname]}',
'bk_draw_descrip_2'		=> '[b]{$L[username1]}[/b]使用定期取款功能，取出金额：{$L[field1]}{$GLOBALS[db_moneyname]}',
'bk_vire_descrip'		=> '[b]{$L[username1]}[/b]使用转帐功能，转帐给[b]{$L[username2]}[/b]'
							.'金额：{$L[field1]}{$GLOBALS[db_moneyname]}，转帐附言：{$GLOBALS[memo]}',
'topped_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章设为置顶{$L[topped]}\n原因：{$L[reason]}',
'untopped_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：解除文章置顶\n原因：{$L[reason]}',
'digest_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章设为精华{$L[digest]}\n原因：{$L[reason]}\n影响：{$L[affect]}',
'undigest_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：取消文章精华\n原因：{$L[reason]}\n影响：{$L[affect]}',
'highlight_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章标题加亮\n原因：{$L[reason]}',
'unhighlight_descrip'	=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章标题取消加亮\n原因：{$L[reason]}',
'push_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章提前\n原因：{$L[reason]}',
'lock_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章锁定\n原因：{$L[reason]}',
'unlock_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章解除锁定\n原因：{$L[reason]}',
'delrp_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：删除回复\n原因：{$L[reason]}\n影响：{$L[affect]}',
'deltpc_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：删除主题\n原因：{$L[reason]}\n影响：{$L[affect]}',
'del_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章删除\n原因：{$L[reason]}\n影响：{$L[affect]}',
'move_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章移动到新版块([url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[tofid]}][b]$L[toforum][/b][/url])\n'
							.'原因：{$L[reason]}',
'copy_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章复制到新版块([url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[tofid]}][b]$L[toforum][/b][/url])\n'
							.'原因：{$L[reason]}',
'edit_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：编辑文章',
'credit_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：文章被评分\n原因：{$L[reason]}\n影响：{$L[affect]}',
'creditdel_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：文章评分被取消\n原因：{$L[reason]}\n影响：{$L[affect]}',
'banuser_descrip'		=> '用户 [b]{$L[username1]}[/b] 被禁言\n操作：用户禁言\n原因：{$L[reason]}',
'deluser_descrip'		=> '用户 [b]{$L[username1]}[/b] 被删除\n操作：批量删除用户',
'join_descrip'			=> '[b]{$L[username1]}[/b] 加入{$GLOBALS[cn_name]}[b]{$L[cname]}[/b]，花费{$GLOBALS[moneyname]}：{$L[field1]}。',
'donate_descrip'		=> '[b]{$L[username1]}[/b] 使用捐献给所在{$GLOBALS[cn_name]}($L[cname])捐献{$GLOBALS[moneyname]}：{$L[field1]}。',
'cy_vire_descrip'		=> '[b]{$L[username2]}[/b] 使用{$GLOBALS[cn_name]}{$GLOBALS[moneyname]}管理功能，'
							.'给用户[b]{$L[username1]}[/b]转帐 {$L[field1]}{$GLOBALS[moneyname]}，系统收取手续费：{$L[tax]}。',
'shield_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：屏蔽主题\n原因：{$L[reason]}',
'banuserip_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：禁止IP\n原因：{$L[reason]}',
'signature_descrip'	=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：禁止签名\n原因：{$L[reason]}',
'unite_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：主题合并\n原因：{$L[reason]}',
'remind_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：管理提示\n原因：{$L[reason]}',
'down_descrip'			=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章压帖\n原因：{$L[reason]}',
'recycle_topic_delete'	=> '版块:$L[forum]\n操作：将文章从主题回收站中彻底删除',
'recycle_topic_restore'	=> '版块:$L[forum]\n操作：将文章从主题回收站中还原',
'recycle_topic_empty'	=> '版块:$L[forum]\n操作：将主题回收站清空',
'recycle_reply_delete'	=> '版块:$L[forum]\n操作：将文章从回复回收站中彻底删除',
'recycle_reply_restore'	=> '版块:$L[forum]\n操作：将文章从回复回收站中还原',
'recycle_reply_empty'	=> '版块:$L[forum]\n操作：将回复回收站清空',
'pushto_descrip'		=> '帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]\n'
							.'操作：将文章推送\n原因：{$L[reason]}'
);
?>