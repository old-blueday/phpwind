<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_General_Forum extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_forums';
	
	function createIndex($searchService, $conditions) {
		$forums = $searchService->getForumsByPage ( $conditions ['page'] );
		if (! $forums)
			return false;
		$out = '';
		foreach ( $forums as $t ) {
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
		$forumIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$forumIds [] = $log ['sid'];
		}
		$forums = $searchService->getForumsByFids ( $forumIds );
		if (! $forums)
			return false;
		$tmpForums = array ();
		foreach ( $forums as $t ) {
			$tmpForums [$t ['fid']] = $t;
		}
		$out = $this->getVersionInfo ( $this->_logTableName );
		foreach ( $logs as $log ) {
			if ($log ['operate'] == 1) {
				$out .= $searchService->createForAdd ( $tmpForums [$log ['sid']], YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $searchService->createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $searchService->createForAdd ( $tmpForums [$log ['sid']], YUN_COMMAND_ADD );
			}
		}
		return $out;
	}
	
	function markIndex($searchService, $conditions) {
		return $this->_markIndex ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($searchService, $conditions) {
		list ( $minId, $maxId ) = array ($conditions ['minid'], $conditions ['maxid'] );
		$ids = $searchService->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
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
			$out .= $searchService->createForDelete ( $id );
		}
		return $out;
	}
}