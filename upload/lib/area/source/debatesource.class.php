<?php
/**
 * 辩论排行数据调用服务 
 */

!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_DebateSource extends SystemData {
	
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		return $this->_getDataBySortType($config['sorttype'],$config['fid'],$num);
	}
	
	function _getDataBySortType($sortType,$fid,$num) {
		$dao = $this->getDebateDao();
		$data = array();
		$fid = $this->_cookFid($fid);
		switch ($sortType) {
			case 'new':
				$data = $dao->newDebate($fid,$num);
				break;
			case 'end':
				$data = $dao->endDebate($fid,$num);
				break;
			case 'replysort':
				$data = $dao->replySortDebate($fid,$num);
				break;
			case 'hotsort':
				$data = $dao->hotSortDebate($fid,$num);
				break;
			case 'hitsort':
				$data = $dao->hitSortDebate($fid,$num);
				break;
		}
		$data = $this->_cookData($data);
		return $data;
	}
	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '辩论排行', 
				'type' => 'select', 
				'value' => array(
					'new'		=> '最新辩论',
					'end'		=> '即将截止',
					'replysort'	=> '回复排行',
					'hotsort'	=> '热门排行',
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
			$v['authorurl']	= 'u.php?uid='.$v['authorid'];
			$v['forumname']	= getForumName($v['fid']);
			$v['forumurl']	= getForumUrl($v['fid']);
			if ($v['anonymous']) {
				$v['author'] ='匿名';
				$v['authorid'] = '';
			}
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
	function getDebateDao(){
		static $sDebateDao;
		if(!$sDebateDao){
			$sDebateDao = L::loadDB('debate', 'forum');
		}
		return $sDebateDao;
	}
}

?>