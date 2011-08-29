<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_ColumnSource extends SystemData {

	/**
	 * 根据配置信息获得栏目数据
	 * @param Array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['columnid'], $num);
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
			'columnid' => array(
				'name' => '选择栏目', 
				'type' => 'select', 
				'value' => $this->_getColumns(),
			)
		);
	}

	function _getColumns() {
		$columnService = C::loadClass('columnservice');

		$columns = $columnService->getAllOrderColumns();
		$temp = array();
		$temp[] = '一级栏目';
		foreach ($columns as $value) {
			if ($value['level'] > 1) continue;
			$name = $value['level'] ? '&nbsp;|-'.$value['name'] : $value['name'];
			$temp[$value['column_id']] = $name;
		}
		return $temp;
	}

	/**
	 * 根据类型获得栏目数据
	 * @param string $type
	 * @param int $columnid
	 * @param int $num
	 */
	function _getData($columnid, $num) {
		$columnService = C::loadClass('columnservice');
		return $columnService->getSubColumnsById($columnid,$num);
	}
	
	/**
	 * 格式化数据统一输出
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		global $db_bbsurl;
		$data['url'] = $db_bbsurl . '/'.getColumnUrl($data['column_id']);
		$data['title'] = strip_tags($data['name']);
		$data['descrip'] = strip_tags($data['name']);
		return $data;
	}

	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['columnid'] = isset($config['columnid']) ? $config['columnid'] : 0;
		return $temp;
	}
}