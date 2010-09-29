<?php
!defined('P_W') && exit('Forbidden');

$readlog = explode(',', GetCookie('readlog'));
@krsort($readlog);
$tids = array();
$i = 0;
foreach ($readlog as $key => $value) {
	if (is_numeric($value)) {
		$tids[] = $value;
		if (++$i > 9) break;
	}
}
Cookie('readlog', ',' . implode(',', $tids) . ',');
$tids && $tids = pwImplode($tids);
!$tids && Showmsg('readlog_data_error');
include_once (D_P . 'data/bbscache/forum_cache.php');
$readb = array();
$query = $db->query("SELECT t.tid,t.fid,t.subject,t.author,t.authorid,t.anonymous,f.f_type,f.password,f.allowvisit FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE t.tid IN($tids)");
while ($rt = $db->fetch_array($query)) {
	if (empty($rt['password']) && $rt['f_type'] != 'hidden' && (empty($rt['allowvisit']) || allowcheck($rt['allowvisit'], $groupid, $winddb['groups']))) {
		if ($rt['anonymous'] && !in_array($groupid, array(
			'3',
			'4'
		)) && $rt['authorid'] != $winduid) {
			$rt['author'] = $db_anonymousname;
			$rt['authorid'] = 0;
		}
		$readb[] = $rt;
	}
}
require_once PrintEot('ajax');
ajax_footer();
