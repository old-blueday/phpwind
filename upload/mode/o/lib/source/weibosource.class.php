<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_WeiboSource extends SystemData {
	
	/**
	 * 根据配置信息获得新鲜事排行数据
	 * @param Array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['weibotype'], $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
			if (empty($_tmp[$key])) unset($_tmp[$key]);
		}
		return $_tmp;
	}
	
	function getSourceConfig() {
		return array(
			'weibotype' => array(
				'name' => '微薄类型',
				'type' => 'select',
				'value' => $this->_getWeiboType()
			)
		);
	}
	
	function _getWeiboType() {
		return array(
			'all' => '所有类型',
			'article' => '帖子',
			'diary' => '日志',
			//'photos' => '相册',
			'group_article' => '群组话题',
			//'group_photos' => '群组相册',
			'group_active' => '群组活动',
			'group_write' => '群组新鲜事'
		);
	}
	
	/**
	 * 根据类型获得文章排行数据
	 * @param string $type
	 * @param int $columnid
	 * @param int $num
	 */
	function _getData($type, $num) {
		$num = (int) $num;
		if (!$num) $num = 10;
		switch ($type) {
			case 'all':
				return $this->_getAllWeibo($num);
			default:
				return $this->_getWeibosByType($type,$num);
		}
	}
	
	function _getAllWeibo($num) {
		global $topic;
		$temp = '';
		if ($topic) {
			$temp = $topic;
			$topic = '';
		}
		$weiboService = $this->_getWeiboService();
		$result = $weiboService->getWeibos(1, $num);
		$topic = $temp;
		return $result;
	}
	
	function _getWeibosByType($type, $num) {
		$weiboService = $this->_getWeiboService();
		return $weiboService->getWeibosByType($type, 1, $num);
	}
	
	/**
	 * 格式化数据统一输出
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		global $db_bbsurl;
		unset($data['password']);
		$data['url'] = $db_bbsurl . '/apps.php?q=weibo&uid='. $data['uid'];
		$data['content'] = strip_tags($data['content']);
		if (!$data['content']) $data['content'] = '链接内容';
		if (!$data['content'] && $data['transmits']) {
			$data['content'] = '转发：' . $data['transmits']['content'];
		}
		$data['title'] = $data['descrip'] = $data['content'];
		if (empty($data['title'])) return array();
		if ($data['extra']['photos'] && is_array($data['extra']['photos'])) {
			$image = $data['extra']['photos'][0];
			$temp = geturl($image['path']);
			$data['image'] = $temp[0] ? $temp[0] : '';
		}
		$pic = showfacedesign($data['icon'],true,'s');
		if (is_array($pic)) {
			$data['icon'] = $pic[0];
		} else {
			$data['icon'] = '';
		}
		return $data;
	}
	
	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['weibotype'] = isset($config['weibotype']) ? $config['weibotype'] : 'all';
		return $temp;
	}
	
	function _getWeiboService() {
		return L::loadClass('weibo', 'sns'); /*@var PW_Weibo*/
	}
}