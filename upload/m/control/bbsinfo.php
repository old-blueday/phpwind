<?php
!defined('W_P') && exit('Forbidden');
if ($db_online) {
	$userinbbs = $guestinbbs = 0;
	$query = $db->query("SELECT uid!=0 as ifuser,COUNT(*) AS count FROM pw_online GROUP BY uid='0'");
	while($rt = $db->fetch_array($query)){
		if($rt['ifuser']) $userinbbs=$rt['count']; else	$guestinbbs=$rt['count'];
	}
} else {
	@include_once(D_P.'data/bbscache/olcache.php');
}
$usertotal = $guestinbbs+$userinbbs;
$rt = $db->get_one ( "SELECT * FROM pw_bbsinfo WHERE id=1" );
$rs = $db->get_one ( "SELECT SUM(fd.topic) as topic,SUM(fd.subtopic) as subtopic,SUM(fd.article) as article,SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'" );
$topic = $rs ['topic'] + $rs ['subtopic'];
$article = $rs ['article'];
$tposts = $rs ['tposts'];
$userService = L::loadClass('UserService', 'user');
$uinfo = $userService->getByUserName($rt['newmember']);
Cookie("wap_scr", serialize(array("page"=>"bbsinfo")));
wap_header ();
require_once PrintWAP ( 'bbsinfo' );
wap_footer ();
?>
