<?php
!defined('P_W') && exit('Forbidden');

$aids = array();
$query = $db->query("SELECT aid,attachurl FROM pw_attachs WHERE tid='0' AND pid='0' AND uid=" . pwEscape($winduid));
while ($rt = $db->fetch_array($query)) {
	if (file_exists($attachpath . '/mutiupload/' . $rt['attachurl'])) {
		P_unlink($attachpath . '/mutiupload/' . $rt['attachurl']);
	}
	$aids[] = $rt['aid'];
}
$db->update("DELETE FROM pw_attachs WHERE aid IN (" . pwImplode($aids) . ')');
echo 'ok';
ajax_footer();
