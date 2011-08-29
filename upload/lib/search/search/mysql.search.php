<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
class Search_Mysql extends Search_Base {
	
	var $_mysqlMethod = "OR";
	var $_mysqlSort = "DESC";
	var $_cacheTime = 1800; //数据库缓存时间
	var $_mysqlLimit = 1000;
	var $_mysqlCache = 0;
	var $_mysqlFilterIds = null;
	var $_expand = array ();
	var $_primaryKey = 'tid';
	
	function Search_Mysql() {
		global $db_filterids;
		parent::Search_Base ();
		$this->_mysqlLimit = $this->_maxResult;
		$this->_mysqlFilterIds = ($db_filterids) ? explode ( ",", $db_filterids ) : false;
	}
	function checkUserLevel() {
		return $this->_checkUserLevel ();
	}
	function checkWaitSegment() {
		return $this->_checkWaitSegment ();
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
	function searchThreads($keywords, $range, $userNames = "", $starttime = "", $endtime = "", $forumIds = array(), $page = 1, $perpage = 20, $expand = array()) {
		$this->_getExpand ( $expand );
		$this->_primaryKey = ($range == 3) ? 'pid' : 'tid';
		if ($this->_mysqlCache) {
			return $this->searchThreadsWithCache ( $keywords, $range, $userNames, $starttime, $endtime, $forumIds, $page, $perpage, $expand );
		} else {
			return $this->searchThreadsNoCache ( $keywords, $range, $userNames, $starttime, $endtime, $forumIds, $page, $perpage, $expand );
		}
	}
	/**
	 * 带有缓存的方式搜索帖子
	 */
	function searchThreadsWithCache($keywords, $range, $userNames = "", $starttime = "", $endtime = "", $forumIds = array(), $page = 1, $perpage = 20) {
		$cacheKey = 'search|' . trim ( $keywords ) . '|' . trim ( $range ) . '|' . trim ( $userNames ) . '|' . trim ( $starttime ) . '|' . trim ( $endtime ) . '|' . serialize ( $forumIds ) . '|search';
		$cacheKey = md5 ( $cacheKey );
		$start = ($page - 1) * $perpage;
		$schCacheDao = $this->getSchcacheDao ();
		$result = array ();
		if (! ($result = $schCacheDao->getBySchline ( $cacheKey )) || $this->_timestamp - $result ['schtime'] > $this->_cacheTime || $start >= $result ['total']) {
			list ( $total, $searchs, $keywords ) = $this->searchThreadsWithCondition ( $keywords, $range, $userNames, $starttime, $endtime, $forumIds, $page, $perpage );
			if (! $total) {
				return array (false, false );
			}
			$total = ($total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
			$ids = '';
			foreach ( $searchs as $search ) {
				$ids .= ($ids) ? "," . $search [$this->_primaryKey] : $search [$this->_primaryKey];
			}
			if ($result) {
				$schCacheDao->delete ( $result ['sid'] );
			}
			$fieldData = array ('sorderby' => '', 'schline' => $cacheKey, 'schtime' => $this->_timestamp, 'total' => $total, 'schedid' => $ids );
			$schCacheDao->insert ( $fieldData );
			list ( $result ['total'], $result ['schedid'] ) = array ($total, $ids );
		}
		$ids = $result ['schedid'];
		$ids = (is_array ( $ids )) ? $ids : explode ( ",", $ids );
		$ids = array_slice ( $ids, $start, $perpage );
		$searchs = ($range == 3) ? $this->_getPosts ( $ids, $keywords, $this->_getPostsTable () ) : $this->_getThreads ( $ids, $keywords );
		return ($searchs) ? array ($result ['total'], $searchs ) : array (false, false );
	}
	/**
	 * 没有缓存的方式搜索帖子
	 */
	function searchThreadsNoCache($keywords, $range, $userNames = "", $starttime = "", $endtime = "", $forumIds = array(), $page = 1, $perpage = 20) {
		list ( $total, $searchs, $keywords ) = $this->searchThreadsWithCondition ( $keywords, $range, $userNames, $starttime, $endtime, $forumIds, $page, $perpage );
		if (! $total) {
			return array (false, false );
		}
		$ids = '';
		foreach ( $searchs as $search ) {
			$ids .= ($ids) ? "," . $search [$this->_primaryKey] : $search [$this->_primaryKey];
		}
		$total = ($total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
		$searchs = ($range == 3) ? $this->_getPosts ( $ids, $keywords, $this->_getPostsTable () ) : $this->_getThreads ( $ids, $keywords );
		return array ($total, $searchs );
	}
	
	function searchThreadsWithCondition($keywords, $range, $userNames = "", $starttime = "", $endtime = "", $forumIds = array(), $page = 1, $perpage = 20) {
		list ( $keywords, $users, $starttime, $endtime ) = $this->_checkThreadConditions ( $keywords, $userNames, $starttime, $endtime );
		if ($userNames && ! $users)
			return false;
			//if(!$keywords) return false;
		$page = $page > 1 ? $page : 1;
		$offset = intval ( ($page - 1) * $perpage );
		$keywords = ($keywords) ? explode ( " ", $keywords ) : '';
		switch ($range) {
			case 1 :
				list ( $total, $result ) = $this->_searchThreadsWithSubject ( $keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage );
				break;
			case 2 :
				list ( $total, $result ) = $this->_searchThreadsWithSubjectAndContent ( $keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage );
				break;
			//case 3:
			//	list($total,$result) = $this->_searchThreadsWithPosts($keywords,$users,$starttime,$endtime,$forumIds,$offset,$perpage);
			//	break;
			case 3 :
				list ( $total, $result ) = $this->_searchPosts ( $keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage );
		}
		$total = ($total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
		//是否开启结果组装
		//return ($total) ? array($total,$this->_buildThreads($result,$keywords)) : array(false,false);
		return ($total) ? array ($total, $result, $keywords ) : array (false, false, false );
	}
	/**
	 * 重载
	 * (non-PHPdoc)
	 * @see lib/search/Search_Base#_checkThreadConditions($keywords, $userNames, $starttime, $endtime)
	 */
	function _checkThreadConditions($keywords, $userNames = "", $starttime = "", $endtime = "") {
		$keywords = $this->_checkKeywordCondition ( $keywords );
		$keywords = ($keywords) ? $keywords : '';
		$users = array ();
		($userNames) ? $users = $this->_checkUserCondition ( $userNames ) : 0;
		list ( $starttime, $endtime ) = $this->_checkTimeNodeCondition ( $starttime, $endtime );
		return array ($keywords, $users, $starttime, $endtime );
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
	function _searchThreadsWithSubject($keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " t.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' );
			}
			$sql .= " ) ";
		}
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		if ($this->_mysqlFilterIds) {
			$sql .= " AND t.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}
		if ($users) {
			$sql .= " AND t.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}
		if ($starttime) {
			$sql .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND t.ifcheck = 1 AND t.fid != 0 ";
		$sql .= " ORDER BY t.postdate " . $this->_mysqlSort . " ";
		$threadsDao = $this->getThreadsDao ();
		if (! ($total = $threadsDao->countSearch ( "SELECT COUNT(*) as total FROM pw_threads t WHERE 1 " . $sql ))) {
			return false;
		}
		if ($this->_mysqlCache) {
			$sql .= "  LIMIT " . $this->_mysqlLimit;
		} else {
			$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		}
		$result = $threadsDao->getSearch ( "SELECT t.tid FROM pw_threads t WHERE 1 " . $sql );
		return array ($total, $result );
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
	function _searchThreadsWithSubjectAndContent($keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " ( t.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . " OR tm.content LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . ") ";
			}
			$sql .= " ) ";
		}
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		if ($this->_mysqlFilterIds) {
			$sql .= " AND t.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}
		if ($users) {
			$sql .= " AND t.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}
		if ($starttime) {
			$sql .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND t.ifcheck = 1  AND t.fid != 0 ";
		$sql .= " ORDER BY t.postdate " . $this->_mysqlSort . " ";
		$threadsDao = $this->getThreadsDao ();
		$tmsgsTable = $this->_getTmsgsTable ();
		if (! ($total = $threadsDao->countSearch ( "SELECT COUNT(*) as total FROM pw_threads t LEFT JOIN $tmsgsTable tm ON tm.tid=t.tid WHERE 1 " . $sql ))) {
			return false;
		}
		if ($this->_mysqlCache) {
			$sql .= "  LIMIT " . $this->_mysqlLimit;
		} else {
			$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		}
		// get all t.tid,t.fid,t.subject,t.postdate,tm.content
		$result = $threadsDao->getSearch ( "SELECT t.tid FROM pw_threads t LEFT JOIN  $tmsgsTable tm ON tm.tid=t.tid WHERE 1 " . $sql );
		return array ($total, $result );
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
	function _searchThreadsWithPosts($keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " ( t.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . " OR p.content LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . ") ";
			}
			$sql .= " ) ";
		}
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		if ($this->_mysqlFilterIds) {
			$sql .= " AND t.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}
		if ($users) {
			$sql .= " AND t.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}
		if ($starttime) {
			$sql .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND t.ifcheck = 1  AND t.fid != 0 ";
		$sql .= " ORDER BY t.postdate " . $this->_mysqlSort . " ";
		$threadsDao = $this->getThreadsDao ();
		$postTable = $this->_getPostsTable ();
		if (! ($total = $threadsDao->countSearch ( "SELECT DISTINCT t.tid,COUNT(*) as total FROM pw_threads t LEFT JOIN $postTable p ON t.tid=p.tid WHERE 1 " . $sql ))) {
			return false;
		}
		if ($this->_mysqlCache) {
			$sql .= "  LIMIT " . $this->_mysqlLimit;
		} else {
			$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		}
		$result = $threadsDao->getSearch ( "SELECT DISTINCT t.tid,t.tid FROM pw_threads t LEFT JOIN $postTable p ON t.tid=p.tid WHERE 1 " . $sql );
		return array ($total, $result );
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
	function _searchPosts($keywords, $users, $starttime, $endtime, $forumIds, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " ( p.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . " OR p.content LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . ") ";
			}
			$sql .= " ) ";
		}
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND p.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		if ($this->_mysqlFilterIds) {
			$sql .= " AND p.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}
		if ($users) {
			$sql .= " AND p.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}
		if ($starttime) {
			$sql .= " AND p.postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND p.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND p.ifcheck = 1 ";
		$sql .= " ORDER BY p.postdate " . $this->_mysqlSort . " ";
		$threadsDao = $this->getThreadsDao ();
		$postTable = $this->_getPostsTable ();
		if (! ($total = $threadsDao->countSearch ( "SELECT DISTINCT p.pid,COUNT(*) as total FROM $postTable p WHERE 1 " . $sql ))) {
			return false;
		}
		if ($this->_mysqlCache) {
			$sql .= "  LIMIT " . $this->_mysqlLimit;
		} else {
			$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		}
		$result = $threadsDao->getSearch ( "SELECT p.pid FROM $postTable p WHERE 1 " . $sql );
		return array ($total, $result );
	}
	
	function _getTmsgsTable() {
		$num = (intval ( $this->_expand ['ttable'] ) > 0) ? intval ( $this->_expand ['ttable'] ) : '';
		return 'pw_tmsgs' . $num;
	}
	
	function _getPostsTable() {
		$num = (intval ( $this->_expand ['ptable'] ) > 0) ? intval ( $this->_expand ['ptable'] ) : '';
		return 'pw_posts' . $num;
	}
	
	function _getExpand($expand) {
		global $db_plist, $db_tlist;
		$this->_expand ['ptable'] = min ( intval ( $expand ['ptable'] ), count ( $db_plist ) );
		$this->_expand ['ttable'] = min ( intval ( $expand ['ttable'] ), count ( $db_tlist ) );
	}
	
	/*********************************************************************/
	/**
	 * 搜索版块统计信息
	 * @param array $forumIds
	 * @param string $keywords
	 * @param int $range
	 * @param string $userNames
	 * @param int $starttime
	 * @param int $endtime
	 * @return array
	 */
	
	
	function searchForumGroups($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20,$expand=array()) {
		$result = $this->searchForumsGroupsWithCondition ($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage,$expand);
		return $this->_buildForumsTotal ($result);		
	}
	
	function searchForumsGroupsWithCondition($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20,$expand=array()) {
		list ( $keywords, $users, $starttime, $endtime ) = $this->_checkForumsGroupsConditions ( $keywords, $userNames, $starttime, $endtime );
		if ($userNames && ! $users)
			return false;
		$keywords = ($keywords) ? explode ( " ", $keywords ) : '';
		switch ($range) {
			case 1 :
				$result = $this->_searchForumsGroupsWithSubject ($keywords, $users, $starttime, $endtime, $forumIds);
				break;
			case 2 :
				$result = $this->_searchForumsGroupsWithSubjectAndContent ($keywords, $users, $starttime, $endtime, $forumIds);
				break;
			case 3 :
				$result = $this->_searchForumsGroupsWithPosts ($keywords, $users, $starttime, $endtime, $forumIds);
		}
		return $result;
	}


	function _checkForumsGroupsConditions($keywords, $userNames = "", $starttime = "", $endtime = "") {
		$keywords = $this->_checkKeywordCondition ( $keywords );
		$keywords = ($keywords) ? $keywords : '';
		$users = array ();
		($userNames) ? $users = $this->_checkUserCondition ( $userNames ) : 0;
		list ( $starttime, $endtime ) = $this->_checkTimeNodeCondition ( $starttime, $endtime );
		return array ($keywords, $users, $starttime, $endtime );
	}

	/**
	 * 按帖子标题搜索,统计版块数量
	 * @param array $forumIds
	 * @param string $keywords
	 * @param array $users array('uid'=>'username')
	 * @param int $starttime
	 * @param int $endtime
	 * @return array()
	 */
	function _searchForumsGroupsWithSubject($keywords, $users, $starttime, $endtime, $forumIds) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " t.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' );
			}
			$sql .= " ) ";
		}
		
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
			
		if ($this->_mysqlFilterIds) {
			$sql .= " AND t.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}

		if ($users) {
			$sql .= " AND t.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}

		if ($starttime) {
			$sql .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}

		if ($endtime) {
			$sql .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND t.ifcheck = 1 AND t.fid != 0 ";
		$sql .= " GROUP BY t.fid ";
		$sql .= " ORDER BY total DESC";
		$threadsDao = $this->getThreadsDao ();
		return $threadsDao->getSearch ( "SELECT t.fid, COUNT(*) as total FROM pw_threads t WHERE 1 " . $sql);
	}

	/**
	* 按标题与内容搜索帖子,统计版块数量
	* @param array $forumIds
	* @param string $keywords
	* @param array $users
	* @param int $starttime
	* @param int $endtime
	* @return array()
	*/
	function _searchForumsGroupsWithSubjectAndContent($keywords, $users, $starttime, $endtime, $forumIds) {
		$sql = "";

		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " ( t.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . " OR tm.content LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . ") ";
			}
			$sql .= " ) ";
		}

		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		
		if ($this->_mysqlFilterIds) {
			$sql .= " AND t.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}
		
		if ($users) {
			$sql .= " AND t.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}
		if ($starttime) {
			$sql .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND t.ifcheck = 1  AND t.fid != 0 ";
		$sql .= " GROUP BY t.fid ";
		$sql .= " ORDER BY total DESC";
		$threadsDao = $this->getThreadsDao ();
		$tmsgsTable = $this->_getTmsgsTable ();
		return $threadsDao->getSearch ( "SELECT t.fid, COUNT(*) as total FROM pw_threads t LEFT JOIN $tmsgsTable tm ON tm.tid=t.tid WHERE 1 " . $sql );
	}
	
	/**
	 * 按回复标题与内容搜索帖子,统计版块数量
	 * @param array $forumIds
	 * @param string $keywords
	 * @param array $users
	 * @param int $starttime
	 * @param int $endtime
	 * @return array()
	 */
	function _searchForumsGroupsWithPosts($keywords, $users, $starttime, $endtime, $forumIds) {
		$sql = "";

		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND ( " . $sql;
				$sql .= " ( p.subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . " OR p.content LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . ") ";
			}
			$sql .= " ) ";
		}

		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$sql .= " AND p.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}

		if ($this->_mysqlFilterIds) {
			$sql .= " AND p.fid NOT IN(" . S::sqlImplode ( $this->_mysqlFilterIds ) . ")";
		}
		
		if ($users) {
			$sql .= " AND p.authorid IN(" . S::sqlImplode ( array_keys ( $users ) ) . ")";
		}
		if ($starttime) {
			$sql .= " AND p.postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND p.postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " AND p.ifcheck = 1 ";
		$sql .= " GROUP BY p.fid ";
		$sql .= " ORDER BY total DESC";
		$threadsDao = $this->getThreadsDao ();
		$postTable = $this->_getPostsTable ();
		return $threadsDao->getSearch ( "SELECT p.fid, COUNT(*) as total FROM $postTable p WHERE 1 " . $sql );
	}
	
	function _buildForumsTotal($forums) {
		if (! $forums)
			return array ();
		$result = array ();
		foreach ($forums as $value) {
			$result[$value['fid']] = intval($value['total']);
		}
		return $result;
	}
	
	/*********************************************************************/
	function searchUsers($keywords, $page = 1, $perpage = 20) {
		if (! ($keywords = $this->_checkKeywordCondition ( $keywords ))) {
			return array (false, false );
		}
		$page = $page > 1 ? $page : 1;
		$offset = intval ( ($page - 1) * $perpage );
		
		$membersDao = $this->_getMembersDao ();
		if (! ($total = $membersDao->countSearch ( $keywords ))) {
			return array (false, false );
		}
		$total = ($total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
		$result = $membersDao->getSearch ( $keywords, $offset, $perpage );
		
		return array ($total, $this->_buildUsers ( $result ) );
	}
	/*********************************************************************/
	/*
	 * 新鲜事搜索
	 * 
	 * */
	function searchWeibo($keywords, $userNames = "", $starttime = "", $endtime = "", $page = 1, $perpage = 20) {
		list ( $keywords, $users, $starttime, $endtime ) = $this->_checkThreadConditions ( $keywords, $userNames, $starttime, $endtime );
		if (! $keywords || ($userNames && ! $users))
			return false;
		$userIds = ($users) ? array_keys ( $users ) : array ();
		$page = $page > 1 ? $page : 1;
		$offset = intval ( ($page - 1) * $perpage );
		$weiboDao = $this->_getWeiboDao();
		$result = $weiboDao->search($keywords,'ALL', $offset, $perpage );
		return $result;
	}
	
	/*********************************************************************/
	function searchDiarys($keywords, $range, $userNames = "", $starttime = "", $endtime = "", $page = 1, $perpage = 20) {
		list ( $keywords, $users, $starttime, $endtime ) = $this->_checkThreadConditions ( $keywords, $userNames, $starttime, $endtime );
		if ($userNames && ! $users)
			return false;
		$userIds = ($users) ? array_keys ( $users ) : array ();
		$page = $page > 1 ? $page : 1;
		$offset = intval ( ($page - 1) * $perpage );
		$keywords = explode ( " ", $keywords );
		switch ($range) {
			case 1 :
				list ( $total, $result ) = $this->_searchDiarysWithSubject ( $keywords, $users, $starttime, $endtime, $offset, $perpage );
				break;
			case 2 :
				list ( $total, $result ) = $this->_searchDiarysWithContent ( $keywords, $users, $starttime, $endtime, $offset, $perpage );
				break;
			case 3 :
				list ( $total, $result ) = $this->_searchDiarysWithSubjectAndContent ( $keywords, $users, $starttime, $endtime, $offset, $perpage );
				break;
		}
		$total = ($total > $this->_mysqlLimit) ? $this->_mysqlLimit : $total;
		return ($total) ? array ($total, $this->_buildDiarys ( $result, $keywords ) ) : array (false, false );
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
	function _searchDiarysWithSubject($keywords, $users, $starttime, $endtime, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND " . $sql;
				$sql .= " subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' );
			}
		}
		if ($users) {
			$sql .= " AND username IN(" . S::sqlImplode ( $users ) . ")";
		}
		
		$sql .= $this->_getFilterDiaryByMysql();
		
		if ($starttime) {
			$sql .= " AND postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " ORDER BY postdate " . $this->_mysqlSort . " ";
		$diarysDao = $this->getDiarysDao ();
		if (! ($total = $diarysDao->countSearch ( "SELECT COUNT(*) as total FROM pw_diary WHERE 1 " . $sql ))) {
			return false;
		}
		$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		$result = $diarysDao->getSearch ( "SELECT * FROM pw_diary WHERE 1 " . $sql );
		return array ($total, $result );
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
	function _searchDiarysWithContent($keywords, $users, $starttime, $endtime, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND " . $sql;
				$sql .= " content LIKE " . S::sqlEscape ( '%' . $keyword . '%' );
			}
		}
		if ($users) {
			$sql .= " AND username IN(" . S::sqlImplode ( $users ) . ")";
		}
		
		$sql .= $this->_getFilterDiaryByMysql();
		
		if ($starttime) {
			$sql .= " AND postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " ORDER BY postdate " . $this->_mysqlSort . " ";
		$diarysDao = $this->getDiarysDao ();
		if (! ($total = $diarysDao->countSearch ( "SELECT COUNT(*) as total FROM pw_diary WHERE 1 " . $sql ))) {
			return false;
		}
		$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		$result = $diarysDao->getSearch ( "SELECT * FROM pw_diary WHERE 1 " . $sql );
		return array ($total, $result );
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
	function _searchDiarysWithSubjectAndContent($keywords, $users, $starttime, $endtime, $offset, $perpage) {
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " " . $this->_mysqlMethod . " " : " AND " . $sql;
				$sql .= " ( subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . " OR content LIKE " . S::sqlEscape ( '%' . $keyword . '%' ) . ") ";
			}
		}
		if ($users) {
			$sql .= " AND username IN(" . S::sqlImplode ( $users ) . ")";
		}
		
		$sql .= $this->_getFilterDiaryByMysql();
			
		if ($starttime) {
			$sql .= " AND postdate > " . S::sqlEscape ( $starttime );
		}
		if ($endtime) {
			$sql .= " AND postdate < " . S::sqlEscape ( $endtime );
		}
		$sql .= " ORDER BY postdate " . $this->_mysqlSort . " ";
		$diarysDao = $this->getDiarysDao ();
		if (! ($total = $diarysDao->countSearch ( "SELECT COUNT(*) as total FROM pw_diary WHERE 1 " . $sql ))) {
			return false;
		}
		$sql .= "  LIMIT " . intval($offset) . "," . intval($perpage);
		$result = $diarysDao->getSearch ( "SELECT * FROM pw_diary  WHERE 1 " . $sql );
		return array ($total, $result );
	}
	/*********************************************************************/
	function searchForums($keywords, $page = 1, $perpage = 20) {
		return $this->_searchForums ( $keywords, $page, $perpage );
	}
	
	function searchGroups($keywords, $page = 1, $perpage = 20) {
		return $this->_searchGroups ( $keywords, $page, $perpage );
	}
	
	/**
	 * 新鲜事DAO
	 * @return PW_Weibo_ContentDB
	 */
	function _getWeiboDao() {
		return L::loadDB('weibo_content','sns');
	}
	/**
	 * 用户表DAO
	 * @return PW_MembersDB
	 */
	function _getMembersDao() {
		return L::loadDB ( 'Members', 'user' );
	}
}