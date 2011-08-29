<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
/**
 * 创建贴子回复索引实现
 * 回复贴子由于采用了分表技术,因此在整合的时候需要处理数据的获取片段
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-9-16
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class YUN_SearchPost extends YUN_Abstract {
	var $_filecache = true;
	var $_logTableName = 'pw_log_posts';
	function createIndex($conditions) {
		$posts = $this->_getPosts ( $conditions ['page'] );
		return $this->_buildIndex ( $posts );
	}
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogPosts ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$tableInfo = ($this->_filecache) ? $this->_initPostInfo () : $this->_initPostInfoNoCache ();
		if (! $tableInfo)
			return false;
		$tableName = '';
		$tmpStart = $tmpEnd = 0;
		foreach ( $tableInfo as $table => $info ) {
			list ( $bool, $tmpStart, $tmpEnd ) = $this->_countPage ( $minId, $maxId, $info ['min'], $info ['max'] );
			if ($bool) {
				$tableName = $table;
				break;
			}
		}
		if (! $tableName || ! $tmpEnd)
			return false;
		$postDao = $this->_getPostsDao ();
		$ids = $postDao->getIdsByRange ( $tableName, intval ( $minId ), intval ( $maxId ) );
		$allIds = range ( $minId, $maxId );
		$filterIds = array ();
		if (! $ids) {
			$filterIds = $allIds;
		} else {
			$_ids = array ();
			foreach ( $ids as $id ) {
				$_ids [] = $id ['pid'];
			}
			$filterIds = array_diff ( $allIds, $_ids );
		}
		if (! $filterIds) {
			return false;
		}
		$out = '';
		foreach ( $filterIds as $id ) {
			$out .= $this->_createForDelete ( $id );
		}
		return $out;
	}
	
	function _getLogPosts($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$postIds = array ();
		foreach ( $logs as $log ) {
			$postIds [] = $log ['sid'];
		}
		$posts = $this->_getPostsByPostIds ( $postIds );
		if (! $posts)
			return false;
		$tmp = array ();
		foreach ( $posts as $t ) {
			$tmp [$t ['pid']] = $t;
		}
		$out = $this->getVersionInfo ( $this->_logTableName );
		foreach ( $logs as $log ) {
			if ($log ['operate'] == 1) {
				$out .= $this->_createForAdd ( $tmp [$log ['sid']], YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $this->_createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $this->_createForAdd ( $tmp [$log ['sid']], YUN_COMMAND_ADD );
			}
		}
		return $out;
	}
	function _getPostsByPostIds($postId) {
		if (! $postId) {
			return false;
		}
		$tableInfo = ($this->_filecache) ? $this->_initPostInfo () : $this->_initPostInfoNoCache ();
		if (! $tableInfo)
			return false;
		$tmp = array ();
		foreach ( $postId as $postId ) {
			$table = $this->_getPostTableByPostId ( $postId, $tableInfo );
			$table && $tmp [$table] [] = $postId;
		}
		if (! $tmp) {
			return false;
		}
		$postDao = $this->_getPostsDao ();
		$result = array ();
		foreach ( $tmp as $table => $postIds ) {
			$posts = $postDao->getsByPostIds ( $postIds, $table );
			$posts && $result = array_merge ( $result, $posts );
		}
		return $result;
	}
	
	function _getPostTableByPostId($postId, $tableInfo) {
		foreach ( $tableInfo as $table => $ids ) {
			if ($ids ['min'] <= $postId && $ids ['max'] >= $postId) {
				return $table;
			}
		}
		return false;
	}
	
	function _getPosts($page) {
		$page = ($page > 0) ? intval ( $page ) : 1;
		$start = ($page - 1) * $this->_perpage;
		$end = $this->_perpage * $page;
		$tableInfo = ($this->_filecache) ? $this->_initPostInfo () : $this->_initPostInfoNoCache ();
		if (! $tableInfo)
			return false;
		$tableName = '';
		$tmpStart = $tmpEnd = 0;
		foreach ( $tableInfo as $table => $info ) {
			list ( $bool, $tmpStart, $tmpEnd ) = $this->_countPage ( $start, $end, $info ['min'], $info ['max'] );
			if ($bool) {
				$tableName = $table;
				break;
			}
		}
		if (! $tableName || ! $tmpEnd)
			return false;
		$postDao = $this->_getPostsDao ();
		return $postDao->getPostsByRange ( $tmpStart, $tmpEnd, $tableName );
	}
	function _countPage($start, $end, $min, $max) {
		if ($start >= $min && $end <= $max) {
			return array (true, $start, $end );
		}
		if ($start >= $min && $start < $max && $end > $max) {
			return array (true, $start, $max );
		}
		if ($start < $min && $end >= $min && $end < $max) {
			return array (true, $min, $end );
		}
		if ($start < $min && $start < $max && $end >= $max) {
			return array (true, $min, $max );
		}
		return array (false, 0, 0 );
	}
	function _initPostInfo() {
		$filepath = D_P . 'data/bbscache/yunsearch_postinfo.php';
		if ((! is_file ( $filepath )) || (pwFilemtime ( $filepath ) + 300 <= $GLOBALS ['timestamp'])) {
			$postInfo = $this->_initPostInfoNoCache ();
			($postInfo) && writeover ( $filepath, "<?php\r\n\$postInfo=" . pw_var_export ( $postInfo ) . ";\r\n?>" );
		} else {
			require $filepath;
		}
		return $postInfo;
	}
	function _initPostInfoNoCache() {
		$dbposts = ($GLOBALS ['db_plist']) ? $GLOBALS ['db_plist'] : array (0 );
		$tables = $tableInfo = array ();
		foreach ( $dbposts as $k => $v ) {
			$k = ($k > 0) ? $k : '';
			$tables [] = 'pw_posts' . $k;
		}
		$postDao = $this->_getPostsDao ();
		foreach ( $tables as $table ) {
			$result = $postDao->getMaxPid ( $table );
			($result ['max']) && $tableInfo [$table] = $result;
		}
		return ($tableInfo) ? $tableInfo : array ();
	}
	function _buildIndex($posts) {
		if (! is_array ( $posts ))
			return '';
		$this->_init ();
		$out = '';
		foreach ( $posts as $p ) {
			$out .= $this->_createForAdd ( $p );
		}
		return $out;
	}
	
	function _createForAdd($post, $command = YUN_COMMAND_ADD) {
		if (! $post)
			return false;
		$out = '';
		$forum = $this->_getForum ( $post ['fid'] );
		$content = $this->_toolsService->_filterString ( $post ['content'] );
		if (! $content) {
			return '';
		}
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . $post ['tid'] . YUN_ROW_SEPARATOR;
		$out .= 'pid=' . $post ['pid'] . YUN_ROW_SEPARATOR;
		$out .= 'subject=' . $this->_toolsService->_filterString ( $post ['subject'], 300 ) . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $content . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $post ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'forumname=' . $this->_toolsService->_filterString ( $forum ['name'] ) . YUN_ROW_SEPARATOR;
		$out .= 'forumlink=' . $this->_getForumUrl ( $post ['fid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'ifcheck=' . $post ['ifcheck'] . YUN_ROW_SEPARATOR;
		$out .= 'authorid=' . $post ['authorid'] . YUN_ROW_SEPARATOR;
		$out .= 'author=' . $this->_toolsService->_filterString ( $post ['author'] ) . YUN_ROW_SEPARATOR;
		$out .= 'lastpost=0' . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $post ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'digest=0' . YUN_ROW_SEPARATOR;
		$out .= 'hits=0' . YUN_ROW_SEPARATOR;
		$out .= 'replies=0' . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $this->_getPostUrl ( $post ['tid'], $post ['pid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _createForDelete($pid) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'pid=' . $pid . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _getForum($fid) {
		if (! $fid)
			return array ();
		static $forums = array ();
		if (! $forums [$fid]) {
			$forums [$fid] = L::forum ( $fid );
		}
		return $forums [$fid];
	}
	function _getForumUrl($fid) {
		return $this->_bbsUrl . '/thread.php?fid=' . $fid;
	}
	function _getThreadUrl($tid) {
		return $this->_bbsUrl . '/read.php?tid=' . $tid;
	}
	
	function _getPostUrl($tid, $pid) {
		return $this->_bbsUrl . '/job.php?action=topost&tid=' . $tid . '&pid=' . $pid;
	}
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	function _getPostsDao() {
		static $sPostsDao;
		if (! $sPostsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_postsdb.class.php';
			$sPostsDao = new PW_YUN_PostsDB ();
		}
		return $sPostsDao;
	}

}