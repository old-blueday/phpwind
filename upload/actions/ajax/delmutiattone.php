<?php
!defined('P_W') && exit('Forbidden');

$aid = (int) GetGP('aid');
if ($aid <= 0) {
	echo "error";
} else {
	$db->update("DELETE FROM pw_attachs WHERE aid=" . pwEscape($aid) . " AND tid='0' AND pid='0' AND uid=" . pwEscape($winduid) . " LIMIT 1");
	echo "ok";
}
ajax_footer();
