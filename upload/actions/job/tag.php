<?php
!defined('P_W') && exit('Forbidden');

!$db_iftag && Showmsg('tag_closed');
S::gp(array(
	'tagname',
	'page'
));
$metakeyword = strip_tags($tagname);
$db_metakeyword = $metakeyword;
$subject = $metakeyword . ' - ';
$webPageTitle = $db_bbsname . '-' . $metakeyword;
require_once (R_P . 'require/header.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
pwCache::getData(D_P . 'data/bbscache/forum_cache.php');

$rs = $db->get_one('SELECT tagid,num FROM pw_tags WHERE tagname=' . S::sqlEscape($tagname));
(!is_numeric($page) || $page < 1) && $page = 1;
$limit = S::sqlLimit(($page - 1) * $db_readperpage, $db_readperpage);
$pages = numofpage($rs['num'], $page, ceil($rs['num'] / $db_readperpage), "link.php?action=tag&tagname=" . rawurlencode($tagname) . "&");
$query = $db->query('SELECT * FROM pw_tagdata tg LEFT JOIN pw_threads t USING(tid) WHERE tg.tagid=' . S::sqlEscape($rs['tagid']) . ' order by t.lastpost desc  '.$limit);
$tiddb = array();
while ($rt = $db->fetch_array($query)) {
	if ($rt['titlefont']) {
		$titledetail = explode("~", $rt['titlefont']);
		if ($titledetail[0]) $rt['subject'] = "<font color=$titledetail[0]>$rt[subject]</font>";
		if ($titledetail[1]) $rt['subject'] = "<b>$rt[subject]</b>";
		if ($titledetail[2]) $rt['subject'] = "<i>$rt[subject]</i>";
		if ($titledetail[3]) $rt['subject'] = "<u>$rt[subject]</u>";
	}
	if ($rt['special'] == 1) {
		$p_status = $rt['locked'] == 0 ? 'vote' : 'votelock';
	} elseif ($rt['locked'] > 0) {
		$p_status = $rt['locked'] == 1 ? 'topiclock' : 'topicclose';
	} else {
		$p_status = $rt['replies'] >= 10 ? 'topichot' : 'topicnew';
	}
	$rt['status'] = "<img src='$imgpath/$stylepath/thread/" . $p_status . ".gif' border=0>";
	
	$rt['forumname'] = $forum[$rt['fid']]['name'];
	$rt['postdate'] = get_date($rt['postdate'], "Y-m-d");
	$rt['lastpost'] = get_date($rt['lastpost']);
	$rt['lastposterraw'] = rawurlencode($rt['lastposter']);
	$rt['anonymous'] && $rt['author'] != $windid && $groupid != '3' && $rt['author'] = $db_anonymousname;
	
	$tiddb[] = $rt;
}
require_once PrintEot('tag');
footer();
