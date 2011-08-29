<?php
/**
 * 商品排行数据调用服务 
 */

!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_TradeSource extends SystemData {
	
	/**
	 * 
	 * 根据配置信息获得商品排行数据
	 * @param Array $config 
	 * @param int $num
	 */
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		return $this->_getDataBySortType($config['sorttype'],$config['fid'],$num);
	}

	/**
	 * 
	 * 根据排行分类获取数据
	 * @param string $sortType
	 * @param array $fid 板块ID
	 * @param int $num
	 * @return array
	 */
	function _getDataBySortType($sortType,$fid,$num) {
		$tradeDao = $this->getTradeDao();
		$data = array();
		$fid = $this->_cookFid($fid);
		switch ($sortType) {
			case 'newTrade':
				$data = $tradeDao->getSourceByPostdate($fid,$num);
				break;
			case 'saleTop':
				$data = $tradeDao->getSourceBySalenum($fid,$num);
				break;
			case 'replysTop':
				$data = $tradeDao->getSourceByReplys($fid,$num);
				break;
			case 'hitsTop':
				$data = $tradeDao->getSourceByHits($fid,$num);
				break;
		}
		$data = $this->_cookData($data);
		return $data;
	}

	/**
	 * 
	 * 获取调用选项信息
	 * @return array
	 */	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '商品排行', 
				'type' => 'select', 
				'value' => array(
					'newTrade'		=> '最新商品',
					'saleTop'		=> '销售排行',
					'replysTop'	=> '回复排行',
					'hitsTop'	=> '点击排行',
				)
			),
			'fid'	=> array(
				'name' 	=> '选择版块',
				'type' 	=> 'mselect',
				'value'	=> $this->_getForums(),
			),
		);
	}

	/**
	 * 
	 * 数据处理
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		foreach ($data as $k=>$v){
			$v['url'] 	= 'read.php?tid='.$v['tid'];
			$v['title'] 	= $v['subject'];
			$v['value'] 	= $v['postdate'];
			$temp = geturl($v['icon']);
			$v['image'] = $temp[0] ? $temp[0] : '';
			$v['authorurl']	= 'u.php?uid='.$v['authorid'];
			$v['forumname']	= getForumName($v['fid']);
			$v['forumurl']	= getForumUrl($v['fid']);
			list($v['topictypename'],$v['topictypeurl']) = getTopicType($v['type'],$v['fid']);
			$v['addition'] = $v;
			$data[$k] = $v;
		}
		return $data;
	}

	function _getForums() {
		$forumOption = L::loadClass('forumoption');
		return $forumOption->getForums();
	}

	function _initConfig($config) {
		$temp = array();
		$temp['fid'] = $config['fid'];
		$temp['sorttype'] = $config['sorttype'];
		return $temp;
	}

	function _cookFid($fid) {
		return getCookedCommonFid($fid);
	}

	function getTradeDao(){
		static $sTradeDao;
		if(!$sTradeDao){
			$sTradeDao = L::loadDB('trade', 'forum');
		}
		return $sTradeDao;
	}
}

?>