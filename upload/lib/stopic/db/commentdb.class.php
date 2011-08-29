<?php
!defined('P_W') && exit('Forbidden');

/**
 * 评论数据层
 * @package  PW_CommentDB
 * @author phpwind @2011-7-5
 */
class PW_CommentDB extends BaseDB {
	var $_tableName 	= 	'pw_stopic_comment';
	var $_primaryKey 	= 	'commentid';

	function insert($fieldsData) {
		$fieldsData = $this->checkFields($fieldsData);
		return $this->_insert($fieldsData);
	}
	
	function addReplyNumByCommentid($num,$commentid) {
		$num = intval($num);
		$commentid = intval($commentid);
		if($num < 1 || $commentid < 1) return false;
		return $this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET replynum=replynum+" . S::sqlEscape($num) . ' WHERE commentid=:commentid', array($this->_tableName, $commentid)));
	}
	
	function reduceReplyNumByCommentid($num,$commentid) {
		$num = intval($num);
		$commentid = intval($commentid);
		if($num < 1 || $commentid < 1) return false;
		return $this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET replynum=replynum-" . S::sqlEscape($num) . ' WHERE commentid=:commentid', array($this->_tableName, $commentid)));
	}
	
	function delete($commentid) {
		$commentid = intval($commentid);
		if ($commentid < 1) return false;
		return (bool)$this->_delete($commentid);
	}
	
	/**
	 * 根据commentid获取数据
	 * 
	 * @param int $commentid
	 * @return array
	 */
	function getByCommentid($commentid) {
		$commentid = intval($commentid);
		if ($commentid < 1) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE  " . $this->_primaryKey . " = " . S::sqlEscape($commentid));
	}

	/**
	 * 根据stopic_id获取数据
	 * 
	 * @param int $stopic_id
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getCommentsByStopicId($stopic_id,$offset = 0,$perpage = 20){
		$stopic_id = intval($stopic_id);
		$offset = intval($offset);
		$perpage = intval($perpage);
		if ($stopic_id < 1 || $offset < 0 || $perpage < 1) return array();
		$query = $this->_db->query('SELECT * FROM '.$this->_tableName.' WHERE  stopic_id = ' . S::sqlEscape($stopic_id) . '  ORDER BY postdate DESC '.$this->_Limit($offset,$perpage));
		return  $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据stopic_id获取评论数
	 * 
	 * @param int $stopic_id
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getCommentsCountByStopicId($stopic_id){
		$stopic_id = intval($stopic_id);
		if ($stopic_id < 1) return false;
		return $this->_db->get_value('SELECT count(*) FROM ' . $this->_tableName . ' WHERE  stopic_id = ' . S::sqlEscape($stopic_id));
	}
	
	/**
	 * 数据表字段
	 * 
	 * @return array
	 */
	function fieldsMap() {
		return array('commentid','uid','stopic_id','content','replynum','postdate','ip');
	}
	
	/**
	 * 检测数组key字段
	 * 
	 * @param array $fieldsData
	 * @return array
	 */
	function checkFields($fieldsData) {
		if(!S::isArray($fieldsData)) return array();
		$fielsdMap = $this->fieldsMap();
		$data = array();
		foreach ($fieldsData as $k=>$v) {
			if (!S::inArray($k,$fielsdMap)) continue;
			$data[$k] = $v;
		}
		return $data;
	}
}