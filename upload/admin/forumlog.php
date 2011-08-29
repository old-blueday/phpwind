<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=forumlog";

if(!$action){
	require_once GetLang('logtype');
	require_once(R_P.'require/bbscode.php');
	//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	pwCache::getData(D_P.'data/bbscache/forum_cache.php');
	//* include pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	S::gp(array('page','username1','username2','fid','type'));
	//增加所属板块@modify panjl@2010-11-2
	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
	$sqladd = "WHERE 1";
	if($type && $lang['logtype'][$type]){
		$sqladd .= " AND type=".S::sqlEscape($type);
	}
	$type_sel[$type] = 'selected';
	$username1 && $sqladd .= " AND username1=".S::sqlEscape($username1);
	$username2 && $sqladd .= " AND username2=".S::sqlEscape($username2);
	//增加所属板块@modify panjl@2010-11-2
	if ( $fid && (int)$fid != '-1' ) {
		$sqladd .= " AND field1=".S::sqlEscape($fid);
	}
	$db_perpage = 30;

	(int)$page<1 && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_adminlog $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&type=$type&username1=$username1&username2=$username2&num=$num&");
	$query = $db->query("SELECT * FROM pw_adminlog $sqladd ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']    = get_date($rt['timestamp']);
		$rt['descrip'] = str_replace("\n","<br>",$rt['descrip']);
		$rt['descrip'] = convert($rt['descrip'],array());
		$logdb[] = $rt;
	}
	require_once PrintEot('forumlog');
} elseif($_POST['action']=='del'){
	S::gp(array('selid'),'P');
	if($admin_gid != 3){
		adminmsg('record_aminonly');
	}
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');
	}
	$deltime = $timestamp - 259100;
	$db->update("DELETE FROM pw_adminlog WHERE id IN($selid) AND timestamp<".S::sqlEscape($deltime));
	adminmsg('operate_success');
} elseif($action=='delall'){
	PostCheck($verify);
	if($admin_gid != 3){
		adminmsg('record_aminonly');
	}
	$deltime = $timestamp - 259100;
	$db->update("DELETE FROM pw_adminlog WHERE timestamp<".S::sqlEscape($deltime));
	adminmsg('operate_success');
}
?>