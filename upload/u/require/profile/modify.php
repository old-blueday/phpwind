<?php
!function_exists('readover') && exit('Forbidden');

require_once(R_P. 'require/functions.php');

$ifppt = false;

if (!$db_pptifopen || $db_ppttype == 'server') {
	$ifppt = true;
}
S::gp(array('step'),'P',2);

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$userdb = $userService->get($winduid, true, true, true);
S::gp(array('info_type'));

!$info_type && $info_type = 'base';
if (file_exists(R_P . "u/require/profile/info_{$info_type}.php")) {
	$customfield = L::loadClass('CustomerField','user');
	$otherFields = $customfield->getFieldsByCategoryName('other');
	$educationFields = $customfield->getFieldsByCategoryName('education');
	$showOtherTab =  S::isArray($otherFields) ? true : false;
	$showEducationTab =  S::isArray($educationFields) ? true : false;
	unset($otherFields, $educationFields);
	require_once S::escapePath(R_P . "u/require/profile/info_{$info_type}.php");
} else {
	Showmsg('undefined_action');
}

function getCustomFieldsAndDefaultValue($infotype) {
	$customfieldService = L::loadClass('CustomerFieldService','user');
	return $customfieldService->getProfileTemplateByInfotype($infotype);
}

exit;
?>