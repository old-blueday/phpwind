<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索日志与创建日志索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchDiary extends YUN_Abstract {
	var $_logTableName = 'pw_log_diary';
	function createIndex($conditions) {
		$this->_init ();
		$diarys = $this->_getDiarys ( $conditions ['page'] );
		return $this->_buildIndex ( $diarys );
	}
	
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogDiarys ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$diaryDao = $this->_getDiarysDao ();
		$ids = $diaryDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
		$allIds = range ( $minId, $maxId );
		$filterIds = array ();
		if (! $ids) {
			$filterIds = $allIds;
		} else {
			$_ids = array ();
			foreach ( $ids as $id ) {
				$_ids [] = $id ['did'];
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
	
	function _getDiarys($page) {
		$diaryDao = $this->_getDiarysDao ();
		return $diaryDao->getDiarysByPage ( $page, $this->_perpage );
	
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
		$out .= 'did=' . $array ['did'] . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . $array ['uid'] . YUN_ROW_SEPARATOR;
		$out .= 'username=' . $this->_toolsService->_filterString ( $array ['username'] ) . YUN_ROW_SEPARATOR;
		$out .= 'subject=' . $this->_toolsService->_filterString ( $array ['subject'], 300 ) . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $this->_toolsService->_filterString ( $array ['content'] ) . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $array ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $this->_getDiaryUrl ( $array ['uid'], $array ['did'] ) . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	function _createForDelete($id) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'did=' . $id . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _getLogDiarys($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$dids = array ();
		foreach ( $logs as $log ) {
			$dids [] = $log ['sid'];
		}
		$diaryDao = $this->_getDiarysDao ();
		$diarys = $diaryDao->getsByDids ( $dids );
		if (! $diarys)
			return false;
		$tmp = array ();
		foreach ( $diarys as $t ) {
			$tmp [$t ['did']] = $t;
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
	
	function buildDeleteLists($diarys) {
		if (! $diarys) {
			return '';
		}
		$diaryIds = array ();
		foreach ( $diarys as $t ) {
			$diaryIds [] = $t ['did'];
		}
		sort ( $diaryIds );
		$allDiaryIds = (range ( $diaryIds [0], $diaryIds [count ( $diaryIds ) - 1] ));
		$ids = array_diff ( $allDiaryIds, $diaryIds );
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
		$lastlog = $this->getSearchConfig ( 'diary_lastlog' );
		if ($lastlog + $period > $GLOBALS ['timestamp']) {
			return false;
		}
		$this->setSearchConfig ( 'diary_lastlog', $GLOBALS ['timestamp'] );
		return true;
	}
	
	function _getDiaryUrl($uid, $did) {
		return $this->_bbsUrl . '/apps.php?q=diary&uid=' . $uid . '&a=detail&did=' . $did;
	}
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	function _getDiarysDao() {
		static $sDiaryDao;
		if (! $sDiaryDao) {
			require_once R_P . 'lib/cloudwind/db/yun_diarysdb.class.php';
			$sDiaryDao = new PW_YUN_DiarysDB ();
		}
		return $sDiaryDao;
	}

}