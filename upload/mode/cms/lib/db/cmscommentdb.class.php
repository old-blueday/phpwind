<?php
!defined('P_W') && exit('Forbidden');

/**
 * 评论数据层
 * @package  PW_CmsCommentDB
 * @author phpwind @2011-6-24
 */
class PW_CmsCommentDB extends BaseDB {
	var $_tableName 	= 	'pw_cms_comment';
	var $_primaryKey 	= 	'commentid';

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
	 * 更新
	 * 
	 * @param int $commentid  
	 * @param array $fieldsData
	 * @return boolean
	 */
	function updateReplynumByCommentid($exp='+1',$commentid) {
		$commentid = intval($commentid);
		if($commentid < 1 || !$exp) return false;
		
		$num = intval(trim($exp,'+-'));
		if (strpos($exp,'+') !== false) {
			return $this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET replynum=replynum+" . S::sqlEscape($num) . ' WHERE commentid=:commentid', array($this->_tableName, $commentid)));
		} else {
			return $this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET replynum=replynum-" . S::sqlEscape($num) . ' WHERE commentid=:commentid', array($this->_tableName, $commentid)));
		}
		return false;
	}
	
	/**
	 * 删除
	 * 
	 * @param int $commentid
	 * @return boolean 
	 */
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

	function getCommentsByArticleId($article_id,$page = 1,$perpage = 20){
		$article_id = intval($article_id);
		$page = intval($page);
		$perpage = intval($perpage);
		if (!$article_id || $page < 0 || $perpage < 1) return array();
		$offset = ($page - 1) * $perpage;
		$query = $this->_db->query('SELECT * FROM '.$this->_tableName.' WHERE  article_id = ' . S::sqlEscape($article_id) . '  ORDER BY postdate DESC '.$this->_Limit($offset,$perpage));
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getCommentsCountByArticleId($article_id){
		$article_id = intval($article_id);
		if ($article_id < 1) return false;
		return $this->_db->get_value('SELECT count(*) FROM ' . $this->_tableName . ' WHERE  article_id = ' . S::sqlEscape($article_id));
	}
}