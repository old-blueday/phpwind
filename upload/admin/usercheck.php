<?php
!function_exists('adminmsg') && exit('Forbidden');
empty($adminitem) && $adminitem = 'checkreg';
$jobUrl="$admin_file?adminjob=usercheck";
$basename="$admin_file?adminjob=usercheck&adminitem=$adminitem";

if(empty($_POST['action'])){
	S::gp(array('page'),'GP',2);
	if($adminitem=='checkemail'){
		$page < 1 && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_members WHERE yz>1");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

		$memdb_E = array();
		$query = $db->query("SELECT uid,username,regdate,email FROM pw_members WHERE yz>1 ORDER BY regdate DESC $limit");
		while($member=$db->fetch_array($query)){
			$member['regdate'] = get_date($member['regdate']);
			$memdb_E[] = $member;
		}
	} elseif($adminitem=='checkreg'){
		$page < 1 && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
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
			if($adminitem=='checkemail'){
				$userService->updates($uids, array('yz'=>1));
			} elseif($adminitem=='checkreg'){
				$userService->updates($uids, array('groupid'=>-1));
			}
		} else{
			$userService->deletes($uids);
			$lastestUser = $userService->getLatestNewUser();
			//* $db->update("UPDATE pw_bbsinfo SET ".S::sqlSingle(array('newmember'=>$lastestUser['username'],'totalmember'=>$userService->count()))."WHERE id='1'");
			pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('newmember'=>$lastestUser['username'],'totalmember'=>$userService->count()));
		}
	}
	adminmsg('operate_success');
}
?>