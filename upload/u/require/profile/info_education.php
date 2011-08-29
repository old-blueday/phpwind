<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('step'));
if (empty($step)) {
	$customFieldsString = getCustomFieldsAndDefaultValue('education');
	require_once uTemplate::PrintEot('info_education');
	pwOutPut();
} elseif ($step == 2) {
	//update customerfield data
	$customfieldService = L::loadClass('CustomerFieldService', 'user'); /* @var $customfieldService PW_CustomerFieldService */
	$customfieldService->saveProfileCustomerData('education');
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}
?>