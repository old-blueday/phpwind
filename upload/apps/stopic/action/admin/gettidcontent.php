<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
S::gp(array('tid','block_id'));
$tid	= (int) $tid;
if (!$tid) exit;
$thread	= $db->get_one("SELECT tid,fid,author,authorid,subject,type,postdate,hits,replies FROM pw_threads WHERE tid=".S::sqlEscape($tid));
$temp	= array();

if ($thread) {
	$temp = array();
	$temp['url'] 	=  ($db_htmifopen == 0) ? 'read.php?tid='.$thread['tid'] : 'read.php?tid-'.$thread['tid'].'.html';
	$temp['title'] 	= $thread['subject'];
	$temp['image']	= '';
	require_once(R_P.'require/functions.php');
	$temp['forumname']= getForumName($thread['fid']);
	$temp['forumurl']	= getForumUrl($thread['fid']);
	$temp['descrip'] = getDescripByTid($tid);
//	$block	= $stopic_service->getBlockById($block_id);
//	foreach ($block['config'] as $value) {
//		if ($value == 'descrip') {
//			$temp[$value] = getDescripByTid($tid);
//		} elseif (array_key_exists($value,$thread)) {
//			$temp[$value] = $thread[$value];
//		} else {
//			$temp[$value] = '';
//		}
//	}
	$temp = pwJsonEncode($temp);
	echo "success\t".$temp;
} else {
	echo "error";
}

ajax_footer();
?>