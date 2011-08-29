<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 通知服务层
 * @2010-4-6 liuhui
 */
class MS_Notice extends MS_Base {
	
	function MS_Notice(){
		parent::MS_Base();
	}
	function sendNotice($userId,$userNames,$messageInfo,$typeId){
		$userId = intval($userId);
		$typeId = intval($typeId);
		if( 1 > $typeId || "" == $userNames || !$messageInfo ){
			return false;
		}
		list($userNames,$categoryId,$typeId) = $this->_checkReceiver($userNames,$this->_notice,$typeId);
		if(!$userNames) return false;
		list($userIds,$userNames) = $this->_getUserByUserNames($userNames,false);
		if(!$userIds){
			return false;
		}
		$virtualUser = $this->virtualUser();/*virtual user*/
		$messageInfo['create_uid']      = $userId = $virtualUser['uid'];
		$messageInfo['create_username'] = $virtualUser['username'];
		if(!($messageId = $this->_doSend($userId,$userIds,$categoryId,$typeId,$messageInfo))){
			return false;
		}
		$this->_updateStatisticsByUserNames($userIds,false,$this->_notice,1);
		$this->_updateUserMessageNumbers($userIds,$this->_notice);
		return $messageId;
	}

	function getAllNotices($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getAll($userId,$this->_notice,$page,$perpage);
	}
	function getNoticesNotRead($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsByStatus($userId,$this->_notice,$this->_s_not_read,$page,$perpage);
	}
	function getNotices($userId,$typeId,$page,$perpage){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage || 1 > $typeId ){
			return false;
		}
		return $this->_getsByTypeId($userId,$this->_notice,$typeId,$page,$perpage);
	}
	function getUpNotice($userId,$relationId,$typeId){
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if( 1 > $userId || 1 > $typeId || 1 > $relationId ){
			return false;
		}
		return $this->_upMessage($userId,$this->_notice,$relationId,$typeId);
	}
	function getDownNotice($userId,$relationId,$typeId){
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if( 1 > $userId || 1 > $typeId || 1 > $relationId ){
			return false;
		}
		return $this->_downMessage($userId,$this->_notice,$relationId, $typeId);
	}
	function countAllNotice($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countAll($userId,$this->_notice);
	}
	function countNoticesNotRead($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countByStatus($userId,$this->_notice,$this->_s_not_read);
	}
	function countNotice($userId,$typeId){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		if( 1 > $userId || 1 > $typeId ){
			return false;
		}
		return $this->_countByTypeId($userId,$this->_notice,$typeId);
	}
	function deleteNotice($userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_deleteRelations($userId,array($relationId));
	}
	function deleteNotices($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_deleteRelations($userId,$relationIds);
	}
	function updateNotice($fieldData,$userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || !$fieldData || 1 > $relationId ){
			return false;
		}
		return $this->_update($fieldData,$userId,$relationId);
	}
	function markNotice($userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_mark($userId,array($relationId));
	}
	function markNotices($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_mark($userId,$relationIds);
	}
	function getNotice($messageId){
		$messageId  = intval($messageId);
		if( 1 > $messageId  ){
			return false;
		}
		return $this->_get($messageId);
	}
	function readNotices($userId,$messageId){
		$messageId  = intval($messageId);
		$userId  = intval($userId);
		if( 1 > $messageId || 1 > $userId ){
			return false;
		}
		return $this->_updateByMessageIds(array('status'=>$this->_s_have_read),$userId,array($messageId));
	}
	function grabMessage($userId,$groupIds,$lastgrab){
		$userId = intval($userId);
		$lastgrab = intval($lastgrab);
		if( 1 > $userId || !is_array($groupIds)){
			return false;
		}
		if(!($groupIds = $this->_filterUserGroups($userId,$groupIds))){
			return false;
		}
		$tasksDao = $this->getTaskDao();
		if(!($tasks = $tasksDao->getsByCreateTime($groupIds,$lastgrab))){
			return false;
		}
		$tmpTasks = array();
		foreach($tasks as $task){
			$tmpTasks[$task['mid']] = $task;// filter
		}
		$categoryId = $this->getMap($this->_notice);
		$typeId     = $this->getMap($this->_notice_system);
		$relations = array();
		foreach($tmpTasks as $task){
			$relation['uid']           = $userId;
			$relation['mid']           = $task['mid'];
			$relation['categoryid']    = $categoryId;
			$relation['typeid']        = $typeId;
			$relation['status']        = $this->_s_not_read;
			$relation['isown']         = $this->_s_other;
			$relation['created_time']  = $task['created_time'];
			$relation['modified_time'] = $this->_timestamp;
			$relations[] = $relation;
		}
		$relationsDao = $this->getRelationsDao();
		if(!( $relationId = $relationsDao->addRelations($relations))){
			return false;
		}
		// update lasttime
		$userService = $this->_getUserService();
		$userService->update($userId, array(), array('lastgrab' => $this->_timestamp));
		return true;
	}
	/**
	 * 过滤用户组消息
	 * @param $userId
	 * @param $groupIds
	 * @return unknown_type
	 */
	function _filterUserGroups($userId,$groupIds){
		if( !$userId || !$groupIds ) return false;
		if(!$this->_userGroup['msggroup']) return $groupIds;//是否开启只接收特定用户组的消息 后台设置
		$blackGroups = $this->_getMsConfig($userId,$this->_c_blackgroup);
		if($blackGroups){
			$blackGroups = ($blackGroups) ? unserialize($blackGroups) : array();
			$groupIds = array_diff($groupIds,$blackGroups);
		}
		return $groupIds;
	}
	function createMessageTasks($groupIds,$messageInfo){
		if( !is_array($groupIds) || !is_array($messageInfo)){
			return false;
		}
		$categoryId = $this->getMap($this->_notice);
		$typeId     = $this->getMap($this->_notice_system);
		$messageInfo['expand'] = serialize(array('categoryid'=>$categoryId,'typeid'=>$typeId));
		$virtualUser = $this->virtualUser();/*virtual user*/
		$messageInfo['create_uid']      = $userId = $virtualUser['uid'];
		$messageInfo['create_username'] = $virtualUser['username'];
		if(!($messageId = $this->_addMessage($messageInfo))){
			return false;
		}
		$tasksDao = $this->getTaskDao();
		$fieldDatas = array();
		foreach($groupIds as $groupId){
			$fieldData = array();
			$fieldData['oid']          = $groupId;
			$fieldData['mid']          = $messageId;
			$fieldData['created_time'] = $this->_timestamp;
			$fieldDatas[] = $fieldData;
		}
		if(!$tasksDao->addTasks($fieldDatas)){
			return false;
		}
		return true;
	}
	function sendOnlineMessages($onlineUserIds,$messageInfo){
		if( !is_array($onlineUserIds) || !$messageInfo ) return false;	
		$configs = $this->_getMsConfigsByUserIds($onlineUserIds);
		if($configs){
			foreach($configs as $uid=>$config){
				if(isset($config[$this->_c_shieldinfo]) && is_array($config[$this->_c_shieldinfo]) && !$config[$this->_c_shieldinfo][$this->_s_notice_system]){
					$onlineUserIds = array_diff($onlineUserIds,array($uid));
					continue;
				}
			}
		}
		if(!$onlineUserIds) return false;
		$categoryId = $this->getMap($this->_notice);
		$typeId     = $this->getMap($this->_notice_system);
		$messageInfo['expand'] = serialize(array('categoryid'=>$categoryId,'typeid'=>$typeId));	
		$virtualUser = $this->virtualUser();/*virtual user*/
		$messageInfo['create_uid']      = $userId = $virtualUser['uid'];
		$messageInfo['create_username'] = $virtualUser['username'];
		if(!($messageId = $this->_addMessage($messageInfo))){
			return false;
		}
		$relations = array();
		foreach($onlineUserIds as $otherId){
			$relation = array();
			$relation['uid']          = $otherId;
			$relation['mid']          = $messageId;
			$relation['categoryid']   = $categoryId;
			$relation['typeid']       = $typeId;
			$relation['status']       = $this->_s_not_read;
			$relation['isown']        = $this->_s_other;
			$relation['created_time'] = $relation['modified_time'] = $this->_timestamp;
			$relations[] = $relation;
		}
		$relationsDao = $this->getRelationsDao();
		if(!$relationsDao->addRelations($relations)){
			return false;
		}
		$this->_updateStatisticsByUserNames($onlineUserIds,false,$this->_notice,1);
		$this->_updateUserMessageNumbers($onlineUserIds,$this->_notice);
		return true;
	}
	
	
	
	
	
	
	
	
	
	
	
}