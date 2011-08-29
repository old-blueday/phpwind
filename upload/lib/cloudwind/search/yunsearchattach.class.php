<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索附件与创建附件索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchAttach extends YUN_Abstract {
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_attachs';
	
	function getArrayResult($conditions) {
		return $this->_arrayResult;
	}
	
	function createIndex($conditions) {
		$this->_init ();
		$attachs = $this->_getAttachs ( $conditions ['page'] );
		return $this->_buildIndex ( $attachs );
	}
	
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogAttachs ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$Attachdao = $this->_getAttachsDao ();
		$ids = $Attachdao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
		$allIds = range ( $minId, $maxId );
		$filterIds = array ();
		if (! $ids) {
			$filterIds = $allIds;
		} else {
			$_ids = array ();
			foreach ( $ids as $id ) {
				$_ids [] = $id ['aid'];
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
	
	function _getAttachs($page = 1) {
		$Attachdao = $this->_getAttachsDao ();
		return $Attachdao->getAttachsByPage ( $page, $this->_perpage );
	}
	function _buildIndex($attachs) {
		if (! is_array ( $attachs ))
			return '';
		$out = '';
		foreach ( $attachs as $t ) {
			$out .= $this->_createForAdd ( $t );
		}
		return $out;
	}
	
	function _createForAdd($attach, $command = YUN_COMMAND_ADD) {
		if (! $attach)
			return false;
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . intval ( $attach ['tid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . intval ( $attach ['fid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'pid=' . intval ( $attach ['pid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'did=' . intval ( $attach ['did'] ) . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . intval ( $attach ['uid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'mid=' . intval ( $attach ['mid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'size=' . intval ( $attach ['size'] ) . YUN_ROW_SEPARATOR;
		$out .= 'hits=' . intval ( $attach ['hits'] ) . YUN_ROW_SEPARATOR;
		$out .= 'special=' . intval ( $attach ['special'] ) . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . intval ( $attach ['uploadtime'] ) . YUN_ROW_SEPARATOR;
		$out .= 'name=' . $this->_toolsService->_filterString ( $attach ['name'] ) . YUN_ROW_SEPARATOR;
		$out .= 'descrip=' . $this->_toolsService->_filterString ( $attach ['descrip'] ) . YUN_ROW_SEPARATOR;
		$out .= 'ctype=' . $this->_toolsService->_filterString ( $attach ['ctype'] ) . YUN_ROW_SEPARATOR;
		$out .= 'type=' . $this->_toolsService->_filterString ( $attach ['type'] ) . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _createForDelete($aid) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'aid=' . $aid . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _getLogAttachs($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$attachIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$attachIds [] = $log ['sid'];
		}
		$AttachDao = $this->_getAttachsDao ();
		$Attachs = $AttachDao->getsAttachsIds ( $attachIds );
		if (! $Attachs)
			return false;
		$tmp = array ();
		foreach ( $Attachs as $t ) {
			$tmp [$t ['aid']] = $t;
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
	
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	
	function _getAttachsDao() {
		static $sAttachsDao;
		if (! $sAttachsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_attachsdb.class.php';
			$sAttachsDao = new PW_YUN_AttachsDB ();
		}
		return $sAttachsDao;
	}

}