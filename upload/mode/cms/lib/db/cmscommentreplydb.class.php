<?php
!defined('P_W') && exit('Forbidden');

/**
 * 评论数据层
 * @package  PW_CmsCommentReplyDB
 * @author phpwind @2011-6-24
 */
class PW_CmsCommentReplyDB extends BaseDB {
	var $_tableName 	= 	'pw_cms_commentreply';
	var $_primaryKey 	= 	'replyid';

	/**
	 * 添加
	 * 
	 * @param array $fieldsData
	 * @return boolean
	 */
	function insert($fieldsData) {
		if(!S::isArray($fieldsData)) return false;
		return $this->_insert($fieldsData);
	}

	/**
	 * 删除
	 * 
	 * @param int $replyid
	 * @return boolean 
	 */
	function delete($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return false;
		return (bool)$this->_delete($replyid);
	}
	
	function get($replyid) {
		return $this->_get($replyid);
	}
	
	function deleteByCommentid($commentid) {
		$commentid = intval($commentid);
		if ($commentid < 1) return false;
		return $this->_db->update('DELETE FROM ' . $this->_tableName . ' WHERE commentid = ' . S::sqlEscape($commentid));
	}
	
	function getCommentsByCommentid($commentid){
		$commentid = intval($commentid);
		if (!$commentid) return array();
		$query = $this->_db->query('SELECT * FROM '.$this->_tableName.' WHERE commentid = ' . S::sqlEscape($commentid) . '  ORDER BY postdate DESC LIMIT 30');
		return  $this->_getAllResultFromQuery($query);
	}
}