<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=usercheck&admintype=$admintype";

if(empty($_POST['action'])){
	InitGP(array('page'),'GP',2);
	if($admintype=='checkemail'){
		$page < 1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_members WHERE yz>1");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

		$memdb_E = array();
		$query = $db->query("SELECT uid,username,regdate,email FROM pw_members WHERE yz>1 ORDER BY regdate DESC $limit");
		while($member=$db->fetch_array($query)){
			$member['regdate'] = get_date($member['regdate']);
			$memdb_E[] = $member;
		}
	} elseif($admintype=='checkreg'){
		$page < 1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_members WHERE groupid='7'");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

		$memdb_R = array();
		$query = $db->query("SELECT m.uid,username,regdate,email,i.regreason FROM pw_members m LEFT JOIN pw_memberinfo i ON i.uid=m.uid WHERE groupid='7' ORDER BY regdate DESC $limit");
		while($member=$db->fetch_array($query)){
			$member['regdate'] = get_date($member['regdate']);
			$memdb_R[] = $member;
		}
	}
	include PrintEot('usercheck');exit;
} elseif($action=='check'){
	!$_POST['yzmem'] && adminmsg('operate_error');
	$uids = array();
	foreach($_POST['yzmem'] as $value){
		is_numeric($value) && $uids[] = $value;
	}
	if($uids){
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if($type=='pass'){
			if($admintype=='checkemail'){
				$userService->updates($uids, array('yz'=>1));
			} elseif($admintype=='checkreg'){
				$userService->updates($uids, array('groupid'=>-1));
			}
		} else{
			$userService->deletes($uids);
			$lastestUser = $userService->getLatestNewUser();
			$db->update("UPDATE pw_bbsinfo SET ".pwSqlSingle(array('newmember'=>$lastestUser['username'],'totalmember'=>$userService->count()))."WHERE id='1'");
		}
	}
	adminmsg('operate_success');
}
?>