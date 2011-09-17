<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索引擎基类服务
 * @author liuhui 2010-4-21
 * @version phpwind 8.0
 */
class Search_Base {
	
	var $_timestamp = null;
	var $_username = null;
	var $_userId = null;
	var $_groupId = null;
	var $_userGroup = null;
	var $_maxResult = null;
	var $_waitSegment = 0;
	var $_isLevel = 1; //是否开启权限检查
	var $_isBuildAttachs = 1; //是否图文并茂功能
	var $_version = true; //php_version
	

	function Search_Base() {
		global $timestamp, $winduid, $windid, $groupid, $_G, $db_maxresult, $db_schwait, $db_openbuildattachs;
		$this->_userId = &$winduid;
		$this->_username = &$windid;
		$this->_groupId = &$groupid;
		$this->_userGroup = &$_G;
		$this->_waitSegment = &$db_schwait;
		$this->_maxResult = ($db_maxresult) ? $db_maxresult : 500;
		$this->_timestamp = &$timestamp;
		$this->_isBuildAttachs = &$db_openbuildattachs;
		$this->_version = (function_exists ( 'str_ireplace' )) ? true : false;
	}
	/**
	 * 检查用户搜索权限
	 * @return unknown_type
	 */
	function _checkUserLevel() {
		$userService = $this->_getUserService ();
		if (!$this->_userGroup ['searchtime']) {
			return true;
		}
		
		$memberInfo = $userService->get ( $this->_userId, false, false, true );
		$memberInfo ['lasttime'] = $memberInfo ? $memberInfo ['lasttime'] : 0;
		
		if ($this->_timestamp - $memberInfo ['lasttime'] < $this->_userGroup ['searchtime']) {
			return false;
		}
		$userService->update ( $this->_userId, array (), array (), array ('lasttime' => $this->_timestamp ) );
		return true;
	}
	/**
	 * 检查用户搜索间隔时间
	 * @return unknown_type
	 */
	function _checkWaitSegment() {
		if (! $this->_waitSegment)
			return true;
		if (file_exists ( D_P . 'data/bbscache/schwait_cache.php' )) {
			if ($this->_timestamp - pwFilemtime ( D_P . 'data/bbscache/schwait_cache.php' ) > $this->_waitSegment) {
				P_unlink(D_P.'data/bbscache/schwait_cache.php');
			} else {
				return false;
			}
		}
		return true;
	}
	/**
	 * 检查关键字查询条件
	 * @param $keyword
	 * @return 关键字数组
	 */
	function _checkKeywordCondition($keyword) {
		if ($this->_sphinxlen && strlen ( $keyword ) < 3) {
			return array ();
		}
		$keyword = trim ( ($keyword) );
		$keyword = str_replace ( array ("&#160;", "&#61;", "&nbsp;", "&#60;", "<", ">", "&gt;", "(", ")", "&#41;" ), ' ', $keyword );
		$ks = explode ( " ", $keyword );
		$keywords = array ();
		foreach ( $ks as $v ) {
			$v = trim ( $v );
			($v) && $keywords [] = $v;
		}
		if (! $keywords) {
			return array ();
		}
		$keywords = implode ( " ", $keywords );
		return $keywords;
	}
	/**
	 * 检查用户查询条件
	 * @param array $userNames
	 * @return 返回数组 array(uid=>username)
	 */
	function _checkUserCondition($userNames) {
		if (! $userNames)
			return false;
		$userNames = (is_array ( $userNames )) ? $userNames : array ($userNames );
		
		$userService = $this->_getUserService ();
		$users = $userService->getByUserNames ( $userNames );
		if (! $users) {
			return false;
		}
		
		$tmp = array ();
		foreach ( $users as $user ) {
			$tmp [$user ['uid']] = $user ['username'];
		}
		return $tmp;
	}
	/**
	 * 检查时间查询条件
	 * @param int $startTime
	 * @param int $endTime
	 * @return unknown_type
	 */
	function _checkTimeNodeCondition($startTime, $endTime) {
		$startTime && $startTime = PwStrtoTime ( $startTime );
		$endTime && $endTime = PwStrtoTime ( $endTime );
		if ($startTime && ! $endTime) {
			$endTime = $this->_timestamp;
		}
		if ($endTime && ! $startTime) {
			$startTime = 0;
		}
		if (! $startTime && ! $endTime) {
			$startTime = 0;
			$endTime = $this->_timestamp;
		}
		return array ($startTime, $endTime );
	}
	
	function _checkThreadConditions($keywords, $userNames = "", $starttime = "", $endtime = "") {
		$keywords = $this->_checkKeywordCondition ( $keywords );
		if (! $keywords)
			return array (false );
		$users = array ();
		if ($userNames && ! $users = $this->_checkUserCondition ( $userNames )) {
			return array (false );
		}
		list ( $starttime, $endtime ) = $this->_checkTimeNodeCondition ( $starttime, $endtime );
		return array ($keywords, $users, $starttime, $endtime );
	}
	
	function _getThreads($threadIds, $keywords) {
		if (! $threadIds)
			return array ();
		$threadsDao = $this->getThreadsDao ();
		if (! ($result = $threadsDao->getsBythreadIds ( $threadIds ))) {
			return array ();
		}
		return $this->_buildThreads ( $result, $keywords );
	}
	
	/**
	 * 获取最新帖子
	 * @param $conditions
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getLatestThreads($conditions, $page, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		$offset = ($page - 1) * $perpage;
		$fid = intval($conditions['fid']);
		list($starttime, $endtime) = $this->_checkTimeNodeCondition($conditions['starttime'], $conditions['endtime']);
		$threadsDao = $this->getThreadsDao ();
		if (! ($total = $threadsDao->getLatestThreadsCount ($fid, $starttime, $endtime))) {
			return array (false, false );
		}
		$result = $threadsDao->getLatestThreads ($fid, $starttime, $endtime, $offset, $perpage );
		return array ($total, $this->_buildThreads ( $result, array () ) );
	}
	
	/**
	 * 获取当日帖子
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getTodayThreads($page, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		$offset = ($page - 1) * $perpage;
		$posttime = PwStrtoTime ( get_date ( $this->_timestamp, 'Y-m-d' ) );
		$threadsDao = $this->getThreadsDao ();
		if (! ($total = $threadsDao->getThreadsCountByPostdate ( $posttime ))) {
			return array (false, false );
		}
		$result = $threadsDao->getThreadsByPostdate ( $offset, $perpage, $posttime );
		return array ($total, $this->_buildThreads ( $result, array () ) );
	}
	
	/**
	 * 获取精华帖子
	 * @param $condition
	 * @param $uid
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getDigestThreads($conditions = array(), $uid, $page, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		
		$fid = intval($conditions['fid']);
		list($starttime, $endtime) = $this->_checkTimeNodeCondition($conditions['starttime'], $conditions['endtime']);
		
		$offset = ($page - 1) * $perpage;
				
		$threadsDao = $this->getThreadsDao ();
		if (! ($total = $threadsDao->getDigestThreadsCount ($uid, $fid, $starttime, $endtime))) {
			return array (false, false );
		}
		
		$result = $threadsDao->getDigestThreads ( $uid, array (1, 2 ), $fid, $starttime, $endtime, $offset, $perpage );
		return array ($total, $this->_buildThreads ( $result, array () ) );
	}
	
	/**
	 * 获取最新日志
	 * @param $condition
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getLatestDiarys($conditions = array(), $page, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		
		list($starttime, $endtime) = $this->_checkTimeNodeCondition($conditions['starttime'], $conditions['endtime']);
		$offset = ($page - 1) * $perpage;
		$diarysDao = $this->getDiarysDao ();
		if (! ($total = $diarysDao->getLatestDiarysCount ($starttime, $endtime))) {
			return array (false, false );
		}
		$result = $diarysDao->getLatestDiarys ($starttime, $endtime, $offset, $perpage);
		return array ($total, $this->_buildDiarys ( $result, array () ) );
	}
	
	/**
	 * 获取最新用户
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getLatestUsers($page, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		$offset = ($page - 1) * $perpage;
		$usersDao = $this->getUsersDao ();
		if (! ($total = $usersDao->getLatestUsersCount ())) {
			return array (false, false );
		}
		$result = $usersDao->getLatestUsers ( $offset, $perpage );
		return array ($total, $this->_buildUsers ( $result ) );
	}
	
	/**
	 * 获取热榜版块排行 
	 */
	function _getHotForums($perpage) {
		$perpage = intval ( $perpage );
		if (1 > $perpage)
			return false;
		L::loadClass ( 'datanalyse', 'datanalyse', false );
		$datanalyse = new Datanalyse ();
		$result = $hotForums = $hotFids = array ();
		$hotForums = $datanalyse->getSortData ( 'forumPost', null, $perpage, 'tpost' );
		foreach ( $hotForums as $key => $val ) {
			$hotFids [] = $val ['id'];
		}
		$forumsDao = $this->getForumsDao ();
		$formusDB = $forumsDao->getFormusByFids ( $hotFids );
		foreach ( $hotFids as $key => $val ) {
			$result [] = $formusDB [$val];
		}
		$total = $result ? count ( $result ) : 0;
		return array ($total, $this->_buildForums ( $result, '' ) );
	}
	
	/**
	 * 获得最新群组
	 * @param $page
	 * @param $perpage
	 */
	function _getLatestColonys($page, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		$offset = ($page - 1) * $perpage;
		$colonysDao = $this->getColonysDao ();
		if (! ($total = $colonysDao->countLatestColonys ( $keywords ))) {
			return array (false, false );
		}
		$result = $colonysDao->getLatestColonys ( $offset, $perpage );
		return array ($total, $this->_buildGroups ( $result, '' ) );
	}
	
	/**
	 * 获取特殊帖子
	 * @param $type
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getSpecialThreads($type = 'latest', $uid, $page = 1, $perpage = 50, $expandCondition = array()) {
		if ($type == 'digest') {
			return $this->_getDigestThreads ($expandCondition, $uid, $page, $perpage);
		} elseif ($type == 'latest') {
			return $this->_getLatestThreads ($expandCondition, $page, $perpage);
		} else {
			return $this->_getTodayThreads ( $page, $perpage );
		}
	}
	
	/**
	 * 获取不同类型默认数据
	 * @param $type
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getDefaultByType($type = 'thread', $page = 1, $perpage = 50, $expandConditions = array()) {
		if (! $type)
			return array ();
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		switch ($type) {
			case "thread" :
				return $this->_getLatestThreads ($expandConditions, $page, $perpage);
				break;
			case "diary" :
				return $this->_getLatestDiarys ($expandConditions, $page, $perpage );
				break;
			case "user" :
				return $this->_getLatestUsers ( $page, $perpage );
			case "forum" :
				return $this->_getHotForums ( $perpage );
			case "group" :
				return $this->_getLatestColonys ( $page, $perpage );
				break;
		}
		return array ();
	}
	
	/**
	 * 组装帖子数据
	 * @param $result
	 * @return unknown_type
	 */
	function _buildThreads($threads, $keywords) {
		if (! $threads)
			return false;
		$keywords = (is_array ( $keywords )) ? $keywords : explode ( " ", $keywords );
		$data = array ();
		require_once (R_P . 'require/bbscode.php');
		foreach ( $threads as $t ) {
			$t ['postdate'] = get_date ( $t ['postdate'], "Y-m-d H:i" );
			$forum = L::forum ( $t ['fid'] );
			$t ['content'] = substrs ( stripWindCode ( strip_tags ( convert ( $t ['content'], array () ) ) ), 170 );
			foreach ( $keywords as $keyword ) {
				$keyword = stripslashes($keyword);
				$keyword && $t ['subject'] = $this->_highlighting ( $keyword, $t ['subject'] );
				$keyword && $t ['content'] = $this->_highlighting ( $keyword, $t ['content'] );
			}
			$t ['subject'] = ($t ['subject']) ? $t ['subject'] : 'RE:';
			$t ['name'] = strip_tags ( $forum ['name'] );
			$data [] = $t;
		}
		return $this->_buildThreadsAttachs($data);
	}
	
	function _buildThreadsAttachs($data) {
		if (!$data || !S::isArray($data)) return array(); 
		if (!$this->_isBuildAttachs) return $data;
		foreach ($data as $value) {
			if (!$value['ifupload'] || $value['ifhide']) continue;
			$_tids[] = $value['tid'];
		}
		if (!$_tids) return $data;
		$attachsDao = $this->getAttachsDao();
		$_sql = " SELECT * FROM pw_attachs WHERE tid IN (".S::sqlImplode($_tids).") AND type='img' AND pid=0 AND special = 0 ORDER BY  aid ASC";
		$_tempAttachsDb = $attachsDao->getSearch($_sql);
		$_tempAttachsDb = $this->_getAttachs($_tempAttachsDb);
		$reslut = array();
		foreach ($data as $value) {
			$value = ($_tempAttachsDb[$value['tid']]) ? array_merge($value, $_tempAttachsDb[$value[tid]]) : $value;
			$reslut[] = $value;
		}
		return $reslut;
	}
	
	function _getAttachs ($data) {
		if (!$data || !S::isArray($data)) return array();
		$result = $t = array();
		foreach ($data as $value) {
			$t['tid'] = $value['tid'];
			$t['aid'] = $value['aid'];
			$t['name'] = $value['name'];
			$t['attachurl'] = $value['attachurl'];
			$t['ifthumb'] = $value['ifthumb'];
			$result[$value[tid]][] =  $t;
		}
		return $this->_buildAttachs($result);
	}
	
	function _buildAttachs($data) {
		if (!$data || !S::isArray($data)) return array();
		$result = $t = array();
		foreach ($data as $key=>$value) {
			$t['imgTotal'] = count($value);
			$t['firstImgName'] = $value[0]['name'];
			$t['firstImgId'] = $value[0]['aid'];
			$attachurl = $value[0]['attachurl'];
			$ifthumb = $value[0]['ifthumb'];
			$a_url = geturl($attachurl, 'show');
			$t['firstImgUrl'] = $this->_getAttachMiniUrl($attachurl, $ifthumb, $a_url[1]);
			
			if (!file_exists($t['firstImgUrl'])) {//不考虑远程
				$t['firstImgUrl'] = $this->_getAttachMiniUrl($attachurl, 1, $a_url[1]);
			}
			
			$result[$key] = $t;
		}
		return $result;
	}
	
	function _getAttachMiniUrl($path, $ifthumb, $where) {
		$dir = '';
		($ifthumb & 1) && $dir = 'thumb/';
		($ifthumb & 2) && $dir = 'thumb/mini/';
		if ($where == 'Local') return $GLOBALS['attachpath'] . '/' . $dir . $path;
		if ($where == 'Ftp') return $GLOBALS['db_ftpweb'] . '/' . $dir . $path;
		if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/' . $dir . $path;
		return $GLOBALS['attach_url'][0] . '/' . $dir . $path;
	}
	
	function _getPosts($postIds, $keywords, $tableName) {
		if (! $postIds || ! $tableName)
			return array ();
		$postsDao = $this->getPostsDao ();
		
		if (! ($result = $postsDao->getsByPostIds ( $postIds, $tableName ))) {
			return array ();
		}
		return $this->_buildThreads ( $result, $keywords );
	}
	
	function _buildUsers($users) {
		if (! $users)
			return false;
		$data = array ();
		require_once (R_P . 'require/showimg.php');
		// $genders = array (0 => "保密", 1 => "男", 2 => "女" );
		foreach ( $users as $t ) {
			list ( $t ['face'] ) = showfacedesign ( $t ['icon'], 1 );
			//$t ['gender'] = $genders [$t ['gender']];
			$t ['constellation'] = $this->_getConstellation ( $t ['bday'] );
			$t ['introduce'] = ($t ['introduce']) ? substrs ( $t ['introduce'], 25, 'N' ) : '暂无';
			$data [] = $t;
		}
		return $data;
	}
	
	function _buildDiarys($diarys, $keywords) {
		if (! $diarys)
			return false;
		$result = $dtids = array ();
		require_once (R_P . 'require/bbscode.php');
		foreach ( $diarys as $t ) {
			$t ['postdate'] = get_date ( $t ['postdate'], "Y-m-d H:i" );
			$t ['content'] = substrs ( strip_tags ( convert ( $t ['content'], array () ) ), 170 );
			foreach ( $keywords as $keyword ) {
				$keyword && $t ['subject'] = $this->_highlighting ( $keyword, $t ['subject'] );
				$keyword && $t ['content'] = $this->_highlighting ( $keyword, $t ['content'] );
			}
			$dtids [] = $t ['dtid'];
			$result [$t ['did']] = $t;
		}
		$diaryTypes = $this->_getDiaryTypes ( $dtids );
		$tmp = array ();
		foreach ( $result as $diary ) {
			$diary ['type'] = ($diary ['dtid'] > 0 && isset ( $diaryTypes [$diary ['dtid']] )) ? $diaryTypes [$diary ['dtid']] : '默认分类';
			$tmp [] = $diary;
		}
		return $tmp;
	}
	
	function _getDiaryTypes($dtids) {
		if (! $dtids)
			return false;
		$diarytypeDao = $this->getDiaryTypeDao ();
		$types = $diarytypeDao->getsByTdids ( $dtids );
		$tmp = array ();
		foreach ( $types as $t ) {
			$tmp [$t ['dtid']] = $t ['name'];
		}
		return $tmp;
	}
	
	function _getFilterDiaryByMysql() {
		$_sqlWhere = '';	
		$privacy = $this->_getDiaryPrivacy();
		if ($privacy) {
			$_sqlWhere .= " AND privacy IN(" . S::sqlImplode ( $privacy ) . ")";
		}
		return $_sqlWhere;
	}
	
	/*日志权限 array(0,1,2) 全站可见，仅好友可见，仅自己可见*/
	function _getDiaryPrivacy() {
		$privacy = array();
		return ($this->_groupId == 3) ? array() : array(0);
	}
	
	function _buildForums($forums, $keywords) {
		if (! $forums)
			return array ();
		$result = array ();
		$keywords = ($keywords) ? explode ( ",", $keywords ) : array ();
		foreach ( $forums as $t ) {
			$t ['name'] = strip_tags ( $t ['name'] );
			$t ['descrip'] = strip_tags ( $t ['descrip'] );
			$t ['descrip'] = substrs ( $t ['descrip'], 100 );
			foreach ( $keywords as $keyword ) {
				$keyword && $t ['name'] = $this->_highlighting ( $keyword, $t ['name'] );
				$keyword && $t ['descrip'] = $this->_highlighting ( $keyword, $t ['descrip'] );
			}
			$t ['forumadmin'] = trim ( $t ['forumadmin'], "," );
			$t ['logo'] = $this->_getForumLogo ( $t );
			$result [] = $t;
		}
		return $result;
	}
	
	function _getForumLogo($forums) {
		global $db_indexfmlogo, $imgdir, $stylepath, $attachpath, $imgpath, $attachdir;
		if ($db_indexfmlogo == 1 && file_exists ( "$imgdir/$stylepath/forumlogo/$forums[fid].gif" )) {
			$forums ['logo'] = "$imgpath/$stylepath/forumlogo/$forums[fid].gif";
		} elseif ($db_indexfmlogo == 2) {
			if (! empty ( $forums ['logo'] ) && strpos ( $forums ['logo'], 'http://' ) === false && file_exists ( $attachdir . '/' . $forums ['logo'] )) {
				$forums ['logo'] = "$attachpath/$forums[logo]";
			}
		} else {
			$forums ['logo'] = '';
		}
		return $forums ['logo'] ? $forums ['logo'] : 'images/wind/old.gif';
	}
	
	function _buildGroups($groups, $keywords) {
		if (! $groups)
			return array ();
		$result = array ();
		$keywords = ($keywords) ? explode ( ",", $keywords ) : array ();
		foreach ( $groups as $group ) {
			$group ['id'] = $group ['id'];
			$group ['createtime'] = get_date ( $group ['createtime'], "Y-m-d H:i" );
			$group ['descrip'] = substrs ( stripWindCode ( strip_tags ( $group ['descrip'] ) ), 100 );
			$group ['credit'] = $this->_calculateCredit ( $group );
			$group ['sname'] = ($group ['sname']) ? $group ['sname'] : '末分类';
			foreach ( $keywords as $keyword ) {
				$keyword && $group ['cname'] = $this->_highlighting ( $keyword, $group ['cname'] );
				$keyword && $group ['descrip'] = $this->_highlighting ( $keyword, $group ['descrip'] );
			}
			if ($group ['cnimg']) {
				list ( $group ['cnimg'] ) = geturl ( "cn_img/" . $group ['cnimg'], 'lf' );
			} else {
				$group ['cnimg'] = "images/search/group.png";
			}
			$result [] = $group;
		}
		return $result;
	}
		
	/**
	 * 注意关联函数 apps/groups/lib/colony.class.php
	 * @param $info
	 */
	function _calculateCredit($info) {
		require_once R_P . 'require/functions.php';
		$info ['pnum'] -= $info ['tnum'];
		return CalculateCredit ( $info, L::config ( 'o_groups_upgrade', 'o_config' ) );
	}
	
	function _highlighting($pattern, $subject) {
		//return preg_replace('/(?<=[^\w=]|^)('.preg_quote($pattern,'/').')(?=[^\w=]|$)/si','<font color="red"><u>\\1</u></font>',$subject);
		if ($this->_version) {
			return function_exists('mb_eregi_replace') ? mb_eregi_replace($pattern, '<em class="s1">' . $pattern . '</em>', $subject) : str_ireplace ( $pattern, '<em class="s1">' . $pattern . '</em>', $subject );
		} else {
			return function_exists('mb_eregi_replace') ? mb_eregi_replace($pattern, '<em class="s1">' . $pattern . '</em>', $subject) : str_replace ( $pattern, '<em class="s1">' . $pattern . '</em>', $subject );
		}
	}
	
	function _checkPage($page, $perpage, $total) {
		$totalPages = ceil ( $total / $perpage );
		return ($page < 0) ? 1 : (($page > $totalPages) ? $totalPages : $page);
	}
	
	function _getConstellation($bday) {
		list ( $y, $month, $day ) = explode ( '-', $bday );
		$signs = array (array ("20" => "水瓶座" ), array ("19" => "双鱼座" ), array ("21" => "白羊座" ), array ("20" => "金牛座" ), array ("21" => "双子座" ), array ("22" => "巨蟹座" ), array ("23" => "狮子座" ), array ("23" => "处女座" ), array ("23" => "天秤座" ), array ("24" => "天蝎座" ), array ("22" => "射手座" ), array ("22" => "摩羯座" ) );
		$k = $month < 1 ? 1 : $month - 1;
		list ( $sign_start, $sign_name ) = each ( $signs [$k] );
		if ($day < $sign_start)
			list ( $sign_start, $sign_name ) = each ( $signs [($month - 2 < 0) ? $month = 11 : $month -= 2] );
		return $sign_name;
	}
	
	function _searchForums($keywords, $page = 1, $perpage = 20) {
		if (! ($keywords = $this->_checkKeywordCondition ( $keywords ))) {
			return array (false, false );
		}
		$page = $page > 1 ? intval($page) : 1;
		$perpage = intval($perpage);
		$offset = intval ( ($page - 1) * $perpage );
		
		$sql = "";
		if ($keywords) {
			$sql .= " AND name LIKE " . S::sqlEscape ( '%' . $keywords . '%' );
		}
		$sql .= $this->_getFilterForums();		
		$forumsDao = $this->getForumsDao ();
		if (! ($total = $forumsDao->countSearch ( "SELECT COUNT(*) as total FROM pw_forums WHERE 1". $sql))) {
			return array (false, false );
		}
		$sql .= "  LIMIT " . $offset . "," . $perpage;
		$result = $forumsDao->getSearch ( "SELECT * FROM pw_forums WHERE 1". $sql );
		return array ($total, $this->_buildForums ( $result, $keywords ) );
	}
	
	function _getFilterForums() {
		$_sqlWhere = '';
		if ($this->_groupId != 3) {
			$_sqlWhere .= " AND f_type = 'forum'";
		}
		return $_sqlWhere;
	}
	
	function _searchGroups($keywords, $page = 1, $perpage = 20) {
		if (! ($keywords = $this->_checkKeywordCondition ( $keywords ))) {
			return array (false, false );
		}
		$page = $page > 1 ? $page : 1;
		$offset = intval ( ($page - 1) * $perpage );
		$colonysDao = $this->getColonysDao ();
		if (! ($total = $colonysDao->countSearch ( $keywords ))) {
			return array (false, false );
		}
		$result = $colonysDao->getSearch ( $keywords, $offset, $perpage );
		return array ($total, $this->_buildGroups ( $result, $keywords ) );
	}
	/**
	 * 新鲜事表DAO
	 * @return unknown_type
	 */
	function getWeiboDao() {
		static $sWeiboDao;
		if (! $sWeiboDao) {
			$sWeiboDao = L::loadDB ('weibo_content','sns');
		}
		return $sWeiboDao;
	}
	/**
	 * 日志表DAO
	 * @return unknown_type
	 */
	function getDiarysDao() {
		static $sDiarysDao;
		if (! $sDiarysDao) {
			$sDiarysDao = L::loadDB ( 'diary', 'diary' );
		}
		return $sDiarysDao;
	}
	/**
	 * 帖子表DAO
	 * @return unknown_type
	 */
	function getThreadsDao() {
		static $sThreadsDao;
		if (! $sThreadsDao) {
			$sThreadsDao = L::loadDB ( 'threads', 'forum' );
		}
		return $sThreadsDao;
	}
	/**
	 * 回复表DAO
	 * @return unknown_type
	 */
	function getPostsDao() {
		static $sPostsDao;
		if (! $sPostsDao) {
			$sPostsDao = L::loadDB ( 'posts', 'forum' );
		}
		return $sPostsDao;
	}
	/**
	 * 版块表DAO
	 * @return unknown_type
	 */
	function getForumsDao() {
		static $sForumsDao;
		if (! $sForumsDao) {
			$sForumsDao = L::loadDB ( 'forums', 'forum' );
		}
		return $sForumsDao;
	}
	/**
	 * 群组表DAO
	 * @return unknown_type
	 */
	function getColonysDao() {
		static $sColonysDao;
		if (! $sColonysDao) {
			$sColonysDao = L::loadDB ( 'colonys', 'colony' );
		}
		return $sColonysDao;
	}
	/**
	 * 日志分类表DAO
	 * @return unknown_type
	 */
	function getDiaryTypeDao() {
		static $sDiaryTypeDao;
		if (! $sDiaryTypeDao) {
			$sDiaryTypeDao = L::loadDB ( 'diarytype', 'diary' );
		}
		return $sDiaryTypeDao;
	}
	
	/**
	 * 用户表DAO
	 * @return unknown_type
	 */
	function getUsersDao() {
		static $sUserDao;
		if (! $sUserDao) {
			$sUserDao = L::loadDB ( 'Members', 'user' );
		}
		return $sUserDao;
	}
	
	/**
	 * 搜索缓存表DAO
	 * @return unknown_type
	 */
	function getSchcacheDao() {
		static $sSchcacheDao;
		if (! $sSchcacheDao) {
			$sSchcacheDao = L::loadDB ( 'schcache', 'search' );
		}
		return $sSchcacheDao;
	}
	
	/**
	 * 搜索附件表DAO
	 * @return unknown_type
	 */
	function getAttachsDao() {
		static $sAttachsDao;
		if (! $sAttachsDao) {
			$sAttachsDao = L::loadDB ( 'attachs', 'forum' );
		}
		return $sAttachsDao;
	}
	
	/**
	 * @return PW_UserService
	 */
	function _getUserService() {
		return L::loadClass ( 'UserService', 'user' );
	}
}