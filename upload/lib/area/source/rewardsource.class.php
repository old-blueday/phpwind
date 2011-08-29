<?php
/**
 * 悬赏排行数据调用服务 
 */

!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_RewardSource extends SystemData {
	
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		return $this->_getDataBySortType($config['sorttype'],$config['fid'],$num);
	}
	
	function _getDataBySortType($sortType,$fid,$num) {
		$dao = $this->getRewardDao();
		$data = array();
		global $timestamp;
		$timestamp = intval($timestamp);
		if(!$timestamp) return array();
		$fid = $this->_cookFid($fid);
		switch ($sortType) {
			case 'new':
				$data = $dao->newReward($fid,$num,$timestamp);
				break;
			case 'top':
				$data = $dao->topReward($fid,$num,$timestamp);
				break;
			case 'replysort':
				$data = $dao->replySortReward($fid,$num,$timestamp);
				break;
			case 'hitsort':
				$data = $dao->hitSortReward($fid,$num,$timestamp);
				break;
		}
		$data = $this->_cookData($data);
		return $data;
	}
	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '悬赏排行', 
				'type' => 'select', 
				'value' => array(
					'new'		=> '最新悬赏',
					'top'		=> '悬赏排行',
					'replysort'	=> '回复排行',
					'hitsort'	=> '点击排行',
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
			$v['image']	= '';
			$v['forumname']	= getForumName($v['fid']);
			$v['forumurl']	= getForumUrl($v['fid']);
			$v['authorurl']	= 'u.php?uid='.$v['authorid'];
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
	function getRewardDao(){
		static $sRewardDao;
		if(!$sRewardDao){
			$sRewardDao = L::loadDB('reward', 'forum');
		}
		return $sRewardDao;
	}
}

?>