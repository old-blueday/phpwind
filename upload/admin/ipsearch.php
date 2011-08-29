<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=ipban&job=ipsearch";

if(empty($action) || $action == 'force'){
	include PrintEot('ipsearch');exit;
} elseif($action == 'byname'){
	S::gp(array('username'));
	!$username && adminmsg('ipsearch_username');
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->getByUserName($username);
	$uids = $rt['uid'];

	$pages = '';
	$ipdb = array();
	if($uids){
		S::gp(array('page'),'GP',2);
		$query=$db->query("SELECT m.uid,m.username,md.onlineip AS userip,md.thisvisit AS lasttime FROM pw_memberdata md LEFT JOIN pw_members m ON m.uid=md.uid WHERE md.onlineip!='' AND md.uid=".S::sqlEscape($uids).'GROUP BY md.onlineip');
		while($rt=$db->fetch_array($query)){
			$rt['lasttime']=get_date($rt['lasttime']);
			$rt['userip']=strpos($rt['userip'],'|') ? substr($rt['userip'],0,strpos($rt['userip'],'|')) : $rt['userip'];
			$ipdb[]=$rt;
		}

		$ttable_a = array('pw_tmsgs');
		if($db_tlist){
			foreach($db_tlist as $key=>$val){
				if($key == 0)continue;
				$ttable_a[]='pw_tmsgs'.$key;
			}
		}
		foreach($ttable_a as $pw_tmsgs){
			$query=$db->query("SELECT tm.userip,t.postdate AS lasttime,t.authorid AS uid,t.author AS username FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE userip!='' AND t.authorid=".S::sqlEscape($uids)."GROUP BY userip");
			while($rt=$db->fetch_array($query)){
				$rt['lasttime']=get_date($rt['lasttime']);
				$ipdb[]=$rt;
			}
		}
		$ptable_a = array('pw_posts');
		if($db_plist && count($db_plist)>1){
			foreach($db_plist as $key => $val){
				if($key == 0) continue;
				$ptable_a[] = 'pw_posts'.$key;
			}
		}
		foreach($ptable_a as $pw_posts){
			$query=$db->query("SELECT userip,postdate AS lasttime,author AS username,authorid AS uid FROM $pw_posts WHERE userip!='' AND authorid=".S::sqlEscape($uids)."GROUP BY userip");
			while($rt=$db->fetch_array($query)){
				$rt['lasttime']=get_date($rt['lasttime']);
				$ipdb[]=$rt;
			}
		}
		$count=count($ipdb);
		$page < 1 && $page=1;
		$start=($page-1)*50;
		$end=min($start+50,$count);
		$numofpage=ceil($count/50);
		$pages=numofpage($count,$page,$numofpage,"$basename&action=byname&username=".rawurlencode($username)."&type=$type&");
	}
	include PrintEot('ipsearch');exit;
} elseif($action=='byip'){
	$rt1=$db->get_one("SELECT totalmember FROM pw_bbsinfo WHERE id=1");
	if($rt1['totalmember']>100000){
		adminmsg('ipsearch_force');
	}
	$rt2=$db->get_one("SELECT SUM(article) AS article FROM pw_forumdata");
	if($rt2['article']>300000){
		adminmsg('ipsearch_force');
	}
	S::gp(array('userip'));
	!$userip && adminmsg('ipsearch_userip');
	$pages='';
	$userdb=array();

	$sql = "md.onlineip LIKE ".S::sqlEscape("$userip%");
	$query=$db->query("SELECT m.uid,m.username,m.email,md.thisvisit AS lasttime,md.postnum,md.onlineip AS userip FROM pw_memberdata md LEFT JOIN pw_members m ON m.uid=md.uid WHERE $sql GROUP BY m.username");
	while($rt=$db->fetch_array($query)){
		if(strpos($rt['userip'],'|')!==false){
			$rt['userip']=substr($rt['userip'],0,strpos($rt['userip'],'|'));
		} else{
			$rt['userip']=$rt['userip'];
		}
		$rt['lasttime']=get_date($rt['lasttime']);
		$userdb[] = $rt;
	}

	$sql = "tm.userip=".S::sqlEscape($userip);
	$ttable_a = array('pw_tmsgs');
	if($db_tlist){
		foreach($db_tlist as $key=>$val){
			if ($key == 0) continue;
			$ttable_a[] = 'pw_tmsgs'.$key;
		}
	}
	foreach($ttable_a as $pw_tmsgs){
		$query=$db->query("SELECT t.authorid AS uid,t.author AS username,t.postdate AS lasttime,tm.userip FROM $pw_tmsgs tm LEFT JOIN pw_threads t ON t.tid=tm.tid WHERE $sql GROUP BY authorid");
		while($rt=$db->fetch_array($query)){
			$rt['lasttime']=get_date($rt['lasttime']);
			$userdb[]=$rt;
		}
	}
	$ptable_a=array('pw_posts');
	
	if($db_plist && count($db_plist)>1){
		foreach($db_plist as $key => $val){
			if($key == 0) continue;
			$ptable_a[] = 'pw_posts'.$key;
		}
	}
	foreach($ptable_a as $pw_posts){
		$query=$db->query("SELECT authorid AS uid,author AS username,postdate AS lasttime,userip FROM $pw_posts tm WHERE $sql GROUP BY authorid");
		while($rt=$db->fetch_array($query)){
			$rt['lasttime']=get_date($rt['lasttime']);
			$userdb[]=$rt;
		}
	}
	if($userdb){
		S::gp(array('page'),'GP',2);
		$count=count($userdb);
		$page < 1 && $page=1;
		$start=($page-1)*50;
		$end=min($start+50,$count);
		$numofpage=ceil($count/50);
		$pages=numofpage($count,$page,$numofpage,"$basename&action=byip&userip=$userip&type=$type&");
	}

	include PrintEot('ipsearch');exit;
}
?>