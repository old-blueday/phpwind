<?php
!defined('P_W') && exit('Forbidden');

/**
 * 回复评论服务层
 * @package  PW_CmsCommentReplyService
 * @author phpwind @2011-6-24
 */
class PW_CmsCommentReplyService {

	/**
	 * 添加
	 * 
	 * @param array $fieldsData
	 * @return int 
	 */
	function insert($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		$cmsCommentReplyDb = $this->_getCmsCommentReplyDB();
		return $cmsCommentReplyDb->insert($fieldsData);
	}
	
	/**
	 * 删除
	 * 
	 * @param int $replyid 
	 * @return boolean
	 */
	function deleteByReplyid($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return false;
		$cmsCommentReplyDb = $this->_getCmsCommentReplyDB();
		return $cmsCommentReplyDb->delete($replyid);
	}
	
	function getByReplyid($replyid) {
		$replyid = intval($replyid);
		if ($replyid < 1) return false;
		$cmsCommentReplyDb = $this->_getCmsCommentReplyDB();
		return $cmsCommentReplyDb->get($replyid);
	}
	
	/**
	 * 删除
	 * 
	 * @param int $commentid 
	 * @return boolean
	 */
	function deleteByCommentid($commentid) {
		$commentid = intval($commentid);
		if ($commentid < 1) return false;
		$cmsCommentReplyDb = $this->_getCmsCommentReplyDB();
		return $cmsCommentReplyDb->deleteByCommentid($commentid);
	}

	function getCommentsByCommentid($commentid){
		$commentid = intval($commentid);
		if (!$commentid) return array();
		$cmsCommentReplyDb = $this->_getCmsCommentReplyDB();
		return $this->buildReplyData($cmsCommentReplyDb->getCommentsByCommentid($commentid));	
	}

	function buildReplyData($data) {
		global $db_windpost;
		if(!S::isArray($data)) return array();
		$uids = $tmpreplydata = array();
		foreach ($data as $v) {
			$uids[] = $v['uid'];
		}
		$userService = L::loadClass('UserService', 'user');
		$userInfo = $userService->getUserInfoWithFace($uids);
		require_once (R_P . 'require/bbscode.php');
		foreach ($data as $value) {
			$value['content'] = convert($value[content],$db_windpost);
			list($value['postdate'], $value['postdate_s']) = getLastDate($value['postdate']);
			$tmpreplydata[] = array_merge((array)$value,(array)$userInfo[$value['uid']]);
		}
		return $tmpreplydata;
	}
	
	/**
	 *加载dao
	 * 
	 * @return PW_CmsCommentDB
	 */
	function _getCmsCommentReplyDB() {
		return C::loadDB('CmsCommentReply');
	}
}