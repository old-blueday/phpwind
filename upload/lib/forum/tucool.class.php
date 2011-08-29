<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 图酷类
 */

class PW_Tucool {
	
	var $foruminfo = array();
	var $tid;

	function PW_Tucool(){}
	
	/**
	 * 
	 * 设置版块
	 * @param mixed $forum (int fid|array foruminfo)
	 */
	function setForum($forum){
		if (!S::isArray($forum)) {
			$forum = intval($forum);
			if ($forum < 1) return false;
			$foruminfo = L::forum($forum);
			if (!S::isArray($foruminfo)) return false;
			$forum = $foruminfo;
		}
		$forum && $this->foruminfo = $forum;
		return true;
	}
	function add($data) {
		$fieldData = array();
		$fieldData['fid'] = intval($data['fid']);
		$fieldData['tid'] = intval($data['tid']);
		$fieldData['tpcnum'] = intval($data['tpcnum']);
		$fieldData['totalnum'] = $data['totalnum'] ? intval($data['totalnum']) : $fieldData['tpcnum'];
		if (in_array(0, $fieldData ,true)) return false;
		$threadService = L::loadClass('threads', 'forum'); /* @var $threadService PW_Threads */
		$threadInfo = $threadService->getByTid($data['tid']);
		if (!is_array($threadInfo)) return false;
		$fieldData['topped'] = $threadInfo['topped'];
		$fieldData['ifcheck'] = $threadInfo['ifcheck'];
		$dao = $this->_getTucoolDB();
		$dao->add($fieldData);
		//set status
		$threadService->setTpcStatusByThreadId($fieldData['tid'],5);
		//set cover
		$attachService = L::loadClass('attachs', 'forum'); /* @var $attachService PW_Attachs */
		$coverInfo = $attachService->getLatestAttachInfoByTidType($fieldData['tid']);
		$coverInfo && $this->setCover($fieldData['tid'],$coverInfo['attachurl'],$coverInfo['ifthumb']);
		return true;
	}
	
	function get($tid) {
		$tid = intval($tid);
		if (!$tid) return false;
		$dao = $this->_getTucoolDB();
		return $dao->get($tid);
	}
	
	function delete($tid) {
		$tid = intval($tid);
		if (!$tid) return false;
		$dao = $this->_getTucoolDB();
		if ($dao->delete($tid)) {
			//set status
			$threadService = L::loadClass('threads', 'forum'); /* @var $threadService PW_Threads */
			$threadService->setTpcStatusByThreadId($tid,5,0);
		}
	}

	/**
	 * 更新图酷数据
	 * @param unknown_type $tid
	 */
	function updateTucoolImageNum ($tid) {
		$tid = intval($tid);
		if($tid < 1) return false;
		if (!$this->foruminfo){
			$this->tid = $tid;
			$this->initForumset();
		}
		$tucoolInfo = $this->get($tid);
		$attachService = L::loadClass('attachs','forum');
		if (!S::isArray($tucoolInfo)) {
			//不存在图酷信息
			if (!$this->foruminfo['forumset']['iftucool']) return false;//未开启图酷
			$topicNum = $attachService->countTopicImagesByTid($tid);
			if ($topicNum < $this->foruminfo['forumset']['tucoolpic']) return false;
			$uid = $attachService->getUidByTidPidType($tid);
			$totalNum = 0;
			if ($uid > 0) {
				$totalNum = (int)$attachService->countThreadImagesByTidUid($tid,$uid);
			}
			$fieldData = array(
				'fid' => $this->foruminfo['fid'],
				'tid' => $tid,
				'tpcnum' => $topicNum,
				'totalnum' => $totalNum
			);
			return $this->add($fieldData);
		} else {
			$topicNum = $attachService->countTopicImagesByTid($tid);
			if (!$this->foruminfo['forumset']['iftucool'] || $topicNum < $this->foruminfo['forumset']['tucoolpic'] || $topicNum < $this->foruminfo['forumset']['tucoolpic']) {
				return $this->delete($tid);
			} else {
				$uid = $attachService->getUidByTidPidType($tid);
				if ($uid < 1) {
					return $this->delete($tid);
				}
				$totalNum = (int)$attachService->countThreadImagesByTidUid($tid,$uid);
				$fieldData = array(
					'fid'	=> $this->foruminfo['fid'],
					'tpcnum' => $topicNum,
					'totalnum' => $totalNum
				);
				$dao = $this->_getTucoolDB();
				return $dao->update($fieldData,$tid);
			}
		}
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $fid
	 */
	function renewToolThreads($fid) {
		$fid = intval($fid);
		if($fid < 1) return false;
		$this->setForum($fid);
		if (!$this->foruminfo['forumset']['iftucool']) return false;//未开启图酷
		$attachService = L::loadClass('attachs', 'forum'); /* @var $attachService PW_Attachs */
		$unSatisfiedTids = $attachService->getUnsatisfiedTidsByTopicImageNum($fid,$this->foruminfo['forumset']['tucoolpic']);
		if($unSatisfiedTids){
			$threadService = L::loadClass('threads', 'forum'); /* @var $threadService PW_Threads */
			$threadService->setTpcStatusByThreadIds($unSatisfiedTids,0xFFEF);
			//删除thread_img
			$threadService->deleteTucoolThreadsByTids($unSatisfiedTids);
		} 
		return true;
	}
	
	function updateTopicImgNum($tid,$newImgNum){
		$tid = intval($tid);
		$newImgNum = intval($newImgNum);
		if (!$this->foruminfo || $tid < 1 || $newImgNum < 1) return false;
		$tucoolInfo = $this->get($tid);
		if (!S::isArray($tucoolInfo)) {
			if (!$this->foruminfo['forumset']['iftucool'] || !$this->foruminfo['forumset']['tucoolpic'] || $newImgNum < $this->foruminfo['forumset']['tucoolpic']) return false;
			//reply图更新（作者）
			$attachService = L::loadClass('attachs','forum');
			$uid = $attachService->getUidByTidPidType($tid);
			$totalNum = 0;
			if ($uid > 0) {
				$totalNum = (int)$attachService->countThreadImagesByTidUid($tid,$uid);
			}
			$fieldData = array(
				'fid' => $this->foruminfo['fid'],
				'tid' => $tid,
				'tpcnum' => $newImgNum,
				'totalnum' => $totalNum
			);
			return $this->add($fieldData);
		} else {
			if ($newImgNum < $this->foruminfo['forumset']['tucoolpic']) {
				$this->delete($tid);
			} else {
				$dao = $this->_getTucoolDB();
				$fieldData = array(
					'tpcnum' => $newImgNum,
					'totalnum' => $tucoolInfo['totalnum'] + $newImgNum - $tucoolInfo['tpcnum']
				);
				$dao->update($fieldData,$tid);
			}
		}
	}

	function initForumset(){
		if ($this->tid < 1) return false;
		$threadService = L::loadClass('threads','forum');
		$threadInfo = $threadService->getByThreadId($this->tid);
		if (!S::isArray($threadInfo)) return false;
		return $this->setForum($threadInfo['fid']);
	}
	/**
	 * 更新图酷总图片变化数
	 * @param int $tid
	 * @param int $change 变化数
	 */
	function updateTotalImgNum($tid,$change) {
		$tid = intval($tid);
		$change = intval($change);
		if ($tid < 1 || !$change) return false;
		$tucoolInfo = $this->get($tid);
		if (!S::isArray($tucoolInfo)) return false;
		$newTotalImgNum = $change + $tucoolInfo['totalnum'];
		if ($newTotalImgNum < 1 || $newTotalImgNum < $tucoolInfo['tpcnum']) return false;
		$dao = $this->_getTucoolDB();
		$fieldData = array(
			'totalnum' => $newTotalImgNum
		);
		$dao->update($fieldData,$tid);
	}
	function _getTucoolDB() {
		return L::loadDB('Tucool', 'forum');
	}
	
	/**
	 * 
	 * 获取最新图酷帖排行
	 * @param string $fid
	 * @param string $order
	 * @return array
	 */
	function newTuCoolSort($fid,$num){
		$num = intval($num);
		if ($num < 1) return array();
		$tucoolDao = $this->_getTucoolDB();
		return $tucoolDao->newTuCoolSort($fid,$num);
	}
	
	/**
	 * 
	 * 图酷帖今日点击排行
	 * @param string $fid
	 * @param int $num
	 * @return array
	 */
	function subjectPicNumSort($fid,$num){
		$num = intval($num);
		if ($num < 1) return array();
		$tucoolDao = $this->_getTucoolDB();
		return $tucoolDao->subjectPicNumSort($fid,$num);
	}
	
	/**
	 * 
	 * 按总图片数获取图酷帖排行
	 * @param string $fids S::sqlImplode过的如('1','2')
	 * @param string $order
	 * @return array
	 */
	function getTucoolThreadsByHitSortToday($fids,$num){
		$num = intval($num);
		if ($num < 1) return array();
		$tucoolDao = $this->_getTucoolDB();
		return $tucoolDao->getTucoolThreadsByHitSortToday($fids,$num);
	}
	
	/**
	 * 
	 * 图酷帖昨日点击排行
	 * @param string $fids S::sqlImplode过的如('1','2')
	 * @param string $order
	 * @return array
	 */
	function getTucoolThreadsByHitSortYesterday($fids,$num){
		$num = intval($num);
		if ($num < 1) return array();
		$tucoolDao = $this->_getTucoolDB();
		return $tucoolDao->getTucoolThreadsByHitSortYesterday($fids,$num);
	}

	/**
	 * 
	 * 根据tids批量查
	 * @param array $tids
	 * @param int $num
	 * @return array
	 */
	function getTucoolThreadsByTids($tids){
		if (!S::isArray($tids)) return array();
		$tucoolDao = $this->_getTucoolDB();
		return $tucoolDao->getTucoolThreadsByTids ($tids);
	}
	
	function setCover($tid,$cover,$ifthumb) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$tucoolDao = $this->_getTucoolDB();
		$tucoolDao->update(array('cover'=>$cover,'ifthumb'=>$ifthumb),$tid);
		return true;
	}

	
	function updateCollectNum($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$tucoolDao = $this->_getTucoolDB();
		$tucoolDao->updateCollectNum($tid);
		return true;
	}
}