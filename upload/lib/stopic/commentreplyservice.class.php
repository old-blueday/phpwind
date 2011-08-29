<?php
!defined('P_W') && exit('Forbidden');

/**
 * 回复评论服务层
 * @package  PW_CommentReplyService
 * @author phpwind @2011-7-5
 */
class PW_CommentReplyService {

	/**
	 * 添加
	 * 
	 * @param array $fieldsData
	 * @return int 
	 */
	function insert($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		$commentReplyDb = $this->_getCommentReplyDB();
		return $commentReplyDb->insert($fieldsData);
	}
	
	/**
	 * 根据replyid单个删除
	 * 
	 * @param int $replyid 
	 * @return boolean
	 */
	function deleteByReplyid($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return false;
		$commentReplyDb = $this->_getCommentReplyDB();
		return $commentReplyDb->delete($replyid);
	}

	function getByReplyid($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return array();
		$commentReplyDb = $this->_getCommentReplyDB();
		return $commentReplyDb->getByReplyid($replyid);
	}

	/**
	 * 根据commentid单个删除
	 * 
	 * @param int $commentid 
	 * @return boolean
	 */
	function deleteByCommentid($commentid) {
		$commentid = intval($commentid);
		if ($commentid < 1) return false;
		$commentReplyDb = $this->_getCommentReplyDB();
		return $commentReplyDb->deleteByCommentid($commentid);
	}

	/**
	 * 根据commentid获取数据
	 * 
	 * @param int $commentid
	 * @return array
	 */
	function getCommentsByCommentid($commentid){
		$commentid = intval($commentid);
		if (!$commentid) return array();
		$commentReplyDb = $this->_getCommentReplyDB();
		return $this->buildReplyData($commentReplyDb->getCommentsByCommentid($commentid));	
	}

	/**
	 * 数据处理
	 * 
	 * @param array $data
	 * @return array
	 */
	function buildReplyData($data) {
		global $db_windpost;
		if(!S::isArray($data)) return array();
		$uids = $tmpreplydata = array();
		foreach ($data as $v) {
			$uids[] = $v['uid'];
		}
		$userService = L::loadClass('UserService', 'user');
		$userInfo = $userService->getUserInfoWithFace($uids);
		foreach ($data as $value) {
			list($value['postdate'], $value['postdate_s']) = getLastDate($value['postdate']);
			$tmpreplydata[] = array_merge((array)$value,(array)$userInfo[$value['uid']]);
		}
		return $tmpreplydata;
	}
	
	function _getCommentReplyDB() {
		return L::loadDB('CommentReply', 'stopic');
	}
}