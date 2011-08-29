<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename="$admin_file?adminjob=adminrecord";
$bbscrecordfile = D_P."data/bbscache/adminrecord.php";
$db_adminrecord == 0 && adminmsg('adminrecord_open');
if ($admintype == 'add') {
	S::gp(array('content','jumpurl'));
	if ($content) {
		!file_exists($bbscrecordfile) && writeover($bbscrecordfile,"<?php die;?>\n");
		/** !file_exists($bbscrecordfile) && pwCache::setData($bbscrecordfile,"<?php die;?>\n"); **/
		$new_crecord = '|'.str_replace('|','&#124;',S::escapeChar($admin_name)).'|'."|$onlineip|$timestamp|".'|'.str_replace('|','&#124;',$content)."\n";
		writeover($bbscrecordfile,$new_crecord,"ab");
		//* pwCache::setData($bbscrecordfile,$new_crecord, false, "ab");
	}
	ObHeader($jumpurl);
} elseif ($admintype == 'del') {
	PostCheck($verify);
	if ($admin_gid == 3){
		$recorddb = readlog($bbscrecordfile);
		$recorddb = array_reverse($recorddb);
		$count = count($recorddb);
		if($count>100){
			$output=array_slice($recorddb,0,100);
			$output=array_reverse($output);
			$output="<?php die;?>\r\n".implode("",$output);
			writeover($bbscrecordfile,$output);
			//* pwCache::setData($bbscrecordfile,$output);
			adminmsg('adminrecord_del');
		}else{
			adminmsg('adminrecord_min');
		}
	} else {
		adminmsg('record_aminonly');
	}
} else {
	S::gp(array('page'),'GP',2);
	S::gp(array('action','keyword'),'P');
	$recorddb = readlog($bbscrecordfile);
	$recorddb = array_reverse($recorddb);
	$count = count($recorddb);
	$db_perpage=50;
	$page < 1 && $page = 1;
	if ($action == 'search') {
		!$keyword && adminmsg('noenough_condition');
		$num=0;
		$start=($page-1)*$db_perpage;
		foreach($recorddb as $value){
			if(strpos($value,$keyword)!==false){
				if($num >= $start && $num < $start+$db_perpage){
					$detail=explode("|",$value);
					$winddate=get_date($detail[4]);
					$detail[6]=htmlspecialchars($detail[6]);
					$adlogfor.="
	<tr class=\"tr1 vt\">
	<td class=\"td2\"><a href='$admin_file?adminjob=setuser&action=search&schname=$detail[1]&schname_s=1'>$detail[1]</a></td>
	<td class=\"td2\">$detail[3]</td>
	<td class=\"td2\">$winddate</td>
	<td class=\"td2\">$detail[6]</td>
	</tr>";
				}
				$num++;
			}
		}
		$numofpage=ceil($num/$db_perpage);
		$pages=numofpage($num,$page,$numofpage,"$basename&action=search".rawurlencode($keyword)."&");
	} else {
		$pagemin=min(($page-1)*$db_perpage , $count-1);
		$pagemax=min($pagemin+$db_perpage-1, $count-1);
		$pages=numofpage($count,$page,ceil($count/$db_perpage),"$basename&");
		for($i=$pagemin; $i<=$pagemax; $i++){
			$detail=explode("|",$recorddb[$i]);
			if($detail[1] && $detail[3] && $detail[4] && $detail[6]){
				$winddate=get_date($detail[4]);
				$detail[6]=htmlspecialchars($detail[6]);
				$adlogfor.="
	<tr class=\"tr1 vt\">
	<td class=\"td2\"><a href='$admin_file?adminjob=setuser&action=search&schname=$detail[1]&schname_s=1'>$detail[1]</a></td>
	<td class=\"td2\">$detail[3]</td>
	<td class=\"td2\">$winddate</td>
	<td class=\"td2\">$detail[6]</td>
	</tr>";
			}
		}
	}
}
include PrintEot('adminrecord');exit;
?>