<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=uniteforum&type=$type";
require_once(R_P.'require/updateforum.php');

if(empty($_POST['action'])){
	//* @include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();

	include PrintEot('uniteforum');exit;
} else{
	S::gp(array('fid','tofid'),'P',2);
	if(empty($fid)){
		adminmsg('unite_type');
	}
	if($fid==$tofid){
		adminmsg('unite_same');
	}
	$sub=$db->get_one("SELECT fid,name FROM pw_forums WHERE fup=".S::sqlEscape($fid)."LIMIT 1");
	if($sub){
		adminmsg('forum_havesub');
	}
	$forum=$db->get_one("SELECT type FROM pw_forums WHERE fid=".S::sqlEscape($tofid)."LIMIT 1");
	if($forum['type']=='category'){
		adminmsg('unite_type');
	}
	$forum=$db->get_one("SELECT fup,type FROM pw_forums WHERE fid=".S::sqlEscape($fid)."LIMIT 1");
	if($forum['type']=='category'){
		adminmsg('unite_type');
	}
	//$db->update("UPDATE pw_threads SET fid=".S::sqlEscape($tofid)."WHERE fid=".S::sqlEscape($fid));
	pwQuery::update('pw_threads', 'fid = :fid', array($fid), array('fid'=>$tofid));
	$ptable_a=array('pw_posts');

	if ($db_plist && count($db_plist)>1) {
		foreach ($db_plist as $key => $value) {
			if($key == 0) continue;
			$ptable_a[] = 'pw_posts'.$key;
		}
	}
	foreach($ptable_a as $val){
		//$db->update("UPDATE $val SET fid=".S::sqlEscape($tofid)."WHERE fid=".S::sqlEscape($fid));
		pwQuery::update($val, 'fid=:fid', array($fid), array('fid' => $tofid));
	}
	$db->update("UPDATE pw_attachs SET fid=".S::sqlEscape($tofid)."WHERE fid=".S::sqlEscape($fid));
	//* $db->update("DELETE FROM pw_forums WHERE fid=".S::sqlEscape($fid));
	pwQuery::delete('pw_forums', 'fid=:fid' , array($fid));
	//* $db->update("DELETE FROM pw_forumdata WHERE fid=".S::sqlEscape($fid));
	pwQuery::delete('pw_forumdata', 'fid=:fid', array($fid));
	$db->update("DELETE FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid));
	//* P_unlink(D_P."data/forums/fid_{$fid}.php");
	pwCache::deleteData(D_P."data/forums/fid_{$fid}.php");

	updatecache_f();
	updateforum($tofid);
	if($forum['type']=='sub'){
		updateforum($forum['fup']);
	}
	adminmsg('operate_success');
}
?>