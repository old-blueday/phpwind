<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索新鲜事与创建新鲜事索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-3-29
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchWeibo extends YUN_Abstract {
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_weibos';
	
	function getArrayResult($conditions) {
		return $this->_arrayResult;
	}
	
	function createIndex($conditions) {
		$this->_init ();
		$weibos = $this->_getWeibos ( $conditions ['page'] );
		return $this->_buildIndex ( $weibos );
	}
	
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogweibos ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$weiboDao = $this->_getWeibosDao ();
		$ids = $weiboDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
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
			$out .= $this->_createForDelete ( $id );
		}
		return $out;
	}
	
	function _getWeibos($page = 1) {
		$weiboDao = $this->_getWeibosDao ();
		return $weiboDao->getWeibosByPage ( $page, $this->_perpage );
	}
	
	function _buildIndex($weibos) {
		if (! is_array ( $weibos ))
			return '';
		$out = '';
		foreach ( $weibos as $t ) {
			$out .= $this->_createForAdd ( $t );
		}
		return $out;
	}
	
	function _createForAdd($weibo, $command = YUN_COMMAND_ADD) {
		if (! $weibo)
			return false;
		if (! isset ( $weibo ['content'] ) || ! $weibo ['content']) {
			return false;
		}
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'mid=' . intval ( $weibo ['mid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . intval ( $weibo ['uid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'replies=' . intval ( $weibo ['replies'] ) . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . intval ( $weibo ['postdate'] ) . YUN_ROW_SEPARATOR;
		$out .= 'contenttype=' . intval ( $weibo ['contenttype'] ) . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $this->_toolsService->_filterString ( $weibo ['content'] ) . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _createForDelete($mid) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'mid=' . $mid . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _getLogweibos($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$weiboIds = $logInfos = array ();
		foreach ( $logs as $log ) {
			$weiboIds [] = $log ['sid'];
		}
		$weiboDao = $this->_getWeibosDao ();
		$weibos = $weiboDao->getsweibosIds ( $weiboIds );
		if (! $weibos)
			return false;
		$tmp = array ();
		foreach ( $weibos as $t ) {
			$tmp [$t ['mid']] = $t;
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
	
	function _getWeibosDao() {
		static $sWeibosDao;
		if (! $sWeibosDao) {
			require_once R_P . 'lib/cloudwind/db/yun_weibosdb.class.php';
			$sWeibosDao = new PW_YUN_weibosDB ();
		}
		return $sWeibosDao;
	}
}