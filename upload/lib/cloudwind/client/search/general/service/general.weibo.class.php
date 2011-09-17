<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_General_Weibo extends CloudWind_General_Abstract {
	
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_weibos';
	
	function createIndex($searchService, $conditions) {
		$weibos = $searchService->getWeibosByPage ( $conditions ['page'] );
		if (! is_array ( $weibos ))
			return '';
		$out = '';
		foreach ( $weibos as $t ) {
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
		$weiboIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$weiboIds [] = $log ['sid'];
		}
		$weibos = $searchService->getWerbosByIds ( $weiboIds );
		if (! $weibos)
			return false;
		$tmp = array ();
		foreach ( $weibos as $t ) {
			$tmp [$t ['mid']] = $t;
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
				$_ids [] = $id ['mid'];
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