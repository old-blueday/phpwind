<?php
!defined('P_W') && exit('Forbidden');

if (!pwWritable(D_P.'data/sql_config.php')) {
	adminmsg('manager_error');
}

include D_P.'data/sql_config.php';
!is_array($manager) && $manager = array();
!is_array($manager_pwd) && $manager_pwd = array();
$newmanager = $newmngpwd = array();
foreach ($manager as $key => $value) {
	if (!empty($value) && !is_array($value)) {
		$newmanager[$key] = $value;
		$newmngpwd[$key] = $manager_pwd[$key];
	}
}
$manager = $newmanager;
$manager_pwd = $newmngpwd;
unset($newmanager,$newmngpwd);

S::gp(array('oldname','username','password'));

if (!$action) {

	ifcheck($db_adminrecord,'adminrecord');
	include PrintEot('manager');exit;

} elseif ($action == 'add') {

	if (!$username || !$password) {
		adminmsg('manager_empty');
	}
	if (S::getGP('check_pwd') != $password) {
		adminmsg('password_confirm');
	}
	if (str_replace(array('\\','&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#'),'',$username) != $username) {
		adminmsg('manager_errorusername');
	}
	if (str_replace(array('\\','&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#'),'',$password) != $password) {
		adminmsg('manager_errorpassword');
	}
	$password = md5($password);
	
	if (S::inArray($username,$manager)) {
		adminmsg('manager_had');
	}
	
	$manager[] = $username;
	$manager_pwd[] = $password;
	$newconfig = array(
		'dbhost' => $dbhost,
		'dbuser' => $dbuser,
		'dbpw' => $dbpw,
		'dbname' => $dbname,
		'database' => $database,
		'PW' => $PW,
		'pconnect' => $pconnect,
		'charset' => $charset,
		'manager' => $manager,
		'manager_pwd' => $manager_pwd,
		'db_hostweb' => $db_hostweb,
		'db_distribute' => $db_distribute,
		'attach_url' => $attach_url,
		'slaveConfigs' => $slaveConfigs
	);
	require_once(R_P.'require/updateset.php');
	write_config($newconfig);
	unset($newconfig);
	
	pwUpdateManager($username,$password);
	adminmsg('operate_success');

} elseif ($action == 'edit') {

	if (!S::inArray($oldname,$manager)) {
		adminmsg('undefined_action');
	}
	if ($_POST['step'] != 2) {

		include PrintEot('manager');exit;

	} else {

		if (!$username) {
			adminmsg('manager_empty');
		}
		if (str_replace(array('\\','&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#'),'',$username) != $username) {
			adminmsg('manager_errorusername');
		}
		$key = (int)array_search($oldname,$manager);
		if (!$password) {
			$password = $manager_pwd[$key];
		} else {
			if (S::getGP('check_pwd')!=$password) {
				adminmsg('password_confirm');
			}
			if (str_replace(array('\\','&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#'),'',$password)!=$password) {
				adminmsg('manager_errorpassword');
			}
			$password = $manager_pwd[$key] = md5($password);
		}
		if ($username != $oldname) {
			if (S::inArray($username,$manager)) {
				adminmsg('manager_had');
			}
			$manager[$key] = $username;
			$oldname == $admin_name && Cookie('AdminUser','',0);
		}
		$newconfig = array(
			'dbhost' => $dbhost,
			'dbuser' => $dbuser,
			'dbpw' => $dbpw,
			'dbname' => $dbname,
			'database' => $database,
			'PW' => $PW,
			'pconnect' => $pconnect,
			'charset' => $charset,
			'manager' => $manager,
			'manager_pwd' => $manager_pwd,
			'db_hostweb' => $db_hostweb,
			'db_distribute' => $db_distribute,
			'attach_url' => $attach_url,
			'slaveConfigs' => $slaveConfigs
		);
		require_once(R_P.'require/updateset.php');
		write_config($newconfig);
		unset($newconfig);

		pwUpdateManager($username,$password);
		adminmsg('operate_success');
	}
} elseif ($action == 'delete') {

	if ($_POST['step'] != 2) {

		$inputmsg = '<input name="step" type="hidden" value="2" /><input name="action" type="hidden" value="delete" /><input name="username" type="hidden" value="'.$oldname.'" />';
		pwConfirm('manager_delusername',$inputmsg);

	} else {

		if (count($manager) < 2) {
			adminmsg('manager_only');
		}
		$newmanager = $newmngpwd = array();
		foreach ($manager as $key => $value) {
			if ($username != $value) {
				$newmanager[$key] = $value;
				$newmngpwd[$key] = $manager_pwd[$key];
			}
		}
		$newconfig = array(
			'dbhost' => $dbhost,
			'dbuser' => $dbuser,
			'dbpw' => $dbpw,
			'dbname' => $dbname,
			'database' => $database,
			'PW' => $PW,
			'pconnect' => $pconnect,
			'charset' => $charset,
			'manager' => $newmanager,
			'manager_pwd' => $newmngpwd,
			'db_hostweb' => $db_hostweb,
			'db_distribute' => $db_distribute,
			'attach_url' => $attach_url,
			'slaveConfigs' => $slaveConfigs
		);
		require_once(R_P.'require/updateset.php');
		write_config($newconfig);
		unset($newconfig);
		$username == $admin_name && Cookie('AdminUser','',0);
		lowerManager($username);
		updatecache_f();
		updateadmin();
		adminmsg('operate_success');
	}
} elseif ($action == 'ifopen') {

	S::gp(array('config'));
	foreach ($config as $key => $value) {
		setConfig("db_$key", $value);
	}
	updatecache_c();
	adminmsg('operate_success');
} else {
	ObHeader($basename);
}
function lowerManager($username){
	global $db;
	
	$userService = L::loadclass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->getByUserName($username);
	if ($rt){
		$userService->update($rt['uid'], array('groupid'=>-1));
	}

	if($rt = $db->get_one('SELECT uid,groups FROM pw_administrators WHERE username='.S::sqlEscape($username))){
		$db->update("UPDATE pw_administrators SET groupid='-1' WHERE uid=".S::sqlEscape($rt['uid']));
	}
}

function pwUpdateManager($username,$password){
	global $db;
	
	$userService = L::loadclass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->getByUserName($username);

	if (!$rt['uid']) {
		global $timestamp,$onlineip;
		
		$mainFields = array(
			'username'	=> $username,
			'password'	=> $password,
			'groupid'	=> 3,
			'memberid'	=> 8,
			'regdate'	=> $timestamp
		);
		
		$memberDataFields = array(
			'postnum'	=> 0,
			'lastvisit'	=> $timestamp,
			'thisvisit'	=> $timestamp,
			'onlineip'	=> $onlineip
		);

		$userService->add($mainFields, $memberDataFields);
	} else {
		$userService->update($rt['uid'], array('groupid'=>3, 'password'=>$password));
	}
	admincheck($rt['uid'],$username,'3',$rt['groups'],'update');
}
?>