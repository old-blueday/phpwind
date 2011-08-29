<?php
!defined('P_W') && exit('Forbidden');

/**
 * 评论数据层
 * @package  PW_CommentReplyDB
 * @author phpwind @2011-7-5
 */
class PW_CommentReplyDB extends BaseDB {
	var $_tableName 	= 	'pw_stopic_commentreply';
	var $_primaryKey 	= 	'replyid';

	function insert($fieldsData) {
		$fieldsData = $this->checkFields($fieldsData);
		return $this->_insert($fieldsData);
	}

	function delete($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return false;
		return (bool)$this->_delete($replyid);
	}

	function deleteByCommentid($commentid) {
		$commentid = intval($commentid);
		if ($commentid < 1) return false;
		return $this->_db->update('DELETE FROM ' . $this->_tableName . ' WHERE commentid = ' . S::sqlEscape($commentid));
	}

	function getByReplyid($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE  " . $this->_primaryKey . " = " . S::sqlEscape($replyid));
	}
	
	function getCommentsByCommentid($commentid){
		$commentid = intval($commentid);
		if (!$commentid) return array();
		$query = $this->_db->query('SELECT * FROM '.$this->_tableName.' WHERE commentid = ' . S::sqlEscape($commentid) . '  ORDER BY postdate DESC LIMIT 30');
		return  $this->_getAllResultFromQuery($query);
	}
	
	function fieldsMap() {
		return array('replyid','uid','commentid','content','postdate','ip');
	}
	
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