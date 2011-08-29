<?php
!function_exists('readover') && exit('Forbidden');
if (!$_POST['step']){
	require_once uTemplate::PrintEot('info_link');
	pwOutPut();
}else if($_POST['step'] == '2'){
	PostCheck();
	S::slashes($userdb);
	S::gp(array('prooicq', 'proaliww','proicq','proyahoo', 'promsn'),'P');

	//联系方式 处理
	$prooicq && !is_numeric($prooicq) && Showmsg('illegal_OICQ');
	$proicq && !is_numeric($proicq) && Showmsg('illegal_OICQ');

	//update member
	$pwSQL = array(
		'oicq' => $prooicq, 'aliww' => $proaliww, 'icq' => $proicq, 'yahoo' => $proyahoo, 'msn' => $promsn
	);
	$userService->update($winduid, $pwSQL);
	//* $_cache = getDatastore();
	//* $_cache->delete('UID_'.$winduid);
	//job sign
	initJob($winduid,"doUpdatedata");
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}