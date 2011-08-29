<?php
/**
 * phpwind数据统计
 * 
 * @author pw team, Sep 20, 2010
 * @copyright 2003-2010 phpwind.net. All rights reserved.
 * @version 1.0
 * @package api
 */

!defined('P_W') && exit('Forbidden');
 
 class Statistics {
 	
 	var $base;
	var $db;
	var $startTime;
	var $endTime;
	var $day;
	var $num;
	
 	function Statistics($base) {
		$this->base = $base;
		$this->db = $base->db;
 	}

	/**
	 * 设置查询的起始时间戳
	 * 
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function _setTimestamp($day){
 		/*未指定日期，默认为头一天数据*/
 		$this->startTime = $day == null?($GLOBALS['tdtime']-86400) : PwStrtoTime($day); 
 		$this->endTime = $day == null?($GLOBALS['tdtime']-1) : PwStrtoTime($day) + 86400 - 1;
 	}
 	
	/**
	 * 设置查询的最近x days,limit y
	 * 
	 * @param int $days 30
	 * @param int $num 10
	 * @return int 
	 */
 	function _setDaysTops($days = 30,$num = 10){
 		$days = intval($days);
 		$num = intval($num);
 		($days <= 0 || $days > 50) && $days = 30;
 		($num <= 0 || $num > 50) && $num = 10;
 		$this->num = $num;
 		$this->startTime = $GLOBALS['tdtime'] - 86400 * ($days - 1);
 		$this->endTime = $GLOBALS['timestamp'];
 	}
 	
	/**
	 * 处理日期
	 * 
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function _getdate($day){
 		$this->day = $day == null? get_date($GLOBALS['timestamp'],'Y-m-d') : get_date(PwStrtoTime($day),'Y-m-d');
 	}

	/**
	 * 处理头像
	 * 
	 * @param array $icon
	 * @return strin url 
	 */
 	function _getAvatar($icon){
 		$iconArray = showfacedesign($icon,true);
 		return $iconArray[0];
 	}
 	
 	/**
 	 * 站点app安装情况(房产,商家导航,新浪微博,实名认证,云搜索)
 	 * @return array
 	 */
 	function getAppInstallState() {
 		$states = array('house' => 0, 'dianpu' => 0, 'sinaweibo' => 0, 'authentication' => 0, 'cloudsearch' => 0);
 		$query = $this->db->query("SELECT db_name, db_value FROM pw_config WHERE db_name IN ('db_modes', 'db_authstate')");
 		while ($rs = $this->db->fetch_array($query)) {
 			if ($rs['db_name'] == 'db_modes') {
 				$result = unserialize($rs['db_value']);
 				isset($result['house']) && $result['house']['ifopen'] == 1 && $states['house'] = 1;
 				isset($result['dianpu']) && $result['dianpu']['ifopen'] == 1 && $states['dianpu'] = 1;
 			} else {
 				$states['authentication'] = (int) $rs['db_value'];
 			}
 		}
 		$platformApiClient = $this->_getPlatformApiClient();
		$sinaweiboResponse = $this->_jsonDecode($platformApiClient->get('weibo.stat.siteinfo'));
		$states['sinaweibo'] = (int) $sinaweiboResponse['isOpen'];
 		return new ApiResponse($states);
 	}
 	
 	/**
 	 * 站点app安装时间(房产,商家导航,新浪微博,实名认证,云搜索)
 	 * @return array
 	 */
 	function getAppInstallTime() {
 		$installTime = array('house' => 0, 'dianpu' => 0, 'sinaweibo' => 0, 'authentication' => 0, 'cloudsearch' => 0);
 		$query = $this->db->query("SELECT name, updatetime FROM pw_statistics_daily WHERE name IN ('houseinstalltime', 'dianpuinstalltime', 'authinstalltime') and typeid = 0 and date = '0000-00-00' and value = 0");
 		while ($rs = $this->db->fetch_array($query)) {
 			switch ($rs['name']) {
 				case 'houseinstalltime' : 
 					$installTime['house'] = (int) $rs['updatetime'];
 					break;
 				case 'dianpuinstalltime' :
 					$installTime['dianpu'] = (int) $rs['updatetime'];
 					break;
 				case 'authinstalltime' :
 					$installTime['authentication'] = (int) $rs['updatetime'];
 					break;
 			}
 		}
 		$platformApiClient = $this->_getPlatformApiClient();
		$sinaweiboResponse = $this->_jsonDecode($platformApiClient->get('weibo.stat.siteinfo'));
		!empty($sinaweiboResponse['openTime']) && $installTime['sinaweibo'] = strtotime($sinaweiboResponse['openTime']);
 		return new ApiResponse($installTime);
 	}
 	
 	/**
 	 * 某日发帖人数
 	 * @param string $day 'Y-m-d'
 	 * @return int
 	 */
 	function getPostUserNumOfDay($day = null) {
 		$this->_setTimestamp($day);
 		$postUserNum = 0;
 		if ($this->db->server_info() > '4.1') {
 			$postUserNum = intval($this->db->get_value('SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_threads WHERE postdate BETWEEN ' . S::sqlEscape($this->startTime) . ' AND ' . S::sqlEscape($this->endTime) . ' GROUP BY authorid) AS tmp'));
 		} else {
 			$this->db->query('CREATE TEMPORARY TABLE tmp_postusers SELECT COUNT(*) FROM pw_threads WHERE postdate BETWEEN ' . S::sqlEscape($this->startTime) . ' AND ' . S::sqlEscape($this->endTime) . ' GROUP BY authorid');
 			$postUserNum = intval($this->db->get_value('SELECT COUNT(*) FROM tmp_postusers'));
 		}
 		return new ApiResponse($postUserNum);
 	}
 	
 	/**
 	 * 注册会员的等级分布
 	 * @return array
 	 */
 	function getUserLevelDistribution() {
 		$userLevel = array();
 		$groupQuery = $this->db->query('SELECT grouptitle,gid FROM pw_usergroups WHERE gptype = "member"');
 		while ($groupResult = $this->db->fetch_array($groupQuery)) {
 			$userLevel[$groupResult['gid']] = array('groupname' => $groupResult['grouptitle'], 'count' => 0);
 		}
 		$query = $this->db->query('SELECT COUNT(*) AS count, memberid FROM pw_members WHERE groupid = -1 GROUP BY memberid');
 		while ($result = $this->db->fetch_array($query)) {
 			$userLevel[$result['memberid']]['count'] = $result['count'];
 		}
 		return new ApiResponse($userLevel);
 	}
 	
 	/**
 	 * 日发帖会员数分布(20帖以下,20-50帖,50-80帖,80-100帖,100帖以上)
 	 * @param string $day 'Y-m-d'
 	 * @return array
 	 */
 	function getPostUsersDistributionOfDay($day = null) {
 		$this->_setTimestamp($day);
 		$postUsersDistribution = array('under20' => 0, '20to50' => 0, '50to80'=> 0, '80to100' => 0, 'above100' => 0);
 		$query = $this->db->query('SELECT COUNT(*) AS count FROM pw_threads WHERE postdate BETWEEN ' . S::sqlEscape($this->startTime) . ' AND ' . S::sqlEscape($this->endTime) . ' GROUP BY authorid');
 		while ($result = $this->db->fetch_array($query)) {
 			if ($result['count'] > 100) {
 				$postUsersDistribution['above100'] += 1;
 			} elseif ($result['count'] >= 80) {
 				$postUsersDistribution['80to100'] += 1;
 			} elseif ($result['count'] >= 50) {
 				$postUsersDistribution['50to80'] += 1;
 			} elseif ($result['count'] >= 20) {
 				$postUsersDistribution['20to50'] += 1;
 			} else {
 				$postUsersDistribution['under20'] += 1;
 			}
 		}
 		return new ApiResponse($postUsersDistribution);
 	}
 	
 	/**
 	 * 站点使用的版本号
 	 * @return string
 	 */
 	function getSiteVersion() {
 		return new ApiResponse(WIND_VERSION);
 	}
 	

 	
	/**
	 * 某日登录会员数
	 * 
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getLoginsOfDay($day = null){
 		$this->_getdate($day);
 		$logins = 0;
 		if($this->day < get_date($GLOBALS['timestamp'],'Y-m-d')){
 			$logins = $this->_getDailyStatisticsByName('login',$this->day);
 		}else{
 			$logins = intval($this->db->get_value(
 				"SELECT COUNT(`typeid`) FROM `pw_statistics_daily` 
 					WHERE `name`='login' AND `date`=".S::sqlEscape($this->day)." AND `typeid`>0
 					GROUP BY `date`"
 			));
 		}
 		return new ApiResponse($logins);
 	}
 	
	/**
	 * 获取版块信息
	 * 
	 * @return array 
	 */
 	function getForums(){
 		$forums = array();
 		//* include_once D_P .'data/bbscache/forum_cache.php';
 		extract(pwCache::getData( D_P .'data/bbscache/forum_cache.php', false));
 		if(is_array($forum)){
 			/*forums 列表*/
 			foreach ($forum as $k=>$v) {
 				if($v['type'] != 'forum')
 					continue;
 				$forums[$v['fup']]['forums'][$v['fid']] = array(
 					'fid' => $v['fid'],
 					'name' => $v['name']
 				);
 			}
 			
 			/*版块category*/
 			foreach($forums as $k=>$v){
 				isset($forum[$k]) && $forums[$k]['name'] = $forum[$k]['name'];
 			}
 		}
 		return new ApiResponse($forums);
 	}
 	
	/**
	 * 全站日发帖量
	 * 
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getPostsOfDay($day = null){
		$this->_setTimestamp($day);
 		$posts = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_threads` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 		));
 		return new ApiResponse($posts);
 	}
 	
  	
	/**
	 * 全站日回复量
	 * 
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getReplysOfDay($day = null){
		$this->_setTimestamp($day);
 		$postTables = array();
 		$replies = 0;
 		$query = $this->db->query('SHOW TABLES LIKE \'pw_posts%\'');
 		$pattern = '/'.preg_quote($this->db->dbpre).'posts\d*$/';
 		while ($rt = $this->db->fetch_array($query,MYSQL_NUM)) {
 			if(!preg_match($pattern,$rt[0])){
 				continue;
 			}
 			$replies += intval($this->db->get_value(
 				"SELECT COUNT(*) FROM `{$rt[0]}` 
 				WHERE `postdate` BETWEEN ".S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 			));
		}
 		return new ApiResponse($replies);
 	}
 	
	/**
	 * (30天)标签使用top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfTags($days = 30, $num = 10){
 		$this->_setDaysTops($days,$num);
		$startTime = get_date($this->startTime, 'Y-m-d');
 		$tags  = array();
 		$query = $this->db->query(
 			"SELECT t.tagname,SUM(s.value) AS count FROM pw_statistics_daily s 
			LEFT JOIN pw_tags t ON s.typeid=t.tagid 
 			WHERE s.`name`='tag' AND s.`date`>".S::sqlEscape($startTime)."  
 			GROUP BY s.`typeid` 
 			ORDER BY `count` DESC 
 			LIMIT $this->num"
 		);
 		while ($rt = $this->db->fetch_array($query)) {
			$tags[] = array(
				$rt['tagname'],
				$rt['count']
			);
 		}
 		return new ApiResponse($tags);
 	}
  	
	/**
	 * 每日新增会员数
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getRegistersOfDay($day = null){
 		return new ApiResponse($this->_getDailyStatisticsByName('register',$day));
 	}

 	
	/**
	 * 当前在线会员数
	 * @param null
	 * @return int 
	 */
 	function getOnlineUser(){
		$userinbbs = 0;
		if (empty($GLOBALS['db_online'])) {
			include_once (D_P . 'data/bbscache/olcache.php');
		} else {
			//* $userinbbs = intval($this->db->get_value("SELECT COUNT(*) FROM `pw_online` WHERE uid!='0'"));
			
			$onlineService = L::loadClass('OnlineService', 'user');
			$userinbbs = $onlineService->countOnlineUser();				
		} 		
 		return new ApiResponse($userinbbs);
 	}

 	
	/**
	 * 以小时段统计的项目
	 * @param string $statisticsName
	 * @param string $day 'today | yesterday'
	 * @return array 
	 */
 	function _getHourlyStatitsticsByName($statisticsName,$day){
 		$date = $day == 'yesterday'?get_date($GLOBALS['tdtime']	- 86400,'Y-m-d') : get_date($GLOBALS['tdtime'],'Y-m-d');
 		$query = $this->db->query(
 			'SELECT `typeid`,`value` FROM `pw_statistics_daily` WHERE name='.S::sqlEscape($statisticsName).' AND `date`='.S::sqlEscape($date)
 		);
 		$statistics = array();
 	 	while ($rt = $this->db->fetch_array($query)) {
 			$statistics[$rt['typeid']] = intval($rt['value']);
 		}
 		
 		//补全需返回的各时间段的数据
 		$hours = $day == 'yesterday'? 24: get_date($GLOBALS['timestamp'],'G') + 1;
 		for($i = 0;$i<$hours;$i++){
 			!isset($statistics[$i]) && $statistics[$i] = 0;
 		}
 		ksort($statistics);
 		return $statistics;
 	}
 	
	/**
	 * 按天统计的项目，如每日新增会员数
	 * @param string $statisticsName
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function _getDailyStatisticsByName($statisticsName,$day,$typeid=0){
 		$this->_getdate($day);
 		$typeid = intval($typeid);
 		$statistics = intval($this->db->get_value(
 			'SELECT `value` FROM `pw_statistics_daily` WHERE name='.S::sqlEscape($statisticsName).' AND `date`='.S::sqlEscape($this->day) . ' AND `typeid`='.S::sqlEscape($typeid)
 		));
 		return $statistics;
 	}
 	
	/**
	 * 普通项目统计，如性别、年龄分布
	 * @param string $statisticsName
	 * @param array  $typeids
	 * @return array 
	 */
 	function _getCommonStatisticsByName($statisticsName,$typeids = array()){
 		$statistics = array();
 		$query = $this->db->query(
 			'SELECT `typeid`,`value` FROM `pw_statistics_daily` WHERE name='.S::sqlEscape($statisticsName)
 		);
 		while ($rt = $this->db->fetch_array($query)){
 			$statistics[$rt['typeid']] = $rt['value'];
 		}
 		foreach($typeids as $v){
 			!isset($statistics[$v]) && $statistics[$v] = 0;
 		}
 		return $statistics;
 	}
 	
	/**
	 * 在线会员数
	 * @param string $day 'today | yesterday'
	 * @return array 
	 */
 	function getOnlineUsersOfDay($day = 'today'){
		return new ApiResponse($this->_getHourlyStatitsticsByName('userinbbs',$day));
 	}

	/**
	 * 在线访客数
	 * @param string $day 'today | yesterday'
	 * @return array 
	 */
 	function getVisitorOfDay($day = 'today'){
		return new ApiResponse($this->_getHourlyStatitsticsByName('guestinbbs',$day));
 	}

	/**
	 * 整站会员性别分布
	 * @param null
	 * @return array 0=保密，1=男，2=女
	 */
 	function getSexDistribution(){
 		$gender = $this->_getCommonStatisticsByName('sexdistribution',array(0,1,2));
 		return new ApiResponse($gender);
 	}

	/**
	 * 整站会员年龄分布
	 * @param null
	 * @return array
	 */
 	function getAgeDistribution(){
 		$ages = array(
 			0	=> 0,/*未知*/
 			'0-20'	=> 0,
 			'20-30'	=> 0,
 			'30-40'	=> 0,
 			'40-50'	=> 0,
 			'50+'	=> 0
 		);
 		$currentYear = get_date($GLOBALS['timestamp'],'Y');
 		$statistics = $this->_getCommonStatisticsByName('agedistribution');
 		foreach($statistics as $k=>$v){
 			$age = $currentYear - $k;
 		 	if($age == $currentYear){
 				$ages[0] += $v;
 			}elseif($age > 50){
				$ages['50+'] += $v;
			}elseif($age >= 40){
				$ages['40-50'] += $v;
			}elseif($age >= 30){
				$ages['30-40'] += $v;
			}elseif($age >= 20){
				$ages['20-30'] += $v;
			}else{
				$ages['0-20'] += $v;
			}
 		}
 		return new ApiResponse($ages);
 	}

	/**
	 * 各版块日发帖量
	 * @param string $day 'Y-m-d'
	 * @return array
	 */
 	function getForumPostsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$posts = array();
		$query = $this->db->query(
			'SELECT t.fid,f.name,HOUR(FROM_UNIXTIME(t.`postdate`)) AS `hour`,COUNT(t.tid) AS `count` FROM `pw_threads` AS t 
			LEFT JOIN `pw_forums` AS f USING(`fid`) 
			WHERE t.`postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
			GROUP BY t.`fid`,`hour`');
		while ($rt = $this->db->fetch_array($query)) {
			$posts[$rt['hour']][$rt['fid']] = array($rt['name'],$rt['count']);
		}
 		return new ApiResponse($posts);
 	}

	/**
	 * 各版块日回复量
	 * @param string $day 'Y-m-d'
	 * @return array
	 */
 	function getForumReplysOfDay($day = null){
		$this->_setTimestamp($day);
 		$postTables = array();
 		$replies = array();
 		$query = $this->db->query('SHOW TABLES LIKE \'pw_posts%\'');
 		$pattern = '/'.preg_quote($this->db->dbpre).'posts\d*$/';
 		while ($rt = $this->db->fetch_array($query,MYSQL_NUM)) {
 			if(!preg_match($pattern,$rt[0])){
 				continue;
 			}
 			$rQuery = $this->db->query(
 				"SELECT p.fid,f.name,HOUR(FROM_UNIXTIME(p.`postdate`)) AS `hour`,COUNT(p.tid) AS count FROM `{$rt[0]}` AS p 
 				LEFT JOIN `pw_forums` AS f ON p.fid=f.fid 
 				WHERE p.`postdate` BETWEEN ".S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
 				GROUP BY p.`fid`,`hour`'
 			);
 			while ($rResult = $this->db->fetch_array($rQuery)) {
 				$replies[$rResult['hour']][$rResult['fid']][0] = $rResult['name'];
 				$replies[$rResult['hour']][$rResult['fid']][1] += $rResult['count'];
 			}
 			
		}
 		return new ApiResponse(array_values($replies));
 	}


	/**
	 * 每日微博发布量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboPostsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$posts = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_weibo_content` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 		));
 		return new ApiResponse($posts);
 	}


	/**
	 * 每日微博转发量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboTransmitsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$transmits = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_weibo_content` 
 			WHERE `type`=1 AND `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 		));
 		return new ApiResponse($transmits);
 	}


	/**
	 * 每日微博评论量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboCommentsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$comments = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_weibo_comment` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 		));
 		return new ApiResponse($comments);
 	}

 	

	/**
	 * 每日关注量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getAttentionOfDay($day = null){
 		$this->_setTimestamp($day);
 		$attentions = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_attention` 
 			WHERE `joindate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 		));
 		return new ApiResponse($attentions);
 	}


	/**
	 * 每日新浪微博绑定量
	 * @param string $day 'Y-m-d'
	 * @return int
	 //TODO 暂不支持 按时间查
 	function getWeiboToSinaOfDay($day = null){
 		return new ApiResponse(1);
 	}
	*/

	/**
	 * 每日新浪微博发布量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboPostsFromSinaOfDay($day = null){
 		$weiboService = L::loadClass('Weibo', 'sns');
 		$this->_setTimestamp($day);
 		$posts = intval($this->db->get_value(
 			'SELECT COUNT(`mid`) FROM `pw_weibo_content` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).'  
 			AND `type`='.S::sqlEscape($weiboService->_map['sinaweibo']).' 
 			GROUP BY `type` 
 			LIMIT 1'
 		));
 		return new ApiResponse($posts);
 	}

 	
	/**
	 * 每日新浪微博转发量
	 * @param string $day 'Y-m-d'
	 * @return int
	 
 	function getWeiboTransmitsFromSinaOfDay($day = null){
 		$weiboService = L::loadClass('Weibo', 'sns');
 		$this->_setTimestamp($day);
 		$transmits = $this->db->get_value(
 			"SELECT COUNT()"
 		);
 		return new ApiResponse(1);
 	}
	*/
 	
	/**
	 * (30天)微博活跃用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfActiveusersFromWeibo($days = 30, $num = 10){
 		require_once R_P.'require/showimg.php';
 		$this->_setDaysTops($days,$num);
 		$query = $this->db->query(
 			'SELECT m.`username`,m.`icon`,m.`uid`,COUNT(c.`mid`) AS `count` FROM `pw_weibo_content` AS c
 				LEFT JOIN `pw_members` AS m USING(`uid`) 
 				WHERE c.`postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
 				GROUP BY c.`uid` 
 				ORDER BY `count` DESC
				LIMIT '.intval($this->num)
 		);
 		$weibo = array();
 		while ($rt = $this->db->fetch_array($query)) {
 			$weibo[] = array(
 				$rt['uid'],
 				$rt['username'],
 				$this->_getAvatar($rt['icon']),
 				$rt['count'],
 			);
 		}
 		return new ApiResponse($weibo);
 	}


 	
	/**
	 * (30天)被关注用户top10
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfFollowedUser($days = 30, $num = 10){
 		require_once R_P.'require/showimg.php';
 		$this->_setDaysTops($days,$num);
 		$query = $this->db->query(
 			'SELECT m.`username`,m.`icon`,m.`uid`,COUNT(a.`uid`) AS `count` FROM `pw_attention` AS a
 				LEFT JOIN `pw_members` AS m ON a.`friendid`=m.`uid` 
 				WHERE a.`joindate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
 				GROUP BY a.`friendid` 
 				ORDER BY `count` DESC 
 				LIMIT '.intval($this->num)
 		);
 		$attentions = array();
 		while ($rt = $this->db->fetch_array($query)) {
 			$attentions[] = array(
 				$rt['uid'],
 				$rt['username'],
 				$this->_getAvatar($rt['icon']),
 				$rt['count'],
 			);
 		}
 		return new ApiResponse($attentions);
 	}

 	
	/**
	 * (30天)微博来源
	 * @param int $days
	 * @return array
	 */
 	function getSourceDistributingFromWeibo($days = 30){
 		$weiboService = L::loadClass('Weibo', 'sns');
 		$this->_setDaysTops($days,count($weiboService->_map));
 		$query = $this->db->query(
 			'SELECT `type`,COUNT(`mid`) AS `count` FROM `pw_weibo_content` 
 				WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).'  
 				GROUP BY `type`
 				ORDER BY `type` DESC'
 			);
 		
 		$weibo = array();
 		$weiboTypes = array_flip($weiboService->_map);
 		while ($rt = $this->db->fetch_array($query)) {
 			if(!isset($weiboTypes[$rt['type']])){
 				$weibo['unknow'] += $rt['count'];
 				continue;
 			}
 			$weibo[$weiboTypes[$rt['type']]] = $rt['count'];
 		}
 		return new ApiResponse($weibo);
 	}


	/**
	 * 微博用户特征(性别)
	 * @param null
	 * @return array 0=保密，1=男，2=女
	*/
 	function getSexDistributionFromWeiboUser(){
 		//TODO
 		/*
 		$query = $this->db->query(
 			"SELECT DISTINCT `uid` FROM `pw_weibo_content`"
 		);
 		return new ApiResponse(array(
 			0	=> 1,
 			1	=> 1,
 			2	=> 1
 		));
 		*/
 		$this->getSexDistribution();//暂时用全站用户数据
 	}

 	
	/**
	 * 每日日志发布量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getDiaryPostsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$posts = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_diary` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)
 		));
 		return new ApiResponse($posts);
 	}

 	
	/**
	 * 每日日志评论量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getDiaryCommentsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$comments = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_comment` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
 			AND `type`=\'diary\''
 		));
 		return new ApiResponse($comments);
 	}

 	
	/**
	 * (30天)日志用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfDiaryUser($days = 30, $num = 10){
		require_once R_P.'require/showimg.php';
		$this->_setDaysTops($days,$num);
		$users = array();
		$query = $this->db->query(
			'SELECT m.`username`,m.`icon`,d.`uid`,COUNT(d.`did`) AS `count` FROM `pw_diary` AS d
				LEFT JOIN `pw_members` AS m ON d.`uid`=m.`uid`
				WHERE d.`postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
				GROUP BY d.`uid` 
				ORDER BY `count` DESC 
				LIMIT '.intval($this->num)
		);
		while ($rt = $this->db->fetch_array($query)) {
			$users[] = array(
				$rt['uid'],
				$rt['username'],
				$this->_getAvatar($rt['icon']),
				$rt['count']
			);
		}
 		return new ApiResponse($users);
 	}

 	
	/**
	 * (30天)日志评论用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfDiaryUserInComment($days = 30, $num = 10){
 		require_once R_P.'require/showimg.php';
 		$this->_setDaysTops($days,$num);
 		$users = array();
 		$query = $this->db->query(
 			'SELECT m.`username`,d.`uid`,d.`did`,d.`subject`,COUNT(c.`id`) AS `count` FROM `pw_comment` AS c 
 			LEFT JOIN `pw_diary` AS d ON d.`did`=c.`typeid` 
 			LEFT JOIN `pw_members` AS m ON d.`uid`=m.`uid` 
 			WHERE c.`postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime).' 
 			GROUP BY c.`typeid` 
 			ORDER BY `count` DESC 
 			LIMIT '.intval($this->num)
 		);
 		while ($rt = $this->db->fetch_array($query)) {
			$users[] = array(
				$rt['did'],
				$rt['subject'],
				$rt['uid'],
				$rt['username'],
				$this->_getAvatar($rt['icon']),
				$rt['count']
			);
 		}
 		return new ApiResponse($users);
 	}

 	
 	 	
	/**
	 * 每日照片发布量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getPhotoPostsOfDay($day = null){
 		$this->_getdate($day);
 		$posts = intval($this->db->get_value(
 			"SELECT SUM(`value`) FROM `pw_statistics_daily` 
 				WHERE `name`='photouser' AND `date`=".S::sqlEscape($this->day)
 		)); 		
 		return new ApiResponse($posts);
 	}
 	
 	 	
	/**
	 * 每日照片评论量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getPhotoCommentsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$comments = intval($this->db->get_value(
 			'SELECT COUNT(*) FROM `pw_comment` 
 			WHERE `postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)." 
 			AND `upid`=0 AND `type`='photo'"
 		));
 		return new ApiResponse($comments);
 	}

 	 	
	/**
	 * (30天)上传照片用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfPhotoUser($days = 30, $num = 10){
 		require_once R_P.'require/showimg.php';
 		$this->_setDaysTops($days,$num);
 		$users = array();
 		/*
 		if($this->db->server_info() > '4.1'){
	 		$query = $this->db->query(
	 			"SELECT m.`uid`,m.`username`,m.`icon`,SUM(tmp.`count`) AS `count` FROM 
	 				(SELECT COUNT(`pid`) AS `count`,`aid` FROM `pw_cnphoto` 
	 				WHERE `uptime` BETWEEN $this->startTime AND $this->endTime 
	 				GROUP BY `aid`) AS tmp 
	 			LEFT JOIN `pw_cnalbum` AS c USING(`aid`)  
	 			LEFT JOIN `pw_members` AS m ON m.uid=c.ownerid 
	 			WHERE c.`atype`=0
	 			GROUP BY c.`ownerid`
	 			ORDER BY `count` DESC 
	 			LIMIT $this->num"
	 		);
 		}else{
 			$this->db->query(
 				"CREATE TEMPORARY TABLE tmp_getTopOfPhotoUser
 					SELECT COUNT(`pid`) AS `count`,`aid` FROM `pw_cnphoto` 
	 				WHERE `uptime` BETWEEN $this->startTime AND $this->endTime 
	 				GROUP BY `aid`"
 			);
 			$query = $this->db->query(
				"SELECT m.`uid`,m.`username`,m.`icon`,SUM(tmp.`count`) AS `count` FROM 
	 			`tmp_getTopOfPhotoUser` AS tmp 
	 			LEFT JOIN `pw_cnalbum` AS c USING(`aid`)  
	 			LEFT JOIN `pw_members` AS m ON m.uid=c.ownerid 
	 			WHERE c.`atype`=0
	 			GROUP BY c.`ownerid` 
	 			ORDER BY `count` DESC 
	 			LIMIT $this->num"
 			);
 		}
 		*/
 		$startDate = S::sqlEscape(get_date($this->startTime,'Y-m-d'));
 		$endDate = S::sqlEscape(get_date($this->endTime,'Y-m-d'));
 		$query = $this->db->query(
 		"SELECT s.`typeid` AS `uid`,s.`value` AS `count`,m.`username`,m.`icon`
 			FROM `pw_statistics_daily` AS s LEFT JOIN `pw_members` AS m ON s.`typeid`=m.`uid`  
 			WHERE s.`name`='photouser' AND s.`date` BETWEEN $startDate AND $endDate"
 		);
 	 	while ($rt = $this->db->fetch_array($query)) {
			$users[] = array(
				$rt['uid'],
				$rt['username'],
				$this->_getAvatar($rt['icon']),
				$rt['count']
			);
 		}		
 		return new ApiResponse($users);
 	}

  	 	
	/**
	 * (30天)照片评论用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfPhotoUserInComment($days = 30, $num = 10){
 		//TODO
 		require_once R_P.'require/showimg.php';
 		$this->_setDaysTops($days,$num);
 		$photos = array();
 		$query = $this->db->query(
 			'SELECT m.`icon`,m.`username`,m.`uid`,p.`pintro`,p.`pid`,p.`path`,COUNT(c.`id`) AS `count` FROM `pw_comment` AS c
				LEFT JOIN `pw_cnphoto` AS p ON p.`pid`=c.`typeid` 
				LEFT JOIN `pw_cnalbum` AS a ON a.`aid`=p.`aid` 
				LEFT JOIN `pw_members` AS m ON (m.`uid`=a.`ownerid`)  
 				WHERE c.`postdate` BETWEEN '.S::sqlEscape($this->startTime).' AND '.S::sqlEscape($this->endTime)." 
 				AND c.`type`='photo' AND a.`atype`=0 
 				GROUP BY c.`typeid` 
 				ORDER BY `count` DESC 
 				LIMIT ".intval($this->num)
 		);
 	 	while ($rt = $this->db->fetch_array($query)) {
 	 		$path = geturl($rt['path']);
 	 		$path = is_array($path) && isset($path[0])? $path[0]:''; 
			$photos[] = array(
				$rt['pid'],
				//$GLOBALS['db_bbsurl'].'/'.$GLOBALS['db_attachname'].'/'.$rt['path'],
				$path,
				$rt['pintro'],
				$rt['uid'],
				$rt['username'],
				$this->_getAvatar($rt['icon']),
				$rt['count']
			);
 		}	 		
 		return new ApiResponse($photos);
 	}
 	
 	/**
	 * @return PlatformApiClient
	 */
	function _getPlatformApiClient() {
		static $client = null;
		if (null === $client) {
			global $db_sitehash, $db_siteownerid;
			L::loadClass('client', 'utility/platformapisdk', false);
			$client = new PlatformApiClient($db_sitehash, $db_siteownerid);
		}
		return $client;
	}
	
	function _jsonDecode ($response) {
		require_once(R_P . 'api/class_json.php');
		$json = new Services_JSON(true);
		return $json->decode($response);
	}
 }
 