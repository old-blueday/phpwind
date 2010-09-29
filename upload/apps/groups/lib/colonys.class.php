<?php
!defined('P_W') && exit('Forbidden');

/**
 * 群组公共服务
 * @author chenjm / sky_hold@163.com
 * @package colony
 */

class PW_Colony {
	
	var $_db;

	function PW_Colony() {
		global $db;
		$this->_db =& $db;
	}

	function getColonyById($id) {
		return $this->_db->get_one("SELECT * FROM pw_colonys WHERE id=" . pwEscape($id));
	}

	function getColonyByName($name) {
		return $this->_db->get_one("SELECT * FROM pw_colonys WHERE cname=" . pwEscape($name));
	}

	function getColonysInForum($fid) {
		global $forumset;
		$array = array();
		$query = $this->_db->query("SELECT id,cname,members,todaypost,styleid,cnimg,ifshowpic FROM pw_colonys WHERE classid = ".pwEscape($fid) . " AND ifshow=1 ORDER BY vieworder ASC");
		while ($rt = $this->_db->fetch_array($query)) {
			if ($rt['ifshowpic']) {
				$rt['cnimg'] = $this->getColonyImg($rt['cnimg']);
			}
			if ($forumset['ifcolonycate']) {
				$array[$rt['styleid']][] = $rt;
			} else {
				$array[] = $rt;
			}
		}
		return $array;
	}

	function getColonyList($where, $nums = null, $start = 0) {
		$sql = $this->_sqlCompound($where);
		$limit = '';
		if ($nums) {
			$limit = pwLimit($start, $nums);
		}
		$array = array();
		$query = $this->_db->query("SELECT * FROM pw_colonys WHERE 1" . $sql . $limit);
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function delColonyById($id) {
		$this->_db->update("DELETE FROM pw_colonys WHERE id=" . pwEscape($id));
	}

	function getSingleMember($id, $uid) {
		return $this->_db->get_one("SELECT * FROM pw_cmembers WHERE colonyid=" . pwEscape($id) . ' AND uid=' . pwEscape($uid));
	}

	/**
	 * 改变帖子归属版块
	 * @param int $cyid 群组id
	 * @param int $ifTopicShowInForum 帖子是否显示在版块中
	 * @param int $tocid 目标群组
	 * @param int $fromcid 来源群组
	 * void
	 */
	function changeTopicToForum($cyid, $ifTopicShowInForum, $tocid, $fromcid) {
		global $db_plist;
		$tocid = intval($tocid);
		$ifcheck = ($tocid > 0 && $ifTopicShowInForum) ? 1 : 2;
		$this->_db->update("REPLACE INTO pw_poststopped 
			SELECT $tocid,p.tid,p.pid,p.floor,p.uptime,p.overtime 
			FROM pw_poststopped p 
			LEFT JOIN pw_argument a ON p.tid=a.tid 
			WHERE p.fid=" . pwEscape($fromcid) . " AND p.pid=0 AND a.cyid=" . pwEscape($cyid)
		);
		$_sql_Where = ($fromcid > 0) ? ' AND t.fid>0' : " AND t.ifcheck='2'";
		$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid 
			SET t.fid=" . pwEscape($tocid) . ",t.ifcheck=" . pwEscape($ifcheck) . 
			" WHERE a.cyid=" . pwEscape($cyid) . $_sql_Where
		);
		$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_posts p ON a.tid=p.tid SET p.fid=" . pwEscape($tocid) . " WHERE a.cyid=" . pwEscape($cyid));
		if ($db_plist && count($db_plist) > 1) {
			foreach ($db_plist as $key => $value) {
				if ($key == 0) continue;
				$pw_posts = GetPtable($key);
				$this->_db->update("UPDATE pw_argument a LEFT JOIN $pw_posts p ON a.tid=p.tid SET p.fid=" . pwEscape($tocid) . " WHERE a.cyid=" . pwEscape($cyid));
			}
		}
		require_once(R_P . 'require/updateforum.php');
		if ($tocid > 0) {
			$this->_db->update("UPDATE pw_cnclass SET cnsum=cnsum+1 WHERE fid=" . pwEscape($tocid));
			updateforum($tocid);
		}
		if ($fromcid > 0) {
			$this->_db->update("UPDATE pw_cnclass SET cnsum=cnsum-1 WHERE fid=" . pwEscape($fromcid) . " AND cnsum>0");
			updateforum($fromcid);
		}
		updatetop();
	}
	/**
	 * 改变帖子是否显示在版块中
	 * @param int $cyid 群组id
	 * @param int $ifTopicShowInForum 帖子是否显示在版块中
	 * @param int $cid 关联版块
	 * void
	 */
	function changeTopicShowInForum($cyid, $ifTopicShowInForum, $cid) {
		$ifcheck = ($cid > 0 && $ifTopicShowInForum) ? 1 : 2;
		$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid SET t.fid=" . pwEscape($cid) . ",t.ifcheck=" . pwEscape($ifcheck) . " WHERE a.cyid=" . pwEscape($cyid) . " AND t.ifcheck>0");
		require_once(R_P . 'require/updateforum.php');
		updateforum($cid);
	}

	/**
	 * 合并群组
	 * @param int $toid 目标群组
	 * @param int $fromid 来源群组
	 * return bool
	 */
	function mergeColony($toid, $fromid) {
		$cydb = $this->getColonyList(array('id' => array($toid, $fromid)));
		if (!isset($cydb[$toid]) || !isset($cydb[$fromid])) {
			return false;
		}
		$tmp = array();
		$this->_mergeMembers($tmp, $toid, $fromid);
		$this->_mergeThreads($tmp, $toid, $fromid, $cydb);
		$this->_mergeAlbums($tmp, $toid, $fromid, $cydb);
		$this->_mergeActives($tmp, $toid, $fromid);
		$this->_mergeWrites($tmp, $toid, $fromid);

		$this->delColonyById($fromid);
		$this->_db->update("UPDATE pw_cnclass SET cnsum=cnsum-1 WHERE fid=" . pwEscape($cydb[$fromid]['classid']) . " AND cnsum>0");
		$this->_db->update("UPDATE pw_cnstyles SET csum=csum-1 WHERE id=" . pwEscape($cydb[$fromid]['styleid']) . " AND csum>0");
		
		$_sql_update = '';
		foreach ($tmp as $key => $value) {
			$_sql_update .= ",$key=$key+" . pwEscape($value);
		}
		$_sql_update = ltrim($_sql_update, ',');
		$this->_db->update("UPDATE pw_colonys SET " . $_sql_update . ' WHERE id=' . pwEscape($toid));

		return true;
	}

	function _mergeMembers(&$tmp, $toid, $fromid) {
		$this->_db->update("UPDATE pw_cmembers a LEFT JOIN pw_cmembers b ON a.uid=b.uid AND b.colonyid=" . pwEscape($toid) . ' SET a.colonyid=' . pwEscape($toid) . ' WHERE a.colonyid=' . pwEscape($fromid) . ' AND b.id IS NULL');
		$affect = $this->_db->affected_rows();
		$this->_db->update("DELETE FROM pw_cmembers WHERE colonyid=" . pwEscape($fromid));
		$tmp['members'] = $affect;
	}

	function _mergeThreads(&$tmp, $toid, $fromid, $cydb) {
		if ($cydb[$toid]['classid'] == $cydb[$fromid]['classid']) {
			$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads b ON a.tid=b.tid SET a.cyid=" . pwEscape($toid) . " WHERE a.cyid=" . pwEscape($fromid));
			$tmp['tnum'] = $this->_db->affected_rows();
			$tmp['pnum'] = $cydb[$fromid]['pnum'];
		} else {
			global $db_plist;
			$ptable_a = array('pw_posts');
			if ($db_plist) {
				foreach ($db_plist as $key => $val) {
					$key > 0 && $ptable_a[] = 'pw_posts'.$key;
				}
			}
			$pnum = 0;
			foreach ($ptable_a as $val) {
				$this->_db->update("UPDATE pw_argument a LEFT JOIN $val b ON a.tid=b.tid SET b.fid=" . pwEscape($cydb[$toid]['classid']) . " WHERE a.cyid=" . pwEscape($fromid));
				$pnum += $this->_db->affected_rows();
			}

			$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_attachs b ON a.tid=b.tid SET b.fid=" . pwEscape($cydb[$toid]['classid']) . " WHERE a.cyid=" . pwEscape($fromid));

			$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads b ON a.tid=b.tid SET a.cyid=" . pwEscape($toid) . ",b.fid=" . pwEscape($cydb[$toid]['classid']) . " WHERE a.cyid=" . pwEscape($fromid));
			$tnum = $this->_db->affected_rows();

			$tmp['tnum'] = $tnum;
			$tmp['pnum'] = $pnum + $tnum;
			
			require_once(R_P . 'require/updateforum.php');
			updateForumCount($cydb[$toid]['classid'], $tnum, $pnum);
			updateForumCount($cydb[$fromid]['classid'], -$tnum, -$pnum);
		}
	}

	function _mergeAlbums(&$tmp, $toid, $fromid, $cydb) {
		$this->_db->update("UPDATE pw_cnalbum SET ownerid=" . pwEscape($toid) . ",owner=" . pwEscape($cydb[$toid]['cname']) . " WHERE atype='1' AND ownerid=" . pwEscape($fromid));
		$tmp['albumnum'] = $this->_db->affected_rows();
		$tmp['photonum'] = $cydb[$fromid]['photonum'];
	}

	function _mergeActives(&$tmp, $toid, $fromid) {
		$this->_db->update("UPDATE pw_active SET cid=" . pwEscape($toid) . ' WHERE cid=' . pwEscape($fromid));
		$tmp['activitynum'] = $this->_db->affected_rows();
	}

	function _mergeWrites(&$tmp, $toid, $fromid) {
		$this->_db->update("UPDATE pw_cwritedata SET cyid=" . pwEscape($toid) . ' WHERE cyid=' . pwEscape($fromid));
		$tmp['writenum'] = $this->_db->affected_rows();
	}

	function _sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . pwImplode($ids) . ')' : '=' . pwEscape($ids);
	}

	function _sqlCompound($where) {
		if (!$where || !is_array($where)) {
			return;
		}
		$_sql_where = '';
		foreach ($where as $_sql_field => $value) {
			switch ($_sql_field) {
				case 'id':
					$_sql_where .= " AND $_sql_field" . $this->_sqlIn($value);break;
				case 'admin':
					$_sql_where .= " AND $_sql_field=" . pwEscape($value);break;
			}
		}
		return $_sql_where;
	}

	function getColonyImg($cnimg){
		global $imgpath;
		if (strstr($cnimg,'http://')) {
			$cnimg_url = $cnimg;
		} else {
			if ($cnimg) {
				list($cnimg_url,$imgtype) = geturl("cn_img/$cnimg",'lf');
			} else {
				$cnimg_url = $imgpath.'/g/groupnopic.gif';
			}
		}
		return $cnimg_url;
	}

	/**
	 * 随机取若干个群组
	 * @param int $num 随机个数
	 * return array 随机群组数组
	 */

	function getRandColonys($num) {
		$query = $this->_db->query("SELECT id,cname,cnimg,descrip FROM pw_colonys ORDER BY RAND() LIMIT ".intval($num));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['cname'] = substrs($rt['cname'],16);
			$rt['descrip'] = substrs(stripWindCode($rt['descrip']),45);
			$rt['cnimg'] = $this->getColonyImg($rt['cnimg']);
			$colonys[] = $rt;
		}
		return $colonys;
	}


	/**
	 * 根据群组的综合积分取排行
	 * @param int $num 排行个数
	 * return array 排行群组数组
	 */

	function getRankByColonyCredit($num) {
		isset($o_groups_upgrade) || include_once(D_P . 'data/bbscache/o_config.php');
		$tnum = $o_groups_upgrade['tnum'] ? $o_groups_upgrade['tnum'] : 0;
		$pnum = $o_groups_upgrade['pnum'] ? $o_groups_upgrade['pnum'] : 0;
		$members = $o_groups_upgrade['members'] ? $o_groups_upgrade['members'] : 0;
		$albumnum = $o_groups_upgrade['albumnum'] ? $o_groups_upgrade['albumnum'] : 0;
		$photonum = $o_groups_upgrade['photonum'] ? $o_groups_upgrade['photonum'] : 0;
		$writenum = $o_groups_upgrade['writenum'] ? $o_groups_upgrade['writenum'] : 0;
		$activitynum = $o_groups_upgrade['activitynum'] ? $o_groups_upgrade['activitynum'] : 0;
		$query = $this->_db->query("SELECT id,cname,(tnum*$tnum+pnum*$pnum+members*$members+albumnum*$albumnum+photonum*$photonum+writenum*$writenum+activitynum*$activitynum) AS credit FROM pw_colonys ORDER BY credit DESC LIMIT ".intval($num));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['cname'] = substrs($rt['cname'],16);
			$colonys[] = $rt;
		}
		return $colonys;
	}

	/**
	 * 群组总个数
	 * return int 群组总个数
	 */

	function getColonyNum() {
		$num = $this->_db->get_value("SELECT COUNT(*) FROM pw_colonys");
		return $num;
	}


	/**
	 * 最新加入的群组成员
	 * @param int $num 个数
	 * return array 成员数组
	 */

	function getNewMembers($num) {
		require_once(R_P.'require/showimg.php');
		$query = $this->_db->query("SELECT cm.id,cm.uid,cm.username,m.icon,c.id as cyid,c.cname FROM pw_cmembers cm LEFT JOIN pw_members m ON cm.uid=m.uid LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.ifadmin!='-1' ORDER BY cm.addtime DESC LIMIT ".intval($num));
		while ($rt = $this->_db->fetch_array($query)) {
			list($rt['faceurl']) = showfacedesign($rt['icon'], 1, 'm');
			$members[] = $rt;
		}
		return $members;
	}


	/**
	 * 热门话题
	 * @param int $num 话题个数
	 * return array 话题数组
	 */

	function getHotTopics($num) {
		//待wuqiong缓存库
	}

	/**
	 * 最新话题
	 * @param int $num 话题个数
	 * return array 话题数组
	 */

	function getNewTopics($num) {
		
		//待wuqiong缓存库
	}

}
?>