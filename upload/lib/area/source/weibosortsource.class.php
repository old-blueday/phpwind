<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');
class PW_WeiboSortSource extends SystemData {
	
	/**
	 * 
	 * 根据配置信息获得话题排行数据
	 * @param array $config 
	 * @param int $num
	 */
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		return $this->_getDataBySortType($config['sorttype'],$num);
	}
	
	/**
	 * 
	 * 获取数据
	 * @param array $config 
	 * @param array $sortType
	 * @param int $num
	 */
	function _getDataBySortType($sortType,$num) {
		$weiboService = $this->_getWeiboService();
		$topicService = $this->_getTopicService();
		$attentionService = $this->_getAttentionService();
		$data = array();
		switch ($sortType) {
			case 'hottransmit':
				$data = $weiboService->getHotTransmit($num);
				break;
			case 'hotcomment':
				$data = $weiboService->getHotComment($num);
				break;
			case 'hottopic';
				$data = $topicService->getWeiboHotTopics();
				break;
			case 'hotuser';
				$data = $attentionService->getTopFansUsers($num);
				break;
		}
		return  $this->_cookData($data);
	}
	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '微博排行',
				'type' => 'select',
				'value' => array(
					'hottransmit'	=> '热门转发',
					'hotcomment'	=> '热门评论',
					'hottopic'		=> '热门话题',
					'hotuser'		=> '新增粉丝',
				)
			)
		);
	}

	/**
	 * 格式化数据统一输出
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		$cookData = array();
		foreach($data as $k => $v){	
			if(isset($v['password'])) unset($v['password']);
			if($v['topicid']){
				if (strpos($v['topicname'],'[s:') !== false && strpos($v['topicname'],']') !== false) {
					unset($data[$k]);
					continue;
				}
				$v['title']	 	= $v['descrip'] = strip_tags($v['topicname']);
				$v['url']		= 'apps.php?q=weibo&do=topics&topic='.$v['topicname'];
				$v['postdate']  = get_date($v['crtime'],'Y-m-d');
			}elseif($v['mid']){
				$v['url'] 	= 'apps.php?q=weibo&do=detail&mid='.$v['mid'].'&uid='.$v['uid'];
				$v['title']	= $v['extra']['title'] ? strip_tags($v['extra']['title']) : strip_tags($v['content']);
				$v['descrip'] = strip_tags($v['content']);
				$v['authorurl']	= 'u.php?uid='.$v['uid'];
				$v['author'] = $v['username'];
				$v['authorid'] = $v['uid'];

				$v['postdate']  = $v['postdate_s'];
				if(S::isArray($v['extra']['photos'])){
					$image = $v['extra']['photos'][0];
					$temp = geturl($image['path']);
					$v['image'] = $temp[0] ? $temp[0] : '';
				}
				$pic = showfacedesign($v['icon'],true,'s');
				$v['icon'] = S::isArray($pic) ? $pic[0] : '';
			}else{
				$v['url'] 	= 'u.php?uid='.$v['uid'];
				$v['title'] = $v['username'];
				$v['uid'] = $v['uid'];
				$v['tags']	= $v['tags'] ? $v['tags'] : "TA还没有标签";
				$v['image'] = $v['icon'] ? $v['icon'] : '';
			}
			if(!$v['title']){
				unset($data[$k]);
				continue;
			}
			$cookData[$k] = $v;
		}
		return $cookData;
	}
	
	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['sorttype'] = $config['sorttype'];
		return $temp;
	}
	
	function _getWeiboService() {
		return L::loadClass('weibo', 'sns'); /*@var PW_Weibo*/
	}
	function _getTopicService() {
		return L::loadClass('topic', 'sns'); /*@var PW_Topic*/
	}
	function _getAttentionService(){
		return L::loadClass('attention', 'friend'); /*@var PW_Topic*/
	}
}