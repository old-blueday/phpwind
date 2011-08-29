<?php
!defined('P_W') && exit('Forbidden');
class PW_ForumOption{
	function getForums() {
		static $result = array();
		if ($result) return $result;
		require_once (R_P . 'require/functions.php');
		$forums = getForumCache();
		$result[] = '全站调用';
		foreach ($forums as $fid=>$forum) {
			if ($forum['type']=='category') continue;
			
			$result[$fid] = $this->_initForumName($forum);
		}
		return $result;
	}
	
	function _initForumName($forum) {
		$forum['name'] = strip_tags($forum['name']);
		if ($forum['type']=='forum') 
			return ' &nbsp;|- '.$forum['name'];
		if ($forum['type']=='sub') 
			return ' &nbsp; &nbsp;|-  '.$forum['name'];
		if ($forum['type']=='sub2') 
			return ' &nbsp; &nbsp; &nbsp;|-  '.$forum['name'];
	}
}