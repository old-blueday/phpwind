<?php
define('SCR','faq');
if (isset($_POST['ajax'])) {
	define('AJAX', '1');
}
require_once('global.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/help_cache.php');
pwCache::getData(D_P.'data/bbscache/help_cache.php');

$pages = $db_unioninfo = '';
$catedb = $helpNav = $hasChildArray = $firstLevel = $secondLevel = array();
S::gp(array('hid'));
$hid = (int) $hid;
if (!$hid) {
	list($db_unioninfo) = explode("\t",readover(D_P.'data/bbscache/info.txt'));
	strpos($db_unioninfo,'<pwhd>') === false && $db_unioninfo = '';
} elseif ($hid && $_HELP[$hid]['fathers']) {
	$fathers = strpos($_HELP[$hid]['fathers'], ',') !== false ? explode(',', $_HELP[$hid]['fathers']) : array($_HELP[$hid]['fathers']);
	foreach ($fathers as $value) {
		$helpNav[] = '<a href="faq.php?hid=' . $value . '">' . $_HELP[$value]['title'] . '</a>';
	}
	$helpNav[] = $_HELP[$hid]['title'];
}
foreach ($_HELP as $key => $value) {
	$tempArray = array();
	!$value['url'] && ($_HELP[$key]['url'] = $value['url'] = "faq.php?hid=$key"); 
	$value['lv'] == 0 && $firstLevel[$key] = $value;
	$value['lv'] == 1 && $secondLevel[$value['hup']][] = $value;
	!$value['fathers'] && $value['fathers'] = 0;
	$tempArray = strpos($value['fathers'], ',') !== false ? explode(',', $value['fathers']) : array($value['fathers']);
	$lastPosition = count($tempArray) - 1;
	!S::inArray($key, $hasChildArray[$tempArray[$lastPosition]]) && $hasChildArray[$tempArray[$lastPosition]][] = $key;
}
if (S::getGP('action') != 'dosch') {
	if ($hid) {
		$queryContentArray = $lowerContent = array();
		$result = $db->get_one('SELECT * FROM pw_help WHERE hid = ' . S::sqlEscape($hid));
		$result['content'] && $result['content'] = nl2br($result['content']);
		if ($result['ifchild']) {
			foreach ($hasChildArray[$hid] as $value) {
				!$_HELP[$value]['ifchild'] && $queryContentArray[] = $value;
			}
			if ($queryContentArray) {
				$query = $db->query('SELECT * FROM pw_help WHERE hid IN (' . S::sqlImplode($queryContentArray) . ')');
				while ($rs = $db->fetch_array($query)) {
					$rs['content'] = nl2br($rs['content']);
					$lowerContent[$rs['hid']] = $rs;
				}
			}
		}
	}
} else {
	@set_time_limit(0);
	S::gp(array('page','sid'),'GP',2);
	S::gp(array('keyword','method','area'));
	if ($keyword && strlen(trim($keyword)) < 2) {
		echo '关键字长度不足2个字符，请重新输入'."\terror";
		ajax_footer();
	}
	$keyword_A = array();
	$schedid = '';
	$orderby  = 'ORDER BY hid DESC';
	if ($sid > 0) {
		@extract($db->get_one('SELECT total,schedid FROM pw_schcache WHERE sid='.S::sqlEscape($sid)));
		$total = (int) $total;
	} else {
		if (empty($keyword)) {
			echo '请输入搜索条件'."\terror";
			ajax_footer();
		}
		$area = 1;
		$method = 'OR';
		$schline = md5('faq|'.trim($keyword).'|'.trim($method).'|'.trim($area).'|faq');
		@extract($db->get_one('SELECT sid,total,schedid FROM pw_schcache WHERE schline='.S::sqlEscape($schline).' LIMIT 1'));
		if (!$schedid) {
			$db->update('DELETE FROM pw_schcache WHERE schtime<'.S::sqlEscape($timestamp-3600));
			if ($keyword && $_G['searchtime'] != 0) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$userInfo = $userService->get($winduid, false, false, true);
				if ($timestamp - $userInfo['lasttime'] < $_G['searchtime']) {
					echo '对不起'.$GLOBALS['_G']['searchtime'].'秒内只能进行一次搜索'."\terror";
					ajax_footer();
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
					$like = S::sqlEscape("%$value%");
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
			if (!$sqlwhere) {
				echo '关键字非法'."\terror";
				ajax_footer();
			}
			!$db_maxresult && $db_maxresult = 500;
			$limit = S::sqlLimit($db_maxresult);
			$total = 0;
			$query = $db->query("SELECT hid FROM pw_help WHERE $sqlwhere $orderby $limit");
			while ($rt = $db->fetch_array($query)) {
				$total++;
				$schedid .= ($schedid ? ',' : '').$rt['hid'];
			}
			$db->free_result($query);
			if ($schedid) {
				$pwSQL = S::sqlSingle(array(
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
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$keyword_A = explode('|',$keyword);
		$rawkeyword = rawurlencode($keyword);
		$sqlwhere = 'hid ';
		if (!is_numeric($schedid)) {
			$sqlwhere .= " IN ('".str_replace(',',"','",$schedid)."')";
		} else {
			$sqlwhere .= " = '$schedid'";
		}
		$returnString = '';
		$query = $db->query("SELECT hid,content FROM pw_help WHERE $sqlwhere $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['content']) {
				foreach ($keyword_A as $value) {
					$value && $rt['content'] = preg_replace('/(?<=[^\w=]|^)('.preg_quote($value,'/').')(?=[^\w=]|$)/si','<font color="red"><u>\\1</u></font>',$rt['content']);
				}
				$rt['title'] = $_HELP[$rt['hid']]['title'];
				$rt['ifchild'] = $_HELP[$rt['hid']]['ifchild'];
				$rt['hup'] = $rt['ifchild'] ? $rt['hid'] : $_HELP[$rt['hid']]['hup'];
				$returnString .= '<h5 class="h b mb10" onclick="whetherDisplay(' . $rt['hid'] . ')" style="cursor:pointer; background:url(images/faq/faq_down.gif) right 12px no-repeat;"><a href="faq.php?hid=' . $rt['hid'] . '">' . $rt['title'] . '</a></h5><div class="tpc_content" id="' . $rt['hid'] . '" style="border:1px dotted #ccc;background:#f7f7f7;">' . $rt['content'] . '</div>';
				//$catedb[$rt['hid']] = $rt;
			}
		}
		$db->free_result($query);
		if ($total > $db_perpage) {
			require_once(R_P.'require/forum.php');
			$numofpage = ceil($total/$db_perpage);
			$pages = numofpage($total,$page,$numofpage,"faq.php?action=dosch&sid=$sid&keyword=$rawkeyword&#faq0");
		}
		echo $returnString . $pages . "\tok";ajax_footer();
	} else {
		echo '没有查找匹配的内容'."\terror";
		ajax_footer();
	}
}
require_once(R_P.'require/header.php');
require_once PrintEot('faq');footer();
?>