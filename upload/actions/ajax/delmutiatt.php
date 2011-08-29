<?php
!defined('P_W') && exit('Forbidden');

$aids = array();
$query = $db->query("SELECT aid,attachurl,ifthumb FROM pw_attachs WHERE tid='0' AND pid='0' AND uid=" . S::sqlEscape($winduid));
while ($rt = $db->fetch_array($query)) {
	$rt['attachurl'] = substr($rt['attachurl'], 11);
	pwDelatt('mutiupload/' . $rt['attachurl'], $db_ifftp);
	($rt['ifthumb'] & 1) && pwDelatt('mutiupload/s1_' . $rt['attachurl'], $db_ifftp);
	($rt['ifthumb'] & 2) && pwDelatt('mutiupload/s2_' . $rt['attachurl'], $db_ifftp);
	$aids[] = $rt['aid'];
}
S::isArray($aids) && $db->update("DELETE FROM pw_attachs WHERE aid IN (" . S::sqlImplode($aids) . ')');
echo 'ok';
ajax_footer();
