<?php
/**
 * 团购排行数据调用服务 
 */

!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_PcvalueSource extends SystemData {
	
	/**
	 * 根据配置信息获得团购排行数据
	 * @param Array $config
	 * @param int $num
	 */
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		return $this->_getDataBySortType($config['sorttype'],$config['fid'],$num);
	}
	
	function _getDataBySortType($sortType,$fid,$num) {
		$pcvalueDao = $this->getPcvalueDao();
		$data = array();
		$fid = $this->_cookFid($fid);
		switch ($sortType) {
			case 'newTop':
				$data = $pcvalueDao->getSourceByPostdate($fid,$num);
				break;
			case 'endtime':
				$data = $pcvalueDao->getSourceByEndtime($fid,$num);
				break;
			case 'replysTop':
				$data = $pcvalueDao->getSourceByReplys($fid,$num);
				break;
			case 'hitsTop':
				$data = $pcvalueDao->getSourceByHits($fid,$num);
				break;
		}
		$data = $this->_cookData($data);
		return $data;
	}
	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '团购排行', 
				'type' => 'select', 
				'value' => array(
					'newTop'		=> '最新团购',
					'endtime'		=> '即将截止',
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

	function _cookData($data) {
		foreach ($data as $k=>$v){
			$v['url'] 	= 'read.php?tid='.$v['tid'];
			$v['title'] 	= $v['subject'];
			$v['value'] 	= $v['postdate'];
			$temp = geturl($v['pcattach']);
			$v['image'] = $temp[0] ? $temp[0] : '';
			$v['authorurl']	= 'u.php?uid='.$v['authorid'];
			$v['author'] = $v['anonymous'] ? '匿名' : $v['author'];
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

	function getPcvalueDao(){
		static $sPcvalueDao;
		if(!$sPcvalueDao){
			$sPcvalueDao = L::loadDB('pcvalue', 'forum');
		}
		return $sPcvalueDao;
	}
}

?>