<?php
!defined('P_W') && exit('Forbidden');
C::loadClass('sourcetype', 'base', false);
class PW_DiarySourceType extends PW_SourceType {
	function getSourceData($sourceId) {
		$data = $this->_getDiaryData($sourceId);
		if (!$data) return array();
		$data['descrip'] = substrs(stripWindCode($data['content']), 100);
		$data['frominfo'] = '日志';
		$data['author'] = $data['username'];
		return $data;
	}
	function _getDiaryData($dtid) {
		$diaryService = L::loadClass('diary','diary');
		return $diaryService->get($dtid);
	}
	
	function getSourceUrl($sourceId) /*Abstract function*/ {
		global $db_bbsurl;
		$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
		$diaryTemp = $diaryService->get($sourceId);
		if (!$diaryTemp) return '';
		return $db_bbsurl.'/apps.php?q=diary&a=detail&did='.$sourceId.'&uid='.$diaryTemp['uid'];
	}
	
	function getSourceType() {
		return 'diary';
	}
}