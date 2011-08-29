<?php
!defined('P_W') && exit('Forbidden');

$aid = (int) S::getGP('aid');
if ($aid <= 0) {
	echo "error";
} else {
	$db->update("DELETE FROM pw_attachs WHERE aid=" . S::sqlEscape($aid) . " AND tid='0' AND pid='0' AND uid=" . S::sqlEscape($winduid) . " LIMIT 1");
	echo "ok";
}
ajax_footer();
