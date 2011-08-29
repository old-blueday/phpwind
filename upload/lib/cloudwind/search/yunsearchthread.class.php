<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索帖子与创建帖子索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchThread extends YUN_Abstract {
	var $_logTableName = 'pw_log_threads';
	function getArrayResult($conditions) {
		return $this->_arrayResult;
	}
	function createIndex($conditions) {
		$this->_init ();
		$threads = $this->_getThreads ( $conditions ['page'] );
		return $this->_buildIndex ( $threads );
	}
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogThreads ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$threadDao = $this->_getThreadsDao ();
		$ids = $threadDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
		$allIds = range ( $minId, $maxId );
		$filterIds = array ();
		if (! $ids) {
			$filterIds = $allIds;
		} else {
			$_ids = array ();
			foreach ( $ids as $id ) {
				$_ids [] = $id ['tid'];
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
	
	function _getLogThreads($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$threadIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$threadIds [] = $log ['sid'];
		}
		$threadDao = $this->_getThreadsDao ();
		$threads = $threadDao->getsBythreadIds ( $threadIds );
		if (! $threads)
			return false;
		$tmpThreads = array ();
		foreach ( $threads as $t ) {
			$tmpThreads [$t ['tid']] = $t;
		}
		$out = $this->getVersionInfo ( $this->_logTableName );
		foreach ( $logs as $log ) {
			$t = isset ( $tmpThreads [$log ['sid']] ) ? $tmpThreads [$log ['sid']] : array ();
			if (isset ( $t ['fid'] ) && ! $t ['fid']) {
				$out .= $this->_createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 1) {
				$out .= $this->_createForAdd ( $t, YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $this->_createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $this->_createForAdd ( $t, YUN_COMMAND_ADD );
			}
		}
		return $out;
	}
	
	function buildDeleteLists($threads) {
		if (! $threads) {
			return '';
		}
		$threadIds = array ();
		foreach ( $threads as $t ) {
			$threadIds [] = $t ['tid'];
		}
		sort ( $threadIds );
		$allThreadIds = (range ( $threadIds [0], $threadIds [count ( $threadIds ) - 1] ));
		$ids = array_diff ( $allThreadIds, $threadIds );
		if (! $ids) {
			return '';
		}
		$out = '';
		foreach ( $ids as $id ) {
			$out .= $this->_createForDelete ( $id );
		}
		return $out;
	}
	
	function checkThreadsByPeriod($period) {
		$lastlog = $this->getSearchConfig ( 'thread_lastlog' );
		if ($lastlog + $period > $GLOBALS ['timestamp']) {
			return false;
		}
		$this->setSearchConfig ( 'thread_lastlog', $GLOBALS ['timestamp'] );
		return true;
	}
	function _getThreads($page = 1) {
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->getThreadsByPage ( $page, $this->_perpage );
	}
	function _buildIndex($threads) {
		if (! is_array ( $threads ))
			return '';
		$out = '';
		foreach ( $threads as $t ) {
			$out .= $this->_createForAdd ( $t );
		}
		return $out;
	}
	function _createForAdd($thread, $command = YUN_COMMAND_ADD) {
		if (! $thread)
			return false;
		$out = '';
		$forum = $this->_getForum ( $thread ['fid'] );
		$content = $this->_toolsService->_filterString ( $thread ['content'] );
		if (! $content) {
			return '';
		}
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . $thread ['tid'] . YUN_ROW_SEPARATOR;
		$out .= 'pid=0' . YUN_ROW_SEPARATOR;
		$out .= 'subject=' . $this->_toolsService->_filterString ( $thread ['subject'], 300 ) . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $content . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $thread ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'forumname=' . $this->_toolsService->_filterString ( strip_tags ( $forum ['name'] ) ) . YUN_ROW_SEPARATOR;
		$out .= 'forumlink=' . $this->_getForumUrl ( $thread ['fid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'ifcheck=' . $thread ['ifcheck'] . YUN_ROW_SEPARATOR;
		$out .= 'authorid=' . $thread ['authorid'] . YUN_ROW_SEPARATOR;
		$out .= 'author=' . $this->_toolsService->_filterString ( $thread ['author'] ) . YUN_ROW_SEPARATOR;
		$out .= 'lastpost=' . $thread ['lastpost'] . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $thread ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'digest=' . $thread ['digest'] . YUN_ROW_SEPARATOR;
		$out .= 'hits=' . $thread ['hits'] . YUN_ROW_SEPARATOR;
		$out .= 'replies=' . $thread ['replies'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $this->_getThreadUrl ( $thread ['tid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'ifupload=' . $thread ['ifupload'] . YUN_ROW_SEPARATOR;
		$out .= 'topped=' . $thread ['topped'] . YUN_ROW_SEPARATOR;
		$out .= 'special=' . $thread ['special'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _createForDelete($tid) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . $tid . YUN_ROW_SEPARATOR;
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
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	function _getThreadsDao() {
		static $sThreadsDao;
		if (! $sThreadsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_threadsdb.class.php';
			$sThreadsDao = new PW_YUN_ThreadsDB ();
		}
		return $sThreadsDao;
	}

}