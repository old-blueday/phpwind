<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Sync extends CloudWind_General_Abstract {
	
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
				return $this->buildData ( $service->getPostsByPids ( $ids ), 6 ); //分表
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
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getPlatformAggregateDao ();
	}
	
	function getAttachsDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchAttachDao ();
	}
	
	function getColonysDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchColonyDao ();
	}
	
	function getDiarysDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchDiaryDao ();
	}
	
	function getForumsDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchForumDao ();
	}
	
	function getMembersDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchMemberDao ();
	}
	
	//TDOD
	function getPostsService() {
		$serviceFactory = $this->getServiceFactory ();
		return $serviceFactory->getSearchPostService ();
	}
	
	function getThreadsDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchThreadDao ();
	}
	
	function getWeibosDao() {
		$daoFactory = $this->getDaoFactory ();
		return $daoFactory->getSearchWeiboDao ();
	}
	
	function getServiceFactory() {
		static $ServiceFactory = null;
		if (! $ServiceFactory) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$ServiceFactory = new CloudWind_Service_Factory ();
		}
		return $ServiceFactory;
	}
	
	function getDaoFactory() {
		static $daoFactory = null;
		if (! $daoFactory) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory ();
		}
		return $daoFactory;
	}
}