<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'typename'
), 'P');
if (!$winduid) Showmsg('undefined_action');
if (strlen($typename) < 1 || strlen($typename) > 20) Showmsg('mode_o_addftype_name_leng');
$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=" . pwEscape($winduid) . " AND name=" . pwEscape($typename));
if ($check) Showmsg('mode_o_addftype_name_exist');
$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_friendtype WHERE uid=" . pwEscape($winduid));
if ($count > 20) Showmsg('mode_o_addftype_length');
$db->update("INSERT INTO pw_friendtype(uid,name) VALUES(" . pwEscape($winduid) . "," . pwEscape($typename) . ")");
$id = $db->insert_id();
if ($id) {
	echo "success\t$id\t$typename";
	ajax_footer();
} else {
	Showmsg('undefined_action');
}
