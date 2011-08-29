<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=draftset";

if(!$action){
	S::gp(array('username','keyword'));
	S::gp(array('page','uid'),'GP',2);
	$sqladd = 'WHERE 1';
	if($uid){
		$sqladd .= " AND d.uid='$uid'";
	} elseif($username){
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$member = $userService->getByUserName($username);
		if(!$member){
			$errorname = $username;
			adminmsg('user_not_exists');
		}
		$sqladd .= " AND d.uid=".S::sqlEscape($member['uid']);
		$uid = $member['uid'];
	}
	if($keyword){
		$sqladd .= " AND content LIKE ".S::sqlEscape("%$keyword%");
	}
	$db_perpage = 15;
	$page < 1 && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS n FROM pw_draft d $sqladd");
	$pages = numofpage($rt['n'],$page,ceil($rt['n']/$db_perpage),"$basename&uid=$uid&keyword=".rawurlencode($keyword)."&");

	$draft = array();
	$query = $db->query("SELECT d.*,m.username FROM pw_draft d LEFT JOIN pw_members m USING(uid) $sqladd $limit");
	while($rt = $db->fetch_array($query)){
		$draft[] = $rt;
	}
	include PrintEot('draftset');exit;
} elseif($action=='del'){
	if(!$_POST['step']){
		include PrintEot('draftset');exit;
	} else{
		if(S::getGP('clear')){
			$db->query("TRUNCATE TABLE pw_draft");
		} else{
			S::gp(array('username','keyword','num'));
			$num<1 && $num = 200;
			$sql = '';
			if($username){
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$member = $userService->getByUserName($username);
				if(!$member){
					$errorname = $username;
					adminmsg('user_not_exists');
				}
				$sql .= " AND uid=".S::sqlEscape($member['uid']);
			}
			if($keyword){
				$sql .= " AND content LIKE ".S::sqlEscape("%$keyword%");
			}
			$db->update("DELETE FROM pw_draft WHERE 1 $sql LIMIT $num");
		}
		adminmsg('operate_success');
	}
} elseif($_POST['action']=='draft'){
	S::gp(array('selid'));
	if(!$selid = checkselid($selid)){
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_draft WHERE did IN($selid)");
	adminmsg("operate_success");
} elseif ($_POST['action']=='empty'){
	$db->query("TRUNCATE TABLE pw_draft");
	adminmsg("operate_success");
}
?>