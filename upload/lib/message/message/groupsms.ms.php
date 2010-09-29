<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 群组服务层
 * @2010-4-6 liuhui
 */
class MS_Groupsms extends MS_Base {
	var $_receiver = 1000; //群组发送最大人数限制
	function sendGroupMessage($userId,$groupId,$messageInfo,$type = null,$userNames=array()){
		$userId = intval($userId);
		if( 1 > $userId || 1 > $groupId || !$messageInfo ){
			return false;
		}
		if(false == ($messageInfo = $this->_checkInfo($messageInfo))){
			return false;
		}
		if( !$this->_checkUserLevle($this->_groupsms,1)){
			return false;
		}
		$colonysDao = $this->getColonysDao();
		if(!($colony = $colonysDao->get($groupId))){
			return false;
		}
		if($userNames){
			list($userIds,$userNames) = $this->_getUserByUserNames($userNames,false);
		}else{
			$userIds = $this->_getGroupUsers($groupId);
		}
		if(!$userIds) return false;	
		//filter groupid
		$userIds = $this->_filterGroups($userIds,$groupId);
		if(!$userIds) return false;
		$messageInfo['extra'] = serialize(array('groupid'=>$groupId));
		array_slice($userIds,0,$this->_receiver);// limit receiver
		$categoryId = $this->getMap($this->_groupsms);
		$typeId     = $this->getMap($this->_groupsms_colony);
		if(!($messageId = $this->_doSend($userId,$userIds,$categoryId,$typeId,$messageInfo))){
			return false;
		}
		$this->_updateStatisticsByUserNames($userIds,false,$this->_groupsms,1);
		$this->_updateUserMessageNumbers($userIds);
		return $messageId;
	}
	function _getGroupUsers($groupId){
		$cMembersDao = $this->getCmembersDao();
		if(!($users = $cMembersDao->getUserIdsByColonyId($groupId))){
			return false;
		}
		$userIds = array();
		foreach($users as $user){
			($user['uid'] > 0) ? $userIds[] = $user['uid'] : 0;
		}
		return $userIds;
	}
	/**
	 * 私有过滤群组功能
	 * @param $users
	 * @return unknown_type
	 */
	function _filterGroups($userIds,$groupId){
		if(!$userIds) return false;
		if( !$this->_checkUserLevle($this->_groupsms,count($userIds))){
			return false;
		}
		$configs = $this->_getMsConfigsByUserIds($userIds);
		if($configs){
			foreach($configs as $userId=>$config){
				if(isset($config[$this->_c_blackcolony]) && is_array($config[$this->_c_blackcolony]) && in_array($groupId,$config[$this->_c_blackcolony])){
					$userIds = array_diff($userIds,array($userId));
				}
			}
		}
		return $userIds;
	}
	
	function getAllGroupMessages($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getAll($userId,$this->_groupsms,$page,$perpage);
	}
	function getGroupMessagesNotRead($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsByStatus($userId,$this->_groupsms,$this->_s_not_read,$page,$perpage);
	}
	function getGroupMessages($userId,$typeId,$page,$perpage){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage || 1 > $typeId ){
			return false;
		}
		return $this->_getsByTypeId($userId,$this->_groupsms,$typeId,$page,$perpage);
	}
	function getGroupUpMessage($userId,$relationId,$typeId){
		$userId = intval($userId);
		$categoryId = intval($this->getMap($this->_groupsms));
		$relationId = intval($relationId);
		if( 1 > $userId || 1 > $categoryId || 1 > $relationId ){
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		if(!$tmpRelation = $relationsDao->get($relationId)){
			return false;
		}
		if(!$relation = $relationsDao->getUpSpecialRelation($userId,$categoryId,$typeId,$relationId,$tmpRelation['modified_time'])){
			return false;
		}
		if(!$message = $this->_get($relation['mid'])){
			return false;
		}
		return $this->_buildColonys($relation+$message);
	}
	function getGroupDownMessage($userId,$relationId,$typeId){
		$userId = intval($userId);
		$categoryId = intval($this->getMap($this->_groupsms));
		$relationId = intval($relationId);
		if( 1 > $userId || 1 > $categoryId || 1 > $relationId ){
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		if(!$tmpRelation = $relationsDao->get($relationId)){
			return false;
		}
		if(!$relation = $relationsDao->getDownSpecialRelation($userId,$categoryId,$typeId,$relationId,$tmpRelation['modified_time'])){
			return false;
		}
		if(!$message = $this->_get($relation['mid'])){
			return false;
		}
		return $this->_buildColonys($relation+$message);
	}
	function getGroupMessagesBySelf($userId,$typeId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsSpecialByIsown($userId,$this->_groupsms,$typeId,$this->_s_self,$page,$perpage);
	}
	function getGroupMessagesByOther($userId,$typeId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsSpecialByIsown($userId,$this->_groupsms,$typeId,$this->_s_other,$page,$perpage);
	}
	function countAllGroupMessage($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countAll($userId,$this->_groupsms);
	}
	function countGroupMessagesNotRead($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countByStatus($userId,$this->_groupsms,$this->_s_not_read,$page,$perpage);
	}
	function countGroupMessagesBySelf($userId,$typeId){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		if( 1 > $userId  ){
			return false;
		}
		return $this->_countSpecialByIsown($userId,$this->_groupsms,$typeId,$this->_s_self);
	}
	function countGroupMessagesByOther($userId,$typeId){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countSpecialByIsown($userId,$this->_groupsms,$typeId,$this->_s_other);
	}
	function countGroupMessage($userId,$typeId){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		if( 1 > $userId || 1 > $typeId ){
			return false;
		}
		return $this->_countByTypeId($userId,$this->_groupsms,$typeId);
	}
	function deleteGroupMessage($userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_deleteRelations($userId,array($relationId));
	}
	function deleteGroupMessages($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_deleteRelations($userId,$relationIds);
	}
	function updateGroupMessage($fieldData,$userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || !$fieldData || 1 > $relationId ){
			return false;
		}
		return $this->_update($fieldData,$userId,$relationId);
	}
	function markGroupMessage($userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_mark($userId,array($relationId));
	}
	function markGroupMessages($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_mark($userId,$relationIds);
	}
	function getGroupMessage($messageId){
		$messageId  = intval($messageId);
		if( 1 > $messageId  ){
			return false;
		}
		if(!($message =  $this->_get($messageId))){
			return false;
		}
		return $this->_buildColonys($message);
	}
	
	function _buildColonys($message){
		if(!$message) return false;
		$colonysDao = $this->getColonysDao();
		$extra = ($message['extra']) ? unserialize($message['extra']) : '';
		if(!$extra || !isset($extra['groupid']) ){
			return $message;
		}
		if(!($colony = $colonysDao->getById($extra['groupid']))){
			return false;
		}
		return $message+$colony;
	}
	
	function readGroupMessages($userId,$messageId){
		$messageId  = intval($messageId);
		$userId  = intval($userId);
		if( 1 > $messageId || 1 > $userId ){
			return false;
		}
		return $this->_updateByMessageIds(array('status'=>$this->_s_have_read),$userId,array($messageId));
	}
	function getGroupReplies($userId,$messageId,$relationId){
		$messageId   = intval($messageId);
		$userId      = intval($userId);
		$relationId  = intval($relationId);
		if( 1 > $messageId || 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_getGroupReplies($userId,$messageId,$relationId);
	}
	function shieldGroupMessage($userId,$relationId,$messageId){
		$userId  = intval($userId);
		if( 1 > $userId || 1 > $relationId || 1 > $messageId ){
			return false;
		}
		
		$userService = $this->_getUserService();
		$user = $userService->get($userId);
		if (!$user) {
			return false;
		}
		$messagesDao = $this->getMessagesDao();
		if(!($message = $messagesDao->get($messageId))){
			return false;
		}
		$userNames = ($message['extra']) ? unserialize($message['extra']) : array();
		if($userNames){
			$userNames = array_diff($userNames,array($user['username']));
			$messagesDao->update(array('extra'=>serialize($userNames)),$messageId);
		}
		return $this->updateRelations(array('typeid'=>$this->getMap($this->_groupsms_shield)),$userId,array($relationId));
	}
	function recoverGroupMessage($userId,$relationId,$messageId){
		$userId  = intval($userId);
		if( 1 > $userId || 1 > $relationId || 1 > $messageId ){
			return false;
		}
		
		$userService = $this->_getUserService();
		$user = $userService->get($userId);
		if (!$user) {
			return false;
		}		
		$messagesDao = $this->getMessagesDao();
		if(!($message = $messagesDao->get($messageId))){
			return false;
		}
		$userNames = ($message['extra']) ? unserialize($message['extra']) : array();
		if($userNames){
			$userNames[] = $user['username'];
			$messagesDao->update(array('extra'=>serialize($userNames)),$messageId);
		}
		return $this->updateRelations(array('typeid'=>$this->getMap($this->_groupsms_normal)),$userId,array($relationId));
	}
	function openGroupMessage($userId,$groupId,$messageId){
		$msConfig = $this->_getMsConfig($userId,$this->_c_blackcolony);
		$mValue = array();
		if($msConfig){
			$mValue = unserialize($msConfig);
			$mValue = array_diff($mValue,array($groupId));
		}else{
			return true;
		}
		return $this->_setMsConfig(array($this->_c_blackcolony=>serialize($mValue)),$userId);
	}
	function closeGroupMessage($userId,$groupId,$messageId){
		$msConfig = $this->_getMsConfig($userId,$this->_c_blackcolony);
		$mValue = array();
		if($msConfig){
			$mValue = unserialize($msConfig);
			(in_array($groupId,$mValue)) ? 0 : $mValue[] = $groupId;
		}else{
			$mValue = array($groupId);
		}
		return $this->_setMsConfig(array($this->_c_blackcolony=>serialize($mValue)),$userId);
	}
}