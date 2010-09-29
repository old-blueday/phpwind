<?php
!defined('P_W') && exit('Forbidden');

InitGP(array('typename','ftid'), 'P');
if (!$winduid) Showmsg('undefined_action');
if (strlen($typename) < 1 || strlen($typename) > 20) Showmsg('mode_o_addftype_name_leng');

$friendTypeName = $db->get_value("SELECT name FROM pw_friendtype WHERE ftid=".pwEscape($ftid));
if (stripslashes($typename) == $friendTypeName) {
	echo "success";
	ajax_footer();
}

$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=".pwEscape($winduid)." AND name=".pwEscape($typename));
if ($check) Showmsg('mode_o_addftype_name_exist');
$db->update("UPDATE pw_friendtype SET name=".pwEscape($typename)." WHERE uid=".pwEscape($winduid)." AND ftid=".pwEscape($ftid));
if ($db->affected_rows()) {
	echo "success";
	ajax_footer();
} else {
	Showmsg('undefined_action');
}
