<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=content";
require_once(R_P.'require/bbscode.php');
S::gp(array('id','tid'),'GP',2);

if ($type == 'tpc') {

	$pw_tmsgs = GetTtable($id);
	$rt = $db->get_one("SELECT t.tid,t.subject,tm.content,tm.ifconvert FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid  WHERE t.tid=".S::sqlEscape($id));
	$rt['content'] = str_replace("\n","<br>",$rt['content']);
	//* include_once pwCache::getPath(D_P.'data/bbscache/wordsfb.php');
	pwCache::getData(D_P.'data/bbscache/wordsfb.php');
	foreach ($alarm as $key => $value) {
		$rt['content'] = str_replace($key,'<span style="background-color:#ffff66">'.$key.'</span>',$rt['content']);
	}
	include PrintEot('content');exit;

} elseif ($type == 'post') {

	$pw_posts = GetPtable('N',$tid);
	$rt = $db->get_one("SELECT pid,tid,subject,content FROM $pw_posts WHERE pid=".S::sqlEscape($id));
	$rt['content'] = str_replace("\n","<br>",$rt['content']);
	//* include_once pwCache::getPath(D_P.'data/bbscache/wordsfb.php');
	pwCache::getData(D_P.'data/bbscache/wordsfb.php');
	foreach ($alarm as $key => $value) {
		$rt['content'] = str_replace($key,'<span style="background-color:#ffff66">'.$key.'</span>',$rt['content']);
	}
	include PrintEot('content');exit;

}
?>