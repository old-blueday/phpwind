<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索版块与创建版块索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchForum extends YUN_Abstract {
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_forums';
	
	function createIndex($conditions) {
		$this->_init ();
		$forums = $this->_getForums ( $conditions ['page'] );
		return $this->_buildIndex ( $forums );
	}
	function _getForums($page) {
		$forumService = $this->_getForumsDao ();
		return $forumService->getForumsByPage ( $page, $this->_perpage );
	
	}
	function _buildIndex($arrays) {
		if (! $arrays)
			return false;
		$out = '';
		foreach ( $arrays as $t ) {
			$out .= $this->_createForAdd ( $t );
		}
		return $out;
	}
	function _createForAdd($array, $command = YUN_COMMAND_ADD) {
		if (! $array)
			return false;
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $array ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'name=' . $this->_toolsService->_filterString ( strip_tags ( $array ['name'] ) ) . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $this->_getForumUrl ( $array ['fid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	function _createForDelete($id) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $id . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogForums ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$forumService = $this->_getForumsDao ();
		$ids = $forumService->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
		$allIds = range ( $minId, $maxId );
		$filterIds = array ();
		if (! $ids) {
			$filterIds = $allIds;
		} else {
			$_ids = array ();
			foreach ( $ids as $id ) {
				$_ids [] = $id ['fid'];
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
	
	function _getLogForums($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$forumIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$forumIds [] = $log ['sid'];
		}
		$forumDao = $this->_getForumsDao ();
		$forums = $forumDao->getsByForumIds ( $forumIds );
		if (! $forums)
			return false;
		$tmpForums = array ();
		foreach ( $forums as $t ) {
			$tmpForums [$t ['fid']] = $t;
		}
		$out = $this->getVersionInfo ( $this->_logTableName );
		foreach ( $logs as $log ) {
			if ($log ['operate'] == 1) {
				$out .= $this->_createForAdd ( $tmpForums [$log ['sid']], YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $this->_createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $this->_createForAdd ( $tmpForums [$log ['sid']], YUN_COMMAND_ADD );
			}
		}
		return $out;
	}
	
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function _getForumUrl($fid) {
		return $this->_bbsUrl . '/thread.php?fid=' . $fid;
	}
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	function _getForumsDao() {
		static $sForumsDao;
		if (! $sForumsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_forumsdb.class.php';
			$sForumsDao = new PW_YUN_ForumsDB ();
		}
		return $sForumsDao;
	}

}