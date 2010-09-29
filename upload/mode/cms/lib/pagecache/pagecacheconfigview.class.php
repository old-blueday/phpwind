<?php
!defined('P_W') && exit('Forbidden');

class PW_PageCacheConfigView {
	
	function getConfig() {
		return $this->_pageConfig();
	}
	
	function _pageConfig() {
		$cache_config = array(
			'hotArticle'=> array('type'=>'article','sorttype'=>'hotday','cachetime'=>1800,'num'=>'10'),
			'newArticle'=> array('type'=>'article','sorttype'=>'new','cachetime'=>400,'num'=>'10'),
			'hotSubject'=> array('type'=>'subject','sorttype'=>'replysortday','cachetime'=>1800,'num'=>'10'),
			'newSubject'=> array('type'=>'subject','sorttype'=>'newsubject','cachetime'=>400,'num'=>'10'),
		);
		return $cache_config;
	}
}
?>