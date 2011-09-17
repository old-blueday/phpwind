<?php 
!defined('P_W') && exit('Forbidden');
/**
 * 话题功能service
 * @package PW_Topic
 */
class PW_Topic{
	
	/**
	 * 保存话题
	 * @param array $topics 数据数组，以数据库字段为key
	 * @return array $data ('topicName'=>topicId)
	 */
	function addTopic($topics){
		$data = array();
		$topics = is_array($topics) ? $topics : array(trim($topics)); 
		if (!S::isArray($topics))  return $data;
		$TopicDb = $this->_getTopicDao();
		foreach ($topics as $v) {
			$v = trim($v);
			if ($v == '') continue;
			$topicId = $TopicDb->addTopic($v);
			$topicId && $data[$v] = $topicId;
		}
		return $data;
	}
	
	/**
	 * 保存话题与新鲜事关系
	 * @param int $mid 新鲜事ID
	 * @param int $topicId 话题id
	 * @return boolean
	 */
	function addTopicRelations($topicId,$mid) {
		$mid = intval($mid);
		$topicId = intval($topicId);
		if($mid < 0 || $topicId < 0) return false;
		$topicRelationsDb = $this->_getTopicRelationsDao();
		$topicRelationsDb->addTopicRelations($topicId,$mid);
		$TopicDb = $this->_getTopicDao();
		$TopicDb->updateTopicNum($topicId,1);
		return true;
	}
	
	/**
	 * 获取最近X天的热门话题
	 * @param int $num 显示的话题数
	 * @param int $days 热门话题榜缓存时间（天）
	 * @return array $hotTopics
	 */
	function getHotTopics($num = 10,$days = 7){
		$hotTopics = array();
		if (!$num || !$days) return $hotTopics;
		$num = intval($num);
		$days = intval($days);
		$TopicDb = $this->_getTopicDao();
		$hotTopics = $TopicDb->getHotTopics($num,$GLOBALS['timestamp'] - 86400*$days);
		if ($hotTopics) {
			$topics = $this->getTopicByIds(array_keys($hotTopics));
			foreach ($hotTopics as $key => $value) {
				if(empty($value['counts'])) unset($hotTopics[$key]);
			}
			foreach (array($topics) as $v) {
				isset($hotTopics[$v['topicid']]) && $hotTopics[$v['topicid']] = array_merge($v,$hotTopics[$v['topicid']]);
			} 
		}
		$order = 0;
		foreach ($hotTopics as $k=>$v) {
			$hotTopics[$k]['order'] = ++$order;
		}
		return $hotTopics;
	}
	
	/**
	 * 添加用户关注的话题
	 * 
	 * @param int $topicId
	 * @param int $userid
	 * @return boolean
	 */
	function addAttentionTopic($topicId,$userId) {
		$topicId = intval($topicId);
		$userId = intval($userId);
		if($topicId < 0 || $userId < 1) return false;
		$topicAttentionsDb = $this->_getTopicAttentionsDao();
		$topicAttentionsDb->addAttentionTopic($topicId,$userId);
		return true;
	}
	
	/**
	 * 删除用户关注的话题
	 * 
	 * @param int $topicId
	 * @param int $userId
	 * @return boolean
	 */
	function deleteAttentionedTopic($topicId,$userId) {
		$topicId = intval($topicId);
		$userId = intval($userId);
		if($topicId < 0 || $userId < 1) return false;
		$topicAttentionsDb = $this->_getTopicAttentionsDao();
		$topicAttentionsDb->deleteAttentionedTopic($topicId,$userId);
		return true;
	}
	
	/**
	 * 
	 * 检查是否已经关注某话题
	 * @param int $topicId 话题id
	 * @param int $userId 用户id
	 * @return array
	 */
	function  getOneAttentionedTopic($topicId,$userId){
		$topicId = intval($topicId);
		$userId = intval($userId);
		if($topicId < 0 || $userId < 1) return false;
		$topicAttentionsDb = $this->_getTopicAttentionsDao();
		return $topicAttentionsDb->getOneAttentionedTopic($topicId,$userId);
	}
	
	/**
	 * 
	 * 检查是否已经关注某些话题
	 * @param int $topicIds 话题id
	 * @param int $userId 用户id
	 * @return array
	 */
	function getAttentionedTopicByTopicIds($topicIds,$userId) {
		$userId = intval($userId);
		if(!$userId || !S::isArray($topicIds)) return array();
		$topicAttentionsDb = $this->_getTopicAttentionsDao();
		return $topicAttentionsDb->getAttentionedTopicByTopicIds($topicIds,$userId);
	}
	
	/**
	 * 获取用户关注的话题
	 * @param int $uid 用户id
	 * @param int $page 页数
	 * @param int $perPage 每页显示数
	 * @param array topics
	 */
	function getUserAttentionTopics($uid,$page = 1,$perPage = 10){
		$page = $page ? intval($page) : 1;
		$uid = intval($uid);
		$perPage = $page ? intval($perPage) : 10;
		if($uid < 1 || $page < 1 || $perPage < 1) return array();
		$TopicDb = $this->_getTopicDao();
		$offset = ($page - 1)*$perPage;
		return $TopicDb->getUserAttentionTopics($uid,$offset,$perPage);
	}
	
	/**
	 * 获取用户关注的话题关注次数
	 * @param int $uid 用户id
	 * @return int
	 */
	function getUserAttentionTopicNum($uid){
		$uid = intval($uid);
		if($uid < 1) return false;
		$topicAttentionsDb = $this->_getTopicAttentionsDao();
		return (int)$topicAttentionsDb->getUserAttentionTopicNum($uid);
	}
	/**
	 * 根据话题id获取新鲜事关系
	 * @param int|array $topicIds
	 * @param int $num
	 */
	function getWeiboByTopicIds ($topicIds,$num){
		$weiboRelations = array();
		if(!$topicIds) return 0;
		if (!is_array($topicIds)) {
			$topicIds = intval($topicIds);
			$topicIds = array($topicIds);
		} else {
			foreach ($topicIds as $k=>$v)
				$topicIds[$k] = intval($v);
		}
		if (is_array($topicIds)) {
			$topicRelationsDb = $this->_getTopicRelationsDao();
			$weiboRelations = $topicRelationsDb->getWeiboByTopicIds($topicIds,$num);
		}
		return $weiboRelations;
	}
	
	/**
	 * 根据话题Id获取话题信息
	 * @param int $topicId 话题id
	 * @return array
	 */
	function  getTopicById($topicId){
		$topicId = intval($topicId);
		if($topicId < 0) return array();
		$TopicDb = $this->_getTopicDao();
		return $TopicDb->getTopic($topicId);
	}
	
	/**
	 * 根据话题Id获取多条热门话题
	 * @param array $topicIds 话题id
	 * @return array
	 */
	function getTopicByIds($topicIds){
		$topics = array();
		if (!S::isArray($topicIds)) return $topics;
		foreach ($topicIds as $k=>$v) {
			$v = intval($v);
			if (!$v){
				unset($topicIds[$k]);
				continue;
			}
			$topicIds[$k] = $v;
		}
		if ($topicIds) {
			$TopicDb = $this->_getTopicDao();
			$topics = $TopicDb->getHotTopics($topicIds,1);
		}
		return $topics;
	}
	
	/**
	 * 根据话题名获取单条话题
	 * @param array $topicname 话题
	 * @return array $TopicDb
	 */
	function getTopicByName($topicName){
		$topicName = trim($topicName);
		if(!$topicName)  return array();	
		$TopicDb = $this->_getTopicDao();
		return $TopicDb->getTopicByName($topicName);
	}
	
	/**
	 * 获取热门话题
	 * @param int $o_weibo_hottopicdays 热门话题榜缓存时间
	 * @param int $timestamp 时间戳
	 * @param array $db
	 * @return array $TopicDb
	 */
	function getWeiboHotTopics(){
		global $o_weibo_hottopicdays,$timestamp,$db;
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_cache');
			$rt =  $_cacheService->getCacheByName('weiboHotTopics_10');			
		} else {
			$rt = $db->get_one("SELECT * FROM pw_cache WHERE name='weiboHotTopics_10'");
		}
		$lastData = @unserialize($rt['cache']);
		$weiboHotTopics = array();
		if ($lastData && ($rt['time'] > $timestamp - 7200)) {
			$weiboHotTopics = (array)$lastData;
		} else {
			$days = $o_weibo_hottopicdays ? intval($o_weibo_hottopicdays) : 7;
			$weiboHotTopics = $this->getHotTopics(10,$days);
			/*与上一次比较排名变化*/
				foreach (array($lastData) as $k=>$v) {
					if (!isset($v['order'])) continue;
					if ($v['order'] < $weiboHotTopics[$k]['order']) {
						 $weiboHotTopics[$k]['change'] = 2;//排名下降
					} elseif ($v['order'] > $weiboHotTopics[$k]['order']) {
						$weiboHotTopics[$k]['change'] = 1;//排名上升
					} else {
						$weiboHotTopics[$k]['change'] = 0;
					}
				}
			/*	
			$db->update("REPLACE INTO pw_cache SET " . S::sqlSingle(array(
				'name'	=> 'weiboHotTopics_10',
				'cache'	=> serialize($weiboHotTopics),
				'time'	=> $timestamp
			)));
			*/
			pwQuery::replace(
				'pw_cache',
				array(
					'name'	=> 'weiboHotTopics_10',
					'cache'	=> serialize($weiboHotTopics),
					'time'	=> $timestamp
				)
			);
		}
		return $weiboHotTopics;
	}
	
	/**
	 * 根据topicId删除topic
	 * @param int $topicId
	 * @return 
	 */
	function deleteTopicById($topicId){
		$topicId = intval($topicId);
		if($topicId < 0) return false;
		$TopicDb = $this->_getTopicDao();
		return $TopicDb->deleteTopicById($topicId);
	}
	
	/**
	 * 统计话题数
	 * 
	 * @param int $topicid
	 * @return int
	 */
	function getTopicCount(){
		$topicDb = $this->_getTopicDao();
		return $topicDb->countTopics();
	}
	
	/**
	 * 设置是否允许热门话题
	 * 
	 * @param array $topicids
	 * @return boolean
	 */
	function setHotTopics($topicIds,$ifHot) {
		$ifHot = $ifHot ? intval($ifHot) : 1;
		if(!S::isArray($topicIds) || $ifHot < 0) return false;
		$topicDb = $this->_getTopicDao();
		if($topicDb->setHotByTopicids($topicIds,$ifHot)) return true;
	}
	
	/**
	 * 后台搜索话题
	 * @param int $ifHot 是否允许热门标签
	 * @param string $topicNames 话题
	 * @param int $startNum 最少使用人数 
	 * @param int $endNum 最多使用人数 
	 * @param int $orderType 排序
	 * @param int $page
	 * @param int $perPage
	 * @return array
	 */
	function getAdminSearchResult($ifHot, $topicNames, $startNum = 0, $endNum = 0, $orderType ='DESC', $page = 1, $perPage = 20){
		$ifHot = $ifHot ? intval($ifHot) : 0;
		$topicNames = trim($topicNames);
		$startNum = intval($startNum);
		$endNum = intval($endNum);
		$orderType = trim($orderType);
		$page = $page ? intval($page) : 1;
		$perPage = $perPage ? intval($perPage) : 20;
		if($startNum < 0 || $endNum < 0 || !$orderType || $perPage < 0 || $page < 1 || $ifHot < 0) return array();
		
		$sqlAdd = '';
	 	($topicNames != '') && $sqlAdd .= ' AND topicname like '.S::sqlEscape('%'.$topicNames.'%');
		($startNum != '') && $sqlAdd .= ' AND num >= ' . S::sqlEscape($startNum);
		($endNum != '') && $sqlAdd .= ' AND num <= ' . S::sqlEscape($endNum);
		$ifHot && $sqlAdd .= ' AND ifhot = ' . S::sqlEscape($ifHot);
		
		$orderType = in_array($orderType,array('DESC','ASC')) ? $orderType : 'DESC';
		$querySql.=$sqlAdd.' ORDER BY crtime '.$orderType;
		$offSet = ($page - 1) * $perPage;
		$TopicDb = $this->_getTopicDao();
		$querySql .= $TopicDb->limit($offSet,$perPage);
		return $TopicDb->adminSearchTopic($sqlAdd,$querySql);
	}

	function _getTopicDao(){
		return L::loadDB('Topic', 'sns'); 
	}
	
	function _getTopicRelationsDao(){
		return L::loadDB('weibo_topicrelations', 'sns');
	}
	
	function _getTopicAttentionsDao(){
		return L::loadDB('weibo_topicattentions', 'sns');
	}
}
?>