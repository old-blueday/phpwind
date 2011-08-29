<?php
!defined('P_W') && exit('Forbidden');

/**
 * 论坛操作
 * 
 * @package Forum
 */
class PwForum {
	
	var $db;
	var $fid;
	var $name;
	var $foruminfo = array();
	var $forumset = array();
	var $creditset = array();
	
	function PwForum($fid) {
		$this->fid = $fid;
		$this->db = & $GLOBALS['db'];
		$this->foruminfo = $this->get($fid);
		$this->name = $this->foruminfo['name'];
		$this->forumset = $this->foruminfo['forumset'];
		$this->creditset = $this->foruminfo['creditset'];
	}

	function get($fid) {
		if (!$fid) {
			return array();
		}
		$info = L::forum($fid);
		if (!$info && ($info = $this->getFromDB($fid))) {
			$info['creditset'] = unserialize($info['creditset']);
			$info['forumset'] = unserialize($info['forumset']);
			$info['commend'] = unserialize($info['commend']);
		}
		return $info;
	}

	function getFromDB($fid) {
		return $this->db->get_one("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid WHERE f.fid=" . S::sqlEscape($fid));
	}
	
	function isForum($allowcate = false) {
		if (empty($this->foruminfo) || !$allowcate && $this->foruminfo['type'] == 'category') {
			return false;
		}
		return true;
	}
	
	function isBM($username) {
		if (!$username) {
			return false;
		}
		if ($this->foruminfo['forumadmin'] && strpos($this->foruminfo['forumadmin'], ",$username,") !== false) {
			return true;
		}
		if ($this->foruminfo['fupadmin'] && strpos($this->foruminfo['fupadmin'], ",$username,") !== false) {
			return true;
		}
		return false;
	}
	
	function forumcheck($user, $groupid) {
		if ($this->foruminfo['f_type'] == 'former' && $groupid == 'guest' && $_COOKIE) {
			Showmsg('forum_former');
		}
		$pwdcheck = GetCookie('pwdcheck');
		if ($this->foruminfo['password'] != '' && ($groupid == 'guest' || $pwdcheck[$this->fid] != $this->foruminfo['password'] && !S::inArray($user['username'], $GLOBALS['manager']))) {
			require_once (R_P . 'require/forumpassword.php');
		}
		if (!$this->allowvisit($user, $groupid)) {
			Showmsg('forum_jiami');
		}
		if (!$this->foruminfo['cms'] && $this->foruminfo['f_type'] == 'hidden' && !$this->foruminfo['allowvisit']) {
			Showmsg('forum_hidden');
		}
	}

	function setForumStyle() {
		if (!empty($this->foruminfo['style']) && file_exists(D_P . "data/style/{$this->foruminfo[style]}.php")) {
			$GLOBALS['skinco'] = $this->foruminfo['style'];
		}
	}

	/**
	 * 
	 * Enter description here ...
	 * @param int $userstatus
	 * @param boolean $or
	 */
	function authStatus($userstatus,$or = false) {
		if (!$GLOBALS['db_authstate']) return true;
		if (!$or) {
			//逻辑与
			if ($this->forumset['auth_cellphone'] && !getstatus($userstatus, PW_USERSTATUS_AUTHMOBILE)) {
				return 'forum_auth_cellphone';
			}
			if ($this->forumset['auth_alipay'] && !getstatus($userstatus, PW_USERSTATUS_AUTHALIPAY)) {
				return 'forum_auth_alipay';
			}
			if ($GLOBALS['db_authcertificate'] && $this->forumset['auth_certificate'] && !getstatus($userstatus, PW_USERSTATUS_AUTHCERTIFICATE)) {
				return 'forum_auth_certificate';
			}
			return true;
		} else {
			//逻辑或
			$messages = array();
			// auth mobile
			if ($this->forumset['auth_cellphone'] && getstatus($userstatus, PW_USERSTATUS_AUTHMOBILE)) {
				return true;
			} else {
				$this->forumset['auth_cellphone'] && array_push($messages, 'forum_auth_cellphone');
			}
			//auth alipay
			if ($this->forumset['auth_alipay'] && getstatus($userstatus, PW_USERSTATUS_AUTHALIPAY)) {
				return true;
			} else {
				$this->forumset['auth_alipay'] && array_push($messages, 'forum_auth_alipay');
			}
			//auth cetificate
			if ($GLOBALS['db_authcertificate'] && $this->forumset['auth_certificate'] && getstatus($userstatus, PW_USERSTATUS_AUTHCERTIFICATE)) {
				return true;
			} else {
				$GLOBALS['db_authcertificate'] && $this->forumset['auth_certificate'] && array_push($messages, 'forum_auth_certificate');
			}
			if (!$messages) return true;
			return current($messages);
		}
	}

	function authCredit($userstatus) {
		if (!$GLOBALS['db_authstate']) return 1;
		$array = array(1);
		if ($this->forumset['auth_cellphone_credit'] > 1 && getstatus($userstatus, PW_USERSTATUS_AUTHMOBILE)) {
			$array[] = intval($this->forumset['auth_cellphone_credit']);
		}
		if ($this->forumset['auth_alipay_credit'] > 1 && getstatus($userstatus, PW_USERSTATUS_AUTHALIPAY)) {
			$array[] = intval($this->forumset['auth_alipay_credit']);
		}
		if ($GLOBALS['db_authcertificate'] && $this->forumset['auth_certificate_credit'] > 1 && getstatus($userstatus, PW_USERSTATUS_AUTHCERTIFICATE)) {
			$array[] = intval($this->forumset['auth_certificate_credit']);
		}
		return max($array);
	}
	
	function creditcheck($user, $groupid) {
		if ($this->foruminfo['allowvisit']) {
			return;
		}
		$check = 1;
		$this->forumset['rvrcneed'] = intval($this->forumset['rvrcneed']);
		$this->forumset['moneyneed'] = intval($this->forumset['moneyneed']);
		$this->forumset['creditneed'] = intval($this->forumset['creditneed']);
		$this->forumset['postnumneed'] = intval($this->forumset['postnumneed']);
		
		if ($this->forumset['rvrcneed'] && intval($user['rvrc'] / 10) < $this->forumset['rvrcneed']) {
			$check = 0;
		} elseif ($this->forumset['moneyneed'] && $user['money'] < $this->forumset['moneyneed']) {
			$check = 0;
		} elseif ($this->forumset['creditneed'] && $user['credit'] < $this->forumset['creditneed']) {
			$check = 0;
		} elseif ($this->forumset['postnumneed'] && $user['postnum'] < $this->forumset['postnumneed']) {
			$check = 0;
		}
		if (!$check) {
			if ($groupid == 'guest') {
				Showmsg('forum_guestlimit');
			} else {
				$GLOBALS['forumset'] = $this->forumset;
				Showmsg('forum_creditlimit');
			}
		}
	}

	function sellcheck($uid) {
		if (!$this->foruminfo['forumsell']) {
			return;
		}
		$rt = $this->db->get_one("SELECT MAX(overdate) AS u FROM pw_forumsell WHERE uid=" . S::sqlEscape($uid) . ' AND fid=' . S::sqlEscape($this->fid));
		if ($rt['u'] < $GLOBALS['timestamp']) {
			Showmsg('forum_sell');
		}
	}
	
	function allowvisit($user, $groupid) {
		return $this->allowcheck($this->foruminfo['allowvisit'], $groupid, $user['groups'], $user['visit']);
	}
	
	function allowpost($user, $groupid) {
		return $this->allowcheck($this->foruminfo['allowpost'], $groupid, $user['groups'], $user['post']);
	}
	
	function allowreply($user, $groupid) {
		return $this->allowcheck($this->foruminfo['allowrp'], $groupid, $user['groups'], $user['reply']);
	}
	
	function allowupload($user, $groupid) {
		return $this->allowcheck($this->foruminfo['allowupload'], $groupid, $user['groups']);
	}

	function allowdownload($user, $groupid) {
		return $this->allowcheck($this->foruminfo['allowdownload'], $groupid, $user['groups']);
	}
	
	function allowtime($hours = null) {
		global $timestamp, $db_timedf;
		!$hours && $hours = gmdate('G', $timestamp + $db_timedf * 3600);
		return $this->allowcheck($this->forumset['allowtime'], $hours, '');
	}
	
	function allowcheck($allowgroup, $groupid, $groups, $allowforum = '') {
		if (empty($allowgroup) || strpos($allowgroup, ",$groupid,") !== false) {
			return true;
		}
		if ($groups) {
			foreach (explode(',', trim($groups, ',')) as $value) {
				if (strpos($allowgroup, ",$value,") !== false) {
					return true;
				}
			}
		}
		if ($allowforum && strpos(",$allowforum,", ",$this->fid,") !== false) {
			return true;
		}
		return false;
	}
	
	function getUpForum() {
		global $forum, $fpage;
		//* isset($forum) || include pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
		isset($forum) || extract(pwCache::getData(D_P . 'data/bbscache/forum_cache.php', false));
		$upforum = array();
		$upforum[] = array(
			$this->stripHtml($this->foruminfo['name']),
			"thread.php?fid={$this->fid}" . ($fpage > 1 ? "&page=$fpage" : '')
		);
		$fup = $this->foruminfo['fup'];
		while ($fup > 0 && isset($forum[$fup]) && $forum[$fup]['type'] != 'category') {
			$upforum[] = array(
				$this->stripHtml($forum[$fup]['name']),
				"thread.php?fid=$fup"
			);
			$fup = $forum[$fup]['fup'];
		}
		return array_reverse($upforum);
	}

	function stripHtml($name) {
		($str = strip_tags($name)) || $str = $name;
		return $str;
	}
	
	function getTitle() {
		$upforum = $this->getUpForum();
		$headguide = array();
		foreach ($upforum as $key => $value) {
			if ($value[0]) {
				$value[1] && $value[0] = "<a href=\"$value[1]\">$value[0]</a>";
				$headguide[] = $value[0];
			}
		}
		$guidename = implode('<em>&gt;</em>', $headguide);
		krsort($headguide);
		return array(
			$guidename,
			strip_tags(implode('|', $headguide)) . ' - '
		);
	}
	
	function headguide($guidename, $onmouseover = true) {
		global $db_menu, $db_bbsname, $db_bfn, $imgpath, $db_menu, $db_mode, $db_bbsurl;
		if ($db_mode == 'bbs' && $db_bfn == 'index.php') {
			$db_bfn_temp = $db_bbsurl . "/index.php?m=bbs";
		} else {
			$db_bfn_temp = $db_bfn;
		}
		if ($db_menu && $onmouseover) {
			$headguide = "<img id=\"td_cate\" src=\"$imgpath/" . L::style('stylepath') . "/thread/home.gif\" title=\"快速跳转至其他版块\" onClick=\"return pwForumList(false,false,null,this);\" class=\"cp breadHome\" /><em class=\"breadEm\"></em><a href=\"$db_bfn_temp\" title=\"$db_bbsname\">$db_bbsname</a>";
		} else {
			$headguide = "<a href=\"$db_bfn\" title=\"$db_bbsname\">$db_bbsname</a>";
		}
		if (!is_array($guidename)) {
			return $headguide . '<em>&gt;</em>' . $guidename;
		}
		foreach ($guidename as $key => $value) {
			if ($value[1]) {
				$headguide .= '<em>&gt;</em><a href="' . $value[1] . '">' . $value[0] . '</a>';
			} else {
				$headguide .= '<em>&gt;</em>' . $value[0];
			}
		}
		return $headguide;
	}
	
	function isOpen() {
		return (!$this->foruminfo['allowvisit'] && $this->foruminfo['f_type'] != 'hidden' && !$this->foruminfo['password'] && !$this->foruminfo['forumsell']);
	}
	
	function forumBan($udb) {
		$retu = $uids = array();
		if (isset($udb['groupid']) && isset($udb['userstatus'])) {
			if ($udb['groupid'] == 6) {
				$retu[$udb['uid']] = 1;
			} elseif (getstatus($udb['userstatus'], PW_USERSTATUS_BANUSER) && ($rt = $this->db->get_one("SELECT uid FROM pw_banuser WHERE uid=" . S::sqlEscape($udb['uid']) . " AND fid=" . S::sqlEscape($this->fid)))) {
				$retu[$udb['uid']] = 2;
			}
		} else {
			foreach ($udb as $key => $u) {
				if ($u['groupid'] == 6) { //是否全局禁言
					$retu[$u['uid']] = 1;
				} elseif (getstatus($u['userstatus'], PW_USERSTATUS_BANUSER)) { //是否版块禁言
					$uids[] = $u['uid'];
				}
			}
			if ($uids) {
				$uids = S::sqlImplode($uids);
				$query = $this->db->query("SELECT uid FROM pw_banuser WHERE uid IN ($uids) AND fid=" . S::sqlEscape($this->fid));
				while ($rt = $this->db->fetch_array($query)) {
					$retu[$rt['uid']] = 2;
				}
			}
		}
		return $retu;
	}
	
	function lastinfo($type, $action = '+', $lastpost = array()) {
		global $db_readdir, $R_url;
		$lp = $topicadd = $fupadd = '';
		$_arrTopicAdd = $_arrFupAdd = $_arrLp = array();
		$_num = intval($action.'1');
		if ($action == '+' || $action == '-') {
			if ($type == 'topic') {
				$topicadd = "tpost=tpost{$action}'1',article=article{$action}'1',topic=topic{$action}'1' ";
				$fupadd = "tpost=tpost{$action}'1',article=article{$action}'1',subtopic=subtopic{$action}'1' ";
				$_arrTopicAdd = array('tpost'=>$_num, 'article'=>$_num, 'topic'=>$_num);
				$_arrFupAdd = array('tpost'=>$_num, 'article'=>$_num, 'subtopic'=>$_num);
			} else {
				$topicadd = "tpost=tpost{$action}'1',article=article{$action}'1' ";
				$fupadd = "tpost=tpost{$action}'1',article=article{$action}'1' ";
				$_arrTopicAdd = $_arrFupAdd = array('tpost'=>$_num, 'article'=>$_num);
			}
		}
		if ($lastpost) {
			$newurl = "read.php?tid=$lastpost[tid]&page=e#a";
			if ($this->foruminfo['allowhtm']) {
				$htmurl = $db_readdir . '/' . $this->fid . '/' . date('ym', $lastpost['t_date']) . '/' . $lastpost['tid'] . '.html';
				if (file_exists(R_P . $htmurl)) {
					$newurl = "$R_url/$htmurl";
				}
			}
			$lp = "lastpost=" . S::sqlEscape($lastpost['subject'] . "\t" . $lastpost['author'] . "\t" . $lastpost['lastpost'] . "\t" . $newurl);
			$_arrLp = array('lastpost'=> $lastpost['subject'] . "\t" . $lastpost['author'] . "\t" . $lastpost['lastpost'] . "\t" . $newurl );
		}
		if ($topicadd || $lp) {
			$sql = trim($topicadd . ',' . $lp, ',');
			$this->db->update("UPDATE pw_forumdata SET $sql WHERE fid=" . S::sqlEscape($this->fid));
			Perf::gatherInfo('changeForumDataWithForumId', array(array_merge($_arrTopicAdd,$_arrLp, array('fid'=>$this->fid))));
			//*$this->db->update(pwQuery::updateClause("UPDATE :pw_table SET $sql WHERE fid=:fid", array('pw_forumdata',$this->fid)));
		}
		if ($this->foruminfo['type'] == 'sub' || $this->foruminfo['type'] == 'sub2') {
			!$this->isOpen() && $lp = '';
			if ($lp || $fupadd) {
				$sql = trim($fupadd . ',' . $lp, ',');
				$this->db->update("UPDATE pw_forumdata SET $sql WHERE fid=" . S::sqlEscape($this->foruminfo['fup']));
				Perf::gatherInfo('changeForumDataWithForumId', array(array_merge($_arrFupAdd,$_arrLp, array('fid'=>$this->foruminfo['fup']))));
				if ($this->foruminfo['type'] == 'sub2') {
					$rt1 = $this->db->get_one("SELECT fup FROM pw_forums WHERE fid=" . S::sqlEscape($this->foruminfo['fup']));
					$this->db->update("UPDATE pw_forumdata SET $sql WHERE fid=" . S::sqlEscape($rt1['fup']));
					Perf::gatherInfo('changeForumDataWithForumId', array(array_merge($_arrFupAdd,$_arrLp, array('fid'=>$rt1['fup']))));
				}
			}
		}
	}

	/**
	 * 获取系统在帖子列表对帖子的管理权限
	 * @author zhudong
	 * @return array $rights 权限数组 admincheck：管理员或版主 
	 */
	function getSystemRight(){
		global $windid,$groupid,$isGM;
		$isBM = $admincheck = $ajaxcheck = $managecheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 0;
		$isBM = $this->isBM($windid);
		$admincheck = ($isGM || $isBM) ? 1 : 0;
		if (!$isGM) {
			$pwSystem = pwRights($isBM);
			if ($pwSystem && ($pwSystem['tpccheck'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'] || $pwSystem['delatc'] || $pwSystem['moveatc'] || $pwSystem['copyatc'] || $pwSystem['topped'] || $pwSystem['unite'] || $pwSystem['tpctype'])) {//system rights
				$managecheck = 1;
			}
			if (($groupid == 3 || $isBM) && $pwSystem['deltpcs']) {
				$ajaxcheck = 1;
			}
			$pwPostHide = $pwSystem['posthide'];
			$pwSellHide = $pwSystem['sellhide'];
			$pwEncodeHide = $pwSystem['encodehide'];
			$pwAnonyHide = $pwSystem['anonyhide'];
		} else {
			$managecheck = $ajaxcheck = $pwAnonyHide = $pwPostHide = $pwSellHide = $pwEncodeHide = 1;
		}
		return array($isBM,$admincheck,$ajaxcheck,$managecheck,$pwAnonyHide,$pwPostHide,$pwSellHide,$pwEncodeHide,$pwSystem);
	}
}
?>