<?php

!defined('P_W') && exit('Forbidden');

/**
 * 话题功能数据库DAO服务
 * @package PW_TopicDB
 */
class PW_Weibo_TopicAttentionsDB extends BaseDB{
	
	var $_tableName = 'pw_weibo_topicattention';
	
	/**
	 * 
	 * 添加用户关注的话题
	 * @param int $topicId
	 * @param int $userid
	 * @return boolean
	 */
	function addAttentionTopic($topicId,$userid){
		$userid = intval($userid);
		$topicId = intval($topicId);
		if(!$userid || !$topicId) return false;
		$fields = array('topicid'=>$topicId,'userid'=>$userid,'crtime'=>$GLOBALS['timestamp']);
		pwQuery::replace($this->_tableName, $fields);
		return true;
	}
	
	/**
	 * 
	 * 删除用户关注的话题
	 * @param int $topicId
	 * @param int $userid
	 * @return boolean
	 */
	function deleteAttentionedTopic($topicId,$userid){
		$userid = intval($userid);
		$topicId = intval($topicId);
		if(!$userid || !$topicId) return false;
		pwQuery::delete($this->_tableName, "userid=:userid and topicid=:topicid", array($userid,$topicId));
		return true;
	}
	
	/**
	 * 
	 * 检查是否已经关注某话题
	 * @param int $topicId
	 * @param int $userid
	 * @return array
	 */
	function getOneAttentionedTopic($topicId,$userid) {
		$userid = intval($userid);
		$topicId = intval($topicId);
		if(!$userid || !$topicId) return array();
		return $this->_db->get_one('SELECT userid,topicid FROM ' . $this->_tableName . ' WHERE userid = ' . $this->_addSlashes($userid) . ' AND  topicid = ' . $this->_addSlashes($topicId));
	}
	
	/**
	 * 
	 * 检查是否已经关注某些话题
	 * @param int $topicIds
	 * @param int $userid
	 * @return array
	 */
	function getAttentionedTopicByTopicIds($topicIds,$userid) {
		if(!$userid && !S::isArray($topicIds)) return array(); 
		$query = $this->_db->query('SELECT userid,topicid FROM ' . $this->_tableName . ' WHERE userid = ' . $this->_addSlashes($userid) . ' AND topicid IN (' . S::sqlImplode($topicIds) .')');
		return $this->_getAllResultFromQuery($query,'topicid');
	}
	
	function getUserAttentionTopicNum($uid){
		$uid = intval($uid);
		return $this->_db->get_value('SELECT COUNT(*) FROM ' . $this->_tableName . ' WHERE userid = ' . $uid);
	}
}
?>