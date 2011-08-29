<?php
!function_exists('readover') && exit('Forbidden');

/**
 * @name:更改用户名道具
 * @type:会员类
 * @effect:可更改自已在论坛的用户名
 */

if ($tooldb['type']!=2) {
	Showmsg('tooluse_type_error');  // 判断道具类型是否设置错误
}
if (!$_POST['step']) {
	require_once uTemplate::PrintEot('profile_toolcenter');
	if( defined('AJAX'))
		ajax_footer();
	else 
		pwOutPut();
} else {
	//* include_once pwCache::getPath(D_P."data/bbscache/dbreg.php");
	extract(pwCache::getData(D_P."data/bbscache/dbreg.php", false));
	if (isset($rg_namelen)) {
		list($rg_regminname,$rg_regmaxname) = explode("\t",$rg_namelen);
	} else {
		$rg_regminname = 3;
		$rg_regmaxname = 12;
	}
	S::gp(array('pwuser'),'P');
	!$pwuser && Showmsg('username_empty');
	if(strlen($pwuser)>$rg_regmaxname || strlen($pwuser)<$rg_regminname){
		Showmsg('reg_username_limit');
	}
	$S_key=array('&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
	foreach($S_key as $value){
		if (strpos($pwuser,$value)!==false){
			Showmsg('illegal_username');
		}
	}
	if(!$rg_rglower){
		for($asc=65;$asc<=90;$asc++){
			if(strpos($pwuser,chr($asc))!==false){
				Showmsg('username_limit');
			}
		}
	}
	$pwuser=='guest' && Showmsg('illegal_username');
	$rg_banname=explode(',',$rg_banname);
	foreach($rg_banname as $value){
		if(strpos($pwuser,$value)!==false){
			Showmsg('illegal_username');
		}
	}
	require_once(R_P.'require/functions.php');
	if($pwuser!==Sql_cv($pwuser)){
		Showmsg('illegal_username');
	}
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if ($userService->isExistByUserName($pwuser)){
		Showmsg('username_same');
	}

	$ucuser = L::loadClass('Ucuser', 'user');
	list($ucstatus, $errmsg) = $ucuser->edit($winduid, $windid, array('username' => $pwuser));
	if ($ucstatus < 0) {
		Showmsg($errmsg);
	}
	/*
	$userService->update($winduid, array('username' => $pwuser));
	$db->update("UPDATE pw_threads SET author=".S::sqlEscape($pwuser)."WHERE authorid=".S::sqlEscape($winduid));
	$ptable_a=array('pw_posts');
	if($db_plist && count($db_plist)>1){
		foreach($db_plist as $key => $val){
			if($key == 0) continue;
			$ptable_a[]='pw_posts'.(int)$key;
		}
	}
	foreach($ptable_a as $val){
		$db->update("UPDATE $val SET author=".S::sqlEscape($pwuser)."WHERE authorid=".S::sqlEscape($winduid));
	}
	$db->update("UPDATE pw_cmembers SET username=".S::sqlEscape($pwuser)."WHERE uid=".S::sqlEscape($winduid));
	$db->update("UPDATE pw_colonys SET admin=".S::sqlEscape($pwuser)."WHERE admin=".S::sqlEscape($windid));
	$db->update("UPDATE pw_announce SET author=".S::sqlEscape($pwuser)."WHERE author=".S::sqlEscape($windid));
	$db->update("UPDATE pw_medalslogs SET awardee=".S::sqlEscape($pwuser)."WHERE awardee=".S::sqlEscape($windid));

	require R_P.'admin/cache.php';
	$query = $db->query("SELECT fid,forumadmin,fupadmin FROM pw_forums WHERE forumadmin LIKE ".S::sqlEscape("%,$windid,%")."OR fupadmin LIKE".S::sqlEscape( "%,$windid,%"));
	while($rt = $db->fetch_array($query)){
		$rt['forumadmin']	= str_replace(",$windid,",",$pwuser,",$rt['forumadmin']);
		$rt['fupadmin']		= str_replace(",$windid,",",$pwuser,",$rt['fupadmin']);
		$db->update("UPDATE pw_forums SET ".S::sqlSingle(array('forumadmin'=>$rt['forumadmin'],'fupadmin'=>$rt['fupadmin']),false)."WHERE fid=".S::sqlEscape($rt['fid']));
		updatecache_forums($rt['fid']);
	}
	*/

	$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid=".S::sqlEscape($winduid)."AND toolid=".S::sqlEscape($toolid));
	$logdata=array(
		'type'		=>	'use',
		'nums'		=>	'',
		'money'		=>	'',
		'descrip'	=>	'tool_8_descrip',
		'uid'		=>	$winduid,
		'username'	=>	$windid,
		'ip'		=>	$onlineip,
		'time'		=>	$timestamp,
		'toolname'	=>	$tooldb['name'],
		'newname'	=>	$pwuser,
		'tid'		=>	$tid,
	);
	writetoollog($logdata);
	//* $_cache = getDatastore();
	//* $_cache->delete('UID_'.$winduid);
	
	perf::gatherInfo('changeMembersWithUserIds', array('uid'=>$winduid));
	Showmsg('toolmsg_8_success');
}
?>