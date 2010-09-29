<?php
!defined('P_W') && exit('Forbidden');

!$db_iftag && Showmsg('tag_closed');
$webPageTitle = $db_bbsname;
require_once (R_P . 'require/header.php');
$query = $db->query("SELECT * FROM pw_tags WHERE ifhot='0' ORDER BY num DESC LIMIT 100");
$tagdb = array();
while ($rt = $db->fetch_array($query)) {
	$tagdb[] = $rt;
}
require_once PrintEot('tag');
footer();
