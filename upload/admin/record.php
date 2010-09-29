<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once GetLang('all');

$basename="$admin_file?adminjob=record&admintype=adminlog";
$bbsrecordfile=D_P."data/bbscache/admin_record.php";

InitGP(array('del','keyword'));
if(file_exists($bbsrecordfile)){
	$bbslogfiledata=readlog($bbsrecordfile);
} else{
	$bbslogfiledata=array();
}
$bbslogfiledata=array_reverse($bbslogfiledata);
$count=count($bbslogfiledata);
if($del=='Y'){
	PostCheck($verify);
	if ($admin_gid == 3){
		if($count>10000){
			$output=array_slice($bbslogfiledata,0,10000);
			$output=array_reverse($output);
			$output="<?php die;?>\r\n".implode("",$output);
			writeover($bbsrecordfile,$output);
			adminmsg('log_del');
		}else{
			adminmsg('log_min');
		}
	} else {
		adminmsg('record_aminonly');
	}
}
$db_perpage=50;
InitGP(array('page'),'GP',2);
$page < 1 && $page=1;
if($count%$db_perpage==0){
	$numofpage=floor($count/$db_perpage);
} else{
	$numofpage=floor($count/$db_perpage)+1;
}
if($page>$numofpage){
	$page=$numofpage;
}
$pagemin=min(($page-1)*$db_perpage , $count-1);
$pagemax=min($pagemin+$db_perpage-1, $count-1);

if($action=='search'){
	if(!$keyword){
		adminmsg('noenough_condition');
	}
	$num=0;
	$start=($page-1)*$db_perpage;
	foreach($bbslogfiledata as $value){
		if(strpos($value,$keyword)!==false){
			if($num >= $start && $num < $start+$db_perpage){
				$detail=explode("|",$value);
				$winddate=get_date($detail[5]);
				$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
				$detail[6]=htmlspecialchars($detail[6]);
				$adlogfor.="
<tr class=\"tr1 vt\">
<td class=\"td2\"><a href='$admin_file?adminjob=setuser&action=search&schname=$detail[1]&schname_s=1'>$detail[1]</a></td>
<td class=\"td2\">$detail[2]</td>
<td class=\"td2\">$detail[3]</td>
<td class=\"td2\">$detail[4]</td>
<td class=\"td2\">$winddate</td>
<td class=\"td2\">$detail[6]</td>
</tr>";
			}
			$num++;
		}
	}
	$numofpage=ceil($num/$db_perpage);
	$pages=numofpage($num,$page,$numofpage,"$admin_file?adminjob=record&admintype=adminlog&action=search&keyword=".rawurlencode($keyword)."&");
} else{
	$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=record&admintype=adminlog&");
	for($i=$pagemin; $i<=$pagemax; $i++){
		$detail=explode("|",$bbslogfiledata[$i]);
		if($detail[1] || $detail[3] || $detail[4] || $detail[6]){
			$winddate=get_date($detail[5]);
			$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
			$detail[6]=htmlspecialchars($detail[6]);
			$adlogfor.="
<tr class=\"tr1 vt\">
<td class=\"td2\"><a href='$admin_file?adminjob=setuser&action=search&schname=$detail[1]&schname_s=1'>$detail[1]</a></td>
<td class=\"td2\">$detail[2]</td>
<td class=\"td2\">$detail[3]</td>
<td class=\"td2\">$detail[4]</td>
<td class=\"td2\">$winddate</td>
<td class=\"td2\">$detail[6]</td>
</tr>";
		}
	}
}
include PrintEot('record');exit;
?>