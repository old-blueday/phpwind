<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 站内信服务层
 * @2010-4-6 liuhui
 */
class MS_Message extends MS_Base {
	function sendMessage($userId, $userNames, $messageInfo, $typeId = null ,$isSuper = false) {
		$userId = intval($userId);
		if (1 > $userId || "" == $userNames || !$messageInfo || !is_array($userNames)) {
			return false;
		}
		$typeId = ($typeId) ? $typeId : $this->getmap($this->_sms_message);
		if (!$isSuper && !$this->_checkUserLevle($this->_sms, count($userNames), $typeId)) {
			return false;
		}
		list($userNames, $categoryId, $typeId) = $this->_checkReceiver($userNames, $this->_sms, $typeId);
		if (!$userNames) return false;
		list($userIds, $userNames) = $this->_getUserByUserNames($userNames, true);
		if (!$userIds) return false;
		if (2 <= count($userIds)) {
			$userIds = array_slice($userIds, 0, $this->_receiver);
			$messageId = $this->_sendMulti($userId, $userIds, $userNames, $categoryId, $typeId, $messageInfo);
		} else {
			$messageId = $this->_sendSingle($userId, $userIds[0], $userNames[0], $categoryId, $typeId, $messageInfo);
		}
		if (!$messageId) return false;
		$this->_doReply($userId, $messageId, $messageInfo);
		$this->_updateStatisticsByUserNames($userIds, false, $category, 1);
		$this->_updateUserMessageNumbers($userIds);
		return $messageId;
	}
	/**
	 * 私用发送单条消息
	 * @param $userId
	 * @param $toUserId
	 * @param $categoryId
	 * @param $typeId
	 * @param $messageInfo
	 * @return unknown_type
	 */
	function _sendSingle($userId, $toUserId, $userName, $categoryId, $typeId, $messageInfo) {
		$userId = intval($userId);
		$toUserId = intval($toUserId);
		if (1 > $userId || 1 > $toUserId) {
			return false;
		}
		$messageInfo['expand'] = serialize(array('categoryid' => $categoryId,'typeid' => $typeId,'actor' => array($userId, $toUserId)));
		$messageInfo['extra'] = serialize(array($userName));
		if (!($messageId = $this->_addMessage($messageInfo))) {
			return false;
		}
		$relations = array(
						array('uid' => $toUserId,'mid' => $messageId,'categoryid' => $categoryId,'typeid' => $typeId,
							'status' => $this->_s_not_read,'isown' => $this->_s_other,
							'created_time' => $this->_timestamp,'modified_time' => $this->_timestamp),
						array('uid' => $userId,'mid' => $messageId,'categoryid' => $categoryId,'typeid' => $typeId,
							'status' => $this->_s_have_read,'isown' => $this->_s_self,
							'created_time' => $this->_timestamp,'modified_time' => $this->_timestamp));
		$relationsDao = $this->getRelationsDao();
		if (!($relationId = $relationsDao->addRelations($relations))) {
			return false;
		}
		$this->_addSearch($userId, $toUserId, $relationId, $messageId, $typeId);
		return $messageId;
	}
	/**
	 * 私有发送多条消息
	 * @param $userId
	 * @param $userIds
	 * @param $userNames
	 * @param $messageInfo
	 * @return unknown_type
	 */
	function _sendMulti($userId, $userIds, $userNames, $categoryId, $typeId, $messageInfo) {
		$categoryId = intval($this->getMap($this->_groupsms));
		$typeId = intval($this->getMap($this->_groupsms_normal));
		$messageInfo['extra'] = (is_array($userNames)) ? serialize($userNames) : $userNames;
		if (!($messageId = $this->_doSend($userId, $userIds, $categoryId, $typeId, $messageInfo))) {
			return false;
		}
		return $messageId;
	}
	function sendReply($userId, $relationId, $parentId, $messageInfo) {
		$userId = intval($userId);
		$parentId = intval($parentId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $parentId || 1 > $relationId || !$messageInfo) {
			return false;
		}
		return $this->_reply($userId, $relationId, $parentId, $messageInfo);
	}
	function getAllMessages($userId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getAll($userId, $this->_sms, $page, $perpage);
	}
	function getMessagesNotRead($userId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getsByStatus($userId, $this->_sms, $this->_s_not_read, $page, $perpage);
	}
	function getMessages($userId, $typeId, $page, $perpage) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage || 1 > $typeId) {
			return false;
		}
		return $this->_getsByTypeId($userId, $this->_sms, $typeId, $page, $perpage);
	}
	function getReplies($userId, $messageId, $relationId) {
		$userId = intval($userId);
		$messageId = intval($messageId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $messageId || 1 > $relationId) {
			return false;
		}
		return $this->_getReplies($userId, $messageId, $relationId);
	}
	function getUpMessage($userId, $relationId, $typeId = null) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		return $this->_upMessage($userId, $this->_sms, $relationId, $typeId);
	}
	function getDownMessage($userId, $relationId, $typeId = null) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		return $this->_downMessage($userId, $this->_sms, $relationId, $typeId);
	}
	/**
	 * 
	 * 获取上一条消息内容
	 * @param int $userId 用户id
	 * @param int $relationId 消息id
	 * @param int $isown 我发的｜别人发给我的
	 * @param init $typeId 消息类型(日志｜评论..)
	 */
	function getUpInfoByType($userId, $relationId, $isown, $typeId = null) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		$isown = intval($isown);		
		$isown  == 1 && $isown =  $this->_s_self;
		$isown  == 2 && $isown = $this->_s_other;
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		$messageInfo = $this->_getUpMsInfoByType($userId, $this->_sms, $relationId, $isown, $typeId);
		if(!$messageInfo){
			return false;
		}
		return $this->getRealMessageInfo($messageInfo);
	}
	
	/**
	 * 
	 * 获取下一条消息内容
	 * @param int $userId 用户id
	 * @param int $relationId 消息id
	 * @param int $isown 我发的｜别人发给我的
	 * @param int $typeId 消息类型(日志｜评论..)
	 */
	function getDownInfoByType($userId, $relationId, $isown, $typeId = null) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		$isown = intval($isown);		
		$isown  == 1 && $isown =  $this->_s_self;
		$isown  == 2 && $isown = $this->_s_other;
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		$messageInfo = $this->_getDownMsInfoByType($userId, $this->_sms, $relationId, $isown, $typeId);
		if(!$messageInfo){
			return false;
		}
		return $this->getRealMessageInfo($messageInfo);
	}
	
	/**
	 * 获取上下条的真正消息内容
	 */
	function getRealMessageInfo($messageInfo){
		if(!is_array($messageInfo)){
			return false;
		}
		if($messageInfo['relation'] != 2){
			return $messageInfo;
		}
		$expand = (isset($messageInfo['expand'])) ? unserialize($messageInfo['expand']) : array();
		if(!($parentMessage = $this->getMessage($expand['parentid']))){
			return false;
		}
		return array_merge($messageInfo,$parentMessage);
	}
	
	function getMessagesBySelf($userId, $typeId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getsByIsown($userId, $this->_sms, $typeId, $this->_s_self, $page, $perpage);
	}
	function getMessagesByOther($userId, $typeId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getsByIsown($userId, $this->_sms, $typeId, $this->_s_other, $page, $perpage);
	}
	//发送 获取全部
	function getAllMessagesBySelf($userId, $typeId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getsAllByIsown($userId, $this->_sms, $typeId, $this->_s_self, $page, $perpage);
	}
	//接受 获取全部
	function getAllMessagesByOther($userId, $typeId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		return $this->_getsAllByIsown($userId, $this->_sms, $typeId, $this->_s_other, $page, $perpage);
	}
	function countAllMessage($userId) {
		$userId = intval($userId);
		if (1 > $userId) {
			return false;
		}
		return $this->_countAll($userId, $this->_sms);
	}
	function countMessagesNotRead($userId) {
		$userId = intval($userId);
		if (1 > $userId) {
			return false;
		}
		return $this->_countByStatus($userId, $this->_sms, $this->_s_not_read, $page, $perpage);
	}
	function countMessagesBySelf($userId, $typeId) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		if (1 > $userId || 1 > $typeId) {
			return false;
		}
		return $this->_countByIsown($userId, $this->_sms, $typeId, $this->_s_self);
	}
	function countMessagesByOther($userId, $typeId) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		if (1 > $userId || 1 > $typeId) {
			return false;
		}
		return $this->_countByIsown($userId, $this->_sms, $typeId, $this->_s_other);
	}
	function countMessage($userId, $typeId) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		if (1 > $userId || 1 > $typeId) {
			return false;
		}
		return $this->_countByTypeId($userId, $this->_sms, $typeId);
	}
	function deleteMessage($userId, $relationId) {
		$userId = intval($userId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		return $this->_deleteRelations($userId, array($relationId));
	}
	function deleteMessages($userId, $relationIds) {
		$userId = intval($userId);
		if (1 > $userId || !$relationIds) {
			return false;
		}
		return $this->_deleteRelations($userId, $relationIds);
	}
	function updateMessage($fieldData, $userId, $relationId) {
		$userId = intval($userId);
		$relationId = intval($relationId);
		if (1 > $userId || !$fieldData || 1 > $relationId) {
			return false;
		}
		return $this->_update($fieldData, $userId, $relationId);
	}
	function markMessage($userId, $relationId) {
		$userId = intval($userId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		return $this->_mark($userId, array($relationId));
	}
	function markMessages($userId, $relationIds) {
		$userId = intval($userId);
		if (1 > $userId || !$relationIds) {
			return false;
		}
		return $this->_mark($userId, $relationIds);
	}
	function getMessage($messageId) {
		$messageId = intval($messageId);
		if (1 > $messageId) {
			return false;
		}
		return $this->_get($messageId);
	}
	function readMessages($userId, $messageId) {
		$messageId = intval($messageId);
		$userId = intval($userId);
		if (1 > $messageId || 1 > $userId) {
			return false;
		}
		return $this->_updateByMessageIds(array('status' => $this->_s_have_read), $userId, array($messageId));
	}
	function clearMessages($userId, $categorys) {
		$userId = intval($userId);
		if (1 > $userId || !$categorys || !is_array($categorys)) return false;
		$categoryIds = array();
		foreach ($categorys as $category) {
			$categoryIds[] = $this->getMap($category);
		}
		if (!$categoryIds) return false;
		return $this->_clearMessages($userId, $categoryIds);
	}
	function statisticsMessage($userId) {
		$userId = intval($userId);
		if (1 > $userId) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->countAllRelationsByUserId($userId);
	}
	function interactiveMessages($userId, $page, $perpage) {
		$userId = intval($userId);
		if (1 > $userId) {
			return array(false,false);
		}
		$relationsDao = $this->getRelationsDao();
		if (!($total = $relationsDao->countRelationsNotRead($userId))) {
			return array(false,false);
		}
		$start = ($page - 1) * $perpage;
		$result = $relationsDao->getRelationsNotRead($userId, $start, $perpage);
		//TODO
		foreach ($result as $r) {
			$messageIds[] = $r['mid'];
		}
		return array($total,$this->_build($result));
	}
	function checkUserLevle($category, $number) {
		return $this->_checkUserLevle($category, $number);
	}
	/**
	 * 检查接收者信息
	 * @param $userId
	 * @return unknown_type
	 */
	function checkReceiver($usernames) {
		if (!is_array($usernames) || count($usernames) > 1) {
			return array(true,true);
		}
		$userService = $this->_getUserService();
		$user = $userService->getByUserName($usernames[0]);
		if (!$user) {
			return array(false,'用户不存在');
		}
		list($userId, $username, $groupId) = array($user['uid'],$user['username'],
												(($user['groupid'] > 0) ? $user['groupid'] : $user['memberid']));
		$permissions = $this->_getPermissions();
		$userInfos = $this->_countUserNumbers(array($userId));
		if (isset($permissions[$groupId]) && isset($userInfos[$userId]) && $permissions[$groupId] > 0 && $userInfos[$userId] >= $permissions[$groupId]) {
			return array(false,'' . $username . '消息数已满,不能接收你的消息');
		}
		return array(true,true);
	}
	
	function deleteRelationsByRelationIds($relationIds) {
		if (!is_array($relationIds)) return false;
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->deleteRelationsByRelationIds($relationIds);
	}
	function getAllNotRead($userId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId) {
			return array();
		}
		$start = ($page - 1) * $perpage;
		$relationsDao = $this->getRelationsDao();
		if (!($result = $relationsDao->getRelationsByStatusAndUserId($userId, $this->_s_not_read, $start, $perpage))) {
			return array();
		}
		$result = $this->_build($result);
		return ($result) ? $result : array();
	}
	function countUserNumbers($userIds) {
		if (!$userIds) return false;
		return $this->_countUserNumbers($userIds);
	}
	function getRelation($userId, $relationId) {
		$userId = intval($userId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $relationId) {
			return array();
		}
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->getRelation($userId, $relationId);
	}
	
	/**********************收件箱与发件箱功能*****************************************/
	
	function getInBox($userId, $page, $perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsSpecialByIsown($userId, $this->_sms, null, $this->_s_other, $page, $perpage);
	}
	
	function countInBox($userId){
		$userId   = intval($userId);
		if( 1 > $userId){
			return false;
		}
		return $this->_countSpecialByIsown($userId, $this->_sms, null, $this->_s_other);
	}
	
	function getOutBox($userId, $page, $perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsSpecialByIsown($userId, $this->_sms, null, $this->_s_self, $page, $perpage);
	}
	
	function countOutBox($userId){
		$userId   = intval($userId);
		if( 1 > $userId){
			return false;
		}
		return $this->_countSpecialByIsown($userId, $this->_sms, null, $this->_s_self);
	}
	
	function getMessageByTypeIdWithBoxName($userId, $typeId, $page, $perpage, $boxName = 'outbox'){
		if($boxName == 'outbox'){
			return $this->getMessagesBySelf($userId, $typeId, $page, $perpage);
		}
		return $this->getMessagesByOther($userId, $typeId, $page, $perpage);
	}
	
	function countMessageByTypeIdWithBoxName($userId, $typeId, $boxName = 'outbox'){
		if($boxName == 'outbox'){
			return $this->countMessagesBySelf($userId, $typeId);
		}
		return $this->countMessagesByOther($userId, $typeId);
	}
	

}
