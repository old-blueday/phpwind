<?php
!defined('P_W') && exit('Forbidden');

InitGP(array('u','ftid'),'P',2);
if (!$winduid) Showmsg('undefined_action');
if (!$ftid) Showmsg('undefined_action');
$db->update("DELETE FROM pw_friendtype WHERE uid=".pwEscape($winduid)." AND ftid=".pwEscape($ftid));
if ($db->affected_rows()) {
	$db->update("UPDATE pw_friends SET ftid=0 WHERE ftid=".pwEscape($ftid));
	echo "success";
	ajax_footer();
} else {
	Showmsg('undefined_action');
}