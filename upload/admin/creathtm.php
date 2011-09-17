<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=creathtm&type=$type";

$sqladd = "WHERE type<>'category' AND allowvisit='' AND f_type!='hidden' AND cms='0'";
if (!$action) {

	//* @include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	$num = 0;
	$forumcheck = "<ul class=\"list_A list_120\">";

	$select = '';
	$query	= $db->query("SELECT fid,name,allowhtm FROM pw_forums $sqladd");
	while ($rt = $db->fetch_array($query)) {
		$num++;
		$htm_tr = $num % 5 == 0 ? '' : '';
		$checked = $rt['allowhtm'] ? 'checked' : $checked='';
		$forumcheck .= "<li><input type='checkbox' name='selid[]' value='$rt[fid]' $checked>$rt[name]</li>$htm_tr";
		$rt['allowhtm'] && $select .= "<option value=\"$rt[fid]\">$rt[name]</option>";
	}
	$forumcheck.="</ul>";
	include PrintEot('creathtm');exit;

} elseif ($_POST['action'] == 'submit') {

	S::gp(array('selid'),'P');
	$_tmpSelid = $selid;
	$selid = checkselid($selid);
	if ($selid === false) {
		$basename = "javascript:history.go(-1);";
		adminmsg('operate_error');
	} elseif ($selid == '') {
		//* $db->update("UPDATE pw_forums SET allowhtm='0' $sqladd");
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET allowhtm='0' $sqladd", array('pw_forums')));
	} elseif ($selid) {
		//* $db->update("UPDATE pw_forums SET allowhtm='1' $sqladd AND fid IN($selid)");
		//* $db->update("UPDATE pw_forums SET allowhtm='0' $sqladd AND fid NOT IN($selid)");
		
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET allowhtm='1' $sqladd AND fid IN(:fid)", array('pw_forums', $_tmpSelid)));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET allowhtm='0' $sqladd AND fid NOT IN(:fid)", array('pw_forums', $_tmpSelid)));
	}
	updatecache_f();
	adminmsg('operate_success');

} elseif ($action == 'creat') {

	@set_time_limit(0);
	$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
	S::gp(array('creatfid','percount','step','tfid','forumnum'));

	$fids = $tid = $fieldadd = $tableadd = $tids = '';
	!is_array($creatfid) && $creatfid = explode(',',$creatfid);
	if (in_array('all', $creatfid)) {
		$query = $db->query("SELECT fid FROM pw_forums $sqladd AND allowhtm='1'");
		while ($rt = $db->fetch_array($query)) {
			$fids .= ($fids ? ',' : '') . $rt['fid'];
		}
		$creatfid = explode(',',$fids);
	} else {
		$fids = implode(',',$creatfid);
	}
	!$fids && adminmsg('template_noforum');

	!$tfid && $tfid = 0;
	$thisfid = (int)$creatfid[$tfid];

	$imgpath	= $db_http	!= 'N' ? $db_http : $db_picpath;
	$attachpath	= $db_attachurl	!= 'N' ? $db_attachurl : $db_attachname;
	$staticPage = L::loadClass('StaticPage');

	if (!$staticPage->initForum($thisfid)) {
		Showmsg('data_error');
	}
	(!is_numeric($forumnum) || $forumnum < 0) && $forumnum = 0;
	!$step && $step = 1;
	!$percount && $percount = 100;
	$start = ($step-1) * $percount;
	$next  = $start + $percount;
	$step++;
	$j_url = "$basename&action=$action&percount=$percount&creatfid=$fids&forumnum=$forumnum";
	$goon  = 0;

	$query = $db->query("SELECT tid FROM pw_threads WHERE fid='$thisfid' AND ifcheck=1 AND special='0' ORDER BY specialsort DESC,lastpost DESC" . S::sqlLimit($start, $percount));
	while ($topic = $db->fetch_array($query)) {
		$goon = 1;
		$staticPage->update($topic['tid']);
	}
	if ($forumnum && $next >= $forumnum) {
		$goon = 0;
	}
	if ($goon) {
		$j_url .= "&step=$step&tfid=$tfid";
		adminmsg('updatecache_step',EncodeUrl($j_url));
	} else {
		$tfid++;
		if (isset($creatfid[$tfid])) {
			$j_url .= "&step=1&tfid=$tfid";
			adminmsg('updatecache_step1',EncodeUrl($j_url));
		}
		adminmsg('operate_success');
	}
} elseif ($_POST['action'] == 'delete') {

	//* @include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	pwCache::getData(D_P.'data/bbscache/forum_cache.php');
	S::gp(array('creatfid'),'P');
	if (in_array('all',$creatfid)) {
		$handle = opendir(R_P.$db_readdir.'/');
		while ($file = readdir($handle)) {
			if (($file != ".") && ($file != "..") && ($file != "")) {
				if (is_dir(R_P.$db_readdir.'/'.$file)){
					//cms
					if (!$forum[$file]['cms']) {
						deldir(R_P.$db_readdir.'/'.$file);
					}
					//cms
				}
			}
		}
	} elseif ($creatfid) {
		foreach ($creatfid as $key => $value) {
			if (is_numeric($value)) {
				deldir(R_P.$db_readdir.'/'.$value);
			}
		}
	} else {
		adminmsg('forumid_error');
	}
	adminmsg('operate_success');
}
/*
 * 函数名和 common.php 里面冲突了
function pwAdvert($ckey,$fid=0,$lou=-1,$scr=0) {
	global $timestamp,$db_advertdb,$_time;
	if (empty($db_advertdb[$ckey])) return false;
	$hours = $_time['hours'] + 1;
	$fid || $fid = $GLOBALS['fid'];
	$scr || $scr = 'read';
	$lou = (int)$lou;
	$tmpAdvert = $db_advertdb[$ckey];
	if ($db_advertdb['config'][$ckey] == 'rand') {
		shuffle($tmpAdvert);
	}
	$arrAdvert = array();$advert = '';
	foreach ($tmpAdvert as $key=>$value) {
            if ($value['stime'] > $timestamp ||
                $value['etime'] < $timestamp ||
                ($value['dtime'] && strpos(",{$value['dtime']},",",{$hours},")===false) ||
		($value['mode'] && strpos($value['mode'],'bbs')===false) ||
		($value['page'] && strpos($value['page'],$scr)===false) ||
		($value['fid'] && strpos(",{$value['fid']},",",$fid,")===false) ||
		($value['lou'] && strpos(",{$value['lou']},",",$lou,")===false)
            ) {
		continue;
            }
            if ((!$value['ddate'] && !$value['dweek']) ||
                ($value['ddate'] && strpos(",{$value['ddate']},",",{$_time['day']},")!==false) ||
                ($value['dweek'] && strpos(",{$value['dweek']},",",{$_time['week']},")!==false)) {
                $arrAdvert[] = $value['code'];
                $advert .= is_array($value['code']) ? $value['code']['code'] : $value['code'];
                if ($db_advertdb['config'][$ckey] != 'all') break;
            }
	}
	return array($advert,$arrAdvert);
}
*/
function pwNavBar() {
	global $db_mainnav,$db_mode;
	$tmpNav = array();

	if (empty($db_mainnav)) $db_mainnav = array();
	foreach ($db_mainnav as $key => $value) {
		if ($value['pos'] == '-1' || strpos(",{$value['pos']},",','.($db_mode?$db_mode:'bbs').',') !== false) {
			$tmpNav['main']['html'] .= 'KEYbbs' == $key ? "<li class=\"current\">{$value['html']}</li>" : "<li>{$value['html']}</li>";
		}
	}
	return array($tmpNav);
}
?>