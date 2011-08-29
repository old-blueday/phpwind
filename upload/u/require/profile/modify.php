<?php
!function_exists('readover') && exit('Forbidden');

require_once(R_P. 'require/functions.php');
@include_once pwCache::getPath(D_P. 'data/bbscache/customfield.php');

$ifppt = false;

if (!$db_pptifopen || $db_ppttype == 'server') {
	$ifppt = true;
}
!is_array($customfield) && $customfield = array();
S::gp(array('step'),'P',2);
foreach ($customfield as $key => $value) {
	$customfield[$key]['id'] = $value['id'] = (int)$value['id'];
	$customfield[$key]['field'] = "field_$value[id]";
	if ($value['type'] == 3 && $step != 2) {
		$customfield[$key]['options'] = explode("\n",$value['options']);
	} elseif ($value['type'] == 2) {
		$SCR = 'post';
	}
}

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$userdb = $userService->get($winduid, true, true, true);

S::gp(array('info_type'));

!$info_type && $info_type = 'base';
if (file_exists(R_P . "u/require/profile/info_{$info_type}.php")) {
	require_once S::escapePath(R_P . "u/require/profile/info_{$info_type}.php");
} else {
	Showmsg('undefined_action');
}
exit;
?>