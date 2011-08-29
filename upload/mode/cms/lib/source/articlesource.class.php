<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_ArticleSource extends SystemData {
	
	/**
	 * 根据配置信息获得文章排行数据
	 * @param Array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['sorttype'], $config['columnid'], $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
		}
		return $_tmp;
	}
	
	/* (non-PHPdoc)
	 * @see lib/base/SystemData#getSourceConfig()
	 */
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '文章排行',
				'type' => 'select',
				'value' => array(
					'new' => '最新文章',
					'hotday' => '24小时热门',
					'hotmonth' => '热门文章'
				)
			),
			'columnid' => array(
				'name' => '选择栏目',
				'type' => 'mselect',
				'value' => $this->_getColumns()
			)
		);
	}
	
	function _getColumns() {
		$columnService = C::loadClass('columnservice');
		
		$columns = $columnService->getAllOrderColumns();
		$temp = array();
		$temp[] = '所有栏目';
		foreach ($columns as $value) {
			$name = $value['level'] ? ($value['level'] == 1 ? '&nbsp;|-' . $value['name'] : '&nbsp;&nbsp;|-' . $value['name']) : $value['name'];
			$temp[$value['column_id']] = $name;
		}
		return $temp;
	}
	
	/**
	 * 根据类型获得文章排行数据
	 * @param string $type
	 * @param int $columnid
	 * @param int $num
	 */
	function _getData($type, $columnid, $num) {
		$columnid = is_array($columnid) ? $columnid:array($columnid);
		if (count($columnid)==1 && $columnid[0]==0) $columnid = array();
		switch ($type) {
			case 'new':
				return $this->_getNewArticle($columnid, $num);
			case 'hotday':
				return $this->_getHotDayArticle($columnid, $num);
			case 'hotmonth':
				return $this->_getHotMonthArticle($columnid, $num);
			default:
				return array();
		}
	}
	function _getNewArticle($columnid, $num) {
		global $timestamp;
		$articleService = C::loadClass('articleservice');
		$type = 1;
		return $articleService->searchAtricles($columnid, '', '', $type, '',$timestamp, 0, $num);
	}
	function _getHotDayArticle($columnid, $num) {
		return $this->_getHotArticle('hotday', $columnid, $num);
	}
	function _getHotMonthArticle($columnid, $num) {
		return $this->_getHotArticle('hotmonth', $columnid, $num);
	}
	
	function _getHotArticle($type, $columnid, $num) {
		global $timestamp;
		$date = PwStrtoTime(get_date($timestamp, 'Y-m-d'));
		$tempDate = $type == 'hotday' ? 1 : 30;
		$date = $date - $tempDate * 86400;
		$datanalyseService = $this->_getDatanalyseService();
		$_action = array();
		foreach ($columnid as $value) {
			$_action[] = 'article_' . $value;
		}
		if (!$columnid) $_action = $datanalyseService->getAllActions('article');
		return $datanalyseService->getHotArticleByAction('article', $_action, $num, $date);
	}
	/**
	 * 格式化数据统一输出
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		global $db_bbsurl;
		$data['author'] = $data['username'];
		$data['authorurl'] = 'u.php?uid='.$data['userid'];
		$data['url'] = urlRewrite($db_bbsurl . '/' . getArticleUrl($data['article_id']));
		$data['title'] = strip_tags($data['subject']);
		$data['forumname'] = $data['columnname'] = $this->_getColumnName($data['column_id']);
		$data['forumurl'] = $data['columnurl'] = getColumnUrl($data['column_id']);
		return $data;
	}
	
	function _getColumnName($cloumnId) {
		$columns = $this->_getClomns();
		foreach ($columns as $value) {
			if ($value['column_id'] == $cloumnId) return $value['name'];
		}
		return '';
	}
	
	function _getClomns() {
		static $columns = array();
		if ($columns) return $columns;
		$columnService = C::loadClass('columnservice');
		$columns = $columnService->findAllColumns();
		return $columns;
	}
	
	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['sorttype'] = isset($config['sorttype']) ? $config['sorttype'] : '';
		$temp['columnid'] = isset($config['columnid']) ? $config['columnid'] : 0;
		return $temp;
	}
	
	function _getDatanalyseService() {
		return C::loadClass('cmsdatanalyseservice', 'datanalyse');
	}
}