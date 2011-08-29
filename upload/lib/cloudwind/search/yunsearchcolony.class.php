<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索群组与创建群组索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchColony extends YUN_Abstract {
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_colonys';
	function createIndex($conditions) {
		$this->_init ();
		$colonys = $this->_getColonys ( $conditions ['page'] );
		return $this->_buildIndex ( $colonys );
	}
	function _getColonys($page) {
		$colonyDao = $this->_getColonysDao ();
		return $colonyDao->getColonysByPage ( $page, $this->_perpage );
	
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
		$out .= 'id=' . $array ['id'] . YUN_ROW_SEPARATOR;
		$out .= 'classid=' . $array ['classid'] . YUN_ROW_SEPARATOR;
		$out .= 'cname=' . $this->_toolsService->_filterString ( strip_tags ( $array ['cname'] ) ) . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $this->_getColonyUrl ( $array ['id'] ) . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	function _createForDelete($id) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'id=' . $id . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogColonys ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$colonyDao = $this->_getColonysDao ();
		$ids = $colonyDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
		$allIds = range ( $minId, $maxId );
		$filterIds = array ();
		if (! $ids) {
			$filterIds = $allIds;
		} else {
			$_ids = array ();
			foreach ( $ids as $id ) {
				$_ids [] = $id ['id'];
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
	
	function _getLogColonys($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$ColonyIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$ColonyIds [] = $log ['sid'];
		}
		$ColonyDao = $this->_getColonysDao ();
		$Colonys = $ColonyDao->getsByColonyIds ( $ColonyIds );
		if (! $Colonys)
			return false;
		$tmpColonys = array ();
		foreach ( $Colonys as $t ) {
			$tmpColonys [$t ['id']] = $t;
		}
		$out = $this->getVersionInfo ( $this->_logTableName );
		foreach ( $logs as $log ) {
			if ($log ['operate'] == 1) {
				$out .= $this->_createForAdd ( $tmpColonys [$log ['sid']], YUN_COMMAND_UPDATE );
			} elseif ($log ['operate'] == 2) {
				$out .= $this->_createForDelete ( $log ['sid'] );
			} elseif ($log ['operate'] == 3) {
				$out .= $this->_createForAdd ( $tmpColonys [$log ['sid']], YUN_COMMAND_ADD );
			}
		}
		return $out;
	}
	
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function _getColonyUrl($id) {
		return $this->_bbsUrl . '/apps.php?q=group&cyid=' . $id;
	}
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	function _getColonysDao() {
		static $sColonysDao;
		if (! $sColonysDao) {
			require_once R_P . 'lib/cloudwind/db/yun_colonysdb.class.php';
			$sColonysDao = new PW_YUN_ColonysDB ();
		}
		return $sColonysDao;
	}

}