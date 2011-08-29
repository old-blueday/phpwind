<?php
/**
 * 分类信息排行数据调用服务 
 */

!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_ClassifySource extends SystemData {
	var $_lang = array(
		'forumurl' => '版块链接',
		'author' => '作者',
		'authorid' => '作者id',
		'postdate' => '发布时间',
		'topictypename' => '分类名称',
		'topictypeurl' => '分类id'
	);
	
	/**
	 * 
	 * 根据配置信息获得分类信息排行数据
	 * @param array $config 
	 * @param int $num
	 */
	function getSourceData($config,$num) {
		$data = array();
		$config = $this->_initConfig($config);
		$data = $this->_getData($config, $num);
		if(empty($data)) return $data;
		return $this->_cookData($data);
	}
	
	/**
	 * 
	 * 获取调用选项信息
	 * @return array
	 */	
	function getSourceConfig() {
		return array(
			'fid' => array(
				'name' => '选择版块',
				'type' => 'mselect',
				'value' => $this->_getForums()
			),
			'type' => array(
				'name' => '分类信息类型',
				'type' => 'mselect',
				'value' => $this->_getClassify()
			),
			'sorttype' => array(
				'name' => '排序类型',
				'type' => 'select',
				'value' => array(
					'newtopic' 		=> '最新主题',
					'newreply' 		=> '最新回复',
					'toppedtopic' 	=> '置顶主题'
				)
			)
		);
	}
	
	/**
	 * 
	 * 获取数据
	 * @param array $config 
	 * @param int $num
	 */
	function _getData($config, $num) {
		$dao = $this->_getClassifyDao();
		$data = array();
		$modelid = $this->_cookModelid($config['type']);
		$fid = $this->_cookFid($config['fid']);
		switch ($config['sorttype']) {
			case 'newtopic' :
				$data = $dao->newClassifyTopic($modelid, $fid, $num);
				break;
			case 'newreply' :
				$data = $dao->newClassifyReply($modelid, $fid, $num);
				break;
			case 'toppedtopic' :
				$data = $dao->toppedClassifyTopic($modelid, $fid, $num);
				break;
		}
		return $data;
	}
	
	/**
	 * 
	 * 数据处理
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		foreach ($data as $key => $value) {
			$v = array();
			$v['url'] = 'read.php?tid='.$value['tid'];
			$v['authorurl'] = 'u.php?uid='.$value['authorid'];
			$v['title'] = $value['subject'];
			$v['forumname'] = getForumName($value['fid']);
			$v['forumurl'] = getForumUrl($value['fid']);
			$v['author'] = $value['anonymous'] ? '匿名' : $value['author'];
			$v['authorid'] = $value['authorid'];
			$v['postdate'] = $value['postdate'];
			$v['topictypename'] = $value['modelname'];
			$v['topictypeurl'] = 'thread.php?fid=' . $value['fid'] . '&modelid=' . $value['modelid'];
			$data[$key] = $v;
		}
		return $data;
	}
	
	/**
	 * 
	 * 过滤条件
	 * @param array 
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['type'] = $config['type'];
		$temp['fid'] = $config['fid'];
		$temp['sorttype'] = $config['sorttype'];
		return $temp;
	}
	
	/**
	 * 
	 * 获取分类信息类型
	 * @return array
	 */
	function _getClassify() {
		$classifyType = array('全部类型');
		$topiccatedb = $this->_getTopicCatedb();
		$topicmodeldb = $this->_getTopicModeldb();
		foreach ($topiccatedb as $key => $value) {
			if (!$value['ifable']) continue;
			$classifyType['c_' . $key] = $value['name'];
			foreach ($topicmodeldb as $k => $v) {
				if (!$v['ifable'] || $v['cateid'] != $key) continue;
				$classifyType['m_' . $k] = '--' . $v['name'];
			}
		}
		return $classifyType;
	}
	
	function _getTopicCatedb() {
		global $db;
		$topiccatedb = array();
		$query = $db->query("SELECT * FROM pw_topiccate ORDER BY vieworder,cateid");
		while ($rt = $db->fetch_array($query)) {
			$topiccatedb[$rt['cateid']] = $rt;
		}
		return $topiccatedb;
	}
	
	
	function _getTopicModeldb() {
		global $db;
		$topicmodeldb = array();
		$query = $db->query("SELECT * FROM pw_topicmodel ORDER BY vieworder,modelid");
		while ($rt = $db->fetch_array($query)) {
			$topicmodeldb[$rt['modelid']] = $rt;
		}
		return $topicmodeldb;
	}
	
	/**
	 * 
	 * 获取版块
	 * @return array
	 */
	function _getForums() {
		$forumOption = L::loadClass('forumoption');
		return $forumOption->getForums();
	}
	
	/**
	 * 
	 * 分类信息类型处理
	 * @param array 
	 * @return array
	 */
	function _cookModelid ($type) {
		$modelids = array();
		!S::isArray($type) && $type = array($type);
		$topicCate = $this->_getTopicCate();
		foreach ($type as $value) {
			if (!$value) return array();
			list($cateType, $id) = explode('_', $value);
			if ($cateType == 'c' && !empty($topicCate[$id])) {
				foreach ($topicCate[$id] as $v) {
					$modelids[] = (int) $v;
				}
				continue;
			}
			$modelids[] = (int) $id;
		}
		return array_unique(array_filter($modelids));
	}
	
	/**
	 * 
	 * 版块处理
	 * @param mixed
	 * @return string
	 */
	function _cookFid($fid) {
		return getCookedCommonFid($fid);
	}
	
	/**
	 * 
	 * 获得包含父类子类的分类
	 * @return array
	 */
	function _getTopicCate() {
		$topiccatedb = $this->_getTopicCatedb();
		$topicmodeldb = $this->_getTopicModeldb();
		if (empty($topiccatedb) || empty($topicmodeldb)) return array();
		$topicCate = array();
		foreach ($topiccatedb as $key => $value) {
			if (!$value['ifable']) continue;
			foreach ($topicmodeldb as $v) {
				if (!$v['ifable'] || $v['cateid'] != $key) continue;
				$topicCate[$key][] = $v['modelid'];
			}
		}
		return $topicCate;
	}
	
	/**
	 * 
	 * 获取分类信息dao服务
	 * @return array
	 */
	function _getClassifyDao() {
		static $sClassifyDao;
		if(!$sClassifyDao){
			$sClassifyDao = L::loadDB('classify', 'forum');
		}
		return $sClassifyDao;
	}
}
?>