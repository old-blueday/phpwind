<?php
!defined('P_W') && exit('Forbidden');

!$_G['leaveword'] && Showmsg('leaveword_right');

if (empty($_POST['step'])) {
	
	S::gp(array('pid'));
	$tpc = $db->get_one('SELECT authorid,ptable FROM pw_threads WHERE tid=' . S::sqlEscape($tid));
	if ($tpc['authorid'] != $winduid) {
		Showmsg('leaveword_error');
	}
	$pw_posts = GetPtable($tpc['ptable']);
	$rt = $db->get_one("SELECT leaveword FROM $pw_posts WHERE pid=" . S::sqlEscape($pid) . ' AND tid=' . S::sqlEscape($tid));
	$reason_sel = '';
	$reason_a = explode("\n", $db_adminreason);
	foreach ($reason_a as $k => $v) {
		if ($v = trim($v)) {
			$reason_sel .= "<option value=\"$v\">$v</option>";
		} else {
			$reason_sel .= "<option value=\"\">-------</option>";
		}
	}
	$rt['leaveword'] = str_replace('&nbsp;', ' ', $rt['leaveword']);
	require_once PrintEot('ajax');
	ajax_footer();

} else {
	
	PostCheck();
	S::gp(array(
		'pid',
		'atc_content',
		'ifmsg'
	), 'P');
	$tpc = $db->get_one("SELECT t.authorid,t.ptable,f.forumadmin,f.fupadmin FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE t.tid=" . S::sqlEscape($tid));
	if ($tpc['authorid'] != $winduid && !S::inArray($windid, $manager) && !admincheck($tpc['forumadmin'], $tpc['fupadmin'], $windid)) {
		Showmsg('leaveword_error');
	}
	require_once (R_P . 'require/bbscode.php');
	$atc_content = str_replace('&#61;', '=', $atc_content);
	$ptable = $tpc['ptable'];
	$content = convert($atc_content, $db_windpost);
	//$sqladd = $atc_content == $content ? '' : ",ifconvert='2'";
	$_tmp = array();
	$_tmp['leaveword'] = $atc_content;
	$atc_content != $content && $_tmp['ifconvert'] = 2;
	$pw_posts = GetPtable($ptable);
	if ($ifmsg && !empty($atc_content)) {
		//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
		pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
		$atc = $db->get_one("SELECT author,fid,subject,content,postdate FROM $pw_posts WHERE pid=" . S::sqlEscape($pid) . ' AND tid=' . S::sqlEscape($tid));
		!$atc['subject'] && $atc['subject'] = substrs($atc['content'], 35);
		if (strpos($atc['subject'],'[/URL]')!==false || strpos($atc['subject'],'[/url]')!==false) {
			$atc['subject'] = preg_replace("/\[url\](.+?)\[\/url\]/is","$1", $atc['subject']);
		}
		M::sendNotice(
			array($atc['author']),
			array(
				'title' => getLangInfo('writemsg','leaveword_title'),
				'content' => getLangInfo('writemsg','leaveword_content',array(
					'fid' => $atc['fid'],
					'tid' => $tid,
					'author' => $windid,
					'subject' => $atc['subject'],
					'postdate' => get_date($atc['postdate']),
					'forum' => strip_tags($forum[$atc['fid']]['name']),
					'affect' => '',
					'admindate' => get_date($timestamp),
					'reason' => stripslashes($atc_content)
				)),
			)
		);
	}
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($atc_content)) !== false) {
		Showmsg('content_wordsfb');
	}
	
	//* $db->update("UPDATE $pw_posts SET leaveword=" . S::sqlEscape($atc_content) . " $sqladd WHERE pid=" . S::sqlEscape($pid) . ' AND tid=' . S::sqlEscape($tid));
	$db->update(pwQuery::buildClause("UPDATE :pw_table SET leaveword=:leaveword WHERE pid=:pid AND tid=:tid", array($pw_posts,$atc_content, intval($pid), $tid)));
	
	echo "success\t" . str_replace(array(
		"\n",
		"\t"
	), array(
		'<br />',
		''
	), stripslashes($content));
	ajax_footer();
}
