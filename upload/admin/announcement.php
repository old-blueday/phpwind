<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename = "$admin_file?adminjob=announcement";

if ($action == 'order') {

	!is_array($vieworder = $_POST['vieworder']) && $vieworder = array();
	$updatedb = array();
	foreach ($vieworder as $key => $value) {
		if (is_numeric($key)) {
			$value = (int)$value;
			$updatedb[$value][] = $key;
		}
	}
	foreach ($updatedb as $key => $value) {
		//* $value && $db->update("UPDATE pw_announce SET vieworder=".S::sqlEscape($key)."WHERE aid IN (".S::sqlImplode($value).')');
		$value && pwQuery::update('pw_announce','aid IN (:aid)', array($value), array('vieworder'=>$key));
	}
	updatecache_i();
	adminmsg('operate_success');

} elseif ($action == 'add') {

	list($fids,$forumcache,$cmscache) = GetForumdb();

	if ($_POST['step'] != 2) {

		$fid = (int)$_GET['fid']==0 ? -1 : $_GET['fid'];
		$ifopen_Y = 'CHECKED'; $vieworder = (int)$vieworder;
		$ifopen_N = $subject = $atc_content = $enddate = '';
		$startdate = get_date($timestamp,'Y-m-d H:i');

		Showoption($fid);
		$ckdisplay = Displayfid();
		include PrintEot('notice');exit;

	} else {

		$successurl = $basename;
		$basename .= '&action=add';
		$fid = (int)$_POST['fid'];
		!$fid && adminmsg('annouce_fid');
		!Checkright($fids,$fid) && adminmsg('annouce_right');
		$basename .= "&fid=$fid";
		$atc_title = trim($_POST['atc_title']);
		!$atc_title && adminmsg('annouce_title');

		$atc_content = trim(ieconvert($_POST['atc_content']));
		$url = trim(S::escapeChar(str_replace(array('"',"'",'\\'),'',$_POST['url'])));
		!$atc_content && !$url && adminmsg('annouce_content');

		$startdate = $_POST['startdate'] ? PwStrtoTime($_POST['startdate']) : $timestamp;
		$enddate = $_POST['enddate'] ? PwStrtoTime($_POST['enddate']) : '';
		$enddate && $enddate<=$startdate && adminmsg('annouce_time');
//		!Datecheck($fid,$startdate,$enddate) && adminmsg('annouce_date');
		S::gp(array('ifopen','vieworder'),'P',2);
		/**
		$db->update("INSERT INTO pw_announce"
			. " SET " . S::sqlSingle(array(
				'fid'		=> $fid,			'ifopen'	=> $ifopen,
				'vieworder'	=> $vieworder,	'author'	=> $admin_name,
				'startdate'	=> $startdate,		'enddate'	=> $enddate,
				'url'		=> $url,			'subject'	=> $atc_title,
				'content'	=> $atc_content
		)));
		**/
		pwQuery::insert('pw_announce', array(
				'fid'		=> $fid,			'ifopen'	=> $ifopen,
				'vieworder'	=> $vieworder,	'author'	=> $admin_name,
				'startdate'	=> $startdate,		'enddate'	=> $enddate,
				'url'		=> $url,			'subject'	=> $atc_title,
				'content'	=> $atc_content
		));
		
		updatecache_i();
		adminmsg('operate_success',$successurl);
	}
} elseif ($action == 'edit') {

	S::gp(array('aid'),'GP',2);
	$sql_select = $_POST['step']!=2 ? ',ifopen,vieworder,startdate,enddate,url,subject,content' : '';
	$rt = $db->get_one("SELECT aid,fid $sql_select FROM pw_announce WHERE aid=".S::sqlEscape($aid));
	!$rt['aid'] && adminmsg('operate_fail');

	list($fids,$forumcache,$cmscache) = GetForumdb();
	!Checkright($fids,$rt['fid']) && adminmsg('annouce_right');

	if ($_POST['step'] != 2) {

		extract($rt,EXTR_SKIP);
		ifcheck($ifopen,'ifopen');
		$subject = S::escapeChar($subject); $atc_content = S::escapeChar($content);

		Showoption($fid);
		$ckdisplay = Displayfid();
		$startdate && $startdate = get_date($startdate,'Y-m-d H:i'); $enddate && $enddate = get_date($enddate,'Y-m-d H:i');
		$vieworder = (int)$vieworder;
		include PrintEot('notice');exit;

	} else {

		$successurl = $basename;
		$basename .= "&action=edit&aid=$aid";
		$fid = (int)$_POST['fid'];
		!$fid && adminmsg('annouce_fid');
		!Checkright($fids,$fid) && adminmsg('annouce_right');
		$basename .= "&fid=$fid";

		$atc_title = trim(ieconvert($_POST['atc_title']));
		!$atc_title && adminmsg('annouce_title');

		$atc_content = trim(ieconvert($_POST['atc_content']));
		$url = trim(S::escapeChar(str_replace(array('"',"'",'\\'),'',$_POST['url'])));
		!$atc_content && !$url && adminmsg('annouce_content');

		$startdate = $_POST['startdate'] ? PwStrtoTime($_POST['startdate']) : $timestamp;
		$enddate = $_POST['enddate'] ? PwStrtoTime($_POST['enddate']) : '';
		$enddate && $enddate<=$startdate && adminmsg('annouce_time');
//		!Datecheck($fid,$startdate,$enddate,$aid) && adminmsg('annouce_date');
		S::gp(array('ifopen','vieworder'),'P',2);
		/**
		$db->update("UPDATE pw_announce"
			. " SET " . S::sqlSingle(array(
					'fid'		=> $fid,			'ifopen'	=> $ifopen,
					'vieworder'	=> $vieworder,	'startdate'	=> $startdate,
					'enddate'	=> $enddate,		'url'		=> $url,
					'subject'	=> $atc_title,		'content'	=> $atc_content
					))
			. " WHERE aid=".S::sqlEscape($aid));
		**/
		pwQuery::update('pw_announce','aid=:aid', array($aid), array(
					'fid'		=> $fid,			'ifopen'	=> $ifopen,
					'vieworder'	=> $vieworder,	'startdate'	=> $startdate,
					'enddate'	=> $enddate,		'url'		=> $url,
					'subject'	=> $atc_title,		'content'	=> $atc_content
					));	
		updatecache_i();
		adminmsg('operate_success',$successurl);
	}
} elseif ($action == 'del') {

	$aid = (int)$_GET['aid'];
	$rt = $db->get_one("SELECT aid,fid FROM pw_announce WHERE aid=".S::sqlEscape($aid));
	!$rt['aid'] && adminmsg('operate_fail');
	list($fids) = GetForumdb();
	!Checkright($fids,$rt['fid']) && adminmsg('annouce_right');
	$db->update("DELETE FROM pw_announce WHERE aid=".S::sqlEscape($aid));
	updatecache_i();
	adminmsg('operate_success');

} else {

	//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	pwCache::getData(D_P.'data/bbscache/forum_cache.php');
	$titledb = $namedb = array();
	list($fids,$forumcache,$cmscache) = GetForumdb();
	$pages = '';
	$sqlwhere = 'WHERE 1';

	$namedb = $forum;
	$namedb[-1]['name'] = getLangInfo('all','whole_notice');
	$namedb[-2]['name'] = getLangInfo('all','cms_notice');
	S::gp(array('fid','page','ifopen'),'GP',2);
	$page<1 && $page = 1;
	if ($fid && Checkright($fids,$fid)) {
		$sqlwhere .= " AND fid=".S::sqlEscape($fid);
		switch ($fid) {
			case -1:
				$titledb[-1] = " (<a href=\"$basename\"><b>".getLangInfo('all','all_notice')."</b></a> &raquo; ".getLangInfo('all','whole_notice').")";
				break;
			case -2:
				$titledb[-2] = " (<a href=\"$basename\"><b>".getLangInfo('all','all_notice')."</b></a> &raquo; ".getLangInfo('all','cms_notice').")";
				break;
			default:
				$titledb[$fid] = " (<a href=\"$basename\"><b>".getLangInfo('all','all_notice')."</b></a> &raquo; {$forum[$fid][name]})";
		}
	} else {
		if ($fids) {
			switch ($admin_gid) {
				case 5:
					$sqlwhere .= " AND fid IN ($fids)"; break;
				default:
					$sqlwhere .= " AND fid NOT IN ($fids)";
			}
		}
	}
	unset($forum);

	if (isset($_POST['ifopen']) && $_POST['ifopen'] >= 0) {
		$sqlwhere .= ' AND ifopen=';
		${'ifopen_'.$ifopen} = 'SELECTED';
		switch ($ifopen) {
			case 3:
				$sqlwhere .= "1 AND startdate>".S::sqlEscape($timestamp); break;//未发布
			case 2:
				$sqlwhere .= "1 AND enddate>0 AND enddate<".S::sqlEscape($timestamp); break;//已过期
			case 1:
				$sqlwhere .= "1 AND startdate<=".S::sqlEscape($timestamp)."AND (enddate=0 OR enddate>=".S::sqlEscape($timestamp).")"; break;//已发布
			default:
				$sqlwhere .= '0';//已关闭
		}
	}

	Showoption($fid);
	$annoucedb = array();
	$query = $db->query("SELECT aid,fid,ifopen,vieworder,author,subject,startdate,enddate FROM pw_announce $sqlwhere ORDER BY fid,vieworder,startdate DESC".S::sqlLimit(($page-1)*$db_perpage,$db_perpage));
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs(strip_tags($rt['subject']),30);
		$rt['starttime'] = $rt['startdate'] ? get_date($rt['startdate'],'Y-m-d H:i') : '--';
		$rt['endtime'] = $rt['enddate'] ? get_date($rt['enddate'],'Y-m-d H:i') : '--';
		$annoucedb[$rt['fid']][] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_announce $sqlwhere");
	if ($count > $db_perpage) {
		$addpage = $fid ? "fid=$fid&" : '';
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&$addpage");
	}
	include PrintEot('notice');exit;
}
function GetForumdb() {
	global $admin_gid,$admin_name;
	if ($admin_gid == 5) {
		list($fids,$forumcache) = GetAllowForum($admin_name);
		$cmscache = '';
	} else {
		//* include pwCache::getPath(D_P.'data/bbscache/forumcache.php');
		extract(pwCache::getData(D_P.'data/bbscache/forumcache.php', false));
		$forumcache = preg_replace('/<option value="\d+">&gt;&gt; (.+?)<\/option>/is', '</optgroup><optgroup label="\\1">', $forumcache);
		$forumcache = '<optgroup>' . $forumcache . '</optgroup>';
		list($fids,$hideforum) = GetHiddenForum();
		if ($admin_gid == 3) {
			$fids = '';
			$forumcache .= $hideforum;
		}
		unset($hideforum);
		$cmscache = trim($cmscache);
	}
	return array($fids,$forumcache,$cmscache);
}
function Checkright($fids,$fid) {
	global $admin_gid;
	if ($fids) {
		$strpos = strpos(",$fids,","$fid");
		if ($admin_gid==5 && $strpos===false) {
			return false;
		} elseif ($admin_gid!=5 && $strpos!==false) {
			return false;
		}
	}
	return true;
}
function Displayfid() {
	//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	extract(pwCache::getData(D_P.'data/bbscache/forum_cache.php', false));
	$ckdisplay = ',-1,';
	foreach ($forum as $value) {
		if ($value['type'] == 'category') {
			$ckdisplay .= "$value[fid],";
		}
	}
	return $ckdisplay;
}
function Showoption($fid) {
	global $admin_gid,$forumcache,$cmscache;
	$admin_gid!=5 && $forumcache = "<option value=\"-1\">".getLangInfo('all','whole_notice')."</option>$forumcache";
	if ($admin_gid==3 && $cmscache) {
		$forumcache .= "<option></option><option value=\"-2\">".getLangInfo('all','cms_notice')."</option>$cmscache";
	}
	$fid && $forumcache = str_replace("\"$fid\"","\"$fid\" SELECTED",$forumcache);
}
function Datecheck($fid,$startdate,$enddate=null,$aid=null){
	global $db;
	!empty($enddate) && $startdate = $enddate;
	$sql_where = empty($aid) ? '' : "AND aid!=".S::sqlEscape($aid);
	$rt = $db->get_one("SELECT startdate,enddate FROM pw_announce WHERE fid=".S::sqlEscape($fid)."$sql_where ORDER BY vieworder,startdate DESC LIMIT 1");
	if ($rt['startdate']) {
		$rt['enddate'] && $rt['startdate'] = $rt['enddate'];
		if ($startdate <= $rt['startdate']) {
			return false;
		}
	}
	return true;
}
?>