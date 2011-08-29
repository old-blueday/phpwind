<?php
!function_exists('readover') && exit('Forbidden');
if (!$_POST['step']){
	$customFieldsString = getCustomFieldsAndDefaultValue('other');
	
	require_once uTemplate::PrintEot('info_other');
	pwOutPut();
}else if( $_POST['step'] == '2' ){
	PostCheck();
	//update customerfield data
	$customfieldService = L::loadClass('CustomerFieldService', 'user'); /* @var $customfieldService PW_CustomerFieldService */
	$customfieldService->saveProfileCustomerData('other');
	
	/*S::slashes($userdb);
	$upmembers = $upmemdata = $upmeminfo = array();
	foreach ($customfield as $value) {
		$fieldvalue = S::escapeChar($_POST[$value['field']]);
		if ($value['required'] && ($value['editable'] == 1 || strlen($userdb[$value['field']]) == 0) && !$fieldvalue) {
			Cookie('pro_modify', 'other', 'F', false);
			Showmsg('field_empty');
		}
		if (strlen($userdb[$value['field']]) == 0 || ($userdb[$value['field']] != $fieldvalue && $value['editable'] == 1)) {
			if ($value['maxlen'] && strlen($fieldvalue) > $value['maxlen']) {
				Showmsg('field_lenlimit');
			}
			$upmeminfo[$value['field']] = $fieldvalue;
		}
	}
	//update meminfo
	if ($upmeminfo) {
		$userService->update($winduid, array(), array(), $upmeminfo);
	}
	unset($upmeminfo);*/
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}
