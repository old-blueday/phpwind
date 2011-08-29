<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P.'lib/base/systemdata.php');
class PW_GroupSource extends SystemData {
	var $_element;
	var $_lang = array(
		'title'		=> '群组名称',
		'tnum'		=> '主题数',
		'pnum'		=> '帖子数',
		'members'	=> '成员数',
		//'todaypost'	=> '今日发帖',
		'createtime'=> '创建时间',
		'stylename'	=> '分类名称',
		'credit'	=> '积分'
	);
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		
		return $this->_getData($config['groupsort'],$config['groupclass'],$num);
	}
	
	function _getData($groupSort,$gourpClass,$num) {
		$num = (int) $num ? (int) $num : 10;
		$temp = $this->_getGroupSortData($groupSort,$gourpClass,$num);

		foreach ($temp as $key=>$value) {
			$temp[$key] = $this->_cookData($value);
		}
		return $temp;
	}
	
	function _getGroupSortData($groupSort,$gourpClass,$num) {
		$groupDAO = L::loadDB('colonys', 'colony');
		return $groupDAO->getSortByTypeAndClassId($groupSort,$gourpClass,$num);
	}
	
	
	function _cookData($data) {
		global $db_bbsurl;
		$data['url'] = $db_bbsurl.'/apps.php?q=group&cyid='.$data['id'];
		$data['title'] = $data['cname'];
		$data['image'] = $this->_getGroupImage($data['cnimg']);
		$data['descrip'] = substrs(strip_tags(stripWindCode($data['descrip'])),100);
		if ($data['credit']) $data['credit'] = (int) $data['credit'];
		return $data;
	}
	
	function _getGroupImage($cnimg) {
		if (!$cnimg) {
			global $imgpath;
			return $imgpath.'/g/groupnopic.gif';
		}
		list($cnimg) = geturl("cn_img/$cnimg",'lf');
		return $cnimg;
	}

	function getSourceConfig() {
		return array(
			'groupsort' 	=> array(
				'name' 	=> '群组排行',
				'type'	=> 'select',
				'value'	=> array(
					'tnum'		=>	'主题排行',
					'pnum'		=>	'发帖排行',
					'members'	=>	'成员排行',
					//'todaypost'	=>	'今日发帖排行',
					'createtime'=>	'最新群组',
					'credit'=>	'群组积分排行',
				),
			),
			'groupclass' 	=> array(
				'name' 	=> '群组分类',
				'type'	=> 'select',
				'value'	=> $this->_getGroupClass(),
			),
		);
	}
	
	function _getGroupClass() {
		require_once(R_P.'apps/groups/lib/groupstyle.class.php');
		
		$temp = array();
		$groupStyle = new GroupStyle();
		$firstGradeStyleIds	= $groupStyle -> getFirstGradeStyles();
		
		$secondGradeStyles  = $groupStyle -> getGradeStylesByUpid(array_keys($firstGradeStyleIds));
		
		$temp = array();
		$temp[] = '全部调用';
		foreach ($firstGradeStyleIds as $first) {
			$temp[$first['id']] = '&gt;&gt; '.strip_tags($first['cname']);
			$tempSecond = isset($secondGradeStyles[$first['id']]) ? $secondGradeStyles[$first['id']] : array();
			$this->_initSecondGradeStyles($temp,$tempSecond);
		}
		return $temp;
	}
	
	function _initSecondGradeStyles(&$temp,$styles) {
		foreach ($styles as $second) {
			$temp[$second['id']] = ' &nbsp;|- '.strip_tags($second['cname']);
		}
	}
	
	function _initConfig($config) {
		$temp = array();
		$temp['groupsort'] = $config['groupsort'];
		$temp['groupclass'] = $config['groupclass'];

		return $temp;
	}
	
}