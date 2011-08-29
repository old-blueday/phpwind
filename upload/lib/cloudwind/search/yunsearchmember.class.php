<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 搜索用户与创建用户索引实现
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class YUN_SearchMember extends YUN_Abstract {
	var $_perpage = 4000;
	var $_logTableName = 'pw_log_members';
	function createIndex($conditions) {
		$this->_init ();
		$members = $this->_getMembers ( $conditions ['page'] );
		return $this->_buildIndex ( $members );
	}
	
	function alterIndex($conditions) {
		$this->_init ();
		return $this->_getLogMembers ( $conditions ['page'], $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function markIndex($conditions) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $this->_logTableName, $conditions ['starttime'], $conditions ['endtime'] );
	}
	
	function detectIndex($conditions) {
		return $this->_detectIndex ( $conditions ['minid'], $conditions ['maxid'] );
	}
	
	function _detectIndex($minId, $maxId) {
		$memberService = $this->_getMembersDao ();
		$ids = $memberService->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
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
			$out .= $this->_createForDelete ( $id );
		}
		return $out;
	}
	
	function _getMembers($page) {
		$memberService = $this->_getMembersDao ();
		return $memberService->getMembersByPage ( $page, $this->_perpage );
	
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
		$out .= 'uid=' . $array ['uid'] . YUN_ROW_SEPARATOR;
		$out .= 'username=' . $this->_toolsService->_filterString ( $array ['username'] ) . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $this->_getMemberUrl ( $array ['uid'] ) . YUN_ROW_SEPARATOR;
		$out .= 'regdate=' . $array ['regdate'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	function _createForDelete($id) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . $id . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function _getLogMembers($page, $startTime, $endTime) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $this->_logTableName, $startTime, $endTime, $page, $this->_perpage ))) {
			return false;
		}
		$userIds = array ();
		foreach ( $logs as $log ) {
			$userIds [] = $log ['sid'];
		}
		$memberDao = $this->_getMembersDao ();
		$members = $memberDao->getsByUserIds ( $userIds );
		if (! $members)
			return false;
		$tmp = array ();
		foreach ( $members as $t ) {
			$tmp [$t ['uid']] = $t;
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
	
	function _getMemberUrl($uid) {
		return $this->_bbsUrl . '/u.php?uid=' . $uid;
	}
	function _init() {
		$this->_toolsService = $this->getToolsService ();
		$configsService = $this->getConfigsService ();
		$this->_bbsUrl = $configsService->getBBSUrl ();
	}
	function _getMembersDao() {
		static $sMembersDao;
		if (! $sMembersDao) {
			require_once R_P . 'lib/cloudwind/db/yun_membersdb.class.php';
			$sMembersDao = new PW_YUN_MembersDB ();
		}
		return $sMembersDao;
	}

}