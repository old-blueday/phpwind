<?php
!function_exists('readover') && exit('Forbidden');

//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

$query = $db->query("SELECT t.tid,t.fid,t.authorid,t.author,t.subject,t.postdate FROM pw_threads t LEFT JOIN pw_reward r USING(tid) WHERE t.special='3' AND t.state='0' AND r.timelimit<'$timestamp' ORDER BY t.postdate ASC LIMIT 100");
$tids = $uiddb = array();
while ($rt = $db->fetch_array($query)) {
	$rt['postdate']	  = get_date($rt['postdate']);
	$tids[$rt['tid']] = $rt;
}
$title	 = S::escapeChar(getLangInfo('writemsg','rewardmsg_notice_title'));

foreach ($tids as $tid => $msg) {
	$L = array(
		'tid'		=> $tid,
		'subject'	=> $msg['subject'],
		'postdate'	=> $msg['postdate'],
		'fid'		=> $msg['fid'],
		'name'		=> $forum[$msg['fid']]['name']
	);
	$content = S::escapeChar(getLangInfo('writemsg','rewardmsg_notice_content',$L));
	M::sendNotice(array($msg['author']),array('title' => $title,'content' => $content));
}
?>