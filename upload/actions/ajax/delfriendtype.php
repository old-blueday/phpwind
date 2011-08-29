<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('u','ftid'),'P',2);
if (!$winduid) Showmsg('undefined_action');
if (!$ftid) Showmsg('undefined_action');
$db->update("DELETE FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid)." AND ftid=" . S::sqlEscape($ftid));
if ($db->affected_rows()) {
	$db->update("UPDATE pw_friends SET ftid=0 WHERE ftid=" . S::sqlEscape($ftid));
	echo "success";
	ajax_footer();
} else {
	Showmsg('undefined_action');
}