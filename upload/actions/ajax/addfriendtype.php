<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('typename'), 'P');
if (!$winduid) Showmsg('undefined_action');
if (strlen($typename) < 1 || strlen($typename) > 20) Showmsg('mode_o_addftype_name_leng');
$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid) . " AND name=" . S::sqlEscape($typename));
if ($check) Showmsg('mode_o_addftype_name_exist');
$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid));
if ($count > 20) Showmsg('mode_o_addftype_length');
$db->update("INSERT INTO pw_friendtype(uid,name) VALUES(" . S::sqlEscape($winduid) . "," . S::sqlEscape($typename) . ")");
$id = $db->insert_id();
if ($id) {
	echo "success\t$id\t$typename";
	ajax_footer();
} else {
	Showmsg('undefined_action');
}
