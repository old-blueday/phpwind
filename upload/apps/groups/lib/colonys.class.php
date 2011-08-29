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
		return $this->_db->get_one("SELECT * FROM pw_colonys WHERE id=" . S::sqlEscape($id));
	}

	function getColonyByName($name) {
		return $this->_db->get_one("SELECT * FROM pw_colonys WHERE cname=" . S::sqlEscape($name));
	}

	function getColonysInForum($fid) {
		global $forumset;
		$array = array();
		$query = $this->_db->query("SELECT id,cname,members,todaypost,styleid,cnimg,ifshowpic FROM pw_colonys WHERE classid = ".S::sqlEscape($fid) . " AND ifshow=1 ORDER BY vieworder ASC");
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
			$limit = S::sqlLimit($start, $nums);
		}
		$array = array();
		$query = $this->_db->query("SELECT * FROM pw_colonys WHERE 1" . $sql . $limit);
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function delColonyById($id) {
		//* $this->_db->update("DELETE FROM pw_colonys WHERE id=" . S::sqlEscape($id));
		pwQuery::delete('pw_colonys', 'id=:id', array($id));
	}

	function getSingleMember($id, $uid) {
		return $this->_db->get_one("SELECT * FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($id) . ' AND uid=' . S::sqlEscape($uid));
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
			WHERE p.fid=" . S::sqlEscape($fromcid) . " AND p.pid=0 AND a.cyid=" . S::sqlEscape($cyid)
		);
		$_sql_Where = ($fromcid > 0) ? ' AND t.fid>0' : " AND t.ifcheck='2'";
		/*$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid 
			SET t.fid=" . S::sqlEscape($tocid) . ",t.ifcheck=" . S::sqlEscape($ifcheck) . 
			" WHERE a.cyid=" . S::sqlEscape($cyid) . $_sql_Where
		);*/
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table1 a LEFT JOIN :pw_table2 t ON a.tid=t.tid SET t.fid=:fid,t.ifcheck=:ifcheck WHERE a.cyid=:cyid {$_sql_Where}", array('pw_argument', 'pw_threads', $tocid, $ifcheck, $cyid)));
		$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_posts p ON a.tid=p.tid SET p.fid=" . S::sqlEscape($tocid) . " WHERE a.cyid=" . S::sqlEscape($cyid));
		if ($db_plist && count($db_plist) > 1) {
			foreach ($db_plist as $key => $value) {
				if ($key == 0) continue;
				$pw_posts = GetPtable($key);
				$this->_db->update("UPDATE pw_argument a LEFT JOIN $pw_posts p ON a.tid=p.tid SET p.fid=" . S::sqlEscape($tocid) . " WHERE a.cyid=" . S::sqlEscape($cyid));
			}
		}
		require_once(R_P . 'require/updateforum.php');
		if ($tocid > 0) {
			$this->_db->update("UPDATE pw_cnclass SET cnsum=cnsum+1 WHERE fid=" . S::sqlEscape($tocid));
			updateforum($tocid);
		}
		if ($fromcid > 0) {
			$this->_db->update("UPDATE pw_cnclass SET cnsum=cnsum-1 WHERE fid=" . S::sqlEscape($fromcid) . " AND cnsum>0");
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
		//$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid SET t.fid=" . S::sqlEscape($cid) . ",t.ifcheck=" . S::sqlEscape($ifcheck) . " WHERE a.cyid=" . S::sqlEscape($cyid) . " AND t.ifcheck>0");
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table1 a LEFT JOIN :pw_table2 t ON a.tid=t.tid SET t.fid=:fid,t.ifcheck=:ifcheck WHERE a.cyid=:cyid AND t.ifcheck>:ifcheck", array('pw_argument', 'pw_threads', $cid, $ifcheck, $cyid, 0)));
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
		$this->_db->update("UPDATE pw_cnclass SET cnsum=cnsum-1 WHERE fid=" . S::sqlEscape($cydb[$fromid]['classid']) . " AND cnsum>0");
		$this->_db->update("UPDATE pw_cnstyles SET csum=csum-1 WHERE id=" . S::sqlEscape($cydb[$fromid]['styleid']) . " AND csum>0");
		
		$_sql_update = '';
		foreach ($tmp as $key => $value) {
			$_sql_update .= ",$key=$key+" . S::sqlEscape($value);
		}
		$_sql_update = ltrim($_sql_update, ',');
		//* $this->_db->update("UPDATE pw_colonys SET " . $_sql_update . ' WHERE id=' . S::sqlEscape($toid));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET {$_sql_update} WHERE id=:id", array('pw_colonys',$toid)));

		return true;
	}

	function _mergeMembers(&$tmp, $toid, $fromid) {
		$this->_db->update("UPDATE pw_cmembers a LEFT JOIN pw_cmembers b ON a.uid=b.uid AND b.colonyid=" . S::sqlEscape($toid) . ' SET a.colonyid=' . S::sqlEscape($toid) . ' WHERE a.colonyid=' . S::sqlEscape($fromid) . ' AND b.id IS NULL');
		$affect = $this->_db->affected_rows();
		$this->_db->update("DELETE FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($fromid));
		$tmp['members'] = $affect;
	}

	function _mergeThreads(&$tmp, $toid, $fromid, $cydb) {
		if ($cydb[$toid]['classid'] == $cydb[$fromid]['classid']) {
			//$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads b ON a.tid=b.tid SET a.cyid=" . S::sqlEscape($toid) . " WHERE a.cyid=" . S::sqlEscape($fromid));
			$this->_db->update(pwQuery::buildClause("UPDATE :pw_table1 a LEFT JOIN :pw_table2 b ON a.tid=b.tid SET a.cyid=:cyidx WHERE a.cyid=:cyid", array('pw_argument', 'pw_threads', $toid, $fromid)));
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
				$this->_db->update("UPDATE pw_argument a LEFT JOIN $val b ON a.tid=b.tid SET b.fid=" . S::sqlEscape($cydb[$toid]['classid']) . " WHERE a.cyid=" . S::sqlEscape($fromid));
				$pnum += $this->_db->affected_rows();
			}

			$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_attachs b ON a.tid=b.tid SET b.fid=" . S::sqlEscape($cydb[$toid]['classid']) . " WHERE a.cyid=" . S::sqlEscape($fromid));

			//$this->_db->update("UPDATE pw_argument a LEFT JOIN pw_threads b ON a.tid=b.tid SET a.cyid=" . S::sqlEscape($toid) . ",b.fid=" . S::sqlEscape($cydb[$toid]['classid']) . " WHERE a.cyid=" . S::sqlEscape($fromid));
			$this->_db->update(pwQuery::buildClause("UPDATE :pw_table1 a LEFT JOIN :pw_table2 b ON a.tid=b.tid SET a.cyid=:cyidx, b.fid=:bid WHERE a.cyid=:cyid", array('pw_argument', 'pw_threads', $toid, $cydb[$toid]['classid'], $fromid)));
			$tnum = $this->_db->affected_rows();

			$tmp['tnum'] = $tnum;
			$tmp['pnum'] = $pnum + $tnum;
			
			require_once(R_P . 'require/updateforum.php');
			updateForumCount($cydb[$toid]['classid'], $tnum, $pnum);
			updateForumCount($cydb[$fromid]['classid'], -$tnum, -$pnum);
		}
	}

	function _mergeAlbums(&$tmp, $toid, $fromid, $cydb) {
		$this->_db->update("UPDATE pw_cnalbum SET ownerid=" . S::sqlEscape($toid) . ",owner=" . S::sqlEscape($cydb[$toid]['cname']) . " WHERE atype='1' AND ownerid=" . S::sqlEscape($fromid));
		$tmp['albumnum'] = $this->_db->affected_rows();
		$tmp['photonum'] = $cydb[$fromid]['photonum'];
	}

	function _mergeActives(&$tmp, $toid, $fromid) {
		$this->_db->update("UPDATE pw_active SET cid=" . S::sqlEscape($toid) . ' WHERE cid=' . S::sqlEscape($fromid));
		$tmp['activitynum'] = $this->_db->affected_rows();
	}

	function _mergeWrites(&$tmp, $toid, $fromid) {
		$this->_db->update("UPDATE pw_cwritedata SET cyid=" . S::sqlEscape($toid) . ' WHERE cyid=' . S::sqlEscape($fromid));
		$tmp['writenum'] = $this->_db->affected_rows();
	}

	function _sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . S::sqlImplode($ids) . ')' : '=' . S::sqlEscape($ids);
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
					$_sql_where .= " AND $_sql_field=" . S::sqlEscape($value);break;
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
		//* isset($o_groups_upgrade) || include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
		isset($o_groups_upgrade) || extract(pwCache::getData(D_P . 'data/bbscache/o_config.php', false));
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