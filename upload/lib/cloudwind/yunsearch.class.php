<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 阿里云搜索服务 公共搜索/索引接口服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class PW_YunSearch {
	var $_objects = array ();
	
	function getArrayResult($type, $conditions) {
		if (! $searchService = $this->_factory ( $type )) {
			return array ();
		}
		return $searchService->getArrayResult ( $conditions );
	}

	function alterIndex($type, $conditions) {
		if (! $searchService = $this->_factory ( $type )) {
			return array ();
		}
		return $searchService->alterIndex ( $conditions );
	}

	
	function createIndex($type, $conditions) {
		if (! $searchService = $this->_factory ( $type )) {
			return array ();
		}
		return $searchService->createIndex ( $conditions );
	}
	
	function markIndex($type, $conditions) {
		if (! $searchService = $this->_factory ( $type )) {
			return array ();
		}
		return $searchService->markIndex ( $conditions );
	}
	
	function detectIndex($type, $conditions){
		if (! $searchService = $this->_factory ( $type )) {
			return array ();
		}
		return $searchService->detectIndex ( $conditions );
	}

	function verify($condition) {
		return true;
	}
	
	function _factory($type) {
		if (! is_object ( $this->_objects [$type] )) {
			switch ($type) {
				case 'thread' :
					require_once R_P . 'lib/cloudwind/search/yunsearchthread.class.php';
					$this->_objects [$type] = new YUN_SearchThread ();
					break;
				case 'post' :
					require_once R_P . 'lib/cloudwind/search/yunsearchpost.class.php';
					$this->_objects [$type] = new YUN_SearchPost ();
					break;
				case 'member' :
					require_once R_P . 'lib/cloudwind/search/yunsearchmember.class.php';
					$this->_objects [$type] = new YUN_SearchMember ();
					break;
				case 'diary' :
					require_once R_P . 'lib/cloudwind/search/yunsearchdiary.class.php';
					$this->_objects [$type] = new YUN_SearchDiary ();
					break;
				case 'colony' :
					require_once R_P . 'lib/cloudwind/search/yunsearchcolony.class.php';
					$this->_objects [$type] = new YUN_SearchColony ();
					break;
				case 'forum' :
					require_once R_P . 'lib/cloudwind/search/yunsearchforum.class.php';
					$this->_objects [$type] = new YUN_SearchForum ();
					break;
				case 'attach' :
					require_once R_P . 'lib/cloudwind/search/yunsearchattach.class.php';
					$this->_objects [$type] = new YUN_SearchAttach ();
					break;
				case 'weibo' :
					require_once R_P . 'lib/cloudwind/search/yunsearchweibo.class.php';
					$this->_objects [$type] = new YUN_SearchWeibo ();
					break;
				default :
					break;
			}
		}
		return $this->_objects [$type];
	}
}
