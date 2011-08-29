<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');
require_once (R_P . 'require/bbscode.php');

class PW_AnnounceSource extends SystemData {

	/**
	 * @param array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['announcesort'], $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
		}
		return $_tmp;
	}

	/**
	 * @param string $type
	 * @param int $num
	 */
	function _getData($type, $num) {
		$write = L::loadDB('announce', 'forum');
		return $write->getNewData($num);
	}

	/**
	 * 格式化输出结果
	 * @param unknown_type $data
	 * @return unknown
	 */
	function _cookData($data) {
		global $db_bbsurl, $db_windpost;
		$data['url'] = $data['url'] ? $data['url'] : $db_bbsurl . '/notice.php?fid='.$data['fid'].'#' . $data['aid'];
		$data['title'] = convert($data['subject'], $db_windpost);
		if($data['author']){
			$userService = L::loadClass('userService', 'user');
			$userId = $userService->getUserIdByUserName($data['author']);
			$data['authorurl'] = 'u.php?uid='.$userId;
		}else{
			$data['authorurl'] = '';
		}
		$data['content'] = convert($data['content'], $db_windpost);
		$data['descrip'] = substrs(strip_tags($data['content']),100);
		$data['postdate'] = $data['startdate'];
		return $data;
	}

	/**(non-PHPdoc)
	 * @see lib/base/SystemData#getSourceConfig()
	 */
	function getSourceConfig() {
		return array(
			'announcesort' => array(
				'name' => '公告排行', 
				'type' => 'select', 
				'value' => array(
					'new' => '最新公告'
				)
			)
		);
	}

	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['announcesort'] = isset($config['announcesort']) ? $config['announcesort'] : '';
		return $temp;
	}

}
?>