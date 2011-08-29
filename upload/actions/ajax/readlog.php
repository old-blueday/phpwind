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
$r_tids = $tids;//新到旧的tids
krsort($tids);
Cookie('readlog', ',' . implode(',', $tids) . ',');
$tids && $sql_tids = S::sqlImplode($tids);
!$sql_tids && Showmsg('readlog_data_error');
//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
$readb = $array = array();
$query = $db->query("SELECT t.tid,t.fid,t.subject,t.author,t.authorid,t.anonymous,f.f_type,f.password,f.allowvisit FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE t.tid IN($sql_tids)");
while ($rt = $db->fetch_array($query)) {
	if (empty($rt['password']) && $rt['f_type'] != 'hidden' && (empty($rt['allowvisit']) || allowcheck($rt['allowvisit'], $groupid, $winddb['groups']))) {
		if ($rt['anonymous'] && !in_array($groupid, array(
			'3',
			'4'
		)) && $rt['authorid'] != $winduid) {
			$rt['author'] = $db_anonymousname;
			$rt['authorid'] = 0;
		}
		$array[$rt['tid']] = $rt;
	}
}
foreach ($r_tids as $v) {
	$readb[] = $array[$v]; 
}
require_once PrintEot('ajax');
ajax_footer();
