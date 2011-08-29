<?php
/**
 *图酷帖排行数据调用服务 
 */

!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_TuCoolSource extends SystemData {
	
	/**
	 * 
	 * 根据配置信息获得图酷排行数据
	 * @param array $config 
	 * @param int $num
	 */
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		return $this->_getDataBySortType($config['sorttype'],$config['fid'],$num);
	}
	
	/**
	 * 
	 * 获取数据
	 * @param array $config 
	 * @param array $sortType
	 * @param int $num
	 */
	function _getDataBySortType($sortType,$fid,$num) {
		$tucoolService = $this->getTuCoolService();
		$data = array();
		$fid = $this->filterForums($fid);
		switch ($sortType) {
			case 'new':
				$data = $tucoolService->newTuCoolSort($fid,$num);
				break;
			case 'total':
				$data = $tucoolService->subjectPicNumSort($fid,$num);
				break;
			case 'hitsortday':
				$data = $tucoolService->getTucoolThreadsByHitSortToday($fid,$num);
				break;
			case 'hitsortyesterday':
				$data = $tucoolService->getTucoolThreadsByHitSortYesterday($fid,$num);
				break;
		}
		return $this->_cookData($data) ;
	}
	
	/**
	 * 
	 * 获取调用选项信息
	 * @return array
	 */	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '图酷排行', 
				'type' => 'select', 
				'value' => array(
					'new'		=> '最新图酷帖',
					'total'		=> '图片数排行',
					'hitsortday'=> '今日点击',
					'hitsortyesterday'	=> '昨日点击',
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
	 * @param int $fid
	 * @return 
	 */
	function _cookData($data) {
		$attachsService = L::loadClass('attachs','forum');
		foreach ($data as $k=>$v){
			$v['url'] 	= 'read.php?tid='.$v['tid'];
			$v['title'] 	= $v['subject'];
			if(!$v['title']){
				unset($data[$k]);
				continue;
			}
			$v['value'] 	= $v['postdate'];
			$v['hits'] 		= $v['hits'];
			$v['totalnum'] 	= $v['totalnum'];
			$v['collectnum'] = $v['collectnum'];
		//	$temp = geturl($v['cover']);
		//	$v['image'] = $temp[0] ? $temp[0] : '';
			$v['image']	= $attachsService->getThreadAttachMini($v['cover']);
			$v['forumname']	= getForumName($v['fid']);
			$v['forumurl']	= getForumUrl($v['fid']);
			$v['authorurl']	= 'u.php?uid='.$v['authorid'];
			$v['addition'] = $v;
			$data[$k] = $v;
		}
		return $data;
	}

	/**
	 * 
	 * 获取版块
	 * @return array
	 */
	function _getForums() {
		$forumOption = L::loadClass('forumoption');
		$forums = $forumOption->getForums();
		$fids = array();
		foreach ($forums as $key => $v) {
			$foruminfo = L::forum($key);
			if (isset($foruminfo['forumset']['iftucool']) && !$foruminfo['forumset']['iftucool']) continue;
			$fids[$key] = $v;
		}
		return $fids;
	}
	
	/**
	 * 
	 * 过滤条件
	 * @param array 
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['fid'] = $config['fid'];
		$temp['sorttype'] = $config['sorttype'];

		return $temp;
	}	

	/**
	 * 
	 * 版块处理
	 * @param string $fid
	 * @return string
	 */
	function _cookFid($fid) {
		if ($fid && is_numeric($fid)) return $fid;
		if (S::isArray($fid)) {
			foreach ($fid as $key=>$value) {
				if (!$value) unset($fid[$key]);
			}
			if (S::isArray($fid)) return $fid;
		}
		$forumsService = L::loadClass('forums', 'forum');
		return $forumsService->getAllForumIds();
	}

	/**
	 * 
	 * 过滤未开启图酷版块
	 * @param string $fid
	 * @return string
	 */
	function filterForums($fid) {
		$tmpfids = $this->_cookFid($fid);
		$fids = array();
		foreach ((array)$tmpfids as $v) {
			$foruminfo = L::forum($v);
			if (isset($foruminfo['forumset']['iftucool']) && !$foruminfo['forumset']['iftucool']) continue;
			$fids[] = $v;
		}
	 	return S::sqlImplode($fids);
	}
	
	/**
	 * 
	 * 获取图酷service服务
	 * @return array
	 */
	function getTuCoolService(){
		static $sTuCoolService;
		if(!$sTuCoolService){
			$sTuCoolService = L::loadClass('tucool', 'forum');
		}
		return $sTuCoolService;
	}
}

?>