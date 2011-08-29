<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 消息中心服务层
 * @2010-4-6 liuhui
 */
class PW_Message {
	/**
	 * 公共发送站内信函数  单人对话/多人对话
	 * @param int $userId  发送用户UID
	 * @param array $usernames 接收用户名数组
	 * @param array $messageinfo array('create_uid','create_username','title','content','expand') 消息体数组
	 * @param string $typeId 站内信类型，默认为短消息，可选评论/留言/评价
	 * @return int 发送成功的messageId
	 */
	function sendMessage($userId, $usernames, $messageInfo, $typeId = null ,$isSuper = false) {
		$service = $this->_serviceFactory("message");
		return $service->sendMessage($userId, $usernames, $messageInfo, $typeId ,$isSuper);
	}
	/**
	 * 公共消息中心全局回复函数
	 * @param int $parentId 父消息ID
	 * @param int $relationId 关系ID
	 * @param int $userId 发送用户UID
	 * @param array $messageinfo 回复体数组
	 * @return int 发送成功的回复id
	 */
	function sendReply($userId, $relationId, $parentId, $messageInfo) {
		$service = $this->_serviceFactory("message");
		return $service->sendReply($userId, $relationId, $parentId, $messageInfo);
	}
	/**
	 * 获取某个人所有站内信
	 * @param int $page
	 * @param int $userId 用户UID
	 * @param int $perpage
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username)) 二维数组结构 关系表和信息表的字段数组
	 */
	function getAllMessages($userId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getAllMessages($userId, $page, $perpage);
	}
	/**
	 * 获取某个人末读站内信
	 * @param int $userId 用户UID
	 * @param int $page
	 * @param int $perpage
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username)) 二维数组结构 关系表和信息表的字段数组
	 */
	function getMessagesNotRead($userId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getMessagesNotRead($userId, $page, $perpage);
	}
	/**
	 * 获取某类型的站内信
	 * @param int $userId 用户UID
	 * @param int $typeId
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getMessages($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getMessages($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取全部对话内容
	 * @param int $userId 用户UID
	 * @param int $messageId
	 * @param int $relationId
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username))
	 */
	function getReplies($userId, $messageId, $relationId) {
		$service = $this->_serviceFactory("message");
		return $service->getReplies($userId, $messageId, $relationId);
	}
	/**
	 * 获取消息的上一条
	 * @param int $relationId
	 * @return array array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username)
	 */
	function getUpMessage($userId, $relationId, $typeId = null) {
		$service = $this->_serviceFactory("message");
		return $service->getUpMessage($userId, $relationId, $typeId);
	}
	
	function getUpInfoByType($userId, $relationId, $isown, $typeId = null) {
		$service = $this->_serviceFactory("message");
		return $service->getUpInfoByType($userId, $relationId, $isown, $typeId);
	}
	
	function getDownInfoByType($userId, $relationId, $isown, $typeId = null) {
		$service = $this->_serviceFactory("message");
		return $service->getDownInfoByType($userId, $relationId, $isown, $typeId);
	}
	/**
	 * 获取消息的下一条
	 * @param int $relationId
	 * @return array array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username)
	 */
	function getDownMessage($userId, $relationId, $typeId = null) {
		$service = $this->_serviceFactory("message");
		return $service->getDownMessage($userId, $relationId, $typeId);
	}
	/**
	 * 获取我发送的站内信
	 * @param int $page
	 * @param int $perpage
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username))
	 */
	function getMessagesBySelf($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getMessagesBySelf($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取我接收的站内信
	 * @param int $page
	 * @param int $perpage
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username))
	 */
	function getMessagesByOther($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getMessagesByOther($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取我发送的站全部内信
	 * @param int $page
	 * @param int $perpage
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username))
	 */
	function getAllMessagesBySelf($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getAllMessagesBySelf($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取我接收的全部站内信
	 * @param int $page
	 * @param int $perpage
	 * @return array array(array(mid,rid,uid,title,content,typeid,categoryid,status,isown,created_time,modified_time,expand,create_uid,create_username))
	 */
	function getAllMessagesByOther($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getAllMessagesByOther($userId, $typeId, $page, $perpage);
	}
	/**
	 * 统计所有站内信
	 * @return int
	 */
	function countAllMessage($userId) {
		$service = $this->_serviceFactory("message");
		return $service->countAllMessage($userId);
	}
	/**
	 * 统计所有末读站内信
	 * @return int
	 */
	function countMessagesNotRead($userId) {
		$service = $this->_serviceFactory("message");
		return $service->countMessagesNotRead($userId);
	}
	/**
	 * 统计所有我发送站内信
	 * @return int
	 */
	function countMessagesBySelf($userId, $typeId) {
		$service = $this->_serviceFactory("message");
		return $service->countMessagesBySelf($userId, $typeId);
	}
	/**
	 * 统计所有我收到站内信
	 * @return int
	 */
	function countMessagesByOther($userId, $typeId) {
		$service = $this->_serviceFactory("message");
		return $service->countMessagesByOther($userId, $typeId);
	}
	/**
	 * 统计某类型的站内信
	 * @param $typeid
	 * @return int
	 */
	function countMessage($userId, $typeId) {
		$service = $this->_serviceFactory("message");
		return $service->countMessage($userId, $typeId);
	}
	/**
	 * 删除一条关系体
	 * @param $relationId
	 * @return int
	 */
	function deleteMessage($userId, $relationId) {
		$service = $this->_serviceFactory("message");
		return $service->deleteMessage($userId, $relationId);
	}
	/**
	 * 删除多条关系体
	 * @param array $relationIds
	 * @return int
	 */
	function deleteMessages($userId, $relationIds) {
		$service = $this->_serviceFactory("message");
		return $service->deleteMessages($userId, $relationIds);
	}
	/**
	 * 更新一条关系体
	 * @param array $fieldData
	 * @param $relationId
	 * @return int
	 */
	function updateMessage($fieldData, $userId, $relationId) {
		$service = $this->_serviceFactory("message");
		return $service->updateMessage($fieldData, $userId, $relationId);
	}
	/**
	 * 标记一条关系体
	 * @param $relationId
	 * @return bool
	 */
	function markMessage($userId, $relationId) {
		$service = $this->_serviceFactory("message");
		return $service->markMessage($userId, $relationId);
	}
	/**
	 * 标记多条关系体
	 * @param array $relationIds
	 * @return bool
	 */
	function markMessages($userId, $relationIds) {
		$service = $this->_serviceFactory("message");
		return $service->markMessages($userId, $relationIds);
	}
	/**
	 * 根椐消息ID获取消息内容
	 * @param $messageId
	 * @return unknown_type
	 */
	function getMessage($messageId) {
		$service = $this->_serviceFactory("message");
		return $service->getMessage($messageId);
	}
	/**
	 * 根椐消息ID更新关系状态为已读
	 * @param $userId
	 * @param $messageId
	 * @return unknown_type
	 */
	function readMessages($userId, $messageId) {
		$service = $this->_serviceFactory("message");
		return $service->readMessages($userId, $messageId);
	}
	/**
	 * 根据大类清空消息
	 * @param $categorys 数组/大类keys数据 如 array('groupsms','sms','notice');
	 * @return unknown_type
	 */
	function clearMessages($userId, $categorys) {
		$service = $this->_serviceFactory("message");
		return $service->clearMessages($userId, $categorys);
	}
	/**
	 * 统计用户所有消息数
	 * @param $userId
	 * @return unknown_type
	 */
	function statisticsMessage($userId) {
		$service = $this->_serviceFactory("message");
		return $service->statisticsMessage($userId);
	}
	/**
	 * 获取互动消息
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @return array('总数','消息数据')
	 */
	function interactiveMessages($userId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->interactiveMessages($userId, $page, $perpage);
	}
	/**
	 * 检查用户发送消息权限
	 * @param $category
	 * @param $number
	 * @return unknown_type
	 */
	function checkUserMessageLevle($category, $number) {
		$service = $this->_serviceFactory("message");
		return $service->checkUserLevle($category, $number);
	}
	/**
	 * 检查接收者的信息
	 * @param $usernames
	 * @return unknown_type
	 */
	function checkReceiver($usernames) {
		$service = $this->_serviceFactory("message");
		return $service->checkReceiver($usernames);
	}
	/**
	 * 根椐关系体删除关系
	 * @param $relationIds
	 * @return unknown_type
	 */
	function deleteRelationsByRelationIds($relationIds) {
		$service = $this->_serviceFactory("message");
		return $service->deleteRelationsByRelationIds($relationIds);
	}
	/**
	 * 获取所有末读消息列表
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getAllNotRead($userId, $page, $perpage) {
		$service = $this->_serviceFactory("message");
		return $service->getAllNotRead($userId, $page, $perpage);
	}
	/**
	 * 批量统计用户消息数
	 * @param array $userIds
	 * @return unknown_type
	 */
	function statisticUsersNumbers($userIds) {
		$service = $this->_serviceFactory("message");
		return $service->countUserNumbers($userIds);
	}
	/**
	 * 根椐用户名和关系ID获取关系
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function getRelation($userId, $relationId) {
		$service = $this->_serviceFactory("message");
		return $service->getRelation($userId, $relationId);
	}
	
	function getInBox($userId, $page, $perpage){
		$service = $this->_serviceFactory("message");
		return $service->getInBox($userId, $page, $perpage);
	}
	
	function countInBox($userId){
		$service = $this->_serviceFactory("message");
		return $service->countInBox($userId);
	}
	
	function getOutBox($userId, $page, $perpage){
		$service = $this->_serviceFactory("message");
		return $service->getOutBox($userId, $page, $perpage);
	}
	
	function countOutBox($userId){
		$service = $this->_serviceFactory("message");
		return $service->countOutBox($userId);
	}
	
	function getMessageByTypeIdWithBoxName($userId, $typeId, $page, $perpage, $boxName = 'outbox'){
		$service = $this->_serviceFactory("message");
		return $service->getMessageByTypeIdWithBoxName($userId, $typeId, $page, $perpage, $boxName);
	}
	
	function countMessageByTypeIdWithBoxName($userId, $typeId, $boxName = 'outbox'){
		$service = $this->_serviceFactory("message");
		return $service->countMessageByTypeIdWithBoxName($userId, $typeId, $boxName);
	}
	
	/**************************************************************/
	/**
	 * 发送一个通知
	 * @param $userId
	 * @param array $usernames
	 * @param array $messageinfo array('create_uid','create_username','title','content','expand') 消息体数组
	 * @param int $typeId
	 * @return $messageId 返回增加成功的消息ID
	 */
	function sendNotice($userId, $usernames, $messageInfo, $typeId = null) {
		$service = $this->_serviceFactory("notice");
		return $service->sendNotice($userId, $usernames, $messageInfo, $typeId);
	}
	/**
	 * 获取某个用户的所有通知
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getAllNotices($userId, $page, $perpage) {
		$service = $this->_serviceFactory("notice");
		return $service->getAllNotices($userId, $page, $perpage);
	}
	/**
	 * 获取某个用户的所有末读通知
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getNoticesNotRead($userId, $page, $perpage) {
		$service = $this->_serviceFactory("notice");
		return $service->getNoticesNotRead($userId, $page, $perpage);
	}
	/**
	 * 获取某个用户的某类型通知
	 * @param $userId
	 * @param $typeId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getNotices($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("notice");
		return $service->getNotices($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取上一条通知
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function getUpNotice($userId, $relationId, $typeId) {
		$service = $this->_serviceFactory("notice");
		return $service->getUpNotice($userId, $relationId, $typeId);
	}
	/**
	 * 获取下一条通知
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function getDownNotice($userId, $relationId, $typeId) {
		$service = $this->_serviceFactory("notice");
		return $service->getDownNotice($userId, $relationId, $typeId);
	}
	/**
	 * 统计所有通知
	 * @param $userId
	 * @return unknown_type
	 */
	function countAllNotice($userId) {
		$service = $this->_serviceFactory("notice");
		return $service->countAllNotice($userId);
	}
	/**
	 * 统计所有末读通知
	 * @param $userId
	 * @return unknown_type
	 */
	function countNoticesNotRead($userId) {
		$service = $this->_serviceFactory("notice");
		return $service->countNoticesNotRead($userId);
	}
	/**
	 * 统计某类型通知
	 * @param $userId
	 * @param $typeId
	 * @return unknown_type
	 */
	function countNotice($userId, $typeId) {
		$service = $this->_serviceFactory("notice");
		return $service->countNotice($userId, $typeId);
	}
	/**
	 * 删除一条通知
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function deleteNotice($userId, $relationId) {
		$service = $this->_serviceFactory("notice");
		return $service->deleteNotice($userId, $relationId);
	}
	/**
	 * 删除多条通知
	 * @param $userId
	 * @param $relationIds
	 * @return unknown_type
	 */
	function deleteNotices($userId, $relationIds) {
		$service = $this->_serviceFactory("notice");
		return $service->deleteNotices($userId, $relationIds);
	}
	/**
	 * 更新一条通知
	 * @param $fieldData
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function updateNotice($fieldData, $userId, $relationId) {
		$service = $this->_serviceFactory("notice");
		return $service->updateNotice($fieldData, $userId, $relationId);
	}
	/**
	 * 标记一条通知已读
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function markNotice($userId, $relationId) {
		$service = $this->_serviceFactory("notice");
		return $service->markNotice($userId, $relationId);
	}
	/**
	 * 标记多条通知已读
	 * @param $userId
	 * @param $relationIds
	 * @return unknown_type
	 */
	function markNotices($userId, $relationIds) {
		$service = $this->_serviceFactory("notice");
		return $service->markNotices($userId, $relationIds);
	}
	/**
	 * 获取一条通知
	 * @param $messageId
	 * @return unknown_type
	 */
	function getNotice($messageId) {
		$service = $this->_serviceFactory("notice");
		return $service->getNotice($messageId);
	}
	/**
	 * 读取一条通知
	 * @param $userId
	 * @param $messageId
	 * @return unknown_type
	 */
	function readNotices($userId, $messageId) {
		$service = $this->_serviceFactory("notice");
		return $service->readNotices($userId, $messageId);
	}
	/**************************************************************/
	/**
	 * 发送消息附件
	 * @param array $fieldDatas 二维数组 array(array('uid','aid','mid','status')......);
	 * @return last_insert_id
	 */
	function sendAttachs($fieldDatas) {
		$service = $this->_serviceFactory("attach");
		return $service->addAttachs($fieldDatas);
	}
	/**
	 * 展示消息附件
	 * @param $userId
	 * @param $messageId
	 * @return array
	 */
	function showAttachs($userId, $messageId) {
		$service = $this->_serviceFactory("attach");
		return $service->getAttachs($userId, $messageId);
	}
	/**
	 * 移除消息附件
	 * @param $userId
	 * @param $id
	 * @return bool
	 */
	function removeAttach($userId, $id) {
		$service = $this->_serviceFactory("attach");
		return $service->removeAttach($userId, $id);
	}
	/**
	 * 获取所有消息附件
	 * @return unknown_type
	 */
	function getAllAttachs($page, $perpage) {
		$service = $this->_serviceFactory("attach");
		return $service->getAllAttachs($page, $perpage);
	}
	/**
	 * 统计所有消息附件
	 * @return unknown_type
	 */
	function countAllAttachs() {
		$service = $this->_serviceFactory("attach");
		return $service->countAllAttachs();
	}
	/**
	 * 根椐消息数组删除附件
	 * @param $messageIds
	 * @return unknown_type
	 */
	function deleteAttachsByMessageIds($messageIds) {
		$service = $this->_serviceFactory("search");
		return $service->deleteAttachsByMessageIds($messageIds);
	}
	/*************************************************/
	/**
	 * 发送请求
	 * @param int $userId
	 * @param array $usernames
	 * @param array array('create_uid','create_username','title','content','expand') 消息体数组
	 * @param int $typeId
	 * @return $messageId 返回增加成功的消息ID
	 */
	function sendRequest($userId, $usernames, $messageInfo, $typeId) {
		$service = $this->_serviceFactory("request");
		return $service->sendRequest($userId, $usernames, $messageInfo, $typeId);
	}
	/**
	 * 获取所有请求
	 * @param int $userId
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getAllRequests($userId, $page, $perpage) {
		$service = $this->_serviceFactory("request");
		return $service->getAllRequests($userId, $page, $perpage);
	}
	/**
	 * 获取所有末读请求
	 * @param int $userId
	 * @param int $page
	 * @param int $perpage
	 * @return array 
	 */
	function getRequestsNotRead($userId, $page, $perpage) {
		$service = $this->_serviceFactory("request");
		return $service->getRequestsNotRead($userId, $page, $perpage);
	}
	/**
	 * 根椐类型获取请求
	 * @param int $userId
	 * @param int $typeId
	 * @param int $page
	 * @param int $perpage
	 * @return array 
	 */
	function getRequests($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("request");
		return $service->getRequests($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取上一条请求
	 * @param int $userId
	 * @param int $relationId
	 * @param int $typeId
	 * @return array 
	 */
	function getUpRequest($userId, $relationId, $typeId) {
		$service = $this->_serviceFactory("request");
		return $service->getUpRequest($userId, $relationId, $typeId);
	}
	/**
	 * 获取下一条请求
	 * @param int $userId
	 * @param int $relationId
	 * @param int $typeId
	 * @return array 
	 */
	function getDownRequest($userId, $relationId, $typeId) {
		$service = $this->_serviceFactory("request");
		return $service->getDownRequest($userId, $relationId, $typeId);
	}
	/**
	 * 统计所有请求
	 * @param int $userId
	 * @return int
	 */
	function countAllRequest($userId) {
		$service = $this->_serviceFactory("request");
		return $service->countAllRequest($userId);
	}
	/**
	 * 统计末读请求
	 * @param int $userId
	 * @return int
	 */
	function countRequestsNotRead($userId) {
		$service = $this->_serviceFactory("request");
		return $service->countRequestsNotRead($userId);
	}
	/**
	 * 统计某类请求
	 * @param int $userId
	 * @param int $typeId
	 * @return int
	 */
	function countRequest($userId, $typeId) {
		$service = $this->_serviceFactory("request");
		return $service->countRequest($userId, $typeId);
	}
	/**
	 * 删除一条请求
	 * @param int $userId
	 * @param int $relationId
	 * @return int
	 */
	function deleteRequest($userId, $relationId) {
		$service = $this->_serviceFactory("request");
		return $service->deleteRequest($userId, $relationId);
	}
	/**
	 * 删除多条请求
	 * @param int $userId
	 * @param int $relationIds
	 * @return int
	 */
	function deleteRequests($userId, $relationIds) {
		$service = $this->_serviceFactory("request");
		return $service->deleteRequests($userId, $relationIds);
	}
	/**
	 * 更新一条请求
	 * @param array $fieldData
	 * @param int $userId
	 * @param int $relationId
	 * @return int
	 */
	function updateRequest($fieldData, $userId, $relationId) {
		$service = $this->_serviceFactory("request");
		return $service->updateRequest($fieldData, $userId, $relationId);
	}
	/**
	 * 标记一条请求
	 * @param int $userId
	 * @param int $relationId
	 * @return bool
	 */
	function markRequest($userId, $relationId) {
		$service = $this->_serviceFactory("request");
		return $service->markRequest($userId, $relationId);
	}
	/**
	 * 标记多条请求
	 * @param int $userId
	 * @param int $relationIds
	 * @return bool
	 */
	function markRequests($userId, $relationIds) {
		$service = $this->_serviceFactory("request");
		return $service->markRequests($userId, $relationIds);
	}
	/**
	 * 获取一条消息体
	 * @param $messageId
	 * @return bool
	 */
	function getRequest($messageId) {
		$service = $this->_serviceFactory("request");
		return $service->getRequest($messageId);
	}
	/**
	 * 读取一条消息
	 * @param $userId
	 * @param $messageId
	 * @return array 
	 */
	function readRequests($userId, $messageId) {
		$service = $this->_serviceFactory("request");
		return $service->readRequests($userId, $messageId);
	}
	/**
	 * 忽略请求
	 * @param int $userId
	 * @param array $relationIds
	 * @return bool
	 */
	function overlookRequests($userId, $relationIds) {
		$service = $this->_serviceFactory("request");
		return $service->overlookRequest($userId, $relationIds);
	}
	/**
	 * 同意请求
	 * @param $userId
	 * @param $relationIds
	 * @return unknown_type
	 */
	function agreeRequests($userId, $relationIds) {
		$service = $this->_serviceFactory("request");
		return $service->agreeRequests($userId, $relationIds);
	}
	/*************************************************/
	/**
	 * 发送群组/用户组消息
	 * @param int $userId
	 * @param int $groupId 群组/用户组ID
	 * @param array array('create_uid','create_username','title','content','expand') 消息体数组
	 * @param int $type  colony/usergroup
	 * @param array $userNames 指定发送用户`
	 * @return $messageId 返回增加成功的消息ID
	 */
	function sendGroupMessage($userId, $groupId, $messageInfo, $type = null, $userNames = array()) {
		$service = $this->_serviceFactory("groupsms");
		return $service->sendGroupMessage($userId, $groupId, $messageInfo, $type, $userNames);
	}
	/**
	 * 获取所有群组/多人对话消息
	 * @param int $userId
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getAllGroupMessages($userId, $page, $perpage) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getAllGroupMessages($userId, $page, $perpage);
	}
	/**
	 * 所有末读群组/多人对话消息
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getGroupMessagesNotRead($userId, $page, $perpage) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupMessagesNotRead($userId, $page, $perpage);
	}
	/**
	 * 获取群组消息
	 * @param $userId
	 * @param $typeId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getGroupMessages($userId, $typeId, $page, $perpage) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupMessages($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取群组/多人对话
	 * @param $userId
	 * @param $messageId
	 * @param $relationId
	 * @return unknown_type
	 */
	function getGroupReplies($userId, $messageId, $relationId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupReplies($userId, $messageId, $relationId);
	}
	/**
	 * 获取群组/多人上一条
	 * @param $userId
	 * @param $relationId
	 * @param $typeId
	 * @return unknown_type
	 */
	function getGroupUpMessage($userId, $relationId, $typeId = null) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupUpMessage($userId, $relationId, $typeId);
	}
	/**
	 * 获取群组/多人下一条
	 * @param $userId
	 * @param $relationId
	 * @param $typeId
	 * @return unknown_type
	 */
	function getGroupDownMessage($userId, $relationId, $typeId = null) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupDownMessage($userId, $relationId, $typeId);
	}
	/**
	 * 获取我发送的群组/多人消息
	 * @param $userId
	 * @param $typeId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getGroupMessagesBySelf($userId, $page, $perpage, $typeId = null) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupMessagesBySelf($userId, $typeId, $page, $perpage);
	}
	/**
	 * 获取我收到的群组/多人消息
	 * @param $userId
	 * @param $typeId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getGroupMessagesByOther($userId, $page, $perpage, $typeId = null) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupMessagesByOther($userId, $typeId, $page, $perpage);
	}
	/**
	 * 统计所有群组/多人消息
	 * @param $userId
	 * @return unknown_type
	 */
	function countAllGroupMessage($userId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->countAllGroupMessage($userId);
	}
	/**
	 * 统计群组/多人末读消息
	 * @param $userId
	 * @return unknown_type
	 */
	function countGroupMessagesNotRead($userId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->countGroupMessagesNotRead($userId);
	}
	/**
	 * 统计群组/多人我发送的消息
	 * @param $userId
	 * @param $typeId
	 * @return unknown_type
	 */
	function countGroupMessagesBySelf($userId, $typeId = null) {
		$service = $this->_serviceFactory("groupsms");
		return $service->countGroupMessagesBySelf($userId, $typeId);
	}
	/**
	 * 统计群组/多人我接收的消息
	 * @param $userId
	 * @param $typeId
	 * @return unknown_type
	 */
	function countGroupMessagesByOther($userId, $typeId = null) {
		$service = $this->_serviceFactory("groupsms");
		return $service->countGroupMessagesByOther($userId, $typeId);
	}
	/**
	 * 统计群组/多人消息
	 * @param $userId
	 * @param $typeId
	 * @return unknown_type
	 */
	function countGroupMessage($userId, $typeId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->countGroupMessage($userId, $typeId);
	}
	/**
	 * 删除一条群组/多人消息
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function deleteGroupMessage($userId, $relationId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->deleteGroupMessage($userId, $relationId);
	}
	/**
	 * 删除多条群组/多人消息
	 * @param $userId
	 * @param $relationIds
	 * @return unknown_type
	 */
	function deleteGroupMessages($userId, $relationIds) {
		$service = $this->_serviceFactory("groupsms");
		return $service->deleteGroupMessages($userId, $relationIds);
	}
	/**
	 * 更新一条群组/多人消息
	 * @param $fieldData
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function updateGroupMessage($fieldData, $userId, $relationId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->updateGroupMessage($fieldData, $userId, $relationId);
	}
	/**
	 * 标记群组/多人消息
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function markGroupMessage($userId, $relationId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->markGroupMessage($userId, $relationId);
	}
	/**
	 * 标记多条群组/多人消息
	 * @param $userId
	 * @param $relationIds
	 * @return unknown_type
	 */
	function markGroupMessages($userId, $relationIds) {
		$service = $this->_serviceFactory("groupsms");
		return $service->markGroupMessages($userId, $relationIds);
	}
	/**
	 * 获取一条群组/多人消息
	 * @param $messageId
	 * @return unknown_type
	 */
	function getGroupMessage($messageId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->getGroupMessage($messageId);
	}
	/**
	 * 读取一条群组/多人消息
	 * @param $userId
	 * @param $messageId
	 * @return unknown_type
	 */
	function readGroupMessages($userId, $messageId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->readGroupMessages($userId, $messageId);
	}
	/**
	 * 屏蔽多人消息
	 * @param $userId
	 * @param $relationId
	 * @param $messageId
	 * @return unknown_type
	 */
	function shieldGroupMessage($userId, $relationId, $messageId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->shieldGroupMessage($userId, $relationId, $messageId);
	}
	/**
	 * 恢复多人消息
	 * @param $userId
	 * @param $relationId
	 * @param $messageId
	 * @return unknown_type
	 */
	function recoverGroupMessage($userId, $relationId, $messageId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->recoverGroupMessage($userId, $relationId, $messageId);
	}
	/**
	 * 启用群组消息
	 * @param $userId
	 * @param $groupId
	 * @param $messageId
	 * @return unknown_type
	 */
	function openGroupMessage($userId, $groupId, $messageId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->openGroupMessage($userId, $groupId, $messageId);
	}
	/**
	 * 关闭群组消息
	 * @param $userId
	 * @param $groupId
	 * @param $messageId
	 * @return unknown_type
	 */
	function closeGroupMessage($userId, $groupId, $messageId) {
		$service = $this->_serviceFactory("groupsms");
		return $service->closeGroupMessage($userId, $groupId, $messageId);
	}
	/*************************************************/
	/**
	 * 根椐类型名称获取类型值
	 * @param $typeName
	 * @return unknown_type
	 */
	function getConst($typeName) {
		$service = $this->_serviceFactory("default");
		return $service->getConst($typeName);
	}
	/**
	 * 根椐类型ID获取类型名称
	 * @return unknown_type
	 */
	function getReverseConst($id) {
		$service = $this->_serviceFactory("default");
		return $service->getReverseConst($id);
	}
	/**
	 * 获取用户屏蔽群组消单
	 * @param $userId
	 * @return array 屏蔽的群组ID数组
	 */
	function getBlackColony($userId) {
		$service = $this->_serviceFactory("default");
		return $service->getBlackColony($userId);
	}
	/**
	 * 设置消息中心配置
	 * @param $userId
	 * @param array $fieldData
	 * @return unknown_type
	 */
	function setMsConfig($fieldData, $userId) {
		$service = $this->_serviceFactory("default");
		return $service->setMsConfig($fieldData, $userId);
	}
	/**
	 * 获取消息中心配置
	 * @param $userId
	 * @param $mKey
	 * @return unknown_type
	 */
	function getMsConfig($userId, $mKey) {
		$service = $this->_serviceFactory("default");
		return $service->getMsConfig($userId, $mKey);
	}
	/**
	 * 根椐键名获取键值
	 * @return unknown_type
	 */
	function getMsKey($key) {
		$service = $this->_serviceFactory("default");
		return $service->getMsKey($key);
	}
	/**
	 * 根椐用户名获取所有用户配置
	 * @param $userId
	 * @return unknown_type
	 */
	function getMsConfigs($userId) {
		$service = $this->_serviceFactory("default");
		return $service->getMsConfigs($userId);
	}
	/**
	 * 获取默认屏蔽消息
	 * @param array $app_array
	 * @return unknown_type
	 */
	function getDefaultShields($app_array = array()) {
		$service = $this->_serviceFactory("default");
		return $service->setDefaultShield($app_array);
	}
	/**
	 * 获取某个用户的屏蔽信息
	 * @param $userId
	 * @param $key
	 * @param array $app_array
	 * @return unknown_type
	 */
	function getMessageShield($userId, $key, $app_array = array()) {
		$service = $this->_serviceFactory("default");
		return $service->getMessageShield($userId, $key, $app_array);
	}
	/**
	 * 根椐用户名获取某个用户的屏蔽信息 
	 * @param $userName
	 * @param $key
	 * @param $app_array
	 * @return unknown_type
	 */
	function getMessageShieldByUserName($userName, $key, $app_array = array()) {
		$service = $this->_serviceFactory("default");
		return $service->getMessageShieldByUserName($userName, $key, $app_array);
	}
	/****************************************************/
	/**
	 * 根椐好友名字与类型搜索信息
	 * @param int $userId  谁搜索 
	 * @param string $userName  搜索谁
	 * @param int $type
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function searchMessages($userId, $userName, $type = null, $page, $perpage) {
		$service = $this->_serviceFactory("search");
		return $service->searchMessages($userId, $userName, $type, $page, $perpage);
	}
	/**
	 * TODO 后台消息管理接口 删除
	 * @param $keyWords  关键字
	 * @param $startTime 起始时间
	 * @param $endTime   结束时间
	 * @param $sender    发送者
	 * @param $isDelete  是否直接删除
	 * @param $page      页数
	 * @param $perpage   每页数
	 * @return array(总页数,消息数组)
	 */
	function manageMessage($keyWords = null, $startTime = null, $endTime = null, $sender = null, $isDelete = 0, $page = 1, $perpage = 30) {
		$service = $this->_serviceFactory("search");
		return $service->manageMessage($keyWords, $startTime, $endTime, $sender, $isDelete, $page, $perpage);
	}
	/**
	 * TODO 后台消息管理接口 删除
	 * @param $category 大类名称
	 * @param $unRead   是否保留末读
	 * @param $isDelete 是否直接删除
	 * @return array(总页数,消息数组)
	 */
	function manageMessageWithCategory($category, $unRead = 0, $isDelete = 0, $page = 1, $perpage = 30) {
		$service = $this->_serviceFactory("search");
		return $service->manageMessageWithCategory($category, $unRead, $isDelete, $page, $perpage);
	}
	/**
	 * TODO 后台消息管理接口 删除
	 * @param $messageIds
	 * @return unknown_type
	 */
	function manageMessageWithMessageIds($messageIds) {
		$service = $this->_serviceFactory("search");
		return $service->manageMessageWithMessageIds($messageIds);
	}
	/*******************************************/
	/**
	 * 根椐用户ID获取历史消息
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getHistoryMessages($userId, $page, $perpage) {
		$service = $this->_serviceFactory("history");
		return $service->getHistoryMessages($userId, $page, $perpage);
	}
	/**
	 * 根椐用户ID统计历史消息
	 * @param $userId
	 * @return unknown_type
	 */
	function countHistoryMessage($userId) {
		$service = $this->_serviceFactory("history");
		return $service->countHistoryMessage($userId);
	}
	/**
	 * 批量删除历史消消息
	 * @param int $userId
	 * @param array $relationIds
	 * @return unknown_type
	 */
	function deleteHistoryMessages($userId, $relationIds) {
		$service = $this->_serviceFactory("history");
		return $service->deleteHistoryMessages($userId, $relationIds);
	}
	/**
	 * 获取一条历史消息详细信息
	 * @param $messageId
	 * @return unknown_type
	 */
	function getHistoryMessage($messageId) {
		$service = $this->_serviceFactory("history");
		return $service->getHistoryMessage($messageId);
	}
	/**
	 * 获取回复历史消息
	 * @param $userId
	 * @param $messageId
	 * @param $relationId
	 * @return unknown_type
	 */
	function getHistoryReplies($userId, $messageId, $relationId) {
		$service = $this->_serviceFactory("history");
		return $service->getHistoryReplies($userId, $messageId, $relationId);
	}
	/**
	 * 把消息转化为历史时间段
	 * @param $timeSegment 时间段  unix 时间戳
	 * @return unknown_type
	 */
	function setHistorys($timeSegment) {
		$service = $this->_serviceFactory("history");
		return $service->setHistorys($timeSegment);
	}
	/**
	 * 重置用户统计数
	 * @param array $userIds
	 * @param string $mKey
	 * @param $mValue
	 * @return unknown_type
	 */
	function resetStatistics($userIds, $mKey) {
		$service = $this->_serviceFactory("default");
		return $service->resetStatistics($userIds, $mKey);
	}
	/**
	 * 根椐用户ID获取用户统计信息
	 * @param $userId
	 * @return array('站内信数','通知数','请求数','群消息数')
	 */
	function getUserStatistics($userId) {
		$service = $this->_serviceFactory("default");
		return $service->getUserStatistics($userId);
	}
	/**
	 * 根椐用户ID获取用户特殊信息
	 * @param $userId
	 * @return unknown_type
	 */
	function getUserSpecialStatistics($userId) {
		$service = $this->_serviceFactory("default");
		return $service->getUserSpecialStatistics($userId);
	}
	/*****************************************************/
	/**
	 * 抓取用户组信息 系统通知
	 * @param int $userId
	 * @param array $groupIds
	 * @param int $lastgrab
	 * @return unknown_type
	 */
	function grabMessage($userId, $groupIds, $lastgrab) {
		$service = $this->_serviceFactory("notice");
		return $service->grabMessage($userId, $groupIds, $lastgrab);
	}
	/**
	 * 创建消息任务 用户组信息
	 * @param array $groupIds   用户组数组
	 * @param array $messageinfo array('create_uid','create_username','title','content','expand') 消息体数组
	 * @return bool
	 */
	function createMessageTasks($groupIds, $messageInfo) {
		$service = $this->_serviceFactory("notice");
		return $service->createMessageTasks($groupIds, $messageInfo);
	}
	/**
	 * 给在线用户发送消息函数
	 * @param array $onlineUserIds 在线用户
	 * @param array $messageinfo array('create_uid','create_username','title','content','expand') 消息体数组
	 * @return unknown_type
	 */
	function sendOnlineMessages($onlineUserIds, $messageInfo) {
		$service = $this->_serviceFactory("notice");
		return $service->sendOnlineMessages($onlineUserIds, $messageInfo);
	}
	
	function sendTaskMessages($userIds, $messageInfo, $messageId = null) {
		$service = $this->_serviceFactory("task");
		return $service->sendTaskMessages($userIds, $messageInfo, $messageId);
	}
	/**
	 * 私有加载消息中心服务入口
	 * @param $name
	 * @return unknown_type
	 */
	function _serviceFactory($name) {
		static $classes = array();
		$name = strtolower($name);
		$filename = R_P . "lib/message/message/" . $name . ".ms.php";
		if (!is_file($filename)) {
			return null;
		}
		$class = 'MS_' . ucfirst($name);
		if (isset($classes[$class])) {
			return $classes[$class];
		}
		if (!class_exists('MS_BASE')) require (R_P . 'lib/message/message/base.ms.php');
		if (!class_exists($class)) include S::escapePath($filename);
		$classes[$class] = new $class();
		return $classes[$class];
	}
	
	function matchTidByConetnt($content) {
		if (strrpos($content,'read.php?tid=') === false) return false;
		preg_match_all('/read.php\?tid=(.*)\]/U', $content, $matches);
		$tid = intval($matches[1][0]);
		return $tid ? $tid : false;
	}
	
	function countAllByUserId($userId) {
		$userId = intval($userId);
		if ($userId < 1) return array();
		$relationsDb = L::loadDB('ms_relations', 'message');
		foreach ($relationsDb->countAllByUserId($userId) as $k=>$v) {
			$result[$k] = $v['total'];
		}
		return $result;
	}
}

