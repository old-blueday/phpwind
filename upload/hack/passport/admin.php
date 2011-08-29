<?php
!function_exists('adminmsg') && exit('Forbidden');

if(!$action){
	ifcheck($db_pptifopen,'ifopen');
	ifcheck($db_pptcmode,'cmode');
	${'type_'.$db_ppttype}='checked';
	if($db_ppttype=='server'){
		$style_server = "";
		$style_client = "display:none;";
	} else{
		$style_server = "display:none;";
		$style_client = "";
	}
	$credit=array(
		'rvrc'=>$db_rvrcname,
		'money'=>$db_moneyname,
		'credit'=>$db_creditname,
		'currency'=>$db_currencyname,
	);

	include PrintHack('admin');exit;

} else {

	S::gp(array('config','ppt_credit','ppturls'),'P');
	$config['db_pptcredit'] = implode(',',$ppt_credit);
	foreach ($config as $key => $value) {
		setConfig($key, $value);
	}
	updatecache_c();
	adminmsg("operate_success");
}
?>