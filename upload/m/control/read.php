<?php
!defined('W_P') && exit('Forbidden');
require_once (R_P . 'require/bbscode.php');
require_once (R_P . 'require/forum.php');
require_once (R_P . 'require/imgfunc.php');
require_once (W_P . 'include/threadfunction.php');

InitGP(array('tid'));
(!is_numeric($tid) || $tid < 1) && $tid = 1;
if ($tid) {
	$isGM = CkInArray($windid, $manager);
	$pw_tmsgs = GetTtable($tid);
	$rt = $db->get_one("SELECT t.fid,t.tid,t.subject,t.author,t.replies,t.locked,t.postdate,t.anonymous,t.ptable,tm.content,t.ifupload,t.authorid,t.ifshield,m.groupid 
						FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid 
						LEFT JOIN pw_members m ON t.authorid = m.uid
						WHERE t.tid=" . pwEscape($tid) . " AND ifcheck=1");
	if ($rt['locked'] == 2) {
		wap_msg('read_locked');
	}
	if (!$rt) {
		wap_msg('illegal_tid');
	}
	$fid = $rt['fid'];
	
	//读取版块信息及权限判断
	if (!($foruminfo = L::forum($fid))) {
		wap_msg('data_error');
	}
	if (!$foruminfo['allowvisit'] && $_G['allowread'] == 0 && $_COOKIE) {
		
		wap_msg('read_group_right', 'index.php?a=login');
	}
	
	$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
	
	/* 获得管理权限 */
	$editright = ($isGM || pwRights($isBM, 'deltpcs') || ($rt['authorid'] == $winduid));
	$delright = ($isGM || pwRights($isBM, 'delatc'));
	$pwAnonyHide = 0;
	if (!$isGM) {
		$pwSystem = pwRights($isBM);
		$pwAnonyHide = $pwSystem['anonyhide'];
	}
	
	forumCheck($fid, 'read');
	InitGP(array('page'));
	(int) $page < 1 && $page = 1;
	if ($rt['ifshield'] || $rt['groupid'] == 6 && $db_shield) {
		if ($rt['ifshield'] == 2) {
			$rt['content'] = shield('shield_del_article');
			$rt['subject'] = '';
			$tpc_shield = 1;
		} else {
			$rt['content'] = shield($rt['ifshield'] ? 'shield_article' : 'ban_article');
			$rt['subject'] = '';
			$tpc_shield = 1;
		}
	}
	$content = viewContent($rt['content']);
	$forumName = wap_cv(strip_tags($forum[$fid]['name']));
	$db_waplimit = $db_waplimit ? $db_waplimit : '';
	$clen = wap_strlen($content,$db_charset); //TODO mbstring
	$maxp = ceil($clen / $db_waplimit);
	$nextp = $page + 1;
	$prep = $page - 1;
	if ($nextp > $maxp) $nextp = $maxp;
	if	($prep <= 0 ) $prep = 1;
	$rt['postdate'] = get_date($rt['postdate']);
	if ($rt['anonymous'] && $rt['authorid'] != $winduid && !$pwAnonyHide) {
		$rt['author'] = $db_anonymousname;
		$rt['authorid'] = 0;
	}
	
	$rt['author'] = wap_cv($rt['author']);
	
	if ($rt['ifupload'] != 0) {
		$imgs = viewAids($tid, 0);
		$downloads = viewDownloads($tid, 0);
	}
	$yxqw = "";
	if ($maxp > 1) {
		$content = wap_substr($content, $db_waplimit*($page-1),$db_waplimit,$db_charset);
		$content = wap_img2($content);
		if(empty($content)){
			wap_msg("已到最后一页","index.php?a=read&tid=$tid");
		}
		if($page == 1 ){
			$yxqw = "<a href='index.php?a=read&tid=" . $tid . "&amp;all=1&amp;page=$nextp'>下一页</a>";
		}elseif($page == $maxp){
			$yxqw = "<a href='index.php?a=read&tid=" . $tid . "&amp;all=1&amp;page=$prep'>上一页</a>&nbsp;";
		}else{
			$yxqw = "<a href='index.php?a=read&tid=" . $tid . "&amp;all=1&amp;page=$nextp'>下一页</a>";
			$yxqw .= "<a href='index.php?a=read&tid=" . $tid . "&amp;all=1&amp;page=$prep'>上一页</a>&nbsp;";
		}
		$yxqw .= "&nbsp;({$page}/{$maxp})<br/>";
	}else{
		$content = wap_img2($content);
	}
	$postdb = viewReply($tid, 1, $rt['replies'], 3, 90, $rt['ptable'],2);

} else {
	wap_msg('illegal_tid');
}
Cookie("wap_scr", serialize(array("page"=>"read","extra"=>array("tid"=>$tid))));
wap_header();
require_once PrintWAP('read');
wap_footer();
?>
