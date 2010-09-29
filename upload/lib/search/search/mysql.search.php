<?php
!function_exists('readover') && exit('Forbidden');
class Search_Mysql extends Search_Base {
	
	var $_mysqlMethod     = "OR";
	var $_mysqlSort       = "DESC";
	var $_cacheTime       = 1800; //数据库缓存时间
	var $_mysqlLimit      = 1000;
	var $_mysqlCache      = 0;
	var $_mysqlFilterIds  = null;
	var $_expand          = array();
	var $_primaryKey      = 'tid';
	
	function Search_Mysql(){
		global $db_filterids;
		parent::Search_Base();
		$this->_mysqlLimit = $this->_maxResult;
		$this->_mysqlFilterIds = ($db_filterids) ? explode(",",$db_filterids) : false;
	}
	function checkUserLevel(){
		return $this->_checkUserLevel();
	}
	function checkWaitSegment(){
		return $this->_checkWaitSegment();
	}
	/**
	 * 搜索帖子
	 * @param string $keywords
	 * @param int $range
	 * @param string $userNames
	 * @param int $starttime
	 * @param int $endtime
	 * @param array $forumIds
	 * @param int $page
	 * @param int $perpage
	 * @return array(total,threads)
	 */
	function searchThreads($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20,$expand=array()){
		$this->_getExpand($expand);
		$this->_primaryKey = ($range == 3) ? 'pid' : 'tid';
		if($this->_mysqlCache){
			return $this->searchThreadsWithCache($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage,$expand);
		}else{
			return $this->searchThreadsNoCache($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage,$expand);
		}
	}
	/**
	 * 带有缓存的方式搜索帖子
	 */
	function searchThreadsWithCache($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20){
		$cacheKey = 'search|'.trim($keywords).'|'.trim($range).'|'.trim($userNames).'|'.trim($starttime).'|'.trim($endtime).'|'.serialize($forumIds).'|search';
		$cacheKey = md5($cacheKey);
		$start = ($page -1) * $perpage;
		$schCacheDao = $this->getSchcacheDao();
		$result = array();
		if(!($result = $schCacheDao->getBySchline($cacheKey)) || $this->_timestamp - $result['schtime'] > $this->_cacheTime || $start >= $result['total'] ){
			list($total,$searchs) = $this->searchThreadsWithCondition($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage);
			if(!$total){
				return array(false,false);
			}
			$total = ( $total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
			$ids = '';
			foreach($searchs as $search){
				$ids .= ($ids) ? ",".$search[$this->_primaryKey] : $search[$this->_primaryKey];
			}
			if($result){
				$schCacheDao->delete($result['sid']);
			}
			$fieldData = array(
						'sorderby'	=> '',
						'schline'	=> $cacheKey,
						'schtime'	=> $this->_timestamp,
						'total'		=> $total,
						'schedid'	=> $ids);
			$schCacheDao->insert($fieldData);
			list($result['total'],$result['schedid']) = array($total,$ids);
		}
		$ids = $result['schedid'];
		$ids = (is_array($ids)) ? $ids : explode(",",$ids);
		$ids = array_slice($ids,$start,$perpage);
		$searchs = ($range == 3) ? $this->_getPosts($ids,$keywords,$this->_getPostsTable()) : $this->_getThreads($ids,$keywords);
		return ($searchs) ? array($result['total'],$searchs) : array(false,false);
	}
	/**
	 * 没有缓存的方式搜索帖子
	 */
	function searchThreadsNoCache($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20){
		list($total,$searchs) = $this->searchThreadsWithCondition($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage);
		if(!$total){
			return array(false,false);
		}
		$ids = '';
		foreach($searchs as $search){
			$ids .= ($ids) ? ",".$search[$this->_primaryKey] : $search[$this->_primaryKey];
		}
		$total = ( $total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
		$searchs = ($range == 3) ? $this->_getPosts($ids,$keywords,$this->_getPostsTable()) : $this->_getThreads($ids,$keywords);
		return array($total,$searchs);
	}

	function searchThreadsWithCondition($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20){
		list($keywords,$users,$starttime,$endtime) = $this->_checkThreadConditions($keywords,$userNames,$starttime,$endtime);
		if( $userNames && !$users ) return false;
		//if(!$keywords) return false;
		$page     = $page>1 ? $page : 1;
		$offset   = intval(($page - 1) * $perpage);
		$keywords = ($keywords) ? explode(" ",$keywords) : '';
		switch ($range){
			case 1:
				list($total,$result) = $this->_searchThreadsWithSubject($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage);
				break;
			case 2:
				list($total,$result) = $this->_searchThreadsWithSubjectAndContent($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage);
				break;
			//case 3:
			//	list($total,$result) = $this->_searchThreadsWithPosts($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage);
			//	break;
			case 3:
				list($total,$result) = $this->_searchPosts($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage);
		}
		//是否开启结果组装
		//return ($total) ? array($total,$this->_buildThreads($result,$keywords)) : array(false,false);
		return ($total) ? array($total,$result) : array(false,false);
	}
	/**
	 * 重载
	 * (non-PHPdoc)
	 * @see lib/search/Search_Base#_checkThreadConditions($keywords, $userNames, $starttime, $endtime)
	 */
	function _checkThreadConditions($keywords,$userNames="",$starttime="",$endtime=""){
		$keywords = $this->_checkKeywordCondition($keywords);
		$keywords = ($keywords) ? $keywords : '';
		$users = array();
		($userNames ) ? $users = $this->_checkUserCondition($userNames) : 0;
		list($starttime,$endtime) = $this->_checkTimeNodeCondition($starttime,$endtime);
		return array($keywords,$users,$starttime,$endtime);
	}
	
	/**
	 * 按帖子标题搜索帖子
	 * @param string $keywords
	 * @param array $users array('uid'=>'username')
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return array(搜索总数,搜索数组)
	 */
	function _searchThreadsWithSubject($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ( ".$sql;
				$sql .= " t.subject LIKE ".pwEscape('%'.$keyword.'%');
			}
			$sql .= " ) ";
		}
		if($forumIds){
			$forumIds = (is_array($forumIds)) ? $forumIds : array($forumIds);
			$sql .= " AND t.fid IN(".pwImplode($forumIds).")";
		}
		if($this->_mysqlFilterIds){
			$sql .= " AND t.fid NOT IN(".pwImplode($this->_mysqlFilterIds).")";
		}
		if($users){
			$sql .= " AND t.authorid IN(".pwImplode(array_keys($users)).")";
		}
		if($starttime){
			$sql .= " AND t.postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND t.postdate < ".pwEscape($endtime);
		}
		$sql .= " AND t.ifcheck = 1 AND t.fid != 0 ";
		$sql .= " ORDER BY t.postdate ".$this->_mysqlSort." ";
		$threadsDao = $this->getThreadsDao();
		if(!($total = $threadsDao->countSearch("SELECT COUNT(*) as total FROM pw_threads t WHERE 1 ".$sql))){
			return false;
		}
		if($this->_mysqlCache){
			$sql .= "  LIMIT ".$this->_mysqlLimit;
		}else{
			$sql .= "  LIMIT ".$offset.",".$perpage;
		}
		$result =  $threadsDao->getSearch("SELECT t.tid FROM pw_threads t WHERE 1 ".$sql);
		return array($total,$result);
	}
	/**
	 * 按主题标题与内容搜索帖子
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return unknown_type
	 */
	function _searchThreadsWithSubjectAndContent($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql  = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ( ".$sql;
				$sql .= " ( t.subject LIKE ".pwEscape('%'.$keyword.'%')." OR tm.content LIKE ".pwEscape('%'.$keyword.'%').") ";
			}
			$sql .= " ) ";
		}
		if($forumIds){
			$forumIds = (is_array($forumIds)) ? $forumIds : array($forumIds);
			$sql .= " AND t.fid IN(".pwImplode($forumIds).")";
		}
		if($this->_mysqlFilterIds){
			$sql .= " AND t.fid NOT IN(".pwImplode($this->_mysqlFilterIds).")";
		}
		if($users){
			$sql .= " AND t.authorid IN(".pwImplode(array_keys($users)).")";
		}
		if($starttime){
			$sql .= " AND t.postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND t.postdate < ".pwEscape($endtime);
		}
		$sql .= " AND t.ifcheck = 1  AND t.fid != 0 ";
		$sql .= " ORDER BY t.postdate ".$this->_mysqlSort." ";
		$threadsDao = $this->getThreadsDao();
		$tmsgsTable = $this->_getTmsgsTable();
		if(!($total = $threadsDao->countSearch("SELECT COUNT(*) as total FROM pw_threads t LEFT JOIN $tmsgsTable tm ON tm.tid=t.tid WHERE 1 ".$sql))){
			return false;
		}
		if($this->_mysqlCache){
			$sql .= "  LIMIT ".$this->_mysqlLimit;
		}else{
			$sql .= "  LIMIT ".$offset.",".$perpage;
		}
		// get all t.tid,t.fid,t.subject,t.postdate,tm.content
		$result =  $threadsDao->getSearch("SELECT t.tid FROM pw_threads t LEFT JOIN  $tmsgsTable tm ON tm.tid=t.tid WHERE 1 ".$sql);
		return array($total,$result);
	}
	/**
	 * 按回复标题与内容搜索帖子
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return unknown_type
	 */
	function _searchThreadsWithPosts($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql  = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ( ".$sql;
				$sql .= " ( t.subject LIKE ".pwEscape('%'.$keyword.'%')." OR p.content LIKE ".pwEscape('%'.$keyword.'%').") ";
			}
			$sql .= " ) ";
		}
		if($forumIds){
			$forumIds = (is_array($forumIds)) ? $forumIds : array($forumIds);
			$sql .= " AND t.fid IN(".pwImplode($forumIds).")";
		}
		if($this->_mysqlFilterIds){
			$sql .= " AND t.fid NOT IN(".pwImplode($this->_mysqlFilterIds).")";
		}
		if($users){
			$sql .= " AND t.authorid IN(".pwImplode(array_keys($users)).")";
		}
		if($starttime){
			$sql .= " AND t.postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND t.postdate < ".pwEscape($endtime);
		}
		$sql .= " AND t.ifcheck = 1  AND t.fid != 0 ";
		$sql .= " ORDER BY t.postdate ".$this->_mysqlSort." ";
		$threadsDao = $this->getThreadsDao();
		$postTable = $this->_getPostsTable();
		if(!($total = $threadsDao->countSearch("SELECT DISTINCT t.tid,COUNT(*) as total FROM pw_threads t LEFT JOIN $postTable p ON t.tid=p.tid WHERE 1 ".$sql))){
			return false;
		}
		if($this->_mysqlCache){
			$sql .= "  LIMIT ".$this->_mysqlLimit;
		}else{
			$sql .= "  LIMIT ".$offset.",".$perpage;
		}
		$result =  $threadsDao->getSearch("SELECT DISTINCT t.tid,t.tid FROM pw_threads t LEFT JOIN $postTable p ON t.tid=p.tid WHERE 1 ".$sql);
		return array($total,$result);
	}
	
	/**
	 * 按回复标题与内容搜索帖子
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return unknown_type
	 */
	function _searchPosts($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql  = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ( ".$sql;
				$sql .= " ( p.subject LIKE ".pwEscape('%'.$keyword.'%')." OR p.content LIKE ".pwEscape('%'.$keyword.'%').") ";
			}
			$sql .= " ) ";
		}
		if($forumIds){
			$forumIds = (is_array($forumIds)) ? $forumIds : array($forumIds);
			$sql .= " AND p.fid IN(".pwImplode($forumIds).")";
		}
		if($this->_mysqlFilterIds){
			$sql .= " AND p.fid NOT IN(".pwImplode($this->_mysqlFilterIds).")";
		}
		if($users){
			$sql .= " AND p.authorid IN(".pwImplode(array_keys($users)).")";
		}
		if($starttime){
			$sql .= " AND p.postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND p.postdate < ".pwEscape($endtime);
		}
		$sql .= " AND p.ifcheck = 1 ";
		$sql .= " ORDER BY p.postdate ".$this->_mysqlSort." ";
		$threadsDao = $this->getThreadsDao();
		$postTable = $this->_getPostsTable();
		if(!($total = $threadsDao->countSearch("SELECT DISTINCT p.pid,COUNT(*) as total FROM $postTable p WHERE 1 ".$sql))){
			return false;
		}
		if($this->_mysqlCache){
			$sql .= "  LIMIT ".$this->_mysqlLimit;
		}else{
			$sql .= "  LIMIT ".$offset.",".$perpage;
		}
		$result =  $threadsDao->getSearch("SELECT p.pid FROM $postTable p WHERE 1 ".$sql);
		return array($total,$result);
	}
	
	function _getTmsgsTable(){
		$num =  ( intval($this->_expand['ttable']) > 0 ) ? intval($this->_expand['ttable']) : '';
		return 'pw_tmsgs'.$num;
	}
	
	function _getPostsTable(){
		$num =  ( intval($this->_expand['ptable']) > 0 ) ? intval($this->_expand['ptable']) : '';
		return 'pw_posts'.$num;
	}
	
	function _getExpand($expand){
		global $db_plist,$db_tlist;
		$this->_expand['ptable'] = min(intval($expand['ptable']),count($db_plist));
		$this->_expand['ttable'] = min(intval($expand['ttable']),count($db_tlist));
	}
	
	/*********************************************************************/
	function searchUsers($keywords,$page=1,$perpage=20){
		if(!($keywords = $this->_checkKeywordCondition($keywords))){
			return array(false,false);
		}
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		
		$membersDao = $this->_getMembersDao();
		if(!($total = $membersDao->countSearch($keywords))){
			return array(false,false);
		}
		$result = $membersDao->getSearch($keywords,$offset,$perpage);
		
		return array($total,$this->_buildUsers($result));
	}
	/*********************************************************************/
	function searchDiarys($keywords,$range,$userNames="",$starttime="",$endtime="",$page=1,$perpage=20){
		list($keywords,$users,$starttime,$endtime) = $this->_checkThreadConditions($keywords,$userNames,$starttime,$endtime);
		if(!$keywords || ($userNames && !$users) ) return false;
		$userIds  = ($users) ? array_keys($users) : array();
		$page     = $page>1 ? $page : 1;
		$offset   = intval(($page - 1) * $perpage);
		$keywords = explode(" ",$keywords);
		switch ($range){
			case 1:
				list($total,$result) = $this->_searchDiarysWithSubject($keywords,$users,$starttime,$endtime,$offset,$perpage);
				break;
			case 2:
				list($total,$result) = $this->_searchDiarysWithContent($keywords,$users,$starttime,$endtime,$offset,$perpage);
				break;
			case 3:
				list($total,$result) = $this->_searchDiarysWithSubjectAndContent($keywords,$users,$starttime,$endtime,$offset,$perpage);
				break;
		}
		return ($total) ? array($total,$this->_buildDiarys($result,$keywords)) : array(false,false);
	}
	/**
	 * 根椐日志标题搜索
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return unknown_type
	 */
	function _searchDiarysWithSubject($keywords,$users,$starttime,$endtime,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ".$sql;
				$sql .= " subject LIKE ".pwEscape('%'.$keyword.'%');
			}
		}
		if($users){
			$sql .= " AND username IN(".pwImplode($users).")";
		}
		if($starttime){
			$sql .= " AND postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND postdate < ".pwEscape($endtime);
		}
		$sql .= " ORDER BY postdate ".$this->_mysqlSort." ";
		$diarysDao = $this->getDiarysDao();
		if(!($total = $diarysDao->countSearch("SELECT COUNT(*) as total FROM pw_diary WHERE 1 ".$sql))){
			return false;
		}
		$sql .= "  LIMIT ".$offset.",".$perpage;
		$result =  $diarysDao->getSearch("SELECT * FROM pw_diary WHERE 1 ".$sql);
		return array($total,$result);
	}
	/**
	 * 根椐日志内容搜索
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return unknown_type
	 */
	function _searchDiarysWithContent($keywords,$users,$starttime,$endtime,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ".$sql;
				$sql .= " content LIKE ".pwEscape('%'.$keyword.'%');
			}
		}
		if($users){
			$sql .= " AND username IN(".pwImplode($users).")";
		}
		if($starttime){
			$sql .= " AND postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND postdate < ".pwEscape($endtime);
		}
		$sql .= " ORDER BY postdate ".$this->_mysqlSort." ";
		$diarysDao = $this->getDiarysDao();
		if(!($total = $diarysDao->countSearch("SELECT COUNT(*) as total FROM pw_diary WHERE 1 ".$sql))){
			return false;
		}
		$sql .= "  LIMIT ".$offset.",".$perpage;
		$result =  $diarysDao->getSearch("SELECT * FROM pw_diary WHERE 1 ".$sql);
		return array($total,$result);
	}
	/**
	 * 根椐日志标题和内容搜索
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $offset
	 * @param int $perpage
	 * @return unknown_type
	 */
	function _searchDiarysWithSubjectAndContent($keywords,$users,$starttime,$endtime,$offset,$perpage){
		$sql = "";
		if($keywords){
			foreach($keywords as $keyword){
				$sql  = ($sql) ? $sql." ".$this->_mysqlMethod." " : " AND ".$sql;
				$sql .= " ( subject LIKE ".pwEscape('%'.$keyword.'%')." OR content LIKE ".pwEscape('%'.$keyword.'%').") ";
			}
		}
		if($users){
			$sql .= " AND username IN(".pwImplode($users).")";
		}
		if($starttime){
			$sql .= " AND postdate > ".pwEscape($starttime);
		}
		if($endtime){
			$sql .= " AND postdate < ".pwEscape($endtime);
		}
		$sql .= " ORDER BY postdate ".$this->_mysqlSort." ";
		$diarysDao = $this->getDiarysDao();
		if(!($total = $diarysDao->countSearch("SELECT COUNT(*) as total FROM pw_diary WHERE 1 ".$sql))){
			return false;
		}
		$sql .= "  LIMIT ".$offset.",".$perpage;
		$result =  $diarysDao->getSearch("SELECT * FROM pw_diary  WHERE 1 ".$sql);
		return array($total,$result);
	}
	/*********************************************************************/
	function searchForums($keywords,$page=1,$perpage=20){
		return $this->_searchForums($keywords,$page,$perpage);
	}
	
	function searchGroups($keywords,$page=1,$perpage=20){
		return $this->_searchGroups($keywords,$page,$perpage);
	}
	
	/**
	 * 用户表DAO
	 * @return PW_MembersDB
	 */
	function _getMembersDao(){
		return L::loadDB('Members', 'user');
	}
}