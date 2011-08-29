<?php
!function_exists('readover') && exit('Forbidden');

$lang['feed'] = array(
	'post'				=> '发表了主题 [url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]',
	'honor'				=> '更改了签名 $L[honor]',
	'friend'			=> '和 [url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}$L[uid]]$L[friend][/url] 成为了好友!',
	'colony_create'		=> '创建了新群组[url={$L[link]}][b]{$L[cname]}[/b][/url],一起去体验吧',
	'colony_pass'		=> '加入了群组[url={$L[link]}][b]{$L[cname]}[/b][/url],快点去看看吧',
	'share_view'		=> ' $L[type_name] \n $L[share_code][url=$L[link]]$L[title][/url]\n $L[abstract]\n $L[imgs]\n $L[descrip]',
	'o_write'			=> '发表了一条记录：$L[text]',
	'photo'				=> ' 上传了{$L[num]}张照片到 {$L[text]}',
	'post_board'		=> ' 在[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[touid]}]{$L[tousername]}[/url]的留言板上留了言',
	'diary_data'		=> '发表了一篇日志 [url={$GLOBALS[db_bbsurl]}/{#APPS_BASEURL#}q=diary&a=detail&uid={$L[uid]}&did={$L[did]}]{$L[subject]}[/url]\n $L[content]',
	'diary_copy'		=> '转载了一篇日志 [url={$GLOBALS[db_bbsurl]}/{#APPS_BASEURL#}&q=diary&a=detail&uid={$L[uid]}&did={$L[did]}]{$L[subject]}[/url]\n $L[content]',
	'colony_post'		=> '在群组[url={$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$L[cyid]}]{$L[colony_name]}[/url]中发表了一个讨论[url={$GLOBALS[db_bbsurl]}/apps.php?q=group&a=read&cyid={$L[cyid]}&tid={$L[tid]}]{$L[title]}[/url]',
	'colony_photo'		=> '在群组[url={$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$L[cyid]}]{$L[colony_name]}[/url]中上传了{$L[num]}张照片\n{$L[text]}',
);
?>