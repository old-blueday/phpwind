<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'tid',
	'page',
	'toread',
	'fpage',
	'anchor',
	'option',
	'ds'
));
$jumpurl = $db_bbsurl . '/read.php?tid=' . $tid;
($page > 1) && $jumpurl .= '&page=' . $page;
($fpage) && $jumpurl .= '&fpage=' . $fpage;
($toread) && $jumpurl .= '&toread=' . $toread;
$ds && $jumpurl .= '&ds=' . $ds;
$jumpurl .= "#" . $anchor;
$descript = getDescript($option);
require_once PrintEot('poplogin');
ajax_footer();
function getDescript($option){
	$descript = '';
	switch($option){
		case 'viewpic' : $descript = '帖子中含有图片或附件，只有在登录后才能查看';break;
		default:$descript = '请先登录，再继续操作';break;
	}
	return $descript;
}