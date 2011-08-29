<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 文章模式搜索扩展
 */
class PW_SearchExtend_Weibo_Searcher extends Search_Base {
	var $_sphinx = array ();
	function PW_SearchExtend_Weibo_Searcher() {
		global $db_sphinx;
		parent::Search_Base ();
		$this->_sphinx = &$db_sphinx;
	}
	function search($conditions, $page = 1, $perpage = 20) {
		return ($this->_sphinx ['isopen']) ? $this->_searchUseSphinx ( $conditions, $page, $perpage ) : $this->_searchUseMySQL ( $conditions, $page, $perpage );
	}
	
	/*
	 * mysql搜索方式
	 */
	function _searchUseMySQL($conditions, $page = 1, $perpage = 20) {
		if (! $conditions || ! $conditions ['keywords'])
			return false;
		$keywords = $conditions ['keywords'];
		$keywords = $this->_checkKeywordCondition ( $keywords );
		if (! $keywords)
			return array (false );
		$page = $page > 1 ? intval($page) : 1;
		$weiboDao = $this->_getWeiboDao();
		return $weiboDao->search($keywords,'','','','DESC',$page,$perpage);
	}
	
	/*
	 * sphinx搜索方式
	 */
	function _searchUseSphinx($conditions, $page = 1, $perpage = 20) {
		if (! $conditions || ! $conditions ['keywords'])
			return false;
		$keywords = $conditions ['keywords'];
		$keywords = $this->_checkKeywordCondition ( $keywords );
		if (! $keywords)
			return array (false );
		$page = $page > 1 ? intval($page) : 1;
		$offset = intval ( ($page - 1) * $perpage );
		require_once R_P . "lib/search/search/sphinx.search.php";
		$_sphinxSearch = new Search_Sphinx ();
		$conditions ['keywords'] = $keywords;
		$conditions ['offset'] = $offset;
		$conditions ['perpage'] = $perpage;
		$conditions ['index'] = (isset($this->_sphinx['weiboindex'])) ? $this->_sphinx['weiboindex'] : 'weiboindex';
		$conditions ['sortby'] = 'postdate';
		$result = $_sphinxSearch->sphinxSearcher ( $conditions, 'id' );
		$total = $result [0];
		$searchs = $this->_getCmsByIds ( $result [1], $keywords );
		return array ($total, $searchs );
	}
	
	
	/**
	 * @return PW_Weibo
	 */
	function _getWeiboService() {
		return L::loadClass ( 'Weibo', 'sns' );
	}
	
	/**
	 * @return PW_Weibo_ContentDB
	 */
	function _getWeiboDao() {
		static $sWeiboDao;
		if (! $sWeiboDao) {
			$sWeiboDao = L::loadDB('weibo_content','sns');
		}
		return $sWeiboDao;
	}
}