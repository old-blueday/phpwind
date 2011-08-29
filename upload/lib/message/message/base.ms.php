<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 消息中心基类服务层
 * 公共全局服务 包括配置/数据访问接口/通用公共函数
 * @copyright phpwind v8.0
 * @author liuhui 2010-4-6
 */
class MS_Base {
	var $_sms = 'sms'; //站内信
	var $_sms_message = 'sms_message'; //短消息
	var $_sms_rate = 'sms_rate'; //评分
	var $_sms_comment = 'sms_comment'; //评论
	var $_sms_guestbook = 'sms_guestbook'; //留言
	var $_sms_share = 'sms_share'; //分享
	var $_sms_reply = 'sms_reply'; //帖子回复
	var $_notice = 'notice'; //通知
	var $_notice_system = 'notice_system'; //系统通知
	var $_notice_postcate = 'notice_postcate'; //团购通知
	var $_notice_active = 'notice_active'; //活动通知
	var $_notice_apps = 'notice_apps'; //应用通知
	var $_notice_comment = 'notice_comment'; //评论通知
	var $_notice_guestbook = 'notice_guestbook'; //留言通知 
	var $_request = 'request'; //请求
	var $_request_friend = 'request_friend'; //好友请求
	var $_request_group = 'request_group'; //群组请求
	var $_request_active = 'request_active'; //活动请求
	var $_request_apps = 'request_apps'; //应用请求
	var $_groupsms = 'groupsms';
	var $_groupsms_colony = 'groupsms_colony'; //群组
	var $_groupsms_normal = 'groupsms_normal'; //正常的多人对话
	var $_groupsms_shield = 'groupsms_shield'; //屏蔽的多人对话
	var $_chat = 'chat';
	var $_history = 'history';
	
	var $_s_have_read = 0; //已读
	var $_s_not_read = 1; //末读
	var $_s_new_reply = 2; //新回复
	var $_s_self = 1; //我发送
	var $_s_other = 0; //我接收
	

	var $_s_overlook = 4; //忽略请求
	var $_s_agree = 5; //同意请求
	

	var $_timestamp = null;
	var $_receiver = 20;
	var $_attachmentPath = null;
	var $_userId = null;
	var $_userName = null;
	var $_groupId = null;
	var $_userGroup = null;
	var $_nodeTime = 0; //每日起点时间 12:00
	var $_super = 0; //超级权限开关 是否开启全局设置
	
	var $_c_relation_reply = 2; //关系类型,回复类型

	var $_c_sms_num = 'sms_num'; //站内信消息数
	var $_c_notice_num = 'notice_num'; //通知消息数
	var $_c_request_num = 'request_num'; //请求消息数
	var $_c_groupsms_num = 'groupsms_num'; //群组消息数
	

	var $_s_notice_system = 'notice_website';
	
	function MS_Base() {
		global $timestamp, $db_attachname, $winduid, $windid, $_G, $tdtime, $db_windpost, $groupid, $winddb;
		$this->_userId = &$winduid;
		$this->_userName = &$windid;
		$this->_groupId = ($groupid > 0) ? $groupid : $winddb['memberid'];
		$this->_userGroup = &$_G;
		$this->_nodeTime = &$tdtime;
		$this->_windpost = &$db_windpost;
		$this->_timestamp = ($timestamp) ? $timestamp : time();
		$this->_attachmentPath = ($db_attachname) ? $db_attachname : 'attachment';
	
	}
	/**
	 * 全局检查用户是否有消息发送权限
	 * @return unknown_type
	 */
	function _checkUserLevle($category, $number = 1, $typeId = null) {
		//发送消息是否开启
		if ($this->_super) {
			return true;
		}
		if (!in_array($category, array($this->_sms,$this->_groupsms))) {
			return true;
		}
		$typeIds = $this->_getSpecialMap(array($this->_sms_message,$this->_groupsms_normal));
		if ($typeId && !in_array($typeId, $typeIds)) {
			return true;
		}
		if (!isset($this->_userGroup['allowmessege']) || !$this->_userGroup['allowmessege']) {
			return false;
		}
		if ($number > 1 && (!isset($this->_userGroup['multiopen']) || !$this->_userGroup['multiopen'])) {
			return false;
		}
		// 每日最大发送消息数目
		$relationsDao = $this->getRelationsDao();
		//$this->_userGroup['maxsendmsg'] = ($this->_userGroup['maxsendmsg']) ? $this->_userGroup['maxsendmsg'] : 20;
		if (isset($this->_userGroup['maxsendmsg']) && $this->_userGroup['maxsendmsg'] > 0) {
			if ($this->_userGroup['maxsendmsg'] - 1 < ($total = $relationsDao->countSelfByUserId($this->_userId, $this->_nodeTime))) {
				return false;
			}
		}
		// 用户最大消息数
		if (isset($this->_userGroup['maxmsg']) && $this->_userGroup['maxmsg'] > 0) {
			$userInfo = $this->_countUserNumbers(array($this->_userId));
			if ($userInfo && $this->_userGroup['maxmsg'] - 1 < $userInfo[$this->_userId]) {
				return false;
			}
		}
		return true;
	}
	/**
	 * 全局检查消息接收者与消息类型信息
	 * @return unknown_type
	 */
	function _checkReceiver($usernames, $category, $typeId) {
		if ("" == $usernames || "" == $category) {
			return array(false,false,false);
		}
		$usernames = is_array($usernames) ? $usernames : array($usernames);
		$usernames = array_unique($usernames);
		$categoryId = intval($this->getMap($category));
		$typeId = intval($typeId);
		if (0 > $categoryId || 1 > $typeId) {
			return array(false,false,false);
		}
		return array($usernames,$categoryId,$typeId);
	}
	/**
	 * 公共发送消息接口服务
	 * @param int $userId
	 * @param array $userIds
	 * @param int $categoryId
	 * @param int $typeId
	 * @param array $messageInfo
	 * @param bool $both 是否双向接收消息
	 * @return messageId 发送的消息体ID
	 */
	function _doSend($userId, $userIds, $categoryId, $typeId, $messageInfo, $both = true) {
		$messageInfo['expand'] = serialize(array('categoryid' => $categoryId,'typeid' => $typeId));
		if (!($messageId = $this->_addMessage($messageInfo))) {
			return false;
		}
		($both && $userId > 0 && !in_array($userId, $userIds)) && array_push($userIds, $userId);
		$relations = array();
		$userIds = array_unique($userIds);
		foreach ($userIds as $otherId) {
			$relation = array();
			$relation['uid'] = $otherId;
			$relation['mid'] = $messageId;
			$relation['categoryid'] = $categoryId;
			$relation['typeid'] = $typeId;
			$relation['status'] = ($otherId == $userId) ? $this->_s_have_read : $this->_s_not_read;
			$relation['isown'] = ($otherId == $userId) ? $this->_s_self : $this->_s_other;
			$relation['created_time'] = $relation['modified_time'] = $this->_timestamp;
			$relations[] = $relation;
		}
		$relationsDao = $this->getRelationsDao();
		if (!$relationsDao->addRelations($relations)) {
			return false;
		}
		return $messageId;
	}
	/**
	 * 私有增加消息体接口服务
	 * @param $messageInfo
	 * @return unknown_type
	 */
	function _addMessage($messageInfo) {
		if (false == ($messageInfo = $this->_checkInfo($messageInfo))) {
			return false;
		}
		$messagesDao = $this->getMessagesDao();
		if (!($messageId = $messagesDao->insert($messageInfo))) {
			return false;
		}
		return $messageId;
	}
	/**
	 * 公共全局回复消息函数
	 * @param int $userId         回复用户UID
	 * @param int $parentId       父消息MID
	 * @param array $messageInfo  消息体内容数组
	 * @return array 返回成功的消息
	 */
	function _reply($userId, $relationId, $parentId, $messageInfo) {
		$messagesDao = $this->getMessagesDao();
		if (!($message = $messagesDao->get($parentId))) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		#if (!($relation = $relationsDao->getRelation($userId, $relationId)) || $relation['mid'] != $parentId) {
		#	return false;
		#}
		if (!($relation = $relationsDao->getRelation($userId, $relationId))) {
			return false;
		}
		if (!($result = $this->_doReply($userId, $parentId, $messageInfo))) {
			return false;
		}
		if($this->getMapByTypeId($relation['typeid']) == $this->_sms ){
			$messageInfo['title'] = 'RE:'.$message['title'];
			$actor = (isset($message['expand']) && ($expand = unserialize($message['expand'])) && (isset($expand['actor']))) ? $expand['actor'] : array();
			$this->_addReplyRelations($userId,$actor,$parentId,$relation['categoryid'],$relation['typeid'], $messageInfo);
			$fieldData = array();
		}else{
			$messagesDao->update(array('modified_time' => $this->_timestamp,'content' => $messageInfo['content']), $parentId);
			$fieldData = array('status' => $this->_s_new_reply,'modified_time' => $this->_timestamp);
		}
		$expand = ($message['expand']) ? unserialize($message['expand']) : array();
		if ($relation['categoryid'] == $this->getMap($this->_history)) {
			$expand = ($message['expand']) ? unserialize($message['expand']) : array();
			$expand && $fieldData['categoryid'] = $expand['categoryid'];
		}
		$categoryId = ($fieldData['categoryid']) ? $fieldData['categoryid'] : $relation['categoryid'];
		$fieldData && $relationsDao->updateRelationsByMessageId($fieldData, $parentId);
		$this->_updateStatisticsByCategoryId($categoryId, $message, $userId);
		return $result;
	}
	/**
	 * 新增回复关系
	 */
	function _addReplyRelations($userId, $actor, $parentId, $categoryId, $typeId, $messageInfo){
		if(!$actor && is_array($actor) && !$messageInfo && !is_array($messageInfo)){
			return false;
		}
		$userIds = array();
		foreach($actor as $tmpUserId){
			($userId != $tmpUserId) && $userIds[] = $tmpUserId;
		}
		$userService = $this->_getUserService();
		if(!($toUser = $userService->get($userIds[0]))){
			return false;
		}
		$messageInfo['expand'] = serialize(array('categoryid' => $categoryId,'typeid' => $typeId, 'parentid' => $parentId));
		$messageInfo['extra'] = serialize(array($toUser['username']));
		if (!($messageId = $this->_addMessage($messageInfo))) {
			return false;
		}
		$relations = array();
		$userIds = array($toUser['uid'],$userId);
		foreach ($userIds as $otherId) {
			$relation = array();
			$relation['uid']          = $otherId;
			$relation['mid']          = $messageId;
			$relation['categoryid']   = $categoryId;
			$relation['typeid']       = $typeId;
			$relation['status']       = ($otherId == $userId) ? $this->_s_have_read : $this->_s_not_read;
			$relation['isown']        = ($otherId == $userId) ? $this->_s_self : $this->_s_other;
			$relation['relation']     = $this->_c_relation_reply;
			$relation['created_time'] = $relation['modified_time'] = $this->_timestamp;
			$relations[] = $relation;
		}
		$relationsDao = $this->getRelationsDao();
		if (!($relationId = $relationsDao->addReplyRelations($relations))) {
			return false;
		}
		$this->_addSearch($userId, $toUser['uid'], $relationId, $messageId, $typeId);
		return $messageId;
	}
	
	function _doReply($userId, $parentId, $messageInfo) {
		$userId = intval($userId);
		$parentId = intval($parentId);
		if (1 > $userId || 1 > $parentId) {
			return false;
		}
		if (false == ($messageInfo = $this->_checkInfo($messageInfo))) {
			return false;
		}
		$repliesDao = $this->getRepliesDao();
		$fieldData = array();
		$fieldData['parentid'] = $parentId;
		$fieldData['create_uid'] = $messageInfo['create_uid'];
		$fieldData['create_username'] = $messageInfo['create_username'];
		$fieldData['title'] = $messageInfo['title'];
		$fieldData['content'] = $messageInfo['content'];
		$fieldData['status'] = $this->_s_not_read;
		$fieldData['created_time'] = $fieldData['modified_time'] = $this->_timestamp;
		if (!($result = $repliesDao->insert($fieldData))) {
			return false;
		}
		return $result;
	}
	/**
	 * 获取某个消息的全部对话
	 * @param int $userId
	 * @param int $messageId
	 * @return unknown_type
	 */
	function _getReplies($userId, $messageId, $relationId) {
		$userId = intval($userId);
		$messageId = intval($messageId);
		if (1 > $userId || 1 > $messageId) {
			return false;
		}
		$repliesDao = $this->getRepliesDao();
		if (!($replies = $repliesDao->getRepliesByMessageId($messageId))) {
			return false;
		}
		//update not self status
		$ids = array();
		foreach ($replies as $r) {
			($r['create_uid'] != $userId) ? $ids[] = $r['id'] : 0;
		}
		$ids && $this->_updateRepliesByIds(array('status' => $this->_s_have_read), $ids);
		//$this->_updateByMessageIds(array('status'=>$this->_s_have_read),$userId,array($messageId));
		$this->_update(array('actived_time' => $this->_timestamp,'status' => $this->_s_have_read), $userId, $relationId);
		if (!($result = $this->_buildUsersLists($replies))) {
			return false;
		}
		return $this->_buildOnLineUser($result);
	}
	/**
	 * 获取某个消息的多人对话
	 * @param $userId
	 * @param $messageId
	 * @param $relationId
	 * @return array
	 */
	function _getGroupReplies($userId, $messageId, $relationId) {
		$userId = intval($userId);
		$messageId = intval($messageId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $messageId || 1 > $relationId) {
			return false;
		}
		$repliesDao = $this->getRepliesDao();
		if (!($replies = $repliesDao->getRepliesByMessageId($messageId))) {
			return false;
		}
		$this->_update(array('actived_time' => $this->_timestamp,'status' => $this->_s_have_read), $userId, $relationId);
		if (!($result = $this->_buildUsersLists($replies))) {
			return false;
		}
		return $this->_buildOnLineUser($result);
	}
	
	function _buildOnLineUser($replies) {
		if (!$replies) return false;
		$userIds = array();
		foreach ($replies as $r) {
			$userIds[] = $r['uid'];
		}
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$onlineUser = array();
		foreach ($userService->getByUserIds($userIds) as $u) {
			$onlineUser[$u['uid']] = $u['thisvisit'];
		}
		
		$tmp = array();
		foreach ($replies as $r) {
			$r['thisvisit'] = (isset($onlineUser[$r['uid']])) ? $onlineUser[$r['uid']] : 0;
			$tmp[] = $r;
		}
		return $tmp;
	}
	/**
	 * 公共根椐类型ID获取消息
	 * @param int $userId   用户UID
	 * @param string $category 大类名称
	 * @param int $typeId      子类ID
	 * @param int $page        页数
	 * @param int $perpage     分页数
	 * @return array  返回消息体+关系体数组 
	 */
	function _getsByTypeId($userId, $category, $typeId, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		$typeId = intval($typeId);
		if (1 > $userId || 1 > $page || 1 > $perpage || 1 > $typeId) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		$start = ($page - 1) * $perpage;
		if (!($relations = $relationsDao->getRelations($userId, $categoryId, $typeId, $start, $perpage))) {
			return false;
		}
		return $this->_build($relations, $category);
	}
	/**
	 * 公共根椐类型统计消息
	 * @param $userId
	 * @param $category
	 * @param $typeId
	 * @return unknown_type
	 */
	function _countByTypeId($userId, $category, $typeId) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		if (1 > $userId || 1 > $typeId) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		return intval($relationsDao->countRelations($userId, $categoryId, $typeId));
	}
	/**
	 * 公共获取所有某类消息
	 * @param $userId
	 * @param $category
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getAll($userId, $category, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		$start = ($page - 1) * $perpage;
		if (!($relations = $relationsDao->getAllRelations($userId, $categoryId, $start, $perpage))) {
			return false;
		}
		return $this->_build($relations, $category);
	}
	/**
	 * 公共统计所有某类消息
	 * @param $userId
	 * @param $category
	 * @return unknown_type
	 */
	function _countAll($userId, $category) {
		$userId = intval($userId);
		if (1 > $userId) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		return intval($relationsDao->countAllRelations($userId, $categoryId));
	}
	/**
	 * 公共获取某类型末读消息
	 * @param $userId
	 * @param $category
	 * @param $status 是否已/末读消息
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getsByStatus($userId, $category, $status, $page, $perpage) {
		$userId = intval($userId);
		$page = intval($page);
		$perpage = intval($perpage);
		$status = intval($status);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		$start = ($page - 1) * $perpage;
		if (!($relations = $relationsDao->getRelationsByStatus($userId, $categoryId, $status, $start, $perpage))) {
			return false;
		}
		return $this->_build($relations, $category);
	}
	/**
	 * 公共统计某类型末读消息
	 * @param $userId
	 * @param $category
	 * @param $status
	 * @return unknown_type
	 */
	function _countByStatus($userId, $category, $status) {
		$userId = intval($userId);
		$status = intval($status);
		if (1 > $userId) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		return intval($relationsDao->countRelationsByStatus($userId, $categoryId, $status));
	}
	
	/**
	 * 公共根椐消息归属获取消息
	 * @param $userId
	 * @param $category
	 * @param $isown   是否我接收/发送
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getsByIsown($userId, $category, $typeId, $isown, $page, $perpage) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$page = intval($page);
		$perpage = intval($perpage);
		$isown = intval($isown);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		$start = ($page - 1) * $perpage;
		if (!($relations = $relationsDao->getRelationsByIsown($userId, $categoryId, $typeId, $isown, $start, $perpage))) {
			return false;
		}
		return $this->_build($relations);
	}
	/**
	 * 公共根椐消息归属获取消息 获取全部消息
	 * @param $userId
	 * @param $category
	 * @param $isown   是否我接收/发送
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getsAllByIsown($userId, $category, $typeId, $isown, $page, $perpage) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$page = intval($page);
		$perpage = intval($perpage);
		$isown = intval($isown);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		$start = ($page - 1) * $perpage;
		if (!($relations = $relationsDao->getAllRelationsByIsown($userId, $categoryId, $typeId, $isown, $start, $perpage))) {
			return false;
		}
		return $this->_build($relations);
	}
	/**
	 * 公共统计消息归属数
	 * @param $userId
	 * @param $category
	 * @param $isown
	 * @return unknown_type
	 */
	function _countByIsown($userId, $category, $typeId, $isown) {
		$userId = intval($userId);
		$isown = intval($isown);
		$typeId = intval($typeId);
		if (1 > $userId) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		return intval($relationsDao->countRelationsByIsown($userId, $categoryId, $typeId, $isown));
	}
	/**
	 * 按大类获取我发送/接收的信息
	 * @param $userId
	 * @param $category
	 * @param $typeId
	 * @param $isown
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getsSpecialByIsown($userId, $category, $typeId, $isown, $page, $perpage) {
		$userId = intval($userId);
		$typeId = intval($typeId);
		$page = intval($page);
		$perpage = intval($perpage);
		$isown = intval($isown);
		if (1 > $userId || 1 > $page || 1 > $perpage) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		$start = ($page - 1) * $perpage;
		if (!($relations = $relationsDao->getSpecialRelationsByIsown($userId, $categoryId, $typeId, $isown, $start, $perpage))) {
			return false;
		}
		return $this->_build($relations);
	}
	/**
	 * 按大类统计我发送/接收的信息
	 * @param $userId
	 * @param $category
	 * @param $typeId
	 * @param $isown
	 * @return unknown_type
	 */
	function _countSpecialByIsown($userId, $category, $typeId, $isown) {
		$userId = intval($userId);
		$isown = intval($isown);
		$typeId = intval($typeId);
		if (1 > $userId) {
			return false;
		}
		$categoryId = intval($this->getMap($category));
		$relationsDao = $this->getRelationsDao();
		return intval($relationsDao->countSpecialRelationsByIsown($userId, $categoryId, $typeId, $isown));
	}
	/**
	 * 公共批量删除关系体
	 * @param $relationIds
	 * @return unknown_type
	 */
	function _deleteRelations($userId, $relationIds) {
		$userId = intval($userId);
		if (1 > $userId || !$relationIds) {
			return false;
		}
		$relationIds = (is_array($relationIds)) ? $relationIds : array($relationIds);
		$relationsDao = $this->getRelationsDao();
		if (!$relationsDao->deleteRelations($userId, $relationIds)) {
			return false;
		}
		$this->_deleteSearch($userId, $relationIds);
		return true;
	}
	/**
	 * 根椐用户UID与关系ID更新关系体
	 * @param array $fieldData
	 * @param $userId
	 * @param $relationId
	 * @return unknown_type
	 */
	function _update($fieldData, $userId, $relationId) {
		$userId = intval($userId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $relationId) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->updateRelationsByUserId($fieldData, $userId, array($relationId));
	}
	/**
	 * 标记多条信息已读
	 * @param $userId
	 * @param array $relationIds
	 * @return unknown_type
	 */
	function _mark($userId, $relationIds) {
		$userId = intval($userId);
		if (1 > $userId || !$relationIds || !is_array($relationIds)) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		$relationsDao->updateRelationsByUserId(array('status' => $this->_s_have_read), $userId, $relationIds);
		return true;
	}
	/**
	 * 根椐消息主键获取一条消息内容
	 * @param $messageId
	 * @return array
	 */
	function _get($messageId) {
		$messageId = intval($messageId);
		if (1 > $messageId) {
			return false;
		}
		$messagesDao = $this->getMessagesDao();
		return $messagesDao->get($messageId);
	}
	
	/**
	 * 根据关系ID批量更新关系状态
	 * @param array $fieldData
	 * @param int $userId
	 * @param array $relationIds
	 * @return unknown_type
	 */
	function updateRelations($fieldData, $userId, $relationIds) {
		if (!$fieldData || !$relationIds || !is_array($relationIds)) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->updateRelations($fieldData, $userId, $relationIds);
	}
	/**
	 * 根椐用户UID与关系ID更新关系体
	 * @param array $fieldData
	 * @param $userId
	 * @param array $messageIds
	 * @return unknown_type
	 */
	function _updateByMessageIds($fieldData, $userId, $messageIds) {
		$userId = intval($userId);
		if (1 > $userId || !$messageIds) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->updateRelationsByUserIdAndMessageId($fieldData, $userId, $messageIds);
	}
	/**
	 * 批量更新用户消息统计数
	 * @param $userIds
	 * @param $category
	 * @param $number
	 * @return unknown_type
	 */
	function _updateNumsByUserIds($userIds, $category, $number) {
		if (!$userIds || !$category) return false;
		list($bool, $eUserIds, $nUserIds) = $this->_checkUsersByUserIds($userIds);
		if (!$bool) return false;
		$configsDao = $this->getConfigsDao();
		switch ($category) {
			case $this->_sms:
				$configsDao->updateSmsNumsByUserIds($eUserIds, $nUserIds, $number);
				break;
			case $this->_notice:
				$configsDao->updateNoticeNumsByUserIds($eUserIds, $nUserIds, $number);
				break;
			case $this->_request:
				$configsDao->updateRequestNumsByUserIds($eUserIds, $nUserIds, $number);
				break;
			case $this->_groupsms:
				$configsDao->updateGroupsmsNumsByUserIds($eUserIds, $nUserIds, $number);
				break;
			default:
				break;
		}
	}
	/**
	 * 根椐用户名获取用户信息
	 * @param $usernames
	 * @param $isFilter 是否过滤用户
	 * @return unknown_type
	 */
	function _getUserByUserNames($usernames, $isFilter = true) {
		if (!$usernames) return array(false,false);
		
		$userService = $this->_getUserService();
		$users = $userService->getByUserNames($usernames);
		if (!$users) {
			return array(false,false);
		}
		$userIds = $userNames = $groupIds = $tmp = array();
		foreach ($users as $user) {
			if ($user['uid'] > 0) {
				$userIds[] = $user['uid'];
				$userNames[] = $user['username'];
				//$groupIds[$user['uid']] = $user['groupid'];
				$groupIds[$user['uid']] = ($user['groupid'] > 0) ? $user['groupid'] : $user['memberid'];
				$tmp[$user['uid']] = $user['username'];
			}
		}
		//black filter
		return ($isFilter) ? $this->_filterUsers($tmp, $groupIds) : array($userIds,$userNames);
	}
	/**
	 * 更新用户配置
	 * @param $userId
	 * @param $mkey
	 * @param $mValue
	 * @return unknown_type
	 */
	function _setMsConfig($fieldData, $userId) {
		if (1 > $userId || !$fieldData) return false;
		$configsDao = $this->getConfigsDao();
		if (!($config = $configsDao->get($userId))) {
			return $configsDao->insertConfigs($fieldData, array($userId));
		}
		return $configsDao->update($fieldData, $userId);
	}
	/**
	 * 获取一个用户配置
	 * @param $userId
	 * @param $mKey
	 * @return unknown_type
	 */
	function _getMsConfig($userId, $mKey) {
		if (1 > $userId || "" == $mKey) return false;
		$configsDao = $this->getConfigsDao();
		$config = $configsDao->get($userId);
		return (isset($config[$mKey])) ? $config[$mKey] : '';
	}
	/**
	 * 根椐回复IDS更新回复状态
	 * @param $fieldData
	 * @param $ids
	 * @return unknown_type
	 */
	function _updateRepliesByIds($fieldData, $ids) {
		if (!$fieldData || !$ids) return false;
		$repliesDao = $this->getRepliesDao();
		return $repliesDao->updateRepliesByIds($fieldData, $ids);
	}
	function _upMessage($userId, $category, $relationId, $typeId = null) {
		$userId = intval($userId);
		$categoryId = intval($this->getMap($category));
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $categoryId || 1 > $relationId) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		if (!$tmpRelation = $relationsDao->get($relationId)) {
			return false;
		}
		if (!$relation = $relationsDao->getUpRelation($userId, $categoryId, $relationId, $tmpRelation['modified_time'], $typeId)) {
			return false;
		}
		if (!$message = $this->_get($relation['mid'])) {
			return false;
		}
		($relation['status'] == $this->_s_not_read) && $this->_mark($userId, array($relationId));
		return $relation + $message;
	}
	/**
	 * 
	 * 获取上一条消息 的内容
	 * @param int $userId
	 * @param int $category
	 * @param int $relationId
	 * @param int $typeId
	 */
	function _getUpMsInfoByType($userId, $category, $relationId, $isown, $typeId = null) {
		$userId = intval($userId);
		$categoryId = intval($this->getMap($category));
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		$isown = intval($isown);
		if (1 > $userId || 1 > $categoryId || 1 > $relationId) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		if (!$tmpRelation = $relationsDao->get($relationId)) {
			return false;
		}
		if (!$relation = $relationsDao->getUpInfoByType($userId, $categoryId, $relationId, $tmpRelation['modified_time'], $isown, $typeId)) {
			return false;
		}
		if (!$message = $this->_get($relation['mid'])) {
			return false;
		}
		($relation['status'] == $this->_s_not_read) && $this->_mark($userId, array($relationId));
		return $relation + $message;
	}
	/**
	 * 
	 * 获取下一条消息 的内容
	 * @param int $userId
	 * @param int $category
	 * @param int $relationId
	 * @param int $typeId
	 */
	function _getDownMsInfoByType($userId, $category, $relationId, $isown, $typeId = null) {
		$userId = intval($userId);
		$categoryId = intval($this->getMap($category));
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		$isown = intval($isown);
		if (1 > $userId || 1 > $categoryId || 1 > $relationId) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		if (!$tmpRelation = $relationsDao->get($relationId)) {
			return false;
		}
		if (!$relation = $relationsDao->getDownInfoByType($userId, $categoryId, $relationId, $tmpRelation['modified_time'], $isown, $typeId)) {
			return false;
		}
		if (!$message = $this->_get($relation['mid'])) {
			return false;
		}
		($relation['status'] == $this->_s_not_read) && $this->_mark($userId, array($relationId));
		return $relation + $message;
	}
	function _downMessage($userId, $category, $relationId, $typeId = null) {
		$userId = intval($userId);
		$categoryId = intval($this->getMap($category));
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if (1 > $userId || 1 > $categoryId || 1 > $relationId) {
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		if (!$tmpRelation = $relationsDao->get($relationId)) {
			return false;
		}
		if (!$relation = $relationsDao->getDownRelation($userId, $categoryId, $relationId, $tmpRelation['modified_time'], $typeId)) {
			return false;
		}
		if (!$message = $this->_get($relation['mid'])) {
			return false;
		}
		($relation['status'] == $this->_s_not_read) && $this->_mark($userId, array($relationId));
		return $relation + $message;
	}
	/**
	 * 私有检查用户某KEY是否存在
	 * @param $userIds
	 * @param $mKey
	 * @return array(bool,已经存在的用户数组，不存在的用户数组)
	 */
	function _checkUsersByUserIds($userIds) {
		$configsDao = $this->getConfigsDao();
		$configs = $configsDao->gets($userIds);
		$eUserIds = $nUserIds = array();
		if ($configs) {
			foreach ($configs as $c) {
				($c['uid'] > 0) ? $eUserIds[] = $c['uid'] : 0;
			}
			$nUserIds = array_diff($userIds, $eUserIds);
			return array(true,$eUserIds,$nUserIds);
		}
		return array(true,array(),$userIds);
	}
	/**
	 * 私有检查消息体内容函数
	 * @param $messageInfo
	 * @return unknown_type
	 */
	function _checkInfo($messageInfo) {
		$data = array();
		$data['create_uid'] = intval($messageInfo['create_uid']);
		$data['create_username'] = trim($messageInfo['create_username']);
		if (0 == $data['create_uid'] || "" == $data['create_username']) {
			return false;
		}
		$data['title'] = trim($messageInfo['title']);
		$data['content'] = trim($messageInfo['content']);
		if ("" == $data['title'] || "" == $data['content']) {
			return false;
		}
		isset($messageInfo['extra']) && $data['extra'] = $messageInfo['extra'];
		isset($messageInfo['expand']) && $data['expand'] = $messageInfo['expand'];
		$data['created_time'] = $data['modified_time'] = time();
		return $data;
	}
	/**
	 * 私有组装消息体与关系体信息函数
	 * @param $relations
	 * @return unknown_type
	 */
	function _build($relations, $category = false) {
		if (!$relations) return false;
		$messageIds = $tmpRelations = array();
		foreach ($relations as $r) {
			($r['mid']) ? $messageIds[] = $r['mid'] : 0;
			$tmpRelations[$r['rid']] = $r;
		}
		if (!$messageIds) return false;
		$messagesDao = $this->getMessagesDao();
		if (!($messages = $messagesDao->getMessagesByMessageIds($messageIds))) {
			return false;
		}
		$tmpMessages = $result = array();
		foreach ($messages as $m) {
			$tmpMessages[$m['mid']] = $m;
		}
		foreach ($tmpRelations as $rid => $r) {
			(isset($tmpMessages[$r['mid']])) ? $result[$rid] = $r + $tmpMessages[$r['mid']] : 0;
		}
		return ($category == $this->_notice) ? $result : $this->_buildUsersLists($result);
	}
	/**
	 * 私用组装前台展示信息函数
	 * @param $arrays 消息体+关系体信息
	 * 注意:$tpc_author参数用于组装表情用户名前缀
	 * @return array
	 */
	function _buildUsersLists($arrays) {
		global $tpc_author;
		if (!$arrays) return false;
		$userIds = array();
		foreach ($arrays as $v) {
			(0 < $v['create_uid']) ? $userIds[] = $v['create_uid'] : 0;
		}
		$tmp = $this->_retrieveUsers($userIds);
		require_once (R_P . 'require/bbscode.php');
		$groupInfos = $tmpArrays = array();
		foreach ($arrays as $rid => $a) {
			$created_timefromat = getLastDate($a['created_time']);
			$modified_timefromat = getLastDate($a['modified_time']);
			$a['title'] = $this->_reverseString($a['title']);
			$tpc_author = $a['create_username'];
			$a['created_time_format'] = $created_timefromat[0];
			$a['modified_time_format'] = $modified_timefromat[0];
			$a['created_time_detail'] = get_date($a['created_time'], 'Y-m-d H:i');
			$a['modified_time_detail'] = get_date($a['modified_time'], 'Y-m-d H:i');
			$a['content'] = $this->_reverseString($this->_stringReplace(convert($a['content'], $this->_windpost)));
			$a['extra'] = ($a['extra']) ? unserialize($a['extra']) : '';
			$tmpArrays[$rid] = isset($tmp[$a['create_uid']]) ? $tmp[$a['create_uid']] + $a : $a;
			($a['typeid'] == $this->getMap($this->_groupsms_colony)) ? $groupInfos[$a['mid']] = $a['extra'] : 0;
		}
		// build group
		if ($groupInfos && ($groups = $this->_buildColonyList($groupInfos))) {
			$t = array();
			foreach ($tmpArrays as $rid => $v) {
				$t[$rid] = (isset($groups[$v['mid']])) ? $groups[$v['mid']] + $v : $v;
			}
			return $t;
		}
		return $tmpArrays;
	}
	function _retrieveUsers($userIds) {
		if (!$userIds) return array();
		array_unique($userIds);
		$userService = $this->_getUserService();
		$members = $userService->getByUserIds($userIds);
		$tmp = array();
		require_once (R_P . 'require/showimg.php');
		foreach ($members as $member) {
			list($member['face']) = showfacedesign($member['icon'], 1, 'm');
			$tmp[$member['uid']] = $member;
		}
		return $tmp;
	}
	function _reverseString($content) {
		return str_replace(array('"' . $this->_userName . '"','[' . $this->_userName . ']',
								'&quot;' . $this->_userName . '&quot;'), '您', $content);
	}
	function _stringReplace($value) {
		return nl2br($value);
	}
	/**
	 * 私有组装群组信息
	 * @param $groupInfos
	 * @return unknown_type
	 */
	function _buildColonyList($groupInfos) {
		if (!$groupInfos) return false;
		$groupIds = $ids = array();
		foreach ($groupInfos as $mid => $group) {
			$groupIds[$mid] = $group['groupid'];
			$ids[] = $group['groupid'];
		}
		$colonysDao = $this->getColonysDao();
		if (!$colonys = $colonysDao->getsIds($ids)) {
			return false;
		}
		$tmpColonys = array();
		foreach ($colonys as $c) {
			$c['cnimg'] = ($c['cnimg']) ? $this->_attachmentPath . '/cn_img/' . $c['cnimg'] : 'images/g/groupnopic.gif';
			$tmpColonys[$c['colonyid']] = $c;
		}
		$result = array();
		foreach ($groupIds as $mid => $v) {
			$result[$mid] = $tmpColonys[$v];
		}
		return $result;
	}
	
	function _clearMessages($userId, $categoryIds) {
		if (1 > $userId || !$categoryIds) return false;
		$relationsDao = $this->getRelationsDao();
		if (!$relationsDao->deleteRelationsByUserIdAndCategoryId($userId, $categoryIds)) {
			return false;
		}
		$categoryId = $this->getMap($this->_sms);
		if (in_array($categoryId, $categoryIds)) {
			$searchsDao = $this->getSearchsDao();
			$searchsDao->deleteAll($userId);
		}
		return false;
	}
	/**
	 * 根根用户名或用户ID更新用户消息数
	 * @param array $userIds
	 * @param array $userNames
	 * @param int $number
	 * @return array($userIds,$userNames)
	 */
	function _updateStatisticsByUserNames($userIds, $userNames = null, $category, $number) {
		if (!$userIds && $userNames) {
			list($userIds) = $this->_getUserByUserNames($userNames);
		}
		if (!$userIds) return false;
		$category = ($category) ? $category : $this->_sms;
		$category = ((count($userIds) >= 2) && ($category == $this->_sms)) ? $this->_groupsms : $category;
		$this->_updateNumsByUserIds($userIds, $category, $number);
		return true;
	}
	/**
	 * 根椐消息类型更新用户消息数
	 * @param $categoryId
	 * @param $message
	 */
	function _updateStatisticsByCategoryId($categoryId, $message, $userId = null) {
		switch ($categoryId) {
			case $this->getMap($this->_groupsms):
				if ($message['extra']) {
					$userNames = unserialize($message['extra']);
					$userNames = $userNames + array($message['create_username']);
					$receiveUserIds = $this->_getParticipantByMessageId($message['mid']);
					if ($receiveUserIds) {
						list($userIds) = $this->_getUserByUserNames($userNames);
						$userIds = array_intersect($userIds, $receiveUserIds);
						$this->_updateStatisticsByUserNames($userIds, null, $this->_groupsms, 1);
						$this->_updateUserMessageNumbers($userIds);
					}
				}
				break;
			default:
				$userIds = $this->_getParticipantByMessageId($message['mid']);
				if ($userIds) {
					$userIds = array_diff($userIds, array($userId));
					$this->_updateStatisticsByUserNames($userIds, null, null, 1);
					$this->_updateUserMessageNumbers($userIds);
				}
				break;
		}
		return true;
	}
	/**
	 * 更新用户消息数
	 * @param $userIds
	 */
	function _updateUserMessageNumbers($userIds,$category = null) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$messageServer = L::loadClass('message', 'message');
		!S::isArray($userIds) && $userIds = array($userIds);
		foreach ($userIds as $userid) {
			list(,$messageNumber,$noticeNumber,$requestNumber,$groupsmsNumber) = $messageServer->countAllByUserId($userid);
			switch ($category) {
				case $this->_notice:
					$userService->update($userid, array(), array('newnotice' => $noticeNumber));
					break;
				case $this->_request:
					$userService->update($userid, array(), array('newrequest' => $requestNumber));
					break;
				default:
					$userService->update($userid, array('newpm' => $messageNumber + $groupsmsNumber));
			}
		}
		return true;
	}
	/**
	 * 通过消息ID获取参与者
	 * @param $messageId
	 * @return unknown_type
	 */
	function _getParticipantByMessageId($messageId) {
		$relationsDao = $this->getRelationsDao();
		if (!($result = $relationsDao->getRelationsByMessageId($messageId))) {
			return array();
		}
		$userIds = array();
		foreach ($result as $r) {
			$userIds[] = $r['uid'];
		}
		return $userIds;
	}
	/**
	 * 增加搜索数据
	 * @param $fieldData
	 * @return unknown_type
	 */
	function _addSearch($userId, $toUserId, $relationId, $messageId, $typeId) {
		$fieldData = array('rid' => $relationId,'uid' => $toUserId,'mid' => $messageId,'typeid' => $typeId,
						'create_uid' => $userId,'created_time' => $this->_timestamp);
		$searchsDao = $this->getSearchsDao();
		return $searchsDao->insert($fieldData);
	}
	/**
	 * 批量删除搜索数据
	 * @param $userId
	 * @param $relationIds
	 * @return unknown_type
	 */
	function _deleteSearch($userId, $relationIds) {
		$userId = intval($userId);
		if (1 > $userId || !$relationIds) {
			return false;
		}
		$searchsDao = $this->getSearchsDao();
		return $searchsDao->deletesByUserId($userId, $relationIds);
	}
	/**
	 * 基础配置类型地图
	 * @return unknown_type
	 */
	function maps() {
		return array($this->_sms => 1,
					$this->_sms_message => 100,$this->_sms_rate => 101,$this->_sms_comment => 102,
					$this->_sms_guestbook => 103,$this->_sms_share => 104,$this->_sms_reply => 105,
					$this->_notice => 2,
					$this->_notice_system => 200,$this->_notice_postcate => 201,$this->_notice_active => 202,
					$this->_notice_apps => 203,$this->_notice_comment => 204,$this->_notice_guestbook => 205,
					$this->_request => 3,
					$this->_request_friend => 300,$this->_request_group => 301,$this->_request_active => 302,$this->_request_apps => 303,
					$this->_groupsms => 4,
					$this->_groupsms_colony => 400,$this->_groupsms_normal => 401,
					$this->_groupsms_shield => 402,$this->_chat => 5,$this->_history => 0);
	}
	/**
	 * 大类与子类关系图
	 * @return unknown_type
	 */
	function maps2() {
		return array(
					$this->_sms => array($this->_sms_message,$this->_sms_rate,$this->_sms_comment,$this->_sms_guestbook,
										$this->_sms_share,$this->_sms_reply),
					$this->_notice => array($this->_notice_system,$this->_notice_postcate,$this->_notice_active,
											$this->_notice_apps,$this->_notice_comment,$this->_notice_guestbook),
					$this->_request => array($this->_request_friend,$this->_request_group,$this->_request_active,
											$this->_request_apps),
					$this->_groupsms => array($this->_groupsms_colony,$this->_groupsms_normal,$this->_groupsms_shield));
	}
	/**
	 * 根椐类型名获取类型ID
	 * @param $types
	 * @return unknown_type
	 */
	function _getSpecialMap($types) {
		if (!$types) return array();
		$maps = $this->maps();
		$typeIds = array();
		foreach ($types as $type) {
			(isset($maps[$type])) ? $typeIds[] = $maps[$type] : 0;
		}
		return $typeIds;
	}
	/**
	 * 统计配置
	 * @param $category
	 * @return unknown_type
	 */
	function getStatisticsByCategory($category) {
		$maps = array($this->_sms => $this->_c_sms_num,$this->_notice => $this->_c_notice_num,
					$this->_request => $this->_c_request_num,$this->_groupsms => $this->_c_groupsms_num);
		return (isset($maps[$category])) ? $maps[$category] : '';
	}
	/**
	 * 根椐类型名称获取类型唯一ID
	 * @param $k
	 * @return unknown_type
	 */
	function getMap($k) {
		$maps = $this->maps();
		return (isset($maps[$k])) ? $maps[$k] : 0;
	}
	/**
	 * 根椐子类型获取大类型名称
	 * @param $typeId
	 * @param $isType 是否获取子类型
	 * @return unknown_type
	 */
	function getMapByTypeId($typeId, $isType = false) {
		$maps = array_flip($this->maps());
		if (!isset($maps[$typeId])) return false;
		$type = $maps[$typeId];
		if ($isType) return $type;
		$maps2 = $this->maps2();
		foreach ($maps2 as $category => $map) {
			if (is_array($map) && in_array($type, $map)) return $category;
		}
		return false;
	}
	/**
	 * 过滤用户 黑名单
	 * @param $users
	 * @return array($userIds,$userNames)
	 */
	function _filterUsers($users, $groupInfos) {
		if (!$users) return array(false,false);
		$configs = $this->_getMsConfigsByUserIds(array_keys($users));
		if ($configs) {
			foreach ($configs as $uid => $config) {
				if (isset($config[$this->_c_blackgroup]) && $this->_groupId && is_array($config[$this->_c_blackgroup]) && in_array($this->_groupId, $config[$this->_c_blackgroup])) {
					unset($users[$uid]);
					continue;
				}
				if (isset($config[$this->_c_blacklist]) && $this->_userName && is_array($config[$this->_c_blacklist]) && in_array($this->_userName, $config[$this->_c_blacklist])) {
					unset($users[$uid]);
				}
			}
		}
		if (!$users) return array(false,false);
		
		//用户组最大消息数目
		$permissions = $this->_getPermissions(); // array('gid'=>'total')
		//统计用户的短消息数
		$userInfos = $this->_countUserNumbers(array_keys($users)); // array('uid'=>'total')
		foreach ($groupInfos as $uid => $groupId) {
			if (isset($permissions[$groupId]) && $permissions[$groupId] > 0 && isset($userInfos[$uid]) && $userInfos[$uid] >= $permissions[$groupId]) {
				unset($users[$uid]);
			}
		}
		return ($users) ? array(array_keys($users),array_values($users)) : array(false,false);
	}
	/**
	 * 统计用户最大消息数
	 * @param $userIds
	 * @return unknown_type
	 */
	function _countUserNumbers($userIds) {
		$relationsDao = $this->getRelationsDao();
		$typeIds = array($this->getMap($this->_sms_message),$this->getMap($this->_groupsms_normal));
		$users = $relationsDao->countAllByUserIds($userIds, $typeIds);
		if (!$users) return false;
		$tmp = array();
		foreach ($users as $user) {
			$tmp[$user['uid']] = $user['total'];
		}
		return $tmp;
	}
	/**
	 * 获取所有会员组最大消息数目
	 * @return unknown_type
	 */
	function _getPermissions() {
		$permissonsDao = $this->getPermissionDao();
		$permissons = $permissonsDao->getsByRkey('maxmsg');
		if (!$permissons) return false;
		$tmp = array();
		foreach ($permissons as $p) {
			$tmp[$p['gid']] = $p['rvalue'];
		}
		return $tmp;
	}
	/**
	 * 根椐用户ID获取用户配置信息
	 * @param $userIds
	 * @return unknown_type
	 */
	function _getMsConfigsByUserIds($userIds) {
		if (!$userIds) return false;
		$configsDao = $this->getConfigsDao();
		$configs = $configsDao->gets($userIds);
		if (!$configs) return false;
		$tmp = array();
		foreach ($configs as $c) {
			$shield = array();
			$shield[$this->_c_blacklist] = (isset($c[$this->_c_blacklist])) ? unserialize($c[$this->_c_blacklist]) : '';
			$shield[$this->_c_shieldinfo] = (isset($c[$this->_c_shieldinfo])) ? unserialize($c[$this->_c_shieldinfo]) : '';
			$shield[$this->_c_blackcolony] = (isset($c[$this->_c_blackcolony])) ? unserialize($c[$this->_c_blackcolony]) : '';
			$shield[$this->_c_blackgroup] = (isset($c[$this->_c_blackgroup])) ? unserialize($c[$this->_c_blackgroup]) : '';
			$tmp[$c['uid']] = $shield;
		}
		return $tmp;
	}
	/**
	 * 批量处理消息附件信息
	 * @param array $messageIds
	 * @return unknown_type
	 */
	function _deleteAttachsByMessageIds($messageIds) {
		if (!$messageIds) return false;
		$msAttachsDao = $this->getMsAttachsDao();
		if (!($msAttachs = $msAttachsDao->getAttachsByMessageIds($messageIds))) {
			return false;
		}
		$attachIds = array();
		foreach ($msAttachs as $attach) {
			$attachIds[] = $attach['aid'];
		}
		$msAttachsDao->deleteAttachsByMessageIds($messageIds);
		$attachsDao = $this->getAttachsDao();
		if (!($attachs = $attachsDao->getsByAids($attachIds))) {
			return false;
		}
		$files = array();
		foreach ($attachs as $attach) {
			$file = $this->_attachmentPath . '/' . $attach['attachurl'];
			if (is_file($file)) {
				P_unlink($file);
			}
		}
		$attachsDao->deleteByAids($attachIds);
		return true;
	}
	/**
	 * 消息中心配置变量keys
	 * @return unknown_type
	 */
	var $_c_blackcolony = 'blackcolony'; //黑群组单
	var $_c_blacklist = 'blacklist'; //黑用户单
	var $_c_categories = 'categories'; //分类
	var $_c_statistics = 'statistics'; //统计
	var $_c_shieldinfo = 'shieldinfo'; //屏蔽类型
	var $_c_blackgroup = 'blackgroup'; //黑用户组
	function _msConfigs() {
		return array($this->_c_blacklist,$this->_c_categories,$this->_c_statistics,$this->_c_blackcolony,
					$this->_c_shieldinfo,$this->_c_blackgroup);
	}
	/**
	 * 获取消息中心变量keys
	 * @param $mkey
	 * @return unknown_type
	 */
	function _getMsConfigByKey($mkey) {
		$msConfigs = $this->_msConfigs();
		return (isset($msConfigs[$mkey])) ? $msConfigs[$mkey] : '';
	}
	
	/**
	 * 私用系统虚拟用户
	 * @return unknown_type
	 */
	function virtualUser() {
		return array('uid' => -1,'username' => 'system');
	}
	/**
	 * 消息表DAO
	 * @return unknown_type
	 */
	function getMessagesDao() {
		static $sMessagesDao;
		if (!$sMessagesDao) {
			$sMessagesDao = L::loadDB('ms_messages', 'message');
		}
		return $sMessagesDao;
	}
	/**
	 * 关系表DAO
	 * @return unknown_type
	 */
	function getRelationsDao() {
		static $sRelationsDao;
		if (!$sRelationsDao) {
			$sRelationsDao = L::loadDB('ms_relations', 'message');
		}
		return $sRelationsDao;
	}
	/**
	 * 回复表DAO
	 * @return unknown_type
	 */
	function getRepliesDao() {
		static $sRepliesDao;
		if (!$sRepliesDao) {
			$sRepliesDao = L::loadDB('ms_replies', 'message');
		}
		return $sRepliesDao;
	}
	
	/**
	 * 消息配置DAO
	 * @return unknown_type
	 */
	function getConfigsDao() {
		static $sConfigsDao;
		if (!$sConfigsDao) {
			$sConfigsDao = L::loadDB('ms_configs', 'message');
		}
		return $sConfigsDao;
	}
	/**
	 * 消息附件关系DAO
	 * @return unknown_type
	 */
	function getMsAttachsDao() {
		static $sMsAttachsDao;
		if (!$sMsAttachsDao) {
			$sMsAttachsDao = L::loadDB('ms_attachs', 'message');
		}
		return $sMsAttachsDao;
	}
	/**
	 * 附件关系DAO
	 * @return unknown_type
	 */
	function getAttachsDao() {
		static $sAttachsDao;
		if (!$sAttachsDao) {
			$sAttachsDao = L::loadDB('attachs', 'forum');
		}
		return $sAttachsDao;
	}
	/**
	 * 群组DAO
	 * @return unknown_type
	 */
	function getColonysDao() {
		static $sColonysDao;
		if (!$sColonysDao) {
			$sColonysDao = L::loadDB('colonys', 'colony');
		}
		return $sColonysDao;
	}
	/**
	 * 群组成员DAO
	 * @return unknown_type
	 */
	function getCmembersDao() {
		static $sCmembersDao;
		if (!$sCmembersDao) {
			$sCmembersDao = L::loadDB('cmembers', 'colony');
		}
		return $sCmembersDao;
	}
	/**
	 * 消息中心搜索DAO
	 * @return unknown_type
	 */
	function getSearchsDao() {
		static $sSearchDao;
		if (!$sSearchDao) {
			$sSearchDao = L::loadDB('ms_searchs', 'message');
		}
		return $sSearchDao;
	}
	/**
	 * 消息中心任务DAO
	 * @return unknown_type
	 */
	function getTaskDao() {
		static $sTaskDao;
		if (!$sTaskDao) {
			$sTaskDao = L::loadDB('ms_tasks', 'message');
		}
		return $sTaskDao;
	}
	/**
	 * 用户组权限DAO
	 * @return unknown_type
	 */
	function getPermissionDao() {
		static $sPermissionDao;
		if (!$sPermissionDao) {
			$sPermissionDao = L::loadDB('permission', 'user');
		}
		return $sPermissionDao;
	}
	
	/**
	 * @return PW_UserService
	 */
	function _getUserService() {
		return L::loadClass('UserService', 'user');
	}
}
?>