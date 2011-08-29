<?php
!defined('P_W') && exit('Forbidden');
include_once (R_P . 'lib/datanalyse/datanalyse.base.php');

class PW_Articleanalyse extends PW_Datanalyse {
	var $pk = 'article_id';
	var $actions = array();

	function PW_Articleanalyse() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct();
	}

	/**
	 * 获得根据类别评价类型
	 * @return array
	 */
	function _getExtendActions() {
		$columnservice = $this->_getColumnService();
		/* @var $columnservice PW_ColumnService */
		$columns = $columnservice->getAllOrderColumns();
		$_tmp = array();
		foreach ((array) $columns as $column) {
			$_tmp[] = 'article_' . $column['column_id'];
		}
		return $_tmp;
	}
	
	/**
	 * 根据文章ID数组获取热门文章列表
	 * @return array
	 */
	function _getHotArticlesByTags() {
		if (empty($this->tags)) return array();
		$articleDB = C::loadDB('article');
		/* @var $articleDB PW_ArticleDB */
		return $articleDB->getHotArticlesByIds($this->tags);
	}

	/**
	 * 根据日志ID数组获得日志信息
	 * @return array
	 */
	function _getDataByTags() {
		if (empty($this->tags)) return array();
		$articleDB = C::loadDB('article');
		/* @var $articleDB PW_ArticleDB */
		return $articleDB->getArticlesByIds($this->tags);
	}

	function _getColumnService() {
		return C::loadClass('columnservice');
	}

}
?>