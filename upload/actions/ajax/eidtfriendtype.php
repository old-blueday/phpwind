<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('typename','ftid'), 'P');
if (!$winduid) Showmsg('undefined_action');
if (strlen($typename) < 1 || strlen($typename) > 20) Showmsg('mode_o_addftype_name_leng');

$friendTypeName = $db->get_value("SELECT name FROM pw_friendtype WHERE ftid=" . S::sqlEscape($ftid));
if (stripslashes($typename) == $friendTypeName) {
	echo "success";
	ajax_footer();
}

$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid) . " AND name=" . S::sqlEscape($typename));
if ($check) Showmsg('mode_o_addftype_name_exist');
$db->update("UPDATE pw_friendtype SET name=" . S::sqlEscape($typename) . " WHERE uid=" . S::sqlEscape($winduid) . " AND ftid=" . S::sqlEscape($ftid));
if ($db->affected_rows()) {
	echo "success";
	ajax_footer();
} else {
	Showmsg('undefined_action');
}
