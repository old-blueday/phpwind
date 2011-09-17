<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_General_Thread extends CloudWind_General_Abstract {
	
	var $_logTableName = 'pw_log_threads';
	
	function createIndex($searchService, $conditions) {
		$threads = $searchService->getThreadsByPage ( $conditions ['page'] );
		if (! is_array ( $threads ))
			return '';
		$out = '';
		foreach ( $threads as $t ) {
			$out .= $searchService->createForAdd ( $t );
		}
		return $out;
	}
	
	function alterIndex($searchService, $conditions) {
		list ( $page, $startTime, $endTime ) = array ($conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
		$logsService = $this->getGeneralLogsService ();
		if (! ($logs = $logsService->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$threadIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$threadIds [] = $log ['sid'];
		}
		$threads = $searchService->getThreadsByThreadIds ( $threadIds );
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
				$out .= $searchService->createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 1) {
				$out .= $searchService->createForAdd ( $t, YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $searchService->createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $searchService->createForAdd ( $t, YUN_COMMAND_ADD );
			}
		}
		return $out;
	}
	
	function markIndex($searchService, $conditions) {
		return $this->_markIndex ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($searchService, $conditions) {
		list ( $minId, $maxId ) = array ($conditions ['minid'], $conditions ['maxid'] );
		$ids = $searchService->getThreadIdsByRange ( $minId, $maxId );
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
			$out .= $searchService->createForDelete ( $id );
		}
		return $out;
	}
}