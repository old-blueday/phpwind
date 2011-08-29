<?php
/**
 * 云搜索同步服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
require_once R_P . 'lib/cloudwind/search/yunabstract.class.php';
class PW_YunSearchSync extends YUN_Abstract {
	
	function syncData() {
		$aggregateDao = $this->getAggregateDao ();
		$logs = $aggregateDao->getAllLogs ();
		if (! $logs) {
			return false;
		}
		$allIds = array ();
		foreach ( $logs as $k => $log ) {
			if ($log ['operate'] != 2) {
				$allIds [$log ['type']] [$k] = $log ['sid'];
			}
		}
		$data = array ();
		foreach ( $allIds as $type => $ids ) {
			$data [$type] = $this->getDataByType ( $type, $ids );
		}
		$aggregateDao->deleteAllLogs ();
		return array ('data' => $data, 'log' => $logs );
	}
	
	function getDataByType($type, $ids) {
		switch ($type) {
			case 1 :
				$dao = $this->getThreadsDao ();
				return $this->buildData ( $dao->getsBythreadIds ( $ids ), 1 );
				break;
			case 2 :
				$dao = $this->getDiarysDao ();
				return $this->buildData ( $dao->getsByDids ( $ids ) );
				break;
			case 3 :
				$dao = $this->getMembersDao ();
				return $this->buildData ( $dao->getsByUserIds ( $ids ) );
				break;
			case 4 :
				$dao = $this->getForumsDao ();
				return $this->buildData ( $dao->getsByForumIds ( $ids ) );
				break;
			case 5 :
				$dao = $this->getColonysDao ();
				return $this->buildData ( $dao->getsByColonyIds ( $ids ) );
				break;
			case 6 :
				$service = $this->getPostsService ();
				return $this->buildData ( $service->_getPostsByPostIds ( $ids ), 6 ); //分表
				break;
			case 7 :
				$dao = $this->getWeibosDao ();
				return $this->buildData ( $dao->getsWeibosIds ( $ids ) );
				break;
			case 8 :
				$dao = $this->getAttachsDao ();
				return $this->buildData ( $dao->getsAttachsIds ( $ids ) );
				break;
			default :
				break;
		}
	}
	
	function buildData($arrays, $type = null) {
		if (! $arrays)
			return array ();
		$tmp = array ();
		if (in_array ( $type, array (1, 6 ) )) {
			foreach ( $arrays as $array ) {
				$data = array ();
				$forum = $this->getForum ( $array ['fid'] );
				$data ['forumname'] = $forum ['name'];
				$tmp [] = array_merge ( $array, $data );
			}
		}
		return ($tmp) ? $tmp : $arrays;
	}
	function getForum($fid) {
		if (! $fid)
			return array ();
		static $forums = array ();
		if (! $forums [$fid]) {
			$forums [$fid] = L::forum ( $fid );
		}
		return $forums [$fid];
	}
	function getAggregateDao() {
		static $sAggregateDao;
		if (! $sAggregateDao) {
			require_once R_P . 'lib/cloudwind/db/yun_aggregatedb.class.php';
			$sAggregateDao = new PW_YUN_AggregateDB ();
		}
		return $sAggregateDao;
	}
	
	function getAttachsDao() {
		static $sAttachsDao;
		if (! $sAttachsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_attachsdb.class.php';
			$sAttachsDao = new PW_YUN_AttachsDB ();
		}
		return $sAttachsDao;
	}
	function getColonysDao() {
		static $sColonysDao;
		if (! $sColonysDao) {
			require_once R_P . 'lib/cloudwind/db/yun_colonysdb.class.php';
			$sColonysDao = new PW_YUN_ColonysDB ();
		}
		return $sColonysDao;
	}
	function getDiarysDao() {
		static $sDiaryDao;
		if (! $sDiaryDao) {
			require_once R_P . 'lib/cloudwind/db/yun_diarysdb.class.php';
			$sDiaryDao = new PW_YUN_DiarysDB ();
		}
		return $sDiaryDao;
	}
	function getForumsDao() {
		static $sForumsDao;
		if (! $sForumsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_forumsdb.class.php';
			$sForumsDao = new PW_YUN_ForumsDB ();
		}
		return $sForumsDao;
	}
	function getMembersDao() {
		static $sMembersDao;
		if (! $sMembersDao) {
			require_once R_P . 'lib/cloudwind/db/yun_membersdb.class.php';
			$sMembersDao = new PW_YUN_MembersDB ();
		}
		return $sMembersDao;
	}
	function getPostsService() {
		static $sPostsService;
		if (! $sPostsService) {
			require_once R_P . 'lib/cloudwind/search/yunsearchpost.class.php';
			$sPostsService = new YUN_SearchPost ();
		}
		return $sPostsService;
	}
	function getThreadsDao() {
		static $sThreadsDao;
		if (! $sThreadsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_threadsdb.class.php';
			$sThreadsDao = new PW_YUN_ThreadsDB ();
		}
		return $sThreadsDao;
	}
	function getWeibosDao() {
		static $sWeibosDao;
		if (! $sWeibosDao) {
			require_once R_P . 'lib/cloudwind/db/yun_weibosdb.class.php';
			$sWeibosDao = new PW_YUN_weibosDB ();
		}
		return $sWeibosDao;
	}

}