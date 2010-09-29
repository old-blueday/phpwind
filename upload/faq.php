<?php
define('SCR','faq');
require_once('global.php');
@include_once(D_P.'data/bbscache/help_cache.php');
require_once(R_P.'require/header.php');

$nav = array();
$pages = $db_unioninfo = '';
$catedb = array();
$hid = (int)$_GET['hid'];
if (!$hid) {
	list($db_unioninfo) = explode("\t",readover(D_P.'data/bbscache/info.txt'));
	if (strpos($db_unioninfo,'<pwhd>')===false) {
		$db_unioninfo = '';
	}
}
if (GetGP('action')!='dosch') {
	$listdb = $fathers = array();
	$hids = array();
	$lv = 0;
	isset($_HELP[$hid]['lv']) && $lv = $_HELP[$hid]['lv']+1;
	if ($_HELP[$hid]['title']) {
		$_HELP[$hid]['fathers'] && $fathers = explode(',',$_HELP[$hid]['fathers']);
		foreach ($fathers as $key) {
			$nav[] = "<a href=\"faq.php?hid={$_HELP[$key][hid]}#faq{$_HELP[$key][hid]}\">{$_HELP[$key][title]}</a>";
		}
		$nav[] = $_HELP[$hid]['title'];
	}
	foreach ($_HELP as $key => $value) {
		if ($hid>0 && strpos(",$value[fathers],",",$hid,")===false) {
			continue;
		}
		if ($lv+2>$value['lv']) {
			$ckarray = 1;
			if ($lv < $value['lv']) {
				$ckarray = 0;
			}
			if ($ckarray) {
				$hids[] = $value['hid'];
				$value['ifchild'] && $value['hup'] = $value['hid'];
				$catedb[$key] = array('hid' => $value['hid'],'hup' => $value['hup'],'ifchild' => $value['ifchild'],'title' => $value['title']);
			} elseif ($value['url'] || $value['ifchild'] || $value['ifcontent']) {
				$value['target'] = ' target="_blank"';
				if (!$value['url']) {
					$value['target'] = '';
					$value['url'] = "faq.php?hid=$value[hup]#faq$value[hid]";
				}
				$listdb[$value['hup']][] = array('title' => $value['title'],'url' => $value['url'],'target' => $value['target']);
			}
		}
	}
	unset($_HELP);
	if ($hids) {
		$query = $db->query('SELECT hid,content FROM pw_help WHERE hid IN ('.pwImplode($hids).')');
		while ($rt = $db->fetch_array($query)) {
			if ($rt['content']) {
				$rt['content'] = nl2br($rt['content']);
				$catedb[$rt['hid']]['content'] = $rt['content'];
			}
		}
		$db->free_result($query);
	}
} else {
	@set_time_limit(0);
	$keyword_A = array();
	$schedid = '';
	InitGP(array('page','sid'),'GP',2);
	InitGP(array('keyword','method','area'));
	$keyword && strlen(trim($keyword)) < 3  && Showmsg('search_word_limit');
	$orderby  = 'ORDER BY hid DESC';
	if ($sid > 0) {
		@extract($db->get_one('SELECT total,schedid FROM pw_schcache WHERE sid='.pwEscape($sid)));
		$total = (int)$total;
	} else {
		$_POST && empty($keyword) && Showmsg('no_condition');
		$area = (int)$area;
		$method != 'AND' && $method = 'OR';
		$schline = md5('faq|'.trim($keyword).'|'.trim($method).'|'.trim($area).'|faq');
		@extract($db->get_one('SELECT sid,total,schedid FROM pw_schcache WHERE schline='.pwEscape($schline).' LIMIT 1'));
		if (!$schedid) {
			$db->update('DELETE FROM pw_schcache WHERE schtime<'.pwEscape($timestamp-3600));
			if ($keyword && $_G['searchtime'] != 0) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$userInfo = $userService->get($winduid, false, false, true);
				if ($timestamp - $userInfo['lasttime'] < $_G['searchtime']) {
					Showmsg('search_limit');
				}
				$userService->update($winduid, array(), array(), array('lasttime'=>$timestamp));
			}
			$keywhere	= $sqlwhere = '';
			$keyword	= str_replace(array('%','_'),array('\%','\_'),trim($keyword));
			$keyword_A  = explode('|',$keyword);
			foreach ($keyword_A as $value) {
				if ($value) {
					$value     = addslashes($value);
					$keywhere .= $method;
					$like = pwEscape("%$value%");
					if ($area == '1' && $_G['allowsearch'] == 2) {
						$keywhere .= " (title LIKE $like OR content LIKE $like)";
					} else {
						$keywhere .= " title LIKE $like";
					}
				}
			}
			if ($keywhere) {
				$keywhere = substr_replace($keywhere,'',0,3);
				$keywhere && $sqlwhere = $keywhere;
			}
			!$sqlwhere && Showmsg('illegal_keyword');
			!$db_maxresult && $db_maxresult = 500;
			$limit = pwLimit($db_maxresult);
			$total = 0;
			$query = $db->query("SELECT hid FROM pw_help WHERE $sqlwhere $orderby $limit");
			while ($rt = $db->fetch_array($query)) {
				$total++;
				$schedid .= ($schedid ? ',' : '').$rt['hid'];
			}
			$db->free_result($query);
			if ($schedid) {
				$pwSQL = pwSqlSingle(array(
					'schline'	=> $schline,
					'schtime'	=> $timestamp,
					'total'		=> $total,
					'schedid'	=> $schedid
				));
				$db->update("INSERT INTO pw_schcache SET $pwSQL");
				$sid = $db->insert_id();
			}
		}
	}
	if ($schedid) {
		$rawkeyword = '';
		$page<1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
		$keyword_A = explode('|',$keyword);
		$rawkeyword = rawurlencode($keyword);
		$sqlwhere = 'hid ';
		if (!is_numeric($schedid)) {
			$sqlwhere .= " IN ('".str_replace(',',"','",$schedid)."')";
		} else {
			$sqlwhere .= " = '$schedid'";
		}
		$query = $db->query("SELECT hid,content FROM pw_help WHERE $sqlwhere $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['content']) {
				foreach ($keyword_A as $value) {
					$value && $rt['content'] = preg_replace('/(?<=[^\w=]|^)('.preg_quote($value,'/').')(?=[^\w=]|$)/si','<font color="red"><u>\\1</u></font>',$rt['content']);
				}
				$rt['title'] = $_HELP[$rt['hid']]['title'];
				$rt['ifchild'] = $_HELP[$rt['hid']]['ifchild'];
				$rt['hup'] = $rt['ifchild'] ? $rt['hid'] : $_HELP[$rt['hid']]['hup'];
				$catedb[$rt['hid']] = $rt;
			}
		}
		$db->free_result($query);
		if ($total > $db_perpage) {
			require_once(R_P.'require/forum.php');
			$numofpage = ceil($total/$db_perpage);
			$pages = numofpage($total,$page,$numofpage,"faq.php?action=dosch&sid=$sid&keyword=$rawkeyword&#faq0");
		}
	} else {
		Showmsg('search_none');
	}
}
require_once PrintEot('faq');footer();
?>