<?php
!defined('P_W') && exit('Forbidden');

if ($groupid != 3 && $groupid != 4) {
	Showmsg('undefined_action');
}
$rightset = $db->get_value("SELECT value FROM pw_adminset WHERE gid=" . S::sqlEscape($groupid));
require_once (R_P . 'require/pw_func.php');
//$rightset = P_unserialize($rightset);
if (!$rightset || !(is_array($rightset = unserialize($rightset)))) {
	$rightset = array();
}
!$rightset['setstyles'] && Showmsg('undefined_action');

if (empty($_POST['step'])) {
	
	extract(L::style());
	require_once PrintEot('ajax');
	ajax_footer();

} else {
	
	PostCheck();
	S::gp(array(
		'set',
		'setskin'
	));
	if (!is_array($set)) {
		Showmsg('undefined_action');
	}
	foreach ($set as $key => $value) {
		if (in_array($key, array(
			'tablewidth',
			'mtablewidth'
		))) {
			if (!preg_match('/(%|px|em)/i', $value)) {
				$set[$key] .= 'px';
			}
		}
	}
	$pwSQL = S::sqlSingle(array(
		'bgcolor' => $set['bgcolor'],
		'linkcolor' => $set['linkcolor'],
		'tablecolor' => $set['tablecolor'],
		'tdcolor' => $set['tdcolor'],
		'tablewidth' => $set['tablewidth'],
		'mtablewidth' => $set['mtablewidth'],
		'headcolor' => $set['headcolor'],
		'headborder' => $set['headborder'],
		'headfontone' => $set['headfontone'],
		'headfonttwo' => $set['headfonttwo'],
		'cbgcolor' => $set['cbgcolor'],
		'cbgborder' => $set['cbgborder'],
		'cbgfont' => $set['cbgfont'],
		'forumcolorone' => $set['forumcolorone'],
		'forumcolortwo' => $set['forumcolortwo']
	));
	$rs = $db->get_one("SELECT sid FROM pw_styles WHERE name=" . S::sqlEscape($setskin) . " AND uid='0'");
	if ($rs) {
		$db->update("UPDATE pw_styles SET $pwSQL WHERE name=" . S::sqlEscape($setskin) . " AND uid='0'");
	} else {
		$db->update("INSERT INTO pw_styles SET $pwSQL");
	}
	require_once (R_P . 'admin/cache.php');
	updatecache_sy($setskin);
	
	Showmsg('operate_success');
}
