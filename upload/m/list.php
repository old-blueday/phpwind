<?php
require_once('wap_global.php');

$listdb = array();
if($fid){
	InitGP(array('page'));
	$per = 10;
	$fm  = $db->get_one("SELECT topic FROM pw_forumdata WHERE fid=".pwEscape($fid));
	$db_maxpage && $page > $db_maxpage && $page=$db_maxpage;
	(!is_numeric($page) || $page < 1) && $page=1;
	$totle = ceil($fm['topic']/$per);
	$totle ==0 ? $page=1 : ($page > $totle ? $page=$totle : '');
	$next  = $page+1;
	$pre   = $page==1 ? 1 : $page-1;
	$pages = wap_numofpage($page,$totle,"list.php?fid=$fid&amp;",$db_maxpage);
	forumcheck($fid,'list');
	$list  = '';
	$satrt = ($page-1)*$per;
	$id    = $satrt;
	$limit = pwLimit($satrt,$per);
	$query = $db->query("SELECT tid,author,subject,postdate,hits,replies,anonymous FROM pw_threads WHERE fid=".pwEscape($fid)." AND topped<3 AND ifcheck=1 ORDER BY topped DESC,lastpost DESC $limit");
	while($rt=$db->fetch_array($query)){
		$id++;
		$rt['anonymous'] && $rt['author'] = $db_anonymousname;
		$rt['postdate'] = get_date($rt['postdate'],"m-d H:i");
		$rt['id'] = $id;
		$rt['subject'] = substrs(str_replace('&nbsp;','',$rt['subject']),30,'N');
		$listdb[] = $rt;
	}
	$forumname = wap_cv(strip_tags($forum[$fid]['name']));
}
wap_header('list',$db_bbsname);
require_once PrintEot('wap_list');
wap_footer();
?>