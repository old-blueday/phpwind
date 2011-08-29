<?php
!defined('W_P') && exit('Forbidden');
include_once (D_P . 'data/bbscache/forum_cache.php');
$fid = ( int ) GetGP ( 'fid' );
if (empty ( $fid ) || ! isset ( $forum [$fid] ) || $forum [$fid] ['type'] != 'category') {
	$fid = 0;
}

foreach($forum as $key=>$value){
	if(!isset($forum[$key]['sub_total'])){
		$forum[$key]['sub_total'] = 0;
	}
}

$fids = $_fids = array ();
$query = $db->query ( "SELECT fid,fup,type,allowvisit,f_type FROM pw_forums WHERE ifcms != 2" );
$parents = array();
while ( $rt = $db->fetch_array ( $query ) ) {

	if ($rt['f_type'] != 'hidden' || ($rt['f_type'] == 'hidden' && strpos($rt['allowvisit'],','.$groupid.',') !== false)) {
		$_fids [] = $rt;
		$forum[$rt['fup']]['sub_total']++;
	}
	if ($rt['type'] == 'forum') {
		$parents[] = $rt['fup'];
	}
}
foreach ($_fids as $value) {
	if ($value['type'] == 'category') {
		if (in_array($value['fid'],$parents)) { 
			$fids[] = $value['fid'];
			if(empty($forum[$value['fid']]['sub_total'])){
				unset($forum[$value['fid']]);
			}
		}
	}else{
		$fids[] = $value['fid'];
	}
}
unset($_fids,$parents);
Cookie("wap_scr", serialize(array("page"=>"list")));
wap_header ();
require_once PrintWAP ( 'list' );
wap_footer ();
?>
