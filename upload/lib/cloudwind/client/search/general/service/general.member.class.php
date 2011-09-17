<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_General_Member extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_members';
	
	function createIndex($searchService, $conditions) {
		$members = $searchService->getMembersByPage ( $conditions ['page'] );
		if (! $members)
			return false;
		$out = '';
		foreach ( $members as $t ) {
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
		$userIds = array ();
		foreach ( $logs as $log ) {
			$userIds [] = $log ['sid'];
		}
		$members = $searchService->getMembersByUids ( $userIds );
		if (! $members)
			return false;
		$tmp = array ();
		foreach ( $members as $t ) {
			$tmp [$t ['uid']] = $t;
		}
		$out = $this->getVersionInfo ( $this->_logTableName );
		foreach ( $logs as $log ) {
			if ($log ['operate'] == 1) {
				$out .= $searchService->createForAdd ( $tmp [$log ['sid']], YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $searchService->createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $searchService->createForAdd ( $tmp [$log ['sid']], YUN_COMMAND_ADD );
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
				$_ids [] = $id ['uid'];
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