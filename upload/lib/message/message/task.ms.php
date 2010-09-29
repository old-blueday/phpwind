<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 消息任务服务层
 * @2010-4-6 liuhui
 */
class MS_Task extends MS_Base {
	
	function sendTaskMessages($userIds, $messageInfo, $messageId = null) {
		if (!is_array($userIds) || (!$messageInfo && !$messageId)) return false;
		$configs = $this->_getMsConfigsByUserIds($userIds);
		if ($configs) {
			foreach ($configs as $uid => $config) {
				if (isset($config[$this->_c_shieldinfo]) && is_array($config[$this->_c_shieldinfo]) && !$config[$this->_c_shieldinfo][$this->_s_notice_system]) {
					$userIds = array_diff($userIds, array($uid));
					continue;
				}
			}
		}
		if (!$userIds) return false;
		$categoryId = $this->getMap($this->_notice);
		$typeId = $this->getMap($this->_notice_system);
		if(!$messageId = $this->_sendMessage($categoryId,$typeId,$messageInfo,$messageId)){
			return false;
		}
		$relations = array();
		foreach ($userIds as $otherId) {
			$relation = array();
			$relation['uid'] = $otherId;
			$relation['mid'] = $messageId;
			$relation['categoryid'] = $categoryId;
			$relation['typeid'] = $typeId;
			$relation['status'] = $this->_s_not_read;
			$relation['isown'] = $this->_s_other;
			$relation['created_time'] = $relation['modified_time'] = $this->_timestamp;
			$relations[] = $relation;
		}
		$relationsDao = $this->getRelationsDao();
		if (!$relationsDao->addRelations($relations)) {
			return false;
		}
		$this->_updateStatisticsByUserNames($userIds, false, $this->_notice, 1);
		return $messageId;
	}
	
	function _sendMessage($categoryId,$typeId,$messageInfo,$messageId){
		if($messageId){
			$messagesDao = $this->getMessagesDao();
			$message = $messagesDao->get($messageId);
			return ($message) ? $messageId : false;
		}else{
			$messageInfo['expand'] = serialize(array('categoryid' => $categoryId,'typeid' => $typeId));
			$virtualUser = $this->virtualUser(); /*virtual user*/
			$messageInfo['create_uid'] = $userId = $virtualUser['uid'];
			$messageInfo['create_username'] = $virtualUser['username'];
			if (!($messageId = $this->_addMessage($messageInfo))) {
				return false;
			}
			return $messageId;
		}
	}

}