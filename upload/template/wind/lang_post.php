<?php
!function_exists('readover') && exit('Forbidden');

$lang['post'] = array (

'hide_post'		=> '[color=red]浏览此帖需要威望[/color]',
'post_post'		=> '[内容隐藏]',
'sell_post'		=> '[内容出售]',
//'info_post_1'	=> '[size=2][color=gray]引用楼主{$GLOBALS[old_author]}于{$GLOBALS[wtof_oldfile]}发表的 {$GLOBALS[atcarray][subject]} :[/color][/size]',
'info_post_1'	=> '[url={$GLOBALS[db_bbsurl]}/u.php?username={$GLOBALS[old_author]}]{$GLOBALS[old_author]}[/url][color=gray]:[/color]',
//'info_post_2'	=> '[size=2][color=gray]引用第{$GLOBALS[article]}楼{$GLOBALS[old_author]}于{$GLOBALS[wtof_oldfile]}发表的 {$GLOBALS[atcarray][subject]} :[/color][/size]',
'info_post_2'	=> '[url={$GLOBALS[db_bbsurl]}/u.php?username={$GLOBALS[old_author]}]{$GLOBALS[old_author]}[/url][color=gray]:[/color]',
'edit_post'		=> '此帖被{$GLOBALS[altername]}在{$GLOBALS[timeofedit]}重新编辑',

);
?>