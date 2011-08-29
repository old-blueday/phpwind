<?php
!defined('P_W') && exit('Forbidden');

/**
 * 群组SEO
 * @author luomingqu
 * @package SearchSEO
 */
class PW_SearchSEO {
	var $_searchname = '搜索';
	var $_bbsname;
	var $_pageTitle = '';
	var $_pageMetadescrip = '';
	var $_pageMetakeyword = '';
	
	function PW_SearchSEO() {
		global $db_bbsname;
		$this->_bbsname = $db_bbsname;
	}
	
	function getPageTitle($title,$keyword) {
		$pageTitle = $title ? $title .' - ' : '';
		$pageTitle .= $keyword ? $keyword. ' - ' : '';
		$pageTitle .= $this->_searchname;
		$pageTitle .= $this->_bbsname ? ' - '.$this->_bbsname : '';
		return $pageTitle;
	}

	function getPageMetadescrip() {
		return '';
	}

	function getPageMetakeyword() {
		return '';
	}
}
?>