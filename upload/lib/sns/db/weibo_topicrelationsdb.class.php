<?php

!defined('P_W') && exit('Forbidden');

/**
 * 话题功能数据库DAO服务
 * @package PW_TopicDB
 */
class PW_Weibo_TopicrelationsDB extends BaseDB{
	
	var $_tableName = 'pw_weibo_topicrelations';
	
	/**
	 * 
	 * 添加话题新鲜事关系
	 * @param int $topicId
	 * @param int $mid
	 */
	function addTopicRelations($topicId,$mid){
		$mid = intval($mid);
		$topicId = intval($topicId);
		if(!$mid || !$topicId) return false;
		$fields = array('topicid'=>$topicId,'mid'=>$mid,'crtime'=>$GLOBALS['timestamp']);
		pwQuery::replace($this->_tableName, $fields);
		return true;
	}
	
	function getHotTopics($num, $time) {
		$query = $this->_db->query("SELECT topicid,COUNT(mid) AS counts FROM " . $this->_tableName . ' WHERE crtime > ' . intval($time) . ' GROUP BY topicid ORDER BY counts DESC' . $this->_limit($num));
		return $this->_getAllResultFromQuery($query, 'topicid');
	}
	
	function getWeiboByTopicIds($topicIds,$num) {
		$query = $this->_db->query("SELECT mid,topicid FROM " . $this->_tableName . ' WHERE topicid IN('. S::sqlImplode($topicIds) .') ORDER BY crtime DESC' . $this->_limit($num));
		return $this->_getAllResultFromQuery($query,'mid');
	}
	
	function getTopicIdsByMid($mid){
		$mid = intval($mid);
		$topicsIds = array();
		if($mid) {
			$query = $this->_db->query('SELECT topicid FROM '.$this->_tableName.' WHERE mid=' . $mid);
			while($row = $this->_db->fetch_array($query)){
				$topicsIds[] = $row['topicid'];
			}
		}
		return $topicsIds;
	}
	
	function deleteRelationByMid($mid){
		$mid = intval($mid);
		return $this->_db->query('DELETE FROM '.$this->_tableName.' WHERE mid=' . $mid);
	}
}
?>