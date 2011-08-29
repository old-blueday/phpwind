<?php
!defined('P_W') && exit('Forbidden');

$rt = $db->get_one('SELECT fid,postdate,lastpost FROM pw_threads WHERE tid=' . S::sqlEscape($tid));
!$rt && Showmsg('data_error');
$fid = (int) $rt['fid'];

$goto = S::escapeChar(S::getGP('goto'));

if ($goto == "next") {
	$tid = $db->get_value("SELECT tid FROM pw_threads WHERE fid=" . S::sqlEscape($fid, false) . "AND ifcheck='1' AND topped='0' AND lastpost<" . S::sqlEscape($rt['lastpost'], false) . "ORDER BY lastpost DESC LIMIT 1");
} else {
	$tid = $db->get_value("SELECT tid FROM pw_threads WHERE fid=" . S::sqlEscape($fid, false) . "AND ifcheck='1' AND topped='0' AND lastpost>" . S::sqlEscape($rt['lastpost'], false) . "ORDER BY lastpost ASC LIMIT 1");
}
if ($tid) {
	ObHeader("read.php?tid=$tid&displayMode=1");
} else {
	ObHeader("thread.php?fid=$fid");
}