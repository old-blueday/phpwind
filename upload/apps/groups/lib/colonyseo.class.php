<?php
!defined('P_W') && exit('Forbidden');

/**
 * 群组SEO
 * @author zhudong
 * @package colony
 */

class PW_ColonySEO {
	
	var $_db;
	var $bbsname;
	

	function PW_ColonySEO() {
		global $db_bbsname;
		$this->bbsname = $db_sitename;
	}
	
	function getPageTitle($firstTitle='',$secondTitle) {
		$pageTitle = $firstTitle ? $firstTitle.'-'.$secondTitle.'-'.$this->bbsname : $secondTitle.'-'.$this->bbsname;
		return $pageTitle;
	}

	function getPageMetadescrip($descrip) {
		return $descrip;
	}

	function getPageMetakeyword($firstKeyword,$secondKeyword='') {
		$pageMetakeyword = $secondKeyword ? $firstKeyword.','.$secondKeyword : $firstKeyword;
		return $pageMetakeyword;
	}

}


?>