<?php
defined('P_W') || exit('Forbidden');

function wind_forumcheck($forum) {
	global $windid,$groupid,$tid,$fid,$skin,$winddb,$manager;

	if ($forum['f_type']=='former' && $groupid=='guest' && $_COOKIE) {
		Showmsg('forum_former');
	}
	if (!empty($forum['style']) && file_exists(D_P."data/style/$forum[style].php")) {
		$skin = $forum['style'];
	}
	$pwdcheck = GetCookie('pwdcheck');
	if ($forum['password'] != '' && ($groupid=='guest' || $pwdcheck[$fid] != $forum['password'] && !S::inArray($windid,$manager))) {
		require_once(R_P.'require/forumpw.php');
	}
	if ($forum['allowvisit'] && !allowcheck($forum['allowvisit'],$groupid,$winddb['groups'],$fid,$winddb['visit'])){
		Showmsg('forum_jiami');
	}
	if (!$forum['cms'] && $forum['f_type']=='hidden' && !$forum['allowvisit']) {
		Showmsg('forum_hidden');
	}
}
function forum_creditcheck() {
	global $db,$winddb,$userrvrc,$forumset,$groupid;

	$forumset['rvrcneed']    = (int) $forumset['rvrcneed'];
	$forumset['moneyneed']   = (int) $forumset['moneyneed'];
	$forumset['creditneed']  = (int) $forumset['creditneed'];
	$forumset['postnumneed'] = (int) $forumset['postnumneed'];
	$check = 1;
	if ($forumset['rvrcneed'] && $userrvrc < $forumset['rvrcneed']) {
		$check = 0;
	} elseif ($forumset['moneyneed'] && $winddb['money'] < $forumset['moneyneed']) {
		$check = 0;
	} elseif ($forumset['creditneed'] && $winddb['credit'] < $forumset['creditneed']) {
		$check = 0;
	} elseif ($forumset['postnumneed'] && $winddb['postnum'] < $forumset['postnumneed']) {
		$check = 0;
	}
	if (!$check) {
		if ($groupid == 'guest') {
			Showmsg('forum_guestlimit');
		} else {
			Showmsg('forum_creditlimit');
		}
	}
}
function forum_sell($fid) {
	global $db,$winduid,$timestamp;
	$rt = $db->get_one("SELECT MAX(overdate) AS u FROM pw_forumsell WHERE uid=".S::sqlEscape($winduid).' AND fid='.S::sqlEscape($fid));
	if ($rt['u'] < $timestamp) {
		Showmsg('forum_sell');
	}
}
function forumindex($fup,$type=0) {
	global $forum,$fid,$cateid,$fpage,$viewbbs;
	$secondurl="thread.php?fid=$fid".($fpage>1 ? "&page=$fpage" : '').$viewbbs;
	$guidename = array();
	$fup_array = $type ? array(strip_tags($forum[$fid]['name']),$secondurl):array(strip_tags($forum[$fid]['name']));
	if ($forum[$fup]['type'] == 'category') {
		$cateid = $forum[$fup]['fid'];
		$guidename = array(
			$cateid => $fup_array
		);
	} elseif ($forum[$fup]['type'] == 'forum') {
		$cateid = $forum[$fup]['fup'];
		$guidename = array(
			$cateid => array(strip_tags($forum[$fup]['name']),"thread.php?fid=".$forum[$fup]['fid'].$viewbbs),
			$fup	=> $fup_array
		);
	} elseif ($forum[$fup]['type'] == 'sub') {
		$fup1 = $forum[$fup]['fup'];
		$cateid = $forum[$fup1]['fup'];
		$guidename = array(
			$cateid => array(strip_tags($forum[$fup1]['name']),"thread.php?fid=".$forum[$fup1]['fid'].$viewbbs),
			$fup1	=> array(strip_tags($forum[$fup]['name']),"thread.php?fid=".$forum[$fup]['fid'].$viewbbs),
			$fup	=> $fup_array
		);
	}
	return $guidename;
}

function simpleGuide($fid,$fpage=null){
	global $forum,$db_bbsurl,$db_bbsname,$cateid;
	static $guidename = array();
	$guidename[] = array('name'=>strip_tags($forum[$fid]['name']),'url'=>"thread.php?fid=$fid");
	if ($forum[$fid]['fup'] != 0) {
		return simpleGuide($forum[$fid]['fup'],$fpage);
	}
	$cateid = $fid;
	$guidename[] = array('name'=>$db_bbsname,'url'=>$db_bbsurl);
	$fpage && $guidename[0]['url'] .= "&page=$fpage";
	return array_reverse($guidename);
}

function headguide($guidename,$onmouseover=true) {
	global $db_menu,$db_bbsname,$db_bfn,$cateid,$fid,$imgpath,$stylepath,$db_menu,$db_mode,$db_bbsurl,$defaultMode;

	if ($db_mode == 'bbs' && $db_bfn == 'index.php') {
		$db_bfn_temp = $defaultMode != 'bbs' ? $db_bbsurl."/index.php?m=bbs" : $db_bbsurl."/index.php";
	} else {
		$db_bfn_temp = $db_bfn;
	}
	if ($db_menu && $onmouseover) {
		$headguide = "<img id=\"td_cate\" align=\"absmiddle\" src=\"$imgpath/$stylepath/thread/home.gif\" title=\"快速跳转至其他版块\" onClick=\"return pwForumList(false,false,null,this);\" class=\"cp breadHome\" /><em class=\"breadEm\"></em><a href=\"$db_bfn_temp\" title=\"$db_bbsname\">$db_bbsname</a>" ;
	} else{
		$headguide = "<a href=\"$db_bfn_temp\" title=\"$db_bbsname\">$db_bbsname</a>" ;
	}

	if (!is_array($guidename)) {
		return $headguide.'<em>&gt;</em>'.$guidename;
	}
	$i = 1;
	$count = count($guidename);
	foreach ($guidename as $key => $value) {
		if ($value[1]) {
			if ($i>=$count) {
				$headguide .= '<em>&gt;</em><a href="'.$value[1].'">'.$value[0].'</a>';
			} else {
				$headguide .= '<em>&gt;</em><a href="'.$value[1].'">'.$value[0].'</a>';
			}
		} else {
			$headguide .= '<em>&gt;</em>'.$value[0];
		}
		$i++;
	}
	return $headguide;
}

function getstyles($skin) {
	global $db_styledb;
	$styles = '';
	foreach ($db_styledb as $key => $value) {
		$cname = $db_styledb[$key][0] ? $db_styledb[$key][0] : $key;
		if($skin && $key == $skin){
			$styles .= "<option value=\"$key\" selected>$cname</option>";
		} else{
			$styles .= "<option value=\"$key\">$cname</option>";
		}
	}
	return $styles;
}
/*
function forumlist() {
	global $forum,$db_menu;
	if ($db_menu && $type=='thread') return;
	$chtml = '';
	if (is_array($forum)) {
		$listdb = array();
		foreach ($forum as $value) {
			if ($value['cms'] || $value['f_type']=='hidden') continue;
			if ($value['type']=='category') {
				$listdb[$value['fid']] = array();
			} elseif ($forum[$value['fup']]['type']=='category' && !S::inArray($value['fid'],$listdb[$value['fup']])) {
				$listdb[$value['fup']][] = $value['fid'];
			}
		}
		foreach ($listdb as $key => $value) {
			if (!empty($value)) {
				$chtml .= '<ul class="ul3"><h2><a href="index.php?cateid='.$key.'">'.$forum[$key]['name'].'</a></h2>';
				$count = 0;
				foreach ($value as $v) {
					$count++;
					$chtml .= '<li><a href="thread.php?fid='.$v.'">'.$forum[$v]['name'].'</a></li>';
				}
				$count%2!=0 && $chtml .= '<li>&nbsp;</li>';
				$chtml .= '</ul>';
			}
		}
	}
	$chtml && $chtml = '<div id="menu_cate" class="menu" style="display:none;"><div style="padding-bottom:8px;overflow-Y:auto;">'.$chtml.'</div></div>';
	return $chtml;
}
*/
function updatecommend($fid,$forumset) {
	global $db,$timestamp;
	$forumset['commendnum']<1 && $forumset['commendnum'] = 10;
	$commend = array();
	$commendlist = '';
	if ($forumset['commendlist']) {
		$commendlist = S::sqlImplode(explode(',',$forumset['commendlist']));
		$query = $db->query("SELECT tid,authorid,author,subject FROM pw_threads WHERE tid IN($commendlist) AND fid=".S::sqlEscape($fid));
		while ($rt = $db->fetch_array($query)) {
			if ($forumset['commendlength'] && strlen($rt['subject'])>$forumset['commendlength']) {
				$rt['subject'] = substrs($rt['subject'],$forumset['commendlength']);
			}
			$commend[] = $rt;
		}
	}
	$count = count($commend);
	if ($forumset['autocommend'] && $count < $forumset['commendnum']) {
		$limit = S::sqlLimit($forumset['commendnum'] - $count);
		switch ($forumset['autocommend']) {
			case '1' : $orderby = 'postdate';break;
			case '2' : $orderby = 'lastpost';break;
			case '3' : $orderby = 'hits';break;
			case '4' : $orderby = 'replies';break;
			default  : $orderby = 'digest';break;
		}
		$sql   = $forumset['commendlist'] ? " AND tid NOT IN($commendlist)" : '';
		$query = $db->query("SELECT tid,authorid,author,subject FROM pw_threads WHERE fid=".S::sqlEscape($fid)." AND specialsort='0' $sql ORDER BY $orderby DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($forumset['commendlength'] && strlen($rt['subject'])>$forumset['commendlength']) {
				$rt['subject'] = substrs($rt['subject'],$forumset['commendlength']);
			}
			$commend[] = $rt;
		}
	}
	$forumset['ifcommend'] = $timestamp;
	$forumsetdb = (serialize($forumset));
	$commend = $commend ? (serialize($commend)) : '';
	$db->update("UPDATE pw_forumsextra"
		. " SET ".S::sqlSingle(array(
				'forumset'	=> $forumsetdb,
				'commend'	=> $commend
			))
		. ' WHERE fid='.S::sqlEscape($fid));
	require_once (R_P.'admin/cache.php');
	updatecache_forums($fid);
}
function getforumtitle($guidename,$type=0) {
	$headguide = array();
	$i = 1;
	foreach ($guidename as $value) {
		if ($value[0]) {
			if ($value[1]) {
				if ($type==0 && $i>=count($guidename)) {
					$headguide[] = "<a href=\"$value[1]\">$value[0]</a>";
				} else {
					$headguide[] = "<a href=\"$value[1]\">$value[0]</a>";
				}

			} else {
				if ($type==0 && $i>=count($guidename)) {
					$headguide[] = "$value[0]";
				} else {
					$headguide[] = $value[0];
				}
			}
		}
		$i++;
	}
	$guidename = implode('<em>&gt;</em>',$headguide);
	krsort($headguide);
	return array($guidename,strip_tags(implode('|',$headguide)).' - ');
}
function isban($udb,$fid = null) {
	global $db;
	$retu = $uids = array();
	if (isset($udb['groupid']) && isset($udb['userstatus'])) {
		if ($udb['groupid'] == 6) {
			$retu[$udb['uid']] = 1;
		} elseif ($fid && getstatus($udb['userstatus'], PW_USERSTATUS_BANUSER) && ($rt = $db->get_one("SELECT uid FROM pw_banuser WHERE uid=".S::sqlEscape($udb['uid'])." AND fid=".S::sqlEscape($fid)))) {
			$retu[$udb['uid']] = 2;
		}
	} else {
		foreach ($udb as $key => $u) {
			if ($u['groupid'] == 6) {//是否全局禁言
				$retu[$u['uid']] = 1;
			} elseif (getstatus($u['userstatus'], PW_USERSTATUS_BANUSER)) {//是否版块禁言
				$uids[] = $u['uid'];
			}
		}
		if ($fid && $uids) {
			$uids = S::sqlImplode($uids);
			$query = $db->query("SELECT uid FROM pw_banuser WHERE uid IN ($uids) AND fid=".S::sqlEscape($fid));
			while ($rt = $db->fetch_array($query)) {
				$retu[$rt['uid']] = 2;
			}
		}
	}
	return $retu;
}

?>