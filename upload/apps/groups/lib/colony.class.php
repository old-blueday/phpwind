<?php
!defined('P_W') && exit('Forbidden');

/**
 * 群组类
 * @author chenjm / sky_hold@163.com
 * @package colony
 */

class PwColony {
	
	var $_db;

	var $cyid;
	var $info  = array(); //群信息
	var $right = array(); //群组权限
	var $useDefault = false;//使用程序设定初始值

	function PwColony($cyid) {
		global $db;
		$this->_db =& $db;
		$this->cyid = $cyid;
		$this->_init();
	}

	/**
	 * 初始化群信息
	 * return array
	 */
	function _init() {
		global $winduid;
		$_sql_sel = $_sql_tab = '';
		if ($winduid) {
			$_sql_sel = ',cm.id AS ifcyer,cm.ifadmin,cm.lastvisit';
			$_sql_tab = ' LEFT JOIN pw_cmembers cm ON c.id=cm.colonyid AND cm.uid=' . S::sqlEscape($winduid);
		}
		$this->info = $this->_db->get_one("SELECT c.*{$_sql_sel} FROM pw_colonys c{$_sql_tab} WHERE c.id=" . S::sqlEscape($this->cyid));
		if ($this->info) {
			$this->info['ifFullMember'] = ($this->info['ifcyer'] && $this->info['ifadmin'] != -1) ? 1 : 0;
			list($this->info['cnimg'], $this->info['imgtype']) = PwColony::getColonyCnimg($this->info['cnimg']);
			$this->info['createtime_s'] = get_date($this->info['createtime'], 'Y-m-d');
			!$this->info['colonystyle'] && $this->info['colonystyle'] = 'skin_default';
			$this->info['descrip'] = str_replace('&#61;', '=', $this->info['descrip']);
			$this->_setRight();
		}
	}

	function &getInfo() {
		return $this->info;
	}
	
	/**
	 * 是否有群管理权限
	 * return bool
	 */
	function getIfadmin() {
		global $windid,$SYSTEM,$manager,$groupid;
		/*关联版块权限*/
		$rForumAdmin = false;
		if($SYSTEM['forumcolonyright'] && $this->info['classid'] > 0){
			if($groupid == 5){
				L::loadClass('forum', 'forum', false);
				$rForum = new PwForum($this->info['classid']);
				$rForumAdmin = $rForum->isBM($windid);
			}else{
				$rForumAdmin = true;
			}
		}
		/* end */
		return ($rForumAdmin || $this->info['ifadmin'] == '1' || $this->info['admin'] == $windid || S::inArray($windid,$manager) || $SYSTEM['colonyright']);
	}
	
	/**
	 * 获取群组logo
	 * static function
	 * @param string $cnimg 图片存储地址
	 * return array(logo地址, 图片存储方式)
	 */
	function getColonyCnimg($cnimg) {
		$imgtype = 'http';
		if (!strstr($cnimg, 'http://')) {
			if ($cnimg) {
				list($cnimg, $imgtype) = geturl("cn_img/$cnimg", 'lf');
			} else {
				$cnimg = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
				$imgtype = 'none';
			}
		}
		return array($cnimg, $imgtype);
	}
	
	/**
	 * 解析群组banner图片路径
	 */
	function initBanner() {
		if ($this->info['banner']) {
			list($this->info['banner']) = geturl("cn_img/{$this->info[banner]}",'lf');
		}
	}

	/**
	 * 获取带样式的群组名称
	 * return string
	 */
	function getNameStyle() {
		return $this->styleFormat($this->info['cname'], $this->info['titlefont']);
	}

	/**
	 * 初始化群组等级权限
	 */
	function _setRight() {
		$level = $this->info['speciallevel'] ? $this->info['speciallevel'] : $this->info['commonlevel'];
		empty($level) && $level = 1;
		$this->right = $this->_db->get_one("SELECT * FROM pw_cnlevel WHERE id=" . S::sqlEscape($level));
		if (empty($this->right)) {
			//$this->right = $this->_db->get_one("SELECT * FROM pw_cnlevel WHERE ltype='common' ORDER BY lpoint ASC LIMIT 1");
			$this->right = array(
				  'ltype' => 'common',
				  'ltitle' => '初始群组',
				  'lpoint' => 0,
				  'albumnum' => 10,
				  'maxphotonum' => 60,
				  'maxmember' => 100,
				  'bbsmode' => 0,
				  'allowmerge' => 1,
				  'allowattorn' => 1,
				  'allowdisband' => 0,
				  'pictopic' => 0,
				  'allowstyle' => 1,
				  'topicadmin' => array(),
				  'modeset'	=>
				  array(
				    'thread' =>
				    array(
				      'vieworder'=> 0,
				      'title' => '话题'
				    ),
				    'active' =>
				    array(
				      'vieworder' => 0,
				      'title' => '活动'
				    ),
				    'write' =>
				    array(
				      'ifopen'=>1,
				      'vieworder'=>1,
				      'title'=>'讨论区'
				    ),
				    'galbum' =>
				    array(
				      'ifopen' =>1,
				      'vieworder'=>2,
				      'title'=>'相册'
				    ),
				    'member'=>
				    array(
				      'ifopen'=>1,
				      'vieworder'=>3,
				      'title'=>'成员'
				    )
				  ),
				  'layout' =>
				  array(
				    'thread'=>
				    array('vieworder' => 0,
				      'num'=>5,
				    ),
				    'active'=>
				    array(
				      'vieworder'=>0,
				      'num'=>4
				    ),
				    'write'=>
				    array(
				      'ifopen'=>1,
				      'vieworder'=>1,
				      'num'=>5
				    ),
				    'galbum'=>
				    array(
				      'ifopen'=>1,
				      'vieworder'=>2,
				      'num'=>10
				    )
				  )
				);
				$this->useDefault = true;
		}
		if ($this->right && !$this->useDefault) {
			$this->right['modeset'] = $this->right['modeset'] ? unserialize($this->right['modeset']) : array();
			$this->right['layout'] = $this->right['layout'] ? unserialize($this->right['layout']) : array();
			$this->right['topicadmin'] = $this->right['topicadmin'] ? unserialize($this->right['topicadmin']) : array();
		}
	}

	/**
	 * 获取权限
	 * return array
	 */
	function &getRight() {
		return $this->right;
	}
	
	/**
	 * 获取话题数
	 * @param string $searchadd 搜索条件
	 * return int
	 */
	function getArgumentCount($searchadd = '') {
		$count = $this->_db->get_value('SELECT COUNT(*) AS count FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid WHERE a.cyid=' . S::sqlEscape($this->cyid) . $this->_getThreadWhere() . $searchadd);
		return (int)$count;
	}
	
	/**
	 * 获取话题列表
	 * @param string $searchadd 搜索条件
	 * @param int $start 搜索记录开始
	 * @param int $limit 获取记录条数
	 * @param string $orderway 排序字段
	 * @param string $asc 排序方式
	 * return array
	 */
	function getArgument($searchadd, $start, $limit, $orderway = 'lastpost', $asc = 'DESC') {
		$array = array();
		$query = $this->_db->query("SELECT t.*,a.topped,a.lastpost,a.titlefont,a.digest,a.toolfield FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid WHERE a.cyid=" . S::sqlEscape($this->cyid) . $this->_getThreadWhere() . $searchadd . ' ORDER BY ' . ($orderway == 'lastpost' ? "a.topped $asc,a.lastpost" : "t.{$orderway}") . " $asc" . S::sqlLimit($start, $limit));
		while ($rt = $this->_db->fetch_array($query)) {
			$array[] = $rt;
		}
		return $array;
	}

	function _getThreadWhere() {
		$ifcheck = ($this->info['classid'] && $this->info['iftopicshowinforum']) ? 1 : 2;
		return ' AND t.fid=' . S::sqlEscape($this->info['classid']) . ' AND t.ifcheck=' . S::sqlEscape($ifcheck);
	}
	
	/**
	 * static function
	 * 计算群组的积分
	 * @param array $info 群组信息
	 * return int
	 */
	function calculateCredit($info) {
		require_once(R_P . 'require/functions.php');
		$info['pnum'] -= $info['tnum'];
		return CalculateCredit($info, L::config('o_groups_upgrade','o_config'));
	}

	function _checkJoinCredit($uid) {
		global $credit;
		require_once(R_P.'require/credit.php');
		$o_groups_creditset = L::config('o_groups_creditset','o_config');
		if (empty($o_groups_creditset['Joingroup'])) {
			return true;
		}
		if(!is_array($o_groups_creditset['Joingroup'])) return true;
		foreach ($o_groups_creditset['Joingroup'] as $key => $value) {
			if ($value > 0 && $value > $credit->get($uid, $key)) {
				$GLOBALS['moneyname'] = $credit->cType[$key];
				$GLOBALS['o_joinmoney'] = $value;
				return 'colony_joinfail';
			}
		}
		return true;
	}
	
	function _checkJoinNum($uid) {
		global $_G;
		if ($_G['allowjoin'] > 0 && $_G['allowjoin'] <= $this->_db->get_value("SELECT COUNT(*) as sum FROM pw_cmembers WHERE uid=" . S::sqlEscape($uid))) {
			return 'colony_joinlimit';
		}
		return true;
	}

	function checkJoinStatus($uid) {
		if ($this->info['ifFullMember']) {
			return 'colony_alreadyjoin';
		}
		if ($this->info['ifcyer'] && $this->info['ifadmin'] == '-1') {
			return 'colony_joinsuccess_check2';
		}
		if (!$this->info['ifcheck']) {
			return 'colony_joinrefuse';
		}
		if ($this->right['maxmember'] > 0 && $this->info['members'] >= $this->right['maxmember']) {
			return 'colony_memberlimit';
		}
		if (($return = $this->_checkJoinNum($uid)) !== true) {
			return $return;
		}
		if (($return = $this->_checkJoinCredit($uid)) !== true) {
			return $return;
		}
		return true;
	}

	/**
	 * 加入群组
	 * @param int $uid 用户id
	 * @param string $username 用户名
	 * return string 
		colony_joinsuccess			加入成功
		colony_joinsuccess_check	加入成功，需要验证
		colony_alreadyjoin			加入失败，已加入
		colony_joinsuccess_check2	加入失败，已加入，未验证
		colony_joinrefuse			加入失败，拒绝加入
		colony_memberlimit			加入失败，群会员达到上限
		colony_joinlimit			加入失败，用户加入的群达到上限
		colony_joinfail				加入失败，用户积分不足
	 */
	function join($uid, $username) {
		if (($return = $this->checkJoinStatus($uid)) !== true) {
			return $return;
		}
		return $this->addColonyUser($uid, $username);
	}

	function addColonyUser($uid, $username, $frombbs=false) {
		global $credit;
		require_once(R_P.'require/credit.php');

		//积分变动
		$o_groups_creditset = L::config('o_groups_creditset','o_config');
		if (!empty($o_groups_creditset['Joingroup'])) {
			require_once(R_P . 'u/require/core.php');
			$credit->appendLogSet(L::config('o_groups_creditlog', 'o_config'), 'groups');
			$creditset = getCreditset($o_groups_creditset['Joingroup'], false);
			$credit->addLog('groups_Joingroup', $creditset, array(
				'uid'		=> $uid,
				'username'	=> $username,
				'ip'		=> $GLOBALS['onlineip']
			));
			$credit->sets($uid, $creditset);
		}
		/**
		$this->_db->update("INSERT INTO pw_cmembers SET " . S::sqlSingle(array(
			'uid'		=> $uid,
			'username'	=> $username,
			'ifadmin'	=> $this->info['ifcheck'] == 2 ? '0' : '-1',
			'colonyid'	=> $this->cyid,
			'addtime'	=> $GLOBALS['timestamp']
		)));
		**/
		pwQuery::insert('pw_cmembers', array(
			'uid'		=> $uid,
			'username'	=> $username,
			'ifadmin'	=> $this->info['ifcheck'] == 2 ? '0' : '-1',
			'colonyid'	=> $this->cyid,
			'addtime'	=> $GLOBALS['timestamp']
		));

		if ($this->info['ifcheck'] == 2) {
			$this->updateInfoCount(array('members' => 1));	
		}
		$this->_sendJoinMsg($frombbs);

		if ($this->info['ifcheck'] == 2) {
			updateUserAppNum($uid, 'group');
			return 'colony_joinsuccess';
		}
		return 'colony_joinsuccess_check';
	}

	function updateInfoCount($info) {
		if (empty($info) || !is_array($info)) {
			return false;
		}
		$_sql_set = $extra = '';
		foreach ($info as $key => $value) {
			if (in_array($key, array('tnum', 'pnum', 'members', 'albumnum', 'photonum', 'writenum', 'activitynum'))) {
				$_sql_set .= $extra . $key . '=' . $key . '+' . intval($value);
				$this->info[$key] += intval($value);
				$extra = ',';
			}
		}
		if (empty($_sql_set))
			return false;

		//* $this->_db->update('UPDATE pw_colonys SET ' . $_sql_set . ' WHERE id=' . S::sqlEscape($this->cyid));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET {$_sql_set} WHERE id=:id", array('pw_colonys',$this->cyid)));
		require_once(R_P . 'u/require/core.php');
		updateGroupLevel($this->cyid, $this->info);
	}

	function _sendJoinMsg($frombbs) {
		global $winduid,$windid;
		$lang = array(
			'cname' => S::escapeChar($this->info['cname']),
			'colonyurl'
		);
		$group = $this->info['ifcheck'] == 1 ? '3' : '2';
		if ($frombbs) {
			$colonyurl = 'thread.php?cyid='.$this->cyid.'&showtype=member&group='.$group;
		} else {
			$colonyurl = 'apps.php?q=group&a=member&cyid='.$this->cyid.'&group='.$group;
		}
		$managers = $this->getUserNames($this->getManager());
		if ($this->info['ifcheck'] == 1) {
			M::sendRequest(
				$windid,
				$managers,
				array(
					'create_uid' => $winduid,
					'create_username' => $windid,
					'title' => getLangInfo('writemsg', 'colony_join_title_check', array(
						'cname'	=> S::escapeChar($this->info['cname'])
					)),
					'content' => getLangInfo('writemsg','colony_join_content_check',array(
						'uid' => $winduid,
						'username' => $windid,
						'cname'	=> S::escapeChar($this->info['cname']),
						'colonyurl' => $colonyurl
					)),
					'extra' => serialize(array('cyid' => $this->cyid,'check'=>1))
				),
				'request_group',
				'request_group'
			);
		} else {
			M::sendNotice($managers, array(
				'title' => getLangInfo('writemsg', 'colony_join_title',array(
					'cname'	=> S::escapeChar($this->info['cname'])
				)),
				'content' => getLangInfo('writemsg', 'colony_join_content',array(
					'cname'	=> S::escapeChar($this->info['cname']),
					'colonyurl' => $colonyurl
				))
			));
		}
	}
	
	/**
	 * 获取最近访客
	 * return array
	 */
	function getVisitor($nums = null, $start = 0) {
		if (!is_array($this->info['visitor'])) {
			$this->info['visitor'] = $this->info['visitor'] ? (array)unserialize($this->info['visitor']) : array();
		}
		return $nums ? array_slice($this->info['visitor'], $start, $nums, true) : $this->info['visitor'];
	}

	function getVisitorNum() {
		if (!is_array($this->info['visitor'])) {
			$num = count((array)unserialize($this->info['visitor']));
		} else {
			$num = count($this->info['visitor']);
		}
		return $num;
	}

	function appendVisitor($uid) {
		global $timestamp;
		$this->getVisitor();
		if ($uid && (!isset($this->info['visitor'][$uid]) || $timestamp - $this->info['visitor'][$uid] > 3600)) {
			$this->info['visitor'][$uid] = $timestamp;
			arsort($this->info['visitor']);
			while (count($this->info['visitor']) > 50) {
				array_pop($this->info['visitor']);
			}
			//* $this->_db->update("UPDATE pw_colonys SET visitor=" . S::sqlEscape(serialize($this->info['visitor'])) . ' WHERE id=' . S::sqlEscape($this->cyid));
			$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET visitor=:visitor WHERE id=:id", array('pw_colonys',serialize($this->info['visitor']), $this->cyid)));
		}
	}

	function styleFormat($str, $titlefont) {
		if ($titlefont) {
			$titledetail = explode("~", $titlefont);
			if ($titledetail[0]) $str = "<font color=\"$titledetail[0]\">$str</font>";
			if ($titledetail[1]) $str = "<b>$str</b>";
			if ($titledetail[2]) $str = "<i>$str</i>";
			if ($titledetail[3]) $str = "<u>$str</u>";
		}
		return $str;
	}

	function _sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . S::sqlImplode($ids) . ')' : '=' . S::sqlEscape($ids);
	}

	function _sqlMs($v) {
		$_sql_v = '';
		switch ($v) {
			case -1://未验证
				$_sql_v .= '=-1';break;
			case 0://普通会员
			case 1://管理员
				$_sql_v .= '=' . S::sqlEscape($value);break;
			case 2://非管理员
				$_sql_v .= "!='1'";break;
			case 3://验证会员
				$_sql_v .= ">=0";break;
			default:
				break;
		}
		return $_sql_v;
	}
	
	/**
	 * sql语句条件组装
	 * @param array $where 搜索条件
	 * return string where条件
	 */
	function _sqlCompound($where) {
		if (!$where || !is_array($where)) {
			return '';
		}
		$_sql_where = '';
		foreach ($where as $_sql_field => $value) {
			switch ($_sql_field) {
				case 'id':
				case 'uid':
					$_sql_where .= " AND $_sql_field" . $this->_sqlIn($value);break;
				case 'ifadmin':
					$_sql_where .= " AND $_sql_field" . $this->_sqlMs($value);break;
				case 'admin':
					$_sql_where .= " AND $_sql_field=" . S::sqlEscape($value);break;
			}
		}
		return $_sql_where;
	}

	/**
	 * 获取对帖子的管理权限
	 * @author zhudong
	 * @param string $action 操作类型
	 * @param int $seltid 对单个帖子进行操作的时候所针对的帖子ID
	 * @return int $ifadmin 最终对该类型操作权限
	 */
	function checkTopicAdmin($action,$seltid) {
		global $manager,$SYSTEM,$windid;
		if (S::inArray($windid,$manager) || $SYSTEM['colonyright']) {
			$ifadmin = 1;
		} elseif ($this->info['ifadmin'] == '1' || $this->info['admin'] == $windid) {
			if ($action == 'type') {//群组管理员对主题分类享有权限
				$ifadmin = 1;
			} else {
				$ifadmin = $this->right['topicadmin'][$action];
			}
		}
		return $ifadmin;
	}

	/**
	 * 检查action是否在指定的范围内
	 * @author zhudong
	 * @param string $action 操作类型
	 * @return null
	 */

	function checkAction($action) {
		$actionAarry = array('del','highlight','lock','pushtopic','downtopic','toptopic','digest','type');
		if (!in_array($action,$actionAarry)) {
			Showmsg('undefined_action');
		}
	}

	/**
	 * 检查被管理的帖子的合法性（是否是本群的帖子，是否越权操作）
	 * @author zhudong
	 * @param array $tidarray 被操作的帖子的tid数组
	 * @return array
	 */

	function checkTopic($tidarray) {
		global $groupid;
		count($tidarray) > 500 && Showmsg('mawhole_count');
		foreach ($tidarray as $k => $v) {
			is_numeric($v) && $tids[] = $v;
		}
		if ($tids) {
			$tids = S::sqlImplode($tids);
			$query = $this->_db->query("SELECT a.*,t.* FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid WHERE a.tid IN($tids)");
			while ($rt = $this->_db->fetch_array($query)) {
				if ($rt['cyid'] != $this->info['id']) {
					Showmsg('colony_topicadmin_other_colony');
				}
				//限制越级管理
				if ($groupid != 3 && $groupid != 4) {
					$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
					$authordb = $userService->get($rt['authorid']);//groupid
					/**Begin modify by liaohu*/
					$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
					if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
						Showmsg('modify_admin');
					}
					/**End modify by liaohu*/
				}
				$rt['date'] = get_date($rt['postdate']);
				$threaddb[$rt['tid']] = $rt;
			}
		} else {
			$threaddb = array();
		}
		return $threaddb;
	}
	
	/**
	 * 获取群管理员列表
	 * return array
	 */
	function getManager($num=0) {
		$array = array();
		$limit = $num ? 'LIMIT '.(int)$num : '';
		$query = $this->_db->query("SELECT uid,username FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($this->cyid) . " AND ifadmin='1' ORDER BY addtime ASC $limit");
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['uid']] = $rt;
		}
		return $array;
	}
	
	/**
	 * 获取用户名数组
	 * return array
	 */
	function getUserNames($users) {
		$array = array();
		foreach ($users as $key => $value) {
			$array[] = $value['username'];
		}
		return $array;
	}
	
	/**
	 * 获取群成员列表
	 * @param array $where 搜索条件
	 * @param int nums 获取记录条数
	 * @param int start 
	 * @param string $orderway 排序字段
	 * @param bool $count 是否获取统计数
	 * return array
	 */
	function getMembers($where, $nums = null, $start = 0, $orderway = '', $count = false) {
		$sql = $this->_sqlCompound($where);
		$limit = $order = '';
		if ($nums) {
			$limit = S::sqlLimit($start, $nums);
		}
		if ($orderway) {
			!in_array($orderway, array('lastvisit', 'lastpost', 'ifadmin')) && $orderway = 'ifadmin';
			$order = " ORDER BY $orderway DESC";
		}
		$array = array();
		$query = $this->_db->query("SELECT uid,username,lastvisit FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($this->cyid) . $sql . $order . $limit);
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['uid']] = $rt;
		}
		return $array;
	}
	
	/**
	 * 审核会员
	 * @param array $where 搜索条件
	 * return array
	 */
	function checkMembers($uids) {
		if (!$this->getIfadmin() || empty($uids)) {
			return false;
		}
		!is_array($uids) && $uids = array($uids);
		$array = $this->getMembers(array('uid' => $uids, 'ifadmin' => -1));
		if ($array) {
			$ids = array_keys($array);
			require_once(R_P . 'u/require/core.php');
			//* $this->_db->update("UPDATE pw_cmembers SET ifadmin='0' WHERE colonyid=" . S::sqlEscape($this->cyid) . ' AND uid IN(' . S::sqlImplode($ids) . ") AND ifadmin='-1'");
			pwQuery::update('pw_cmembers', 'colonyid=:colonyid AND uid IN (:uid) AND ifadmin=:ifadmin', array($this->cyid, $ids, -1), array('ifadmin'=>0));
			updateUserAppNum($ids, 'group');
		}
		$newMemberCount = count($array);
		$this->updateInfoCount(array('members' => $newMemberCount));
		return $this->getUserNames($array);
	}
	
	/**
	 * 热门群组
	 * return array
	 */
	function getLikeGroup() {
		global $o_groups_upgrade;
		$array = array();
		$query = $this->_db->query("SELECT id,cname,cnimg,createtime,members,tnum,pnum,albumnum,photonum,writenum,activitynum FROM pw_colonys WHERE styleid=" . S::sqlEscape($this->info['styleid']) . " AND id!=" . S::sqlEscape($this->cyid) . " ORDER BY visit DESC LIMIT 4");
		while ($rt = $this->_db->fetch_array($query)) {
			list($rt['cnimg']) = PwColony::getColonyCnimg($rt['cnimg']);
			$rt['colonyNums'] = CalculateCredit($rt, $o_groups_upgrade);
			$rt['createtime'] = get_date($rt['createtime'], 'Y-m-d');
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function getOwnDelRight($action,$authorid,$seltid) {
		global $winduid,$_G;
		if(empty($seltid)) {
			$right = 0;
		}

		if ($action == 'del' && $winduid == $authorid && $_G['allowdelatc']) {
			$right = 1;
		}
		return $right;
	}

	function getSetAble($t) {
		global $windid,$SYSTEM,$groupRight;
		$ifable = 0;
		$colony = $this->getInfo();
		switch ($t) {
			case 'style' :
				$ifable = $this->right['allowstyle'];
				break;
			case 'merge' :
				$ifable = ((1 == $SYSTEM['colonyright'] || $colony['admin'] == $windid) && $groupRight['allowmerge']) ? '1' : '0';
				break;
			case 'attorn' :
				$ifable = ((1 == $SYSTEM['colonyright'] || $colony['admin'] == $windid) && $groupRight['allowattorn']) ? '1' : '0';
				break;
			case 'disband' :
				$ifable = ((1 == $SYSTEM['colonyright'] || $colony['admin'] == $windid) && $groupRight['allowdisband']) ? '1' : '0';
				break;
			default :
				$ifable = 1;
				break;
		}
		return $ifable;
	}

	function jumpToBBS($q,$a,$cyid) {
		if($q == 'group') {
			$showtype = $a;	
		} elseif ($q == 'galbum') {
			$showtype = 'galbum';	
		}
		if($showtype) {
			ObHeader("thread.php?cyid=$cyid&showtype=$showtype");
		} else {
			ObHeader("thread.php?cyid=$cyid");
		}
	}

	function jumpToColony($showtype,$cyid) {
		if($showtype != 'galbum') {
			$q = 'group';
			$a = $showtype;
		} elseif ($showtype == 'galbum') {
			$q = 'galbum';
		}
		if($q == 'group' && $a) {
			ObHeader("apps.php?q=group&a=$a&cyid=$cyid");
		} elseif ($q == 'galbum') {
			ObHeader("apps.php?q=galbum&cyid=$cyid");
		} else {
			ObHeader("apps.php?q=group&cyid=$cyid");
		}
	}

	/**
	 * 获取对帖子列表的管理权限
	 * @author zhudong
	 * @return 
	 */

	function getManageCheck ($ifbbsadmin,$ifcolonyadmin) {
		if($ifbbsadmin || array_sum($this->right['topicadmin'])>0 && $ifcolonyadmin){
			return true;
		}
	}

	function getColonyAdmin() {
		global $windid;
		$right = ($this->info['ifadmin'] == '1' || $this->info['admin'] == $windid) ? 1 : 0;
		return $right;
	}

	function getBbsAdmin ($isGM) {
		global $SYSTEM;
		$right = ($isGM || $SYSTEM['colonyright']) ? 1 : 0;
		return $right;
	}
}
?>