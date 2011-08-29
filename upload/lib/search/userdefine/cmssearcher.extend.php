<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 文章模式搜索扩展
 */
class PW_SearchExtend_CMS_Searcher extends Search_Base {
	var $_sphinx = array ();
	function PW_SearchExtend_CMS_Searcher() {
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
		$offset = intval ( ($page - 1) * $perpage );
		$keywords = explode ( " ", $keywords );
		list ( $total, $searchs ) = $this->_searchCmsWithSubject ( $keywords, $offset, $perpage );
		if (! $total) {
			return array (false, false );
		}
		$total = ($total > $this->_maxResult) ? $this->_maxResult : $total;
		$ids = "";
		foreach ( $searchs as $search ) {
			$ids .= ($ids) ? "," . $search ['article_id'] : $search ['article_id'];
		}
		$searchs = $this->_getCmsByIds ( $ids, $keywords );
		return array ($total, $searchs );
	}
	
	function _searchCmsWithSubject($keywords, $offset, $perpage) {
		$offset = intval($offset);
		$perpage = intval($perpage);
		$sql = "";
		if ($keywords) {
			foreach ( $keywords as $keyword ) {
				$sql = ($sql) ? $sql . " OR " : " AND " . $sql;
				$sql .= " subject LIKE " . S::sqlEscape ( '%' . $keyword . '%' );
			}
		}
		
		if ($this->_groupId != 3) {
			$sql .= " AND postdate <=" . S::sqlEscape ( $this->_timestamp );
		}
		
		$cmsDao = $this->getCmsDao ();
		if (! ($total = $cmsDao->countSearch ( "SELECT COUNT(*) as total FROM pw_cms_article WHERE 1 " . $sql ))) {
			return array (false, false );
		}
		$sql .= "  LIMIT " . $offset . "," . $perpage;
		$result = $cmsDao->getSearch ( "SELECT article_id FROM pw_cms_article WHERE 1 " . $sql );
		return array ($total, $result );
	}
	
	function _getCmsByIds($ids, $keywords) {
		if (! $ids)
			return array ();
		$cmsDao = $this->getCmsDao ();
		$sql = "SELECT a.*,e.hits FROM pw_cms_article a LEFT JOIN pw_cms_articleextend e ON a.article_id=e.article_id WHERE a.article_id IN (" . $ids . ")";
		$sql .= " ORDER BY postdate DESC";
		if (! ($cms = $cmsDao->getSearch ( $sql ))) {
			return array ();
		}
		return $this->_buildCms ( $cms, $keywords );
	}
	
	function _buildCms($cms, $keywords) {
		if (! $cms)
			return array ();
		$result = $columnInfo = array ();
		$keywords = (s::isArray ( $keywords )) ? $keywords : array ($keywords );
		$columnDb = $this->_getColumnName ( $cms );
		foreach ( $columnDb as $value ) {
			$columnInfo [$value ['column_id']] = $value ['name'];
		}
		
		foreach ( $cms as $value ) {
			$value ['article_id'] = $value ['article_id'];
			$value ['createtime'] = get_date ( $value ['postdate'], "Y-m-d H:i" );
			$value ['descrip'] = strip_tags ( $value ['descrip'] );
			$value ['descrip'] = substrs ( $value ['descrip'], 170);
			$value ['column_id'] = $value ['column_id'];
			$value ['column_name'] = ($value ['column_id']) ? $columnInfo [$value ['column_id']] : '末分类';
			$value ['postdate'] = get_date ( $value ['postdate'], "Y-m-d H:i" );
			foreach ( $keywords as $keyword ) {
				$keyword && $value ['subject'] = $this->_highlighting ( $keyword, $value ['subject'] );
				$keyword && $value ['descrip'] = $this->_highlighting ( $keyword, $value ['descrip'] );
			}
			$result [] = $value;
		}
		return $result;
	}
	
	function _getColumnName($cms) {
		if (! $cms)
			return array ();
		$cmsColumnDao = $this->getCmsColumnDao ();
		$columnIds = array ();
		foreach ( $cms as $value ) {
			$columnIds [] = $value ['column_id'];
		}
		return $cmsColumnDao->getColumn ( $columnIds );
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
		$conditions ['index'] = (isset($this->_sphinx['cmsindex'])) ? $this->_sphinx['cmsindex'] : 'cmsindex';
		$conditions ['sortby'] = 'postdate';
		if ($this->_groupId != 3) {
			$conditions ['filterRange'] = array( array('attribute' => 'postdate','min' => 0,'max' => $this->_timestamp,'exclude' => false));
		}
		$result = $_sphinxSearch->sphinxSearcher ( $conditions, 'id' );
		$total = $result [0];
		$searchs = $this->_getCmsByIds ( $result [1], $keywords );
		return array ($total, $searchs );
	}
	
	function searchDefault($page = 1, $perpage = 50) {
		$page = intval ( $page );
		$perpage = intval ( $perpage );
		if (1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getLatestCms ( $page, $perpage );
	}
	
	/**
	 * 获取最新文章
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getLatestCms($page, $perpage = 50) {
		$page = $page > 1 ? $page : 1;
		$offset = intval ( ($page - 1) * $perpage );
		$cmsDao = $this->getCmsDao ();
		$_sqlWhere = '';
		$_sqlWhere .= " AND a.postdate <=" . S::sqlEscape ( $this->_timestamp );
		if (! ($total = $cmsDao->countSearch ( "SELECT count(*) as total FROM pw_cms_article a WHERE 1 $_sqlWhere" ))) {
			return array (false, false );
		}
		$total = ($total < 500) ? $total : 500;
		$sql = "SELECT a.*,e.hits FROM pw_cms_article a LEFT JOIN pw_cms_articleextend e ON a.article_id=e.article_id WHERE 1 $_sqlWhere";
		$sql .= " ORDER BY postdate DESC";
		$sql .= "  LIMIT " . $offset . "," . $perpage;
		$result = $cmsDao->getSearch ( $sql );
		return array ($total, $this->_buildCms ( $result, array () ) );
	}
	
	/**
	 * cms表DAO
	 * @return unknown_type
	 */
	function getCmsDao() {
		static $sCmsDao;
		if (! $sCmsDao) {
			require_once (R_P . 'mode/cms/require/core.php');
			$sCmsDao = C::loadDB ( 'article' );
		}
		return $sCmsDao;
	}
	
	/**
	 * 文章栏目表DAO
	 * @return unknown_type
	 */
	function getCmsColumnDao() {
		static $sCmsColumnDao;
		if (! $sCmsColumnDao) {
			require_once (R_P . 'mode/cms/require/core.php');
			$sCmsColumnDao = C::loadDB ( 'Column' );
		}
		return $sCmsColumnDao;
	}
}