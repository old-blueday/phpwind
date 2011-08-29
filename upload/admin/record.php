<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once GetLang('all');
S::gp(array('adminitem'));
$basename="$admin_file?adminjob=record";
empty($adminitem) && $adminitem='adminlog';
if ($adminitem == 'adminlog') {
	$bbsrecordfile=D_P."data/bbscache/admin_record.php";
	S::gp(array('del','keyword'));
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
			if($count>100){
				$output=array_slice($bbslogfiledata,0,100);
				$output=array_reverse($output);
				$output="<?php die;?>\r\n".implode("",$output);
				pwCache::writeover($bbsrecordfile,$output);
				adminmsg('log_del');
			}else{
				adminmsg('log_min');
			}
		} else {
			adminmsg('record_aminonly');
		}
}
$db_perpage=50;
S::gp(array('page'),'GP',2);
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
<td class=\"td2\"><a href='$db_bbsurl/u.php?username=$detail[1]' target=_blank\">$detail[1]</a></td>
<td class=\"td2\">$detail[2]</td>
<td class=\"td2\">$detail[3]</td>
<td class=\"td2\">$detail[4]</td>
<td class=\"td2\">$winddate</td>
<td class=\"td2\"><div style=\"overflow:auto;max-height:80px;\">$detail[6]</div></td>
</tr>";
			}
			$num++;
		}
	}
	$numofpage=ceil($num/$db_perpage);
	$pages=numofpage($num,$page,$numofpage,"$admin_file?adminjob=record&adminitem=adminlog&action=search&keyword=".rawurlencode($keyword)."&");
} else{
	$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=record&adminitem=adminlog&");
	for($i=$pagemin; $i<=$pagemax; $i++){
		$detail=explode("|",$bbslogfiledata[$i]);
		if($detail[1] || $detail[3] || $detail[4] || $detail[6]){
			$winddate=get_date($detail[5]);
			$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
			$detail[6]=htmlspecialchars($detail[6]);
			$adlogfor.="
<tr class=\"tr1 vt\">
<td class=\"td2\"><a href='$db_bbsurl/u.php?username=$detail[1]' target=_blank\">$detail[1]</a></td>
<td class=\"td2\">$detail[2]</td>
<td class=\"td2\">$detail[3]</td>
<td class=\"td2\">$detail[4]</td>
<td class=\"td2\">$winddate</td>
<td class=\"td2\"><div style=\"overflow:auto;max-height:80px;\">$detail[6]</div></td>
</tr>";
		}
	}
}
	include PrintEot('record');exit;
} elseif($adminitem == 'forumlog') {
	$basename .= "&adminitem=forumlog";
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
} elseif($_POST['action']=='del') {
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
}} elseif ($adminitem == 'adminrecord'){
	$basename .= "&adminitem=adminrecord";
	$bbscrecordfile = D_P."data/bbscache/adminrecord.php";
	$db_adminrecord == 0 && adminmsg('adminrecord_open');
	if ($type == 'add') {
	S::gp(array('content','jumpurl'));
	if ($content) {
		/** !file_exists($bbscrecordfile) && writeover($bbscrecordfile,"<?php die;?>\n"); **/
		!file_exists($bbscrecordfile) && pwCache::writeover($bbscrecordfile,"<?php die;?>\n");
		$new_crecord = '|'.str_replace('|','&#124;',S::escapeChar($admin_name)).'|'."|$onlineip|$timestamp|".'|'.str_replace('|','&#124;',$content)."\n";
		//* writeover($bbscrecordfile,$new_crecord,"ab");
		pwCache::writeover($bbscrecordfile,$new_crecord, false, "ab");
	}
	ObHeader($jumpurl);
	} elseif ($type == 'del') {
	PostCheck($verify);
	if ($admin_gid == 3){
		$recorddb = readlog($bbscrecordfile);
		$recorddb = array_reverse($recorddb);
		$count = count($recorddb);
		if($count>100){
			$output=array_slice($recorddb,0,100);
			$output=array_reverse($output);
			$output="<?php die;?>\r\n".implode("",$output);
			//* writeover($bbscrecordfile,$output);
			pwCache::writeover($bbscrecordfile,$output);
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
	<td class=\"td2\"><a href='$db_bbsurl/u.php?username=$detail[1]' target=_blank\">$detail[1]</a></td>
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
	<td class=\"td2\"><a href='$db_bbsurl/u.php?username=$detail[1]' target=_blank\">$detail[1]</a></td>
	<td class=\"td2\">$detail[3]</td>
	<td class=\"td2\">$winddate</td>
	<td class=\"td2\">$detail[6]</td>
	</tr>";
			}
		}
	}
}
include PrintEot('adminrecord');exit;
}
?>