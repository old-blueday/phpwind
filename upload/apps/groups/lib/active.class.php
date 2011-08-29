<?php
!defined('P_W') && exit('Forbidden');

/**
 * 活动服务
 * @author chenjm / sky_hold@163.com
 * @package active
 */

class PW_Active {
	
	var $_db;

	function PW_Active() {
		global $db;
		$this->_db =& $db;
	}
	
	/**
	 * 获取活动信息
	 * return array
	 */
	function getActiveById($id) {
		static $array = array();
		if (!isset($array[$id])) {
			$array[$id] = $this->_db->get_one("SELECT * FROM pw_active WHERE id=" . S::sqlEscape($id));
		}
		return $array[$id];
	}
	
	/**
	 * 获取活动详细信息
	 * return array
	 */
	function getActiveInfoById($id) {
		return $this->_db->get_one("SELECT a.*,m.username,m.icon FROM pw_active a LEFT JOIN pw_members m ON a.uid=m.uid WHERE a.id=" . S::sqlEscape($id));
	}
	
	/**
	 * 获取活动详情附件列表
	 * return array
	 */
	function getAttById($id) {
		$array = array();
		$query = $this->_db->query("SELECT * FROM pw_actattachs WHERE actid=" . S::sqlEscape($id) . ' ORDER BY aid');
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['aid']] = $rt;
		}
		return $array;
	}
	
	/**
	 * 获取某群组活动的统计数
	 * return int
	 */
	function getActiveCount($cid) {
		return (int)$this->_db->get_value("SELECT COUNT(*) AS sum FROM pw_active WHERE cid=" . S::sqlEscape($cid));
	}
	
	/**
	 * 获取活动列表
	 * @param $cid int 群组id
	 * @param $nums int 调用数量
	 * @param $start int 
	 * return array
	 */
	function getActiveList($cid, $nums, $start = 0) {
		$array = array();
		$query = $this->_db->query("SELECT * FROM pw_active WHERE cid=" . S::sqlEscape($cid) . ' ORDER by id DESC ' . S::sqlLimit($start, $nums));
		while ($rt = $this->_db->fetch_array($query)) {
			$array[] = $this->convert($rt);
		}
		return $array;
	}
	
	/**
	 * 按条件搜索活动列表
	 * return array
	 */
	function searchList($where, $nums = null, $start = 0, $orderway = null, $ordertype = null, $count = false) {
		$sql = $this->sqlCompound($where);
		$order = $limit = '';$total = 0;
		if ($count) {
			$total = (int)$this->_db->get_value("SELECT COUNT(*) AS sum FROM pw_active WHERE 1" . $sql);
		}
		if ($nums) {
			$limit = S::sqlLimit($start, $nums);
		}
		if ($orderway) {
			!in_array($orderway, array('members','id')) && $orderway = 'id';
			$ordertype != 'ASC' && $ordertype = 'DESC';
			$order = " ORDER BY $orderway $ordertype";
		}
		$array = array();
		$query = $this->_db->query("SELECT * FROM pw_active WHERE 1" . $sql . $order . $limit);
		while ($rt = $this->_db->fetch_array($query)) {
			$array[] = $rt;
		}
		return array($array, $total);
	}
	
	/**
	 * 获取某群组的相关活动
	 * return array
	 */
	function getRelateActive($id, $nums) {
		$actids = $array = array();
		$query = $this->_db->query("SELECT distinct(a.actid) FROM pw_actmembers a LEFT JOIN pw_actmembers b ON a.uid=b.uid WHERE a.actid!=" . S::sqlEscape($id)." ORDER BY a.actid DESC " . S::sqlLimit(0, $nums));
		while ($rt = $this->_db->fetch_array($query)) {
			$actids[] = $rt['actid'];
		}
		$this->_db->free_result($query);
		if (!$actids) return $array;
		$query = $this->_db->query("SELECT id,title,cid,address,begintime,endtime,members,hits FROM pw_active WHERE id IN(".S::sqlImplode($actids).") ORDER BY id DESC");
		while ($rt = $this->_db->fetch_array($query)) {
			$array[] = $rt;
		}
		return $array;
	}

	function sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . S::sqlImplode($ids) . ')' : '=' . S::sqlEscape($ids);
	}

	function sqlCompound($where) {
		global $timestamp;
		if (!$where || !is_array($where)) {
			return;
		}
		$_sql_where = '';
		foreach ($where as $_sql_field => $value) {
			switch ($_sql_field) {
				case 'id':
				case 'cid':
				case 'uid':
					$_sql_where .= " AND $_sql_field" . $this->sqlIn($value);break;
				case 'type':
					$_sql_where .= " AND type=" . S::sqlEscape($value);break;
				case 'createtime_s':
					$_sql_where .= " AND createtime>=" . S::sqlEscape($value);break;
				case 'createtime_e':
					$_sql_where .= " AND createtime<=" . S::sqlEscape($value);break;
				case 'title':
					$_sql_where .= " AND title LIKE " . S::sqlEscape('%' . $value . '%');break;
				case 'activestate':
					if ($value == 5) {
						$_sql_where .= " AND endtime<" . S::sqlEscape($timestamp);
					} elseif($value == 4) {
						$_sql_where .= " AND deadline<" . S::sqlEscape($timestamp) . " AND begintime<" . S::sqlEscape($timestamp) . " AND endtime>" . S::sqlEscape($timestamp);
					} elseif($value == 3) {
						$_sql_where .= " AND deadline<" . S::sqlEscape($timestamp) . " AND begintime>" . S::sqlEscape($timestamp);
					} elseif($value == 2) {
						$_sql_where .= " AND limitnum!=0 AND members>=limitnum AND deadline>" . S::sqlEscape($timestamp);
					} elseif($value == 1) {
						$_sql_where .= " AND deadline>" . S::sqlEscape($timestamp) . ' AND (limitnum=0 OR members<limitnum)';
					}
					break;
			}
		}
		return $_sql_where;
	}

	function convert($data) {
		$data['begintime_s'] = get_date($data['begintime'], 'Y-m-d H:i');
		$data['endtime_s'] = get_date($data['endtime'], 'Y-m-d H:i');
		$data['deadline_s'] = $data['deadline'] ? get_date($data['deadline'], 'Y-m-d H:i') : '';
		if ($data['poster']) {
			list($data['poster_img']) = geturl($data['poster'], 'lf');
		} else {
			$data['poster_img'] = $GLOBALS['imgpath'] . '/defaultactive.jpg';
		}
		return $data;
	}
	
	/**
	 * 增加一个报名用户
	 * @param $id int 活动id
	 * @param $uid int 用户id
	 * @param $info array 报名信息
	 * return mixed
	 */
	function appendMember($id, $uid, $info) {
		if (($return = $this->checkJoinInfo($info)) !== true) {
			return $return;
		}
		$this->_db->update("INSERT INTO pw_actmembers SET " . S::sqlSingle(array(
			'uid'		=> $uid,
			'actid'		=> $id,
			'realname'	=> $info['realname'],
			'phone'		=> $info['phone'],
			'mobile'	=> $info['mobile'],
			'address'	=> $info['address'],
			'anonymous'	=> intval($info['anonymous'])
		)));
		$this->_db->update("UPDATE pw_active SET members=members+1 WHERE id=" . S::sqlEscape($id));
		return true;
	}
	
	/**
	 * 验证加入活动的合法信息
	 * return mixed
	 */
	function checkJoinInfo($info) {
		if (!$info['realname']) {
			return '请填写真实姓名!';
		}
		if (strlen($info['realname']) > 30) {
			return '真实姓名长度不能超过30个字节!';
		}
		if (!$info['mobile']) {
			return '请填写手机号码!';
		}
		if (!preg_match("/^[\d\-]{1,15}$/", $info['mobile'])) {
			return '无效的手机号码!';
		}
		if ($info['phone'] && !preg_match("/^[\d\-]{1,15}$/", $info['phone'])) {
			return '无效的电话号码!';
		}
		if ($info['address'] && strlen($info['address']) > 255) {
			return '地址长度不能超过255个字节!';
		}
		return true;
	}
	
	function checkJoinStatus($id, $uid) {
		if ($this->isJoin($id, $uid)) {
			return '你已经报名了!';
		}
		$active = $this->getActiveById($id);

		if ($active['objecter'] == 1) {
			require_once(R_P . 'apps/groups/lib/colonys.class.php');
			$colonyServer = new PW_Colony();
			$cm = $colonyServer->getSingleMember($active['cid'], $uid);
			if (!$cm) {
				return '你还不是本群群员，请先加入群组!';
			}
			if ($cm['ifadmin'] == -1) {
				return '你还没有通过身份审核，暂时不能加入活动!';
			}
		}
		if ($active['limitnum'] && $active['members'] >= $active['limitnum']) {
			return '活动参加人数已满!';
		}
		return true;
	}
	
	/**
	 * 判断用户是否加入某群组
	 * return bool
	 */
	function isJoin($id, $uid) {
		if ($this->_db->get_one("SELECT id FROM pw_actmembers WHERE actid=" . S::sqlEscape($id) . ' AND uid=' . S::sqlEscape($uid))) {
			return true;
		}
		return false;
	}

	/**
	 * 获取热门活动
	 * return array
	 */
	function getHotActive($nums) {
		global $timestamp;
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_cache');
			$rt =  $_cacheService->getCacheByName('hotactive_3');			
		} else {
			$rt = $this->_db->get_one("SELECT * FROM pw_cache WHERE name='hotactive_3'");
		}
		if ($rt && $rt['time'] > $timestamp - 1800) {
			return unserialize($rt['cache']);
		} else {
			list($activedb) = $this->searchList(array('createtime_s' => $timestamp - 2592000), 3, 0, 'members', 'DESC');
			pwQuery::replace(
				'pw_cache',
				array('name' => 'hotactive_3', 'cache' => serialize($activedb), 'time' => $timestamp)
			);
			//$this->_db->update("REPLACE INTO pw_cache SET " . S::sqlSingle(array('name' => 'hotactive_3', 'cache' => serialize($activedb), 'time' => $timestamp)));
			return $activedb;
		}
	}

	function updateHits($id, $hits = 1) {
		$this->_db->update("UPDATE pw_active SET hits=hits+" . S::sqlEscape($hits) . " WHERE id=" . S::sqlEscape($id));
	}
	
	/**
	 * 退出活动
	 * void
	 */
	function quitActive($id, $uid) {
		$this->_db->update("DELETE FROM pw_actmembers WHERE actid=" . S::sqlEscape($id) . ' AND uid=' . S::sqlEscape($uid));
		if ($this->_db->affected_rows() > 0) {
			$this->_db->update("UPDATE pw_active SET members=members-1 WHERE id=" . S::sqlEscape($id));
		}
	}

	/**
	 * 删除活动
	 * void
	 */
	function delActive($id) {
		list($activedb) = $this->searchList(array('id' => $id));
		foreach ($activedb as $key => $value) {
			if ($value['poster']) {
				pwDelatt($value['poster'], $GLOBALS['db_ifftp']);
			}
			//* $this->_db->update("UPDATE pw_colonys SET activitynum=activitynum-1 WHERE id=". S::sqlEscape($value['cid']));
			$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET activitynum=activitynum-1 WHERE id=:id", array('pw_colonys',$value['cid'])));
		}
		$this->_db->update("DELETE FROM pw_actmembers WHERE actid" . $this->sqlIn($id));
		$this->_db->update("DELETE FROM pw_active WHERE id" . $this->sqlIn($id));
	}
	
	/**
	 * 获取活动的参加人员
	 * return array
	 */
	function getActMembers($id, $nums = null, $start = 0) {
		$limit = $nums ? S::sqlLimit($start, $nums) : '';
		$array = array();
		$query = $this->_db->query("SELECT a.*,m.username,m.icon,anonymous FROM pw_actmembers a LEFT JOIN pw_members m ON a.uid=m.uid WHERE a.actid=" . S::sqlEscape($id) . ' ORDER BY id ASC ' . $limit);
		while ($rt = $this->_db->fetch_array($query)) {
			list($rt['icon']) = showfacedesign($rt['icon'], 1, 'm');
			$array[] = $rt;
		}
		return $array;
	}

	function getCyidById($id) {
		return $this->_db->get_value("SELECT cid FROM pw_active WHERE id=" . S::sqlEscape($id));
	}
}
?>