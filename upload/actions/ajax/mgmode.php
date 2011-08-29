<?php
!defined('P_W') && exit('Forbidden');

if ($groupid != 3 && $groupid != 4) {
	echo 'false';
	ajax_footer();
}
$rightset = $db->get_value("SELECT value FROM pw_adminset WHERE gid=" . S::sqlEscape($groupid));
require_once (R_P . 'require/pw_func.php');
//$rightset = P_unserialize($rightset);
if (!$rightset || !(is_array($rightset = unserialize($rightset)))) {
	$rightset = array();
}

if ($rightset['setforum'] || $rightset['setstyles']) {
	require_once PrintEot('ajax');
} else {
	echo 'false';
}
ajax_footer();
