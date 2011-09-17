<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Thread extends CloudWind_General_Abstract {
	
	function getThreadsByPage($page) {
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->getThreadsByPage ( $page, $this->_perpage );
	}
	
	function getThreadsByThreadIds($threadIds) {
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->getsBythreadIds ( $threadIds );
	}
	
	function getThreadIdsByRange($minId, $maxId) {
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->getIdsByRange ( intval ( $minId ), intval ( $maxId ) );
	}
	
	function deleteThreadByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->deleteThreadByTid($tid);
	}
	
	function setThreadCheckedByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->setThreadCheckedByTid($tid);
	}
	
	function maxThreadId() {
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->maxThreadId();
	}
	
	function countThreadsNum() {
		$threadDao = $this->_getThreadsDao ();
		return $threadDao->countThreadsNum();
	}
	
	function createForAdd($thread, $command = YUN_COMMAND_ADD) {
		if (! $thread)
			return false;
		$out = '';
		$forum = $this->_getForum ( $thread ['fid'] );
		$content = $this->_toolsService->_filterString ( $thread ['content'] );
		if (! $content) {
			return '';
		}
		$data = array ();
		$data ['tid'] = $thread ['tid'];
		$data ['pid'] = 0;
		$data ['subject'] = $this->_toolsService->_filterString ( $thread ['subject'], 300 );
		$data ['content'] = $content;
		$data ['fid'] = $thread ['fid'];
		$data ['forumname'] = $this->_toolsService->_filterString ( strip_tags ( $forum ['name'] ) );
		$data ['forumlink'] = $this->_getForumUrl ( $thread ['fid'] );
		$data ['ifcheck'] = $thread ['ifcheck'];
		$data ['authorid'] = $thread ['authorid'];
		$data ['author'] = $this->_toolsService->_filterString ( $thread ['author'] );
		$data ['lastpost'] = $thread ['lastpost'];
		$data ['postdate'] = $thread ['postdate'];
		$data ['digest'] = $thread ['digest'];
		$data ['hits'] = $thread ['hits'];
		$data ['replies'] = $thread ['replies'];
		$data ['link'] = $this->_getThreadUrl ( $thread ['tid'] );
		$data ['ifupload'] = $thread ['ifupload'];
		$data ['topped'] = $thread ['topped'];
		$data ['special'] = $thread ['special'];
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getThreadFormat ( $data, $command );
	}
	
	function createForDelete($tid) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'tid', $tid );
	}
	
	function _getForum($fid) {
		if (! $fid)
			return array ();
		static $forums = array ();
		if (! $forums [$fid]) {
			$forums [$fid] = L::forum ( $fid );
		}
		return $forums [$fid];
	}
	
	function _getForumUrl($fid) {
		return $this->_bbsUrl . '/thread.php?fid=' . $fid;
	}
	
	function _getThreadUrl($tid) {
		return $this->_bbsUrl . '/read.php?tid=' . $tid;
	}
	
	function _getThreadsDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory();
			$dao = $daoFactory->getSearchThreadDao();
		}
		return $dao;
	}
}