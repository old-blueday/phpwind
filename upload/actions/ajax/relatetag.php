<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('tagname'));
$rs = $db->get_one("SELECT tagid,num FROM pw_tags WHERE tagname=" . S::sqlEscape($tagname));
if (!$rs || $rs['num'] < 1) {
	Showmsg('tag_limit');
}
$query = $db->query("SELECT tg.tid,t.subject FROM pw_tagdata tg LEFT JOIN pw_threads t USING(tid) WHERE tg.tagid=" . S::sqlEscape($rs['tagid']) . " LIMIT 5");
$readdb = array();
while ($rt = $db->fetch_array($query)) {
	$rt['subject'] = substrs($rt['subject'], 65);
	$readdb[] = $rt;
}
require_once PrintEot('ajax');
ajax_footer();
