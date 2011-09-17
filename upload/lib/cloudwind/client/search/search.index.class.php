<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Search_Index extends CloudWind_Core_Service {
	
	function CloudWind_Search_Index() {
		$this->__construct ();
	}
	
	function __construct() {
	}
	
	function detectIndex($type, $minid, $maxid) {
		if (! $this->_checkType ( $type )) {
			exit ( 'cann`t find the type' );
		}
		$minid = intval ( $minid );
		$maxid = intval ( $maxid );
		if (! $minid) {
			exit ( 'minid cann`t be null' );
		}
		$maxid = ($maxid) ? $maxid : $minid + 10000;
		if ($minid >= $maxid) {
			exit ( 'minid cann`t exceed maxid' );
		}
		$_tmpMaxId = $this->getMaxIdByType ( $type );
		$maxid = min ( $maxid, $_tmpMaxId );
		
		$yunsearch = $this->getYunSearchGeneralService ();
		$result = $yunsearch->detectIndex ( (($type) ? $type : 'thread'), array ('minid' => $minid, 'maxid' => $maxid ) );
		$this->_outPut ( $result, "cann`t find data,from {$minid} to {$maxid} " );
	}
	
	function createIndex($type, $page, $versionid) {
		if (! $this->_checkType ( $type )) {
			exit ( 'cann`t find the type' );
		}
		$yunsearch = $this->getYunSearchGeneralService ();
		$page = ($page > 1) ? intval ( $page ) : 1;
		$result = $yunsearch->createIndex ( (($type) ? $type : 'thread'), array ('page' => $page, 'versionid' => $versionid ) );
		$this->_outPut ( $result, 'cann`t find data,current page is ' . $page );
	}
	
	function alterIndex($type, $page, $starttime, $endtime) {
		$page = ($page > 1) ? intval ( $page ) : 1;
		$starttime = intval ( $starttime );
		$endtime = intval ( $endtime );
		if ($endtime < 1) {
			exit ( 'the versionid cann`t be null' );
		}
		$yunsearch = $this->getYunSearchGeneralService ();
		$result = $yunsearch->alterIndex ( (($type) ? $type : 'thread'), array ('page' => $page, 'starttime' => $starttime, 'endtime' => $endtime ) );
		$this->_outPut ( $result, 'cann`t find data,current page is ' . $page );
	}
	
	function _outPut($result, $tips = '') {
		return $this->_outPutCompress ( ($result) ? $result : $tips );
	}
	
	function _outPutCompress($content) {
		$setting = $this->getPlatformSettings ();
		if (! $setting ['searchcompress'] && ! headers_sent () && extension_loaded ( "zlib" ) && strstr ( $_SERVER ["HTTP_ACCEPT_ENCODING"], "gzip" )) {
			$content = gzencode ( $content, 9 );
			header ( "Content-Type:text/html; charset=utf8" );
			header ( "Content-Encoding:gzip" );
			header ( "Content-Length:" . strlen ( $content ) );
		}
		echo ($content);
	}
	
	function _checkType($type) {
		return (in_array ( $type, $this->_getAllType () )) ? true : false;
	}
	
	function _getAllType() {
		return CloudWind_getConfig ( 'search_types' );
	}
	
	function createLists($type, $hashid = 0) {
		$max = $this->getMaxIdByType ( $type );
		$versionid = $this->getVersionId ();
		if ($max < 1)
			return $this->_outputlist ( $this->_buildListResult ( $versionid ) );
		$total = $this->countByType ( $type );
		return $this->_outputlist ( $this->_buildListResult ( $versionid, $total, $max ) );
	}
	
	function _buildListResult($versionid, $total = 0, $max = 0) {
		return "versionid={$versionid}&total={$total}&max={$max}\r\n";
	}
	
	function createAddLists($type, $starttime, $endtime, $hashid = 0) {
		$count = $this->countLogsByType ( $type, $starttime, $endtime );
		$versionid = ($endtime) ? $endtime : $this->getVersionId ();
		if ($count < 1)
			return $this->_outputlist ( "versionid={$versionid}&total=0&max=0\r\n" );
		$out = "versionid={$versionid}&total={$count}&max={$count}\r\n";
		return $this->_outputlist ( $out );
	}
	
	function createAllAddLists($starttime, $endtime, $hashid = 0) {
		$types = $this->_getAllType ();
		$out = "";
		$versionid = ($endtime) ? $endtime : $this->getVersionId ();
		foreach ( $types as $type ) {
			$count = $this->countLogsByType ( $type, $starttime, $endtime );
			if ($count < 1) {
				$out .= "{$type}:versionid={$versionid}&total=0&max=0\r\n";
			} else {
				$out .= "{$type}:versionid={$versionid}&total={$count}&max={$count}\r\n";
			}
		}
		return $this->_outputlist ( $out );
	}
	
	function createFullAllLists($hashid, $show = true) {
		$types = $this->_getAllType ();
		$out = "";
		$versionid = $this->getVersionId ();
		foreach ( $types as $type ) {
			$max = $this->getMaxIdByType ( $type );
			if ($max < 1) {
				$out .= "{$type}:" . $this->_buildListResult ( $versionid );
			} else {
				$total = $this->countByType ( $type );
				$out .= "{$type}:" . $this->_buildListResult ( $versionid, $total, $max );
			}
		}
		return ($show) ? $this->_outputlist ( $out ) : $out;
	}
	
	function _outputlist($result) {
		echo $result;
	}
	
	function getVersionId() {
		return CloudWind_getConfig ( 'g_timestamp' );
	}
	
	function getMaxIdByType($type) {
		$serviceFactory = $this->getServiceFactory ();
		switch ($type) {
			case 'thread' :
				$threadService = $serviceFactory->getSearchThreadService ();
				$result = $threadService->maxThreadId ();
				break;
			case 'post' :
				$postService = $serviceFactory->getSearchPostService ();
				$result = $postService->maxPostId ();
				break;
			case 'member' :
				$memberService = $serviceFactory->getSearchMemberService ();
				$result = $memberService->maxMemberId ();
				break;
			case 'diary' :
				$diaryService = $serviceFactory->getSearchDiaryService ();
				$result = $diaryService->maxDiaryId ();
				break;
			case 'forum' :
				$forumService = $serviceFactory->getSearchForumService ();
				$result = $forumService->maxForumId ();
				break;
			case 'colony' :
				$colonyService = $serviceFactory->getSearchColonyService ();
				$result = $colonyService->maxColonyId ();
				break;
			case 'attach' :
				$attachService = $serviceFactory->getSearchAttachService ();
				$result = $attachService->maxAttachId ();
				break;
			case 'weibo' :
				$weiboService = $serviceFactory->getSearchWeiboService ();
				$result = $weiboService->maxWeiboId ();
				break;
			default :
				$result = array ();
				break;
		}
		return $result;
	}
	
	function countByType($type) {
		$serviceFactory = $this->getServiceFactory ();
		switch ($type) {
			case 'thread' :
				$threadService = $serviceFactory->getSearchThreadService ();
				$result = $threadService->countThreadsNum ();
				break;
			case 'post' :
				$postService = $serviceFactory->getSearchPostService ();
				$result = $postService->countPostsNum ();
				break;
			case 'member' :
				$memberService = $serviceFactory->getSearchMemberService ();
				$result = $memberService->countMembersNum ();
				break;
			case 'diary' :
				$diaryService = $serviceFactory->getSearchDiaryService ();
				$result = $diaryService->countDiarysNum ();
				break;
			case 'forum' :
				$forumService = $serviceFactory->getSearchForumService ();
				$result = $forumService->countForumsNum ();
				break;
			case 'colony' :
				$colonyService = $serviceFactory->getSearchColonyService ();
				$result = $colonyService->countColonysNum ();
				break;
			case 'attach' :
				$attachService = $serviceFactory->getSearchAttachService ();
				$result = $attachService->countAttachsNum ();
				break;
			case 'weibo' :
				$weiboService = $serviceFactory->getSearchWeiboService ();
				$result = $weiboService->countWeibosNum ();
				break;
			default :
				$result = array ();
				break;
		}
		return $result;
	}
	
	function countLogsByType($type, $starttime, $endtime) {
		$starttime = intval ( $starttime );
		$endtime = intval ( $endtime );
		$starttime = ($starttime > 0) ? $starttime : 0;
		$endtime = ($endtime > 0) ? $endtime : $this->getVersionId ();
		$generalFactory = $this->getGeneralFactory();
		$generalLogsService = $generalFactory->getGeneralLogsService();
		return $generalLogsService->countLogsByTypeAndTime($type, $starttime, $endtime);
	}
	
	function markAllLogs($starttime, $endtime) {
		$types = $this->_getAllType ();
		foreach ( $types as $type ) {
			$this->markIndex ( $type, $starttime, $endtime );
		}
		exit ( '1' );
	}
	
	function markLogs($type, $starttime, $endtime) {
		$result = $this->markIndex ( $type, $starttime, $endtime );
		exit ( '1' );
	}
	
	function markIndex($type, $starttime, $endtime) {
		$yunsearch = $this->getYunSearchGeneralService ();
		if ($yunsearch->markIndex ( $type, array ('starttime' => $starttime, 'endtime' => $endtime ) )) {
			return true;
		}
		return false;
	}
	
	function getYunSearchGeneralService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND . '/client/search/search.general.class.php';
			$service = new CloudWind_Search_General ();
		}
		return $service;
	}
	
	function getServiceFactory() {
		static $factory = null;
		if (! $factory) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
		}
		return $factory;
	}
	
	function getGeneralFactory() {
		static $generalFactory = null;
		if (! $generalFactory) {
			require_once CLOUDWIND . '/client/search/general/service/general.factory.class.php';
			$generalFactory = new CloudWind_General_Factory ();
		}
		return $generalFactory;
	}
}