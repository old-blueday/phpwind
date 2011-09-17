<?php
!function_exists('readover') && exit('Forbidden');

class PW_ReplyRewardRecord {
	
	/**
	 * 回帖奖励
	 * @param unknown_type $uid
	 * @param unknown_type $tid
	 * @param unknown_type $pid
	 */
	function rewardReplyUser($uid, $tid, $pid) {
		list($uid, $tid, $pid) = array(intval($uid), intval($tid), intval($pid));
		if ($uid < 1 || $tid < 1 || $pid < 1) return false;
		$threadsService = L::loadClass('Threads', 'forum');
		$threadData = $threadsService->getByThreadId($tid);
		if (!$threadData || $threadData['authorid'] == $uid) return false;
		
		$replyRewardService = L::loadClass('ReplyReward', 'forum');/* @var $replyRewardService PW_ReplyReward */
		$rewardInfo = $replyRewardService->getRewardByTid($tid);
		if (!$this->_checkRewardCondition($rewardInfo, $uid, $tid) || !$this->_checkIfReward($rewardInfo['chance'])) return false;
		return $this->_rewardUser($uid, $tid, $pid, $rewardInfo);
	}
	
	/**
	 * 检查条件
	 * @param $rewardInfo
	 * @param $uid
	 * @param $tid
	 */
	function _checkRewardCondition($rewardInfo, $uid, $tid) {
		if (!$rewardInfo || !$rewardInfo['rewardtimes'] || $rewardInfo['lefttimes'] < 1) return false;
		$rewardRecords = $this->countRecordsByTidAndUid($tid, $uid);
		if ($rewardInfo['repeattimes'] && $rewardRecords >= $rewardInfo['repeattimes']) return false;
		return true;
	}
	
	/**
	 * 中奖概率
	 * @param $chance
	 */
	function _checkIfReward($chance) {
		return rand(1, 10) <= ($chance / 10);
	}
	
	/**
	 * 具体奖励操作
	 * @param $uid
	 * @param $tid
	 * @param $pid
	 * @param $rewardInfo
	 */
	function _rewardUser($uid, $tid, $pid, $rewardInfo) {
		global $credit;
		$record = array(
			'tid' => intval($tid),
			'pid' => intval($pid),
			'uid' => intval($uid),
			'credittype' => $rewardInfo['credittype'],
			'creditnum' => $rewardInfo['creditnum'],
			'rewardtime' => $GLOBALS['timestamp']
		);
		$this->addRewardRecord($record);
		$replyRewardService = L::loadClass('ReplyReward', 'forum');/* @var $replyRewardService PW_ReplyReward */
		$lefttimes = ($rewardInfo['lefttimes'] - 1 >= 0) ? $rewardInfo['lefttimes'] - 1 : 0;
		$replyRewardService->updateByTid($tid, array('lefttimes' => $lefttimes));
		if (!$credit) require_once R_P . 'require/credit.php';
		$credit->set($uid, $rewardInfo['credittype'], $rewardInfo['creditnum']);
		return $this->_addCreditPop($uid, $rewardInfo['credittype'], $rewardInfo['creditnum']);
	}
	
	/**
	 * 记录用户creditpop信息
	 * @param $uid
	 * @param $creditType
	 * @param $creditNum
	 */
	function _addCreditPop($uid, $creditType, $creditNum) {
		global $db_ifcredit;
		list($creditNum, $creditpop) = array(intval($creditNum), '');
		if (!$db_ifcredit || !$creditNum) return false;
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userMemberData = $userService->get($uid, false, true);
		$creditpop = $userMemberData['creditpop'] ? $userMemberData['creditpop'] . ",reply_reward|$creditType:+$creditNum|" : "reply_reward|$creditType:+$creditNum|";
		$userService->update($uid, array(), array('creditpop' => $creditpop));
		return true;
	}
	
	/**
	 * 根据tid获取中奖信息
	 */
	function getRewardRecordByTid($tid) {
		list($tid) = intval($tid);
		if ($tid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByTid($tid);
	}
	
	/**
	 * 根据uid获取中奖信息
	 * @param $uid
	 */
	function getRewardRecordByUid($uid) {
		list($uid) = intval($uid);
		if ($uid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByUid($uid);
	}
	
	/**
	 * 根据uids批量获取中奖信息
	 * @param $uids
	 */
	function getRewardRecordByUids($uids) {
		if (!S::isArray($uids)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByUids($uids);
	}
	
	/**
	 * 根据tid, uid获取中奖信息
	 * @param $tid
	 * @param $uid
	 */
	function getRewardRecordByTidAndUid($tid, $uid) {
		list($tid, $uid) = array(intval($tid), intval($uid));
		if ($tid < 1 || $uid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByTidAndUid($tid, $uid);
	}
	
	/**
	 * 根据tid, pid获取中奖信息
	 * @param $tid
	 */
	function getRewardRecordByTidAndPid($tid, $pid) {
		list($tid, $pid) = array(intval($tid), intval($pid));
		if ($tid < 1 || $pid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByTidAndPid($tid, $pid);
	}
	
	/**
	 * 根据tid,pids获取信息
	 */
	function getRewardRecordByTidAndPids($tid, $pids) {
		$tid = intval($tid);
		if ($tid < 1 || !S::isArray($pids)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByTidAndPids($tid, $pids);
	}
	
	/**
	 * 批量获取中奖信息
	 * @param $tids
	 */
	function getRewardRecordByTids($tids) {
		if (!S::isArray($tids)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->getRewardRecordByTids($tids);
	}
	
	/**
	 * 插入新的中奖信息到数据库中
	 * @param $data
	 */
	function addRewardRecord($data) {
		if (!S::isArray($data)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->addRewardRecord($data);
	}
	
	/**
	 * 更新数据库中的中奖信息
	 * @param $tid
	 * @param $data
	 */
	function updateRecordByTidAndPid($tid, $pid, $data) {
		list($tid, $pid) = array(intval($tid), intval($pid));
		if ($tid < 1 || $pid < 1 || !S::isArray($data)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->updateRecordByTidAndPid($tid, $pid, $data);
	}
	
	/**
	 * 根据tid删除信息
	 * @param $tid
	 */
	function deleteByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->deleteByTid($tid);
	}
	
	/**
	 * 批量删除信息
	 * @param $tids
	 */
	function deleteByTids($tids) {
		if (!S::isArray($tids)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->deleteByTids($tids);
	}
	
	/**
	 * 根据tid,pid删除信息
	 * @param $tid
	 * @param $pid
	 */
	function deleteByTidAndPid($tid, $pid) {
		list($tid, $pid) = array(intval($tid), intval($pid));
		if ($tid < 1 || $pid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->deleteByTidAndPid($tid, $pid);
	}
	
	/**
	 * 根据tid,pids删除信息
	 * @param $tid
	 * @param $pids
	 */
	function deleteByTidAndPids($tid, $pids) {
		$tid = intval($tid);
		if ($tid < 1 || !S::isArray($pids)) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->deleteByTidAndPids($tid, $pids);
	}
	
	/**
	 * 统计某个用户在一个帖子当中的中奖次数
	 * @param $tid
	 * @param $uid
	 */
	function countRecordsByTidAndUid($tid, $uid) {
		list($tid, $uid) = array(intval($tid), intval($uid));
		if ($tid < 1 || $uid < 1) return false;
		$replyRewardRecordDao = $this->_getReplyRewardRecordDao();
		return $replyRewardRecordDao->countRecordsByTidAndUid($tid, $uid);
	}
	
	/**
	 * 获取dao服务
	 */
	function _getReplyRewardRecordDao() {
		static $replyRewardRecordDao = null;
		if (is_null($replyRewardRecordDao)) {
			$replyRewardRecordDao = L::loadDb('ReplyRewardRecord', 'forum');
		}
		return $replyRewardRecordDao;
	}
}
