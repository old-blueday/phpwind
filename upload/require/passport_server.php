<?php
!function_exists('readover') && exit('Forbidden');

if(!$db_pptifopen || $db_ppttype != 'server'){
	Showmsg('passport_close');
}
$jumpurl=str_replace('&#61;','=',$jumpurl);

$userdb = array();
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$rt = $userService->get($winduid, true, true);

$userdb['uid']		= $rt['uid'];
$userdb['username']	= $rt['username'];
$userdb['password']	= $rt['password'];
$userdb['email']	= $rt['email'];
$userdb['rvrc']		= $rt['rvrc'];
$userdb['money']	= $rt['money'];
$userdb['credit']	= $rt['credit'];
$userdb['currency']	= $rt['currency'];
$userdb['time']		= $timestamp;
$userdb['cktime']	= $cktime ? $cktime : 'F';

$userdb_encode='';
foreach($userdb as $key=>$val){
	$userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
}
$db_hash=$db_pptkey;
$userdb_encode=str_replace('=','',StrCode($userdb_encode));

if($action=='login'){
	$verify = md5("login$userdb_encode$forward$db_pptkey");
	ObHeader("$jumpurl/passport_client.php?action=login&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
}elseif($action=='quit'){
	$verify = md5("quit$userdb_encode$forward$db_pptkey");
	ObHeader("$jumpurl/passport_client.php?action=quit&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
}
?>