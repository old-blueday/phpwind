<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_General_Post extends CloudWind_General_Abstract {
	
	var $_logTableName = 'pw_log_posts';
	
	function createIndex($searchService, $conditions) {
		$posts = $searchService->getPostsByPage($conditions ['page']);
		if (! is_array ( $posts ))
			return '';
		$out = '';
		foreach ( $posts as $p ) {
			$out .= $searchService->createForAdd ( $p );
		}
		return $out;
	}
	
	function alterIndex($searchService, $conditions) {
		list ( $page, $startTime, $endTime ) = array ($conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
		$logsService = $this->getGeneralLogsService ();
		if (! ($logs = $logsService->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$postIds = array ();
		foreach ( $logs as $log ) {
			$postIds [] = $log ['sid'];
		}
		$posts = $searchService->getPostsByPids($postIds);
		if (! $posts)
			return false;
		$tmp = array ();
		foreach ( $posts as $t ) {
			$tmp [$t ['pid']] = $t;
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
		$ids = $searchService->getValidIds($minId, $maxId);
		if ($ids === false) return false;
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
			$out .= $searchService->createForDelete ( $id );
		}
		return $out;
	}
	
}