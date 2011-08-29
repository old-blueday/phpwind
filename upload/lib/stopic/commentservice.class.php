<?php
!defined('P_W') && exit('Forbidden');

/**
 * 评论服务层
 * @package  PW_CommentService
 * @author phpwind @2011-7-5
 */
class PW_CommentService {

	/**
	 * 添加
	 * 
	 * @param array $fieldsData
	 * @return int 
	 */
	function insert($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		$commentDb = $this->_getCommentDB();
		return $commentDb->insert($fieldsData);
	}
	
	/**
	 * 更新
	 * 
	 * @param array $fieldsData
	 * @param int $commentid 
	 * @return boolean 
	 */
	function updateByCommentid($fieldsData,$commentid) {
		$commentid = intval($commentid);
		if($commentid < 1 || !S::isArray($fieldsData)) return false;
		$commentDb = $this->_getCommentDB();
		return $commentDb->update($fieldsData,$commentid);
	}
	
	/**
	 * 加回复数
	 * 
	 * @param int $num
	 * @param int $commentid 
	 * @return boolean 
	 */
	function addReplyNumByCommentid($num,$commentid) {
		$num = intval($num);
		$commentid = intval($commentid);
		if($num < 1 || $commentid < 1) return false;
		$commentDb = $this->_getCommentDB();
		return $commentDb->addReplyNumByCommentid($num,$commentid);
	}
	
	/**
	 * 减回复数
	 * 
	 * @param int $num
	 * @param int $commentid 
	 * @return boolean 
	 */
	function reduceReplyNumByCommentid($num,$commentid) {
		$num = intval($num);
		$commentid = intval($commentid);
		if($num < 1 || $commentid < 1) return false;
		$commentDb = $this->_getCommentDB();
		return $commentDb->reduceReplyNumByCommentid($num,$commentid);
	}
	
	/**
	 * 更新回复数
	 * 
	 * @param string $expnum -1|+1
	 * @param int $commentid 
	 * @return boolean 
	 */
	function updateReplynumByCommentid($expnum,$commentid) {
		$commentid = intval($commentid);
		if($commentid < 1 || !$expnum) return false;
		$num = intval(trim($expnum,'+-'));
		if (strpos($expnum,'-') !== false) {
			return $this->reduceReplyNumByCommentid($num,$commentid);
		}
		return $this->addReplyNumByCommentid($num,$commentid);
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
		$commentDb = $this->_getCommentDB();
		return $commentDb->delete($commentid);
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
		$commentDb = $this->_getCommentDB();
		return $commentDb->getByCommentid($commentid);
	}

	/**
	 * 根据stopic_id获取数据
	 * 
	 * @param int $commentid
	 * @return array
	 */
	function getCommentsCountByStopicId($stopic_id){
		$stopic_id = intval($stopic_id);
		if ($stopic_id < 1) return false;
		$commentDb = $this->_getCommentDB();
		return $commentDb->getCommentsCountByStopicId($stopic_id);
	}

	/**
	 * 根据stopic_id获取数据
	 * 
	 * @param int $stopic_id
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getCommentsByStopicId($stopic_id,$page,$perpage){
		$stopic_id = intval($stopic_id);
		$page = intval($page);
		$perpage = intval($perpage);
		if (!$stopic_id || $page < 0 || $perpage < 1) return array();
		$commentDb = $this->_getCommentDB();
		return $this->buildReplyData($commentDb->getCommentsByStopicId($stopic_id,($page - 1) * $perpage,$perpage));	
	}

	/**
	 * 组装数据
	 * 
	 * @param array $data
	 * @return array
	 */
	function buildReplyData($data) {
		if(!S::isArray($data)) return array();
		$uids = $comment = array();
		foreach ($data as $v) {
			$uids[] = $v['uid'];
		}
		$userService = L::loadClass('UserService', 'user');
		$userInfo = $userService->getUserInfoWithFace($uids);
		foreach ($data as $value) {
			list($value['postdate'], $value['postdate_s']) = getLastDate($value['postdate']);
			$comment[] = array_merge((array)$value, (array)$userInfo[$value['uid']]);
		}
		return $comment;
	}
	
	/**
	 * 检测评论
	 * 
	 * @param string $content
	 * @param int $groupid
	 * @return array
	 */
	function addCheck($content, $groupid) {
		global $winduid;
		if (!$winduid) return '您还未登录!';
		if ($groupid == '6') return '您已被禁言!';
		if (!$content) return '内容不为空';
		if (strlen($content) > 255) return '内容不能多于255字节';
		$filterService = L::loadClass('FilterUtil', 'filter');
		if (($GLOBALS['banword'] = $filterService->comprise($content)) !== false) {
			return 'content_wordsfb';
		}
		return true;
	}

	function _getCommentDB() {
		return L::loadDB('Comment', 'stopic');
	}
}