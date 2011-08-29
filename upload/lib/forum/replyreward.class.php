<?php
!function_exists('readover') && exit('Forbidden');

class PW_ReplyReward {
	
	var $credit;
	
	function PW_ReplyReward() {
		$this->credit = $GLOBALS['credit'];
		if (!$this->credit) {
			require_once R_P . 'require/credit.php';
			$this->credit = $credit;
		}
	}
	
	/**
	 * 增加一个新的回帖奖励
	 * @param $uid
	 * @param $data
	 */
	function addNewReward($uid, $data) {
		$uid = intval($uid);
		if ($uid < 1 || !S::isArray($data)) return false; 
		$this->addReward($data);
		$points = ceil($data['creditnum'] * $data['rewardtimes']);
		return $this->credit->set($uid, $data['credittype'], -$points);
	}
	
	/**
	 * 更新回帖奖励设置项
	 * @param $tid
	 * @param $uid
	 * @param $reward
	 */
	function updateRewardData($tid, $uid, $reward) {
		list($tid, $uid) = array(intval($tid), intval($uid));
		if ($tid < 1 || $uid < 1) return false;
		
		$rewardData = $this->getRewardByTid($tid);
		if ($rewardData) return $this->_updateOriginalData($tid, $uid, $reward, $rewardData);

		if (!$reward['replyreward'] || !($newRewardData = $this->setReplyRewardData($uid, $reward))) return false;
		$newRewardData['tid'] = $tid;
		$this->addNewReward($uid, $newRewardData);
		return 1;
	}
	
	/**
	 * 更新原有的设置项
	 * @param unknown_type $tid
	 * @param unknown_type $uid
	 * @param unknown_type $reward
	 * @param unknown_type $rewardData
	 */
	function _updateOriginalData($tid, $uid, $reward, $rewardData) {
		$leftCredits = intval($rewardData['lefttimes'] * $rewardData['creditnum']);
		if (!$reward['replyreward']) {
			$this->deleteByTid($tid);
			$leftCredits > 0 && $this->credit->set($uid, $rewardData['credittype'], $leftCredits);
			return 0;
		}
		$method = ($reward['rewardcredit'] == $rewardData['credittype']) ? '_changeRewardSettingInSameCredit' : '_changeRewardSettingToDiffCredit';
		return $this->$method($tid, $uid, $reward, $rewardData, $leftCredits);
	}
	
	/**
	 * 新旧奖励类型相同的时候
	 * @param $tid
	 * @param $uid
	 * @param $reward
	 * @param $rewardData
	 * @param $leftCredits
	 */
	function _changeRewardSettingInSameCredit($tid, $uid, $reward, $rewardData, $leftCredits) {
		if (!($newRewardData = $this->setReplyRewardData($uid, $reward, $leftCredits))) return false;
		$diffCredits = $leftCredits - $newRewardData['creditnum'] * $newRewardData['rewardtimes'];
		$success = $this->updateByTid($tid, $newRewardData);
		$diffCredits && $success && $this->credit->set($uid, $newRewardData['credittype'], $diffCredits);
		return true;
	}
	
	/**
	 * 新旧奖励类型不同的时候
	 * @param $tid
	 * @param $uid
	 * @param $reward
	 * @param $rewardData
	 * @param $leftCredits
	 */
	function _changeRewardSettingToDiffCredit($tid, $uid, $reward, $rewardData, $leftCredits) {
		if (!($newRewardData = $this->setReplyRewardData($uid, $reward))) return false;
		$creditChage = array();
		$leftCredits && $creditChage[$rewardData['credittype']] = $leftCredits;
		$success = $this->updateByTid($tid, $newRewardData);
		if (!$success) return false;
		$points = ceil($newRewardData['creditnum'] * $newRewardData['rewardtimes']);
		$creditChage[$newRewardData['credittype']] = -$points;
		$this->credit->sets($uid, $creditChage);
		return true;
	}
	
	/**
	 * 组装回帖奖励的设置项
	 * @param $uid
	 * @param $reward
	 * @param $leftCredits
	 */
	function setReplyRewardData($uid, $reward, $leftCredits = '') {
		list($uid, $leftCredits) = array(intval($uid), intval($leftCredits));
		if (!$uid || !($reward = $this->_checkData($reward))) return false;
		$userCredits = $this->_getUserCredits($uid, $reward['rewardcredit']);
		if ($reward['replyrewardnum'] * $reward['replyrewardtimes'] > intval($userCredits[$reward['rewardcredit']] + ($leftCredits > 0 ? $leftCredits : 0))) return false;
		return array(
			'credittype' => $reward['rewardcredit'],
			'creditnum' => $reward['replyrewardnum'],
			'rewardtimes' => $reward['replyrewardtimes'],
			'repeattimes' => $reward['replyrewardreptimes'],
			'chance' => $reward['replyrewardchance'],
			'lefttimes' => $reward['replyrewardtimes'],
		);
	}
	
	/**
	 * 校验数据
	 * @param $reward
	 */
	function _checkData($reward) {
		if (!S::isArray($reward)) return false;
		list($allowedKeys, $checkedData) = array(array('replyreward', 'rewardcredit', 'replyrewardnum', 'replyrewardtimes', 'replyrewardreptimes', 'replyrewardchance'), array());
		foreach ($reward as $key => $value) {
			$key != 'rewardcredit' && $value = intval($value);
			if (!S::inArray($key, $allowedKeys) || $value < 0) continue;
			$checkedData[$key] = $value; 
		}
		if (!S::isArray($checkedData) || !$checkedData['replyreward'] || !S::inArray($checkedData['rewardcredit'], array_keys($this->credit->cType)) || $checkedData['replyrewardnum'] <= 0 || $checkedData['replyrewardtimes'] <= 0) return false;
		return $checkedData;
	}
	
	/**
	 * 获取用户积分信息
	 * @param $uid
	 * @param $credittype
	 */
	function _getUserCredits($uid, $credittype) {
		global $winduid, $winddb;
		list($uid, $userCredits) = array(intval($uid), array());
		if ($uid != $winduid) return $this->credit->get($uid, 'ALL');
		$userCredits = array('money' => $winddb['money'], 'rvrc' => $winddb['rvrc'] / 10, 'credit' => $winddb['credit'], 'currency' => $winddb['currency']);
		if (S::inArray($credittype, array('money', 'rvrc', 'credit', 'currency'))) return $userCredits;
		$userCredits = $this->credit->get($uid, 'CUSTOM');
		return $userCredits;
	}
	
	/**
	 * 根据tid获取设置项信息
	 * @param $tid
	 */
	function getRewardByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$replyRewardDao = $this->_getReplyRewardDao();
		return $replyRewardDao->getRewardByTid($tid);
	}
	
	/**
	 * 批量获取设置项信息
	 * @param $tids
	 */
	function getRewardByTids($tids) {
		if (!S::isArray($tids)) return false;
		$replyRewardDao = $this->_getReplyRewardDao();
		return $replyRewardDao->getRewardByTids($tids);
	}
	
	/**
	 * 插入新的设置信息到数据库中
	 * @param $data
	 */
	function addReward($data) {
		if (!S::isArray($data)) return false;
		$replyRewardDao = $this->_getReplyRewardDao();
		return $replyRewardDao->addReward($data);
	}
	
	/**
	 * 更新数据库中的设置信息
	 * @param $tid
	 * @param $data
	 */
	function updateByTid($tid, $data) {
		$tid = intval($tid);
		if ($tid < 1 || !S::isArray($data)) return false;
		$replyRewardDao = $this->_getReplyRewardDao();
		return $replyRewardDao->updateByTid($tid, $data);
	}
	
	/**
	 * 根据tid删除信息
	 * @param $tid
	 */
	function deleteByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$replyRewardDao = $this->_getReplyRewardDao();
		return $replyRewardDao->deleteByTid($tid);
	}
	
	/**
	 * 批量删除信息
	 * @param $tids
	 */
	function deleteByTids($tids) {
		if (!S::isArray($tids)) return false;
		$replyRewardDao = $this->_getReplyRewardDao();
		return $replyRewardDao->deleteByTids($tids);
	}
	
	/**
	 * 获取dao服务
	 */
	function _getReplyRewardDao() {
		static $replyRewardDao = null;
		if (is_null($replyRewardDao)) {
			$replyRewardDao = L::loadDb('ReplyReward', 'forum');
		}
		return $replyRewardDao;
	}
}
