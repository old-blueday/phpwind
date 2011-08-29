<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=toollog";

require_once(R_P.'require/bbscode.php');

if(!$action || $action == 'search'){
	S::gp(array('page','keyword'));
	if($action == 'search' && $keyword){
		$sqladd = "AND descrip LIKE ".S::sqlEscape("%$keyword%");
	} else{
		$sqladd = '';
	}
	$page<1 && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_toollog WHERE type='转换' $sqladd");
	$sum   = $rt['sum'];
	$total = ceil($sum/$db_perpage);
	$pages = numofpage($sum,$page,$total,"$basename&action=search&type=".rawurlencode('转换')."keyword=".rawurlencode($keyword)."&");

	$logdb = array();
	$query = $db->query("SELECT * FROM pw_toollog WHERE type='转换' $sqladd ORDER BY time DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['time']   = get_date($rt['time']);
		$rt['descrip']= convert($rt['descrip'],array());
		$logdb[]      = $rt;
	}
	include PrintEot('toollog');
	exit;
} elseif($_POST['action'] == 'del'){
	S::gp(array('selid'),'P');
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_toollog WHERE id IN($selid)");
	adminmsg('operate_success');
}
?>