<?php
!defined('P_W') && exit('Forbidden');
C::loadClass('sourcetype', 'base', false);
class PW_ThreadSourceType extends PW_SourceType {
	function getSourceData($sourceId) {
		$data = $this->_getThreadData($sourceId);
		if (!$data || $this->_checkIfDelete($data)) return array();
		$data['content'] = preg_replace("/\[attachment=[0-9]+\]/is", '', $data['content']);
		$data['descrip'] = substrs(stripWindCode($data['content']), 100);
		$data['frominfo'] = '论坛';
		return $data;
	}
	function _getThreadData($tid) {
		//* $threadService = L::loadClass('threads','forum');
		//* return $threadService->getThreads($tid, true);
		$_cacheService = Perf::gatherCache('pw_threads');
		return $_cacheService->getThreadAndTmsgByThreadId($tid);		
	}
	/**
	 * 判断该帖子是否已被删除
	 * @param unknown_type $thread
	 */
	function _checkIfDelete($thread) {
		if (!S::isArray($thread)) return true;
		return $thread['fid'] == 0 && $thread['ifcheck'] == 1;
	}

	function getSourceUrl($sourceId) /*Abstract function*/ {
		return 'read.php?tid='.$sourceId;
		/*
		global $db_bbsurl;
		return $db_bbsurl.'/read.php?tid='.$sourceId;
		*/
	}

	function getSourceType() {
		return 'thread';
	}
}