<?php
!function_exists('adminmsg') && exit('Forbidden');

//* include pwCache::getPath(D_P.'data/bbscache/bk_config.php');
pwCache::getData(D_P.'data/bbscache/bk_config.php');

if(!$action){
	if($bk_open)$bk_open_1="checked";else $bk_open_0="checked";
	if($bk_rvrc)$bk_rvrc_1="checked";else $bk_rvrc_0="checked";
	if($bk_virement)$bk_virement_1="checked";else $bk_virement_0="checked";
	include PrintHack('admin');exit;
} elseif($action == "log"){
	S::gp(array('page','username1','keyword'));
	require_once GetLang('logtype');
	$sqladd = '';
	$select = array();
	if($type && in_array($type,array('bk_save','bk_draw','bk_vire','bk_credit'))){
		$sqladd = " AND type=".S::sqlEscape($type);
		$select[$type] = "selected";
	}
	$username1 && $sqladd .= " AND username1=".S::sqlEscape($username1);
	$keyword   && $sqladd .= " AND descrip LIKE ".S::sqlEscape("%$keyword%");

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE type LIKE 'bk\_%' $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=log&type=$type&username1=$username1&keyword=$keyword&");
	$query = $db->query("SELECT * FROM pw_forumlog WHERE type LIKE 'bk\_%' $sqladd ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']  = get_date($rt['timestamp']);
		$rt['descrip']= str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$logdb[] = $rt;
	}
	include PrintHack('admin');exit;
} elseif($action=="dellog"){
	S::gp(array('selid'),'P');
	$basename="$basename&action=log";
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_forumlog WHERE id IN($selid) AND type IN('bk_save','bk_draw','bk_vire','bk_credit')");
	adminmsg('operate_success');
} elseif($action=="unsubmit"){
	S::gp(array('config'),'P');
	if(!is_numeric($config['open'])) $config['open']=1;
	if(!is_numeric($config['virement'])) $config['virement']=0;
	if(!is_numeric($config['timelimit'])) $config['timelimit']=60;
	if(!is_numeric($config['virelimit'])) $config['virelimit']=500;
	if(!is_numeric($config['virerate'])) $config['virerate']=10;
	if(!is_numeric($config['rate'])) $config['rate']=1;
	if(!is_numeric($config['drate'])) $config['drate']=1;
	if(!is_numeric($config['ddate'])) $config['ddate']=12;
	foreach($config as $key=>$value){
		$rt = $db->get_one("SELECT * FROM pw_hack WHERE hk_name=".S::sqlEscape("bk_$key"));
		if($rt){
			$db->update("UPDATE pw_hack SET hk_value=".S::sqlEscape($value)."WHERE hk_name=".S::sqlEscape("bk_$key"));
		} else{
			$db->update("INSERT INTO pw_hack SET hk_name=".S::sqlEscape("bk_$key").",hk_value=".S::sqlEscape($value));
		}
	}
	updatecache_bk();
	adminmsg('operate_success');
}
?>