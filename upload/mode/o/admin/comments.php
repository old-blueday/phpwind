<?php
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');

if (empty($action)) {
	if ($job == 'delete') {
		S::gp(array('selid','type','typeid','title','username','postdate_s','postdate_e','ordertype','page','lines'));
		empty($selid) && adminmsg("no_album_selid");
		if(!function_exists('countPosts')){
			require_once (R_P . 'u/require/core.php');
		}
		foreach ($selid as $key => $id) {
			$thiscomm = $db->get_one("SELECT uid,type,typeid FROM pw_comment WHERE id=".S::sqlEscape($id));
			$updatenum = 0;
			$db->update("DELETE FROM pw_comment WHERE id=".S::sqlEscape($id));
			$updatenum += $db->affected_rows();
			$db->update("DELETE FROM pw_comment WHERE upid=".S::sqlEscape($id));
			$updatenum += $db->affected_rows();
			list($app_table,$app_filed) = getCommTypeTable($thiscomm['type']);
			if ($updatenum && $app_table && $thiscomm['typeid']) {
				$db->update("UPDATE $app_table SET c_num=c_num-".S::sqlEscape($updatenum)." WHERE $app_filed=".S::sqlEscape($thiscomm['typeid']));
			}
			countPosts("-$updatenum");
		}
		adminmsg('operate_success',"$basename&job=list&title=".rawurlencode($title)."&username=".rawurlencode($username)."&type=$type&typeid=$typeid&postdate_s=$postdate_s&postdate_e=$postdate_e&ordertype=$ordertype&lines=$lines&page=$page&");
	} else {
		S::gp(array('type','typeid','title','username','postdate_s','postdate_e','ordertype','page','lines'));
		
		$lines = $lines ? $lines : $db_perpage;
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';
		$postdateStartString = $postdate_s && is_numeric($postdate_s) ? get_date($postdate_s, 'Y-m-d') : $postdate_s;
		$postdateEndString = $postdate_e && is_numeric($postdate_e) ? get_date($postdate_e, 'Y-m-d') : $postdate_e;
		$commentTypeSelections = formSelect('type', $type, array('diary'=>'日志', 'photo'=>'照片', 'board'=>'留言'), 'class="select_wa"', '--请选择--');
		
		if (empty($type) && empty($title) && empty($username) && empty($postdate_s) && empty($postdate_e)) {
			$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
		} else {
			
		$postdate_s && !is_numeric($postdate_s) && $postdate_s = PwStrtoTime($postdate_s);
		$postdate_e && !is_numeric($postdate_e) && $postdate_e = PwStrtoTime($postdate_e);
		$postdate_e && $sqlpostdate_e = $postdate_e + 86400;
		$sql = $urladd = '';
		if ($type) {
			$sql .= $sql ? ' AND' : '';
			$sql .= ' type='.S::sqlEscape($type);
			$urladd .= '&type='.$type;
		}
		if ($typeid) {
			$sql .= $sql ? ' AND' : '';
			$sql .= ' typeid='.S::sqlEscape($typeid);
			$urladd .= '&typeid='.$typeid;
		}
		if ($title) {
			$title = str_replace('*','%',$title);
			$sql .= $sql ? ' AND' : '';
			$sql .= ' title LIKE '.S::sqlEscape($title);
			$urladd .= '&title='.rawurlencode($title);
		}
		if ($username) {
			$username = str_replace('*','%',$username);
			$sql .= $sql ? ' AND' : '';
			$sql .= ' username LIKE '.S::sqlEscape($username);
			$urladd .= '&username='.rawurlencode($username);
		}
		if ($postdate_s) {
			$sql .= $sql ? ' AND' : '';
			$sql .= ' postdate>'.S::sqlEscape($postdate_s);
			$urladd .= "&postdate_s=$postdate_s";
		}
		if ($postdate_e) {
			$sql .= $sql ? ' AND' : '';
			$sql .= ' postdate<'.S::sqlEscape($sqlpostdate_e);
			$urladd .= "&postdate_e=$postdate_e";
		}
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&ordertype=$ordertype&lines=$lines";
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_comment WHERE $sql");
		//empty($count) && adminmsg('comment_not_exist');
		!is_numeric($lines) && $lines=30;
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&job=list$urladd&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);
		$query = $db->query("SELECT id,uid,username,title,type,postdate FROM pw_comment WHERE $sql "."ORDER BY postdate $ordertype ".$limit);
		while ($rt = $db->fetch_array($query)) {
			$rt['s_title'] = stripWindCode($rt['title']);
			$rt['s_title'] = substrs($rt['s_title'],40);
			$rt['ch_type'] = getLangInfo('other',$rt['type']);
			$rt['postdate'] = $rt['postdate'] ? get_date($rt['postdate']) : '-';
			$commentdb[] = $rt;
		}
		
		$title = str_replace('%', '*', $title);
		$username = str_replace('%', '*', $username);
		
		}
		require_once PrintMode('comments');
	}
}
?>