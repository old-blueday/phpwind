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
 		$this->startTime = $day == null?$GLOBALS['tdtime'] : pwEscape(PwStrtoTime($day)); 
 		$this->endTime = $day == null?$GLOBALS['timestamp'] : pwEscape(PwStrtoTime($day) + 86400);
 	}
 	
	/**
	 * 全站日发帖量
	 * 
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getPostsOfDay($day = null){
		$this->_setTimestamp($day);
 		$posts = $this->db->get_value("SELECT COUNT(*) FROM `pw_threads` WHERE `postdate` BETWEEN $this->startTime AND $this->endTime");
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
 			$replies += $this->db->get_value("SELECT COUNT(*) FROM `{$rt[0]}` WHERE `postdate` BETWEEN $this->startTime AND $this->endTime");
		}
 		return new ApiResponse($replies);
 	}
 	
  	
	/**
	 * 每日新增会员数
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getRegistersOfDay($day = null){
 		$this->_setTimestamp($day);
 		$registers = $this->db->get_value("SELECT COUNT(*) FROM `pw_members` WHERE `regdate` BETWEEN $this->startTime AND $this->endTime");
 		return new ApiResponse($registers);
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
			$userinbbs = $this->db->get_value("SELECT COUNT(*) FROM `pw_online` WHERE uid!='0'");
		} 		
 		return new ApiResponse($userinbbs);
 	}

 	
	/**
	 * 在线会员数
	 * @param string 'today | yesterday'
	 * @return array 
	 */
 	function getOnlineUsersOfDay($day = 'today'){
 		//TODO
 		return new ApiResponse(array(
 			0	=> 1,
 			1	=> 1,
 			23	=> 1
 		));
 	}

	/**
	 * 在线访客数
	 * @param string 'today | yesterday'
	 * @return array 
	 */
 	function getVisitorOfDay($day = 'today'){
 		//TODO
 		return new ApiResponse(array(
 			0	=> 1,
 			1	=> 1,
 			23	=> 1
 		));
 	}


	/**
	 * 整站会员性别分布
	 * @param null
	 * @return array 0=保密，1=男，2=女
	 */
 	function getSexDistribution(){
 		$gender = array(0,0,0);
 		$query = $this->db->query('SELECT gender,COUNT(*) AS count FROM `pw_members` GROUP BY `gender`');
 		while ($rt = $this->db->fetch_array($query)) {
 			$gender[$rt['gender']] = $rt['count'];
 		}
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
 		$query = $this->db->query("SELECT $currentYear - year(bday) AS `yeardiff`,COUNT(*) AS count FROM `pw_members` GROUP BY `yeardiff`");
 		while ($rt = $this->db->fetch_array($query)) {
 			if($rt['yeardiff'] == $currentYear){
 				$ages[0] += $rt['count'];
 			}elseif($rt['yeardiff'] > 50){
				$ages['50+'] += $rt['count'];
			}elseif($rt['yeardiff'] >= 40){
				$ages['40-50'] += $rt['count'];
			}elseif($rt['yeardiff'] >= 30){
				$ages['30-40'] += $rt['count'];
			}elseif($rt['yeardiff'] >= 20){
				$ages['20-30'] += $rt['count'];
			}else{
				$ages['0-20'] += $rt['count'];
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
		$query = $this->db->query("SELECT t.fid,f.name,COUNT(t.tid) AS count FROM `pw_threads` AS t LEFT JOIN `pw_forums` AS f ON t.fid=f.fid WHERE t.`postdate` BETWEEN $this->startTime AND $this->endTime GROUP BY t.`fid`");
		while ($rt = $this->db->fetch_array($query)) {
			$posts[] = array($rt['fid'],$rt['name'],$rt['count']);
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
 			$rQuery = $this->db->query("SELECT p.fid,f.name,COUNT(p.tid) AS count FROM `{$rt[0]}` AS p LEFT JOIN `pw_forums` AS f ON p.fid=f.fid  WHERE p.`postdate` BETWEEN $this->startTime AND $this->endTime GROUP BY p.`fid`");
 			while ($rResult = $this->db->fetch_array($rQuery)) {
 				$replies[$rResult['fid']][0] = $rResult['fid'];
 				$replies[$rResult['fid']][1] = $rResult['name'];
 				$replies[$rResult['fid']][2] += $rResult['count'];
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
 		$posts = $this->db->get_value("SELECT COUNT(*) FROM `pw_weibo_content` WHERE `postdate` BETWEEN $this->startTime AND $this->endTime");
 		return new ApiResponse($posts);
 	}


	/**
	 * 每日微博转发量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboTransmitsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$transmits = $this->db->get_value("SELECT COUNT(*) FROM `pw_weibo_content` WHERE `type`=1 AND `postdate` BETWEEN $this->startTime AND $this->endTime");
 		return new ApiResponse($transmits);
 	}


	/**
	 * 每日微博评论量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboCommentsOfDay($day = null){
 		$this->_setTimestamp($day);
 		$comments = $this->db->get_value("SELECT COUNT(*) FROM `pw_weibo_comment` WHERE `postdate` BETWEEN $this->startTime AND $this->endTime");
 		return new ApiResponse($comments);
 	}

 	

	/**
	 * 每日关注量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getAttentionOfDay($day = null){
 		$this->_setTimestamp($day);
 		$attentions = $this->db->get_value("SELECT COUNT(*) FROM `pw_attention` WHERE `joindate` BETWEEN $this->startTime AND $this->endTime");
 		return new ApiResponse($attentions);
 	}


	/**
	 * 每日新浪微博绑定量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboToSinaOfDay($day = null){
 		return new ApiResponse(1);
 	}


	/**
	 * 每日新浪微博发布量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboPostsFromSinaOfDay($day = null){
 		return new ApiResponse(1);
 	}

 	
	/**
	 * 每日新浪微博转发量
	 * @param string $day 'Y-m-d'
	 * @return int
	 */
 	function getWeiboTransmitsFromSinaOfDay($day = null){
 		return new ApiResponse(1);
 	}

 	
	/**
	 * (30天)微博活跃用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfActiveusersFromWeibo($days = 30, $num = 10){
 		return new ApiResponse(array(
 			array(1,'username1','icon1',1),
 			array(2,'username2','icon2',1)
 		));
 	}


 	
	/**
	 * (30天)被关注用户top10
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfFollowedUser($days = 30, $num = 10){
 		return new ApiResponse(1);
 	}

 	
	/**
	 * (30天)微博来源
	 * @param int $days
	 * @return array
	 */
 	function getSourceDistributingFromWeibo($days = 30){
 		return new ApiResponse(array(
 			'weibo'	=> 1,
 			'diary'	=> 1,
 			'thread'=> 1,
 			'sina'	=> 1
 		));
 	}


	/**
	 * 微博用户特征(性别)
	 * @param null
	 * @return array 0=保密，1=男，2=女
	 */
 	function getSexDistributionFromWeiboUser(){
 		return new ApiResponse(array(
 			0	=> 1,
 			1	=> 1,
 			2	=> 1
 		));
 	}
 	
	/**
	 * 每日日志发布量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getDiaryPostsOfDay($day = null){
 		return new ApiResponse(1);
 	}

 	
	/**
	 * 每日日志评论量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getDiaryCommentsOfDay($day = null){
 		return new ApiResponse(1);
 	}

 	
	/**
	 * (30天)日志用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfDiaryUser($days = 30, $num = 10){
 		return new ApiResponse(array(
 			array(1,'username1','icon1',1),
 			array(2,'username2','icon2',1)
 		));
 	}

 	
	/**
	 * (30天)日志评论用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfDiaryUserInComment($days = 30, $num = 10){
 		return new ApiResponse(array(
 			array(1,'username1','icon1',1),
 			array(2,'username2','icon2',1)
 		));
 	}

 	
 	 	
	/**
	 * 每日照片发布量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getPhotoPostsOfDay($day = null){
 		return new ApiResponse(1);
 	}
 	
 	 	
	/**
	 * 每日照片评论量
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */
 	function getPhotoCommentsOfDay($day = null){
 		return new ApiResponse(1);
 	}

 	 	
	/**
	 * (30天)上传照片用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfPhotoUser($days = 30, $num = 10){
 		return new ApiResponse(array(
 			array(1,'username1','icon1',1),
 			array(2,'username2','icon2',1)
 		));
 	}

  	 	
	/**
	 * (30天)照片评论用户top10（数据量）
	 * @param int $days
	 * @param int $num
	 * @return array
	 */
 	function getTopOfPhotoUserInComment($days = 30, $num = 10){
 		return new ApiResponse(array(
 			array(1,'username1','icon1',1),
 			array(2,'username2','icon2',1)
 		));
 	}

 	
 }
 