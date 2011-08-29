<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=viewtoday";

if (empty($action)) {
	if ($db_today == 0) {
		include PrintEot('viewtoday');exit;
	}
	S::gp(array('page'),'GP',2);
	$page < 1 && $page = 1;
	$filename = D_P.'data/bbscache/today.php';
	$dbtdsize = 100+1;
	$seed = $page*$db_perpage;$count=0;
	if ($fp = @fopen($filename,"rb")) {
		flock($fp,LOCK_SH);
		$node=fread($fp,$dbtdsize);
		$nodedb=explode("\t",$node);/*头结点在第二个数据段*/
		$nodefp=$dbtdsize*$nodedb[1];
		fseek($fp,$nodefp,SEEK_SET);
		$todayshow=fseeks($fp,$dbtdsize,$seed);/*传回数组*/
		fseek($fp,0,SEEK_END);
		$count=floor(ftell($fp)/$dbtdsize)-1;
		fclose($fp);
	}
	if ($count%$db_perpage==0) {
		$numofpage = $count/$db_perpage;  //$numofpage为 一共多少页
	} else {
		$numofpage = floor($count/$db_perpage)+1;
	}
	if ($page > $numofpage)
		$page = $numofpage;

	$pagemin = min(($page-1)*$db_perpage , $count-1);
	$pagemax = min($pagemin+$db_perpage-1, $count-1);
	$pages = numofpage($count,$page,$numofpage,"$basename&");
	require_once R_P.'require/postfunc.php';
	$inbbsdb = array();
	for ($i = $pagemin; $i <= $pagemax; $i++) {
		if (!trim($todayshow[$i])) continue;
		list($inbbs['user'],$null1,$null2,$inbbs['rgtime'],$inbbs['logintime'],$inbbs['intime'],$inbbs['ip'],$inbbs['post'],$inbbs['rvrc'],$null) = explode("\t",$todayshow[$i]);
		$inbbs['rawuser']	= rawurlencode($inbbs['user']);
		$inbbs['rvrc']		= floor($inbbs['rvrc']/10);
		$inbbs['rgtime']	= get_date($inbbs['rgtime']);
		$inbbs['logintime']	= get_date($inbbs['logintime']);
		$inbbs['intime']	= get_date($inbbs['intime']);
		$inbbs['ipfrom']	= cvipfrom($inbbs['ip']);
		$inbbsdb[] = $inbbs;
	}

	include PrintEot('viewtoday');exit;

}
adminmsg('operate_success');

function fseeks($fp,$dbtdsize,$seed) {
	$break = $num = 0;
	while ($break!=1 && $num<$seed) {
		$num++;
		$sdata=fread($fp,$dbtdsize);
		$sdb=explode("\t",$sdata);
		$sdbnext=$sdb[2]*$dbtdsize;
		if ($sdbnext!='NULL') {
			fseek($fp,$sdbnext,SEEK_SET);
		} else {
			$break = 1;
		}
		$todayshow[] = $sdata;
	}
	return $todayshow;
}
?>