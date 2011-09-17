<?php
!function_exists('readover') && exit('Forbidden');

require_once(R_P.'require/forum.php');

if (!($foruminfo = L::forum($fid))) {
	Showmsg('data_error');
}
if ($foruminfo['type'] == 'category') {
	header("Location: index.php");exit;
}
wind_forumcheck($foruminfo);
$foruminfo['topic'] = $db->get_value("SELECT topic FROM pw_forumdata WHERE fid=".S::sqlEscape($fid));
$forumset  = $foruminfo['forumset'];
$forumname = strip_tags($foruminfo['name']);
if ($forumset['link']) {
	$flink = str_replace("&amp;","&",$forumset['link']);
	ObHeader($flink);
}

//SEO setting
$_seo = array('title'=>$foruminfo['title'],'metaDescription'=>$foruminfo['metadescrip'],'metaKeywords'=>$foruminfo['keywords']);
bbsSeoSettings('thread',$_seo,$foruminfo['name']);

if ($groupid != 3 && !$foruminfo['allowvisit'] && !admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)) {
	forum_creditcheck();
}

if ($groupid != 3 && $foruminfo['forumsell'] && !admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)) {
	forum_sell($fid);
}
$db_perpage = 100;
$db_maxpage && $page > $db_maxpage && $page=$db_maxpage;
(!is_numeric($page) || $page < 1) && $page=1;
if ($page > 1) {
	$start_limit = ($page - 1) * $db_perpage;
} else{
	$start_limit = 0;
	$page = 1;
}
$startid = $start_limit+1;
$count = $foruminfo['topic'];
$numofpage = ceil($count/$db_perpage);
if ($numofpage && $page > $numofpage) {
	$page = $numofpage;
}
$pages = PageDiv($count,$page,$numofpage,"{$DIR}f$fid",$db_maxpage);

$threaddb = array();
$query = $db->query("SELECT * FROM pw_threads WHERE fid=".S::sqlEscape($fid)." AND topped<=3 AND ifcheck='1' ORDER BY specialsort DESC, lastpost DESC".S::sqlLimit($start_limit,$db_perpage));
while ($thread = $db->fetch_array($query)) {
	$threaddb[] = $thread;
}
$db->free_result($query);

require_once PrintEot('simple_header');
require_once PrintEot('simple_thread');
?>