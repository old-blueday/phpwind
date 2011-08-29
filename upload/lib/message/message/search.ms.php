<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 搜索消息服务层
 * @2010-4-6 liuhui
 */
class MS_Search extends MS_Base {
	function searchMessages($userId,$userName,$type,$page,$perpage ){
		$userId  = intval($userId);
		$page    = intval($page);
		$perpage = intval($perpage);
		if( 1 > $userId || "" == $userName || 1 > $page || 1 > $perpage ){
			return false;
		}

		$userService = $this->_getUserService();
		$user = $userService->getByUserName($userName);
		if (!$user) {
			return false;
		}
		if(!$type){
			return $this->_doSearchMessages($userId,$user['uid'],$page,$perpage);
		}
		$typeId = intval($this->getMap($type));
		if( 1 > $typeId){
			return false;
		}
		return $this->_doSearchMessagesWithTypeId($userId,$user['uid'],$typeId,$page,$perpage);
	}
	function _doSearchMessagesWithTypeId($userId,$createUserId,$typeId,$page,$perpage){
		$searchsDao = $this->getSearchsDao();
		if(!($total = $searchsDao->countByTypeId($userId,$typeId,$createUserId))){
			return false;
		}
		$totalPages = ceil($total/$perpage);
		$page = ($page < 0 ) ? 1 : (($page>$totalPages) ? $totalPages : $page);
		$start = ($page-1) * $perpage;
		$result = $searchsDao->getsByTypeId($userId,$typeId,$createUserId,$start,$perpage);
		if(!$result) return false;
		$relationIds = array();
		foreach($result as $r){
			$relationIds[] = $r['rid'];
		}
		return array($total,$this->_getMessagesByRelationIds($relationIds));
	}
	function _doSearchMessages($userId,$createUserId,$page,$perpage){
		$searchsDao = $this->getSearchsDao();
		if(!($total = $searchsDao->countByUserIdAndCreateUserId($userId,$createUserId))){
			return false;
		}
		$totalPages = ceil($total/$perpage);
		$page = ($page < 0 ) ? 1 : (($page>$totalPages) ? $totalPages : $page);
		$start = ($page-1) * $perpage;
		$result = $searchsDao->getsByUserIdAndCreateUserId($userId,$createUserId,$start,$perpage);
		if(!$result) return false;
		$relationIds = array();
		foreach($result as $r){
			$relationIds[] = $r['rid'];
		}
		$total = ($total) ? $total : 0;
		return array($total,$this->_getMessagesByRelationIds($relationIds));
	}
	function _getMessagesByRelationIds($relationIds){
		if(!$relationIds) return false;
		$relationsDao = $this->getRelationsDao();
		if(!($result = $relationsDao->getRelationsByRelationIds($relationIds))){
			return false;
		}
		return $this->_build($result);
	}	
	function manageMessage($keyWords,$startTime,$endTime,$sender,$isDelete,$page,$perpage){
		$sql = '';
		if($keyWords){
			$sql .= " AND (title LIKE ".S::sqlEscape("%".$keyWords."%")." OR content LIKE ".S::sqlEscape("%".$keyWords."%") . ')';
		}
		if($startTime ){
			$sql .= " AND created_time > ".S::sqlEscape($startTime);
		}
		if($endTime ){
			$sql .= " AND created_time < ".S::sqlEscape($endTime);
		}
		if($sender){
			$sql .= " AND create_username = ".S::sqlEscape($sender);
		}
		$sql = ($sql) ? " WHERE 1 ".$sql : '';
		$messagesDao = $this->getMessagesDao();
		if(!$total = $messagesDao->countManageMessages($sql)){
			return array(0,false);
		}
		if($isDelete){
			$number = ($perpage) ? $perpage : $total;
			return $this->_manageMessages($sql,$number);
		}
		if( $page && $perpage){
			$page    = intval($page) ? intval($page) : 1;
			$perpage = intval($perpage);
			$totalPages = ceil($total/$perpage);
			$page = ($page < 0 ) ? 1 : (($page>$totalPages) ? $totalPages : $page);
			$start   = ($page -1) * $perpage;
			$sql .= " ORDER BY modified_time DESC LIMIT ".$start.",".$perpage;
		}
		$result = $messagesDao->getManageMessages($sql);
		return array($total,$result);
	}
	/**
	 * 私有信息体关联关系体的数据清理
	 * @desc 根椐信息体的ID删除关联的关系体和回复体
	 * @param $condition 表示信息体的查询SQL
	 * @param $number    表示删除的个数
	 * @return unknown_type
	 */
	function _manageMessages($condition,$number){
		$messageIds = array();
		$sqlFor = $condition." LIMIT 0,".$number;
		$messagesDao = $this->getMessagesDao();
		$messages = $messagesDao->getManageMessages($sqlFor);
		if(!$messages) return false;
		foreach($messages as $m){
			$messageIds[] = $m['mid'];
		}
		//clear relations
		$relationsDao = $this->getRelationsDao();
		$relationsDao->deleteRelationsByMessageIds($messageIds);
		//clear replies
		$repliesDao = $this->getRepliesDao();
		$repliesDao->deleteRepliesByMessageIds($messageIds);
		//clear attach
		$this->deleteAttachsByMessageIds($messageIds);
		//clear search
		$searchsDao = $this->getSearchsDao();
		$searchsDao->deleteByMessageIds($messageIds);
		//clear messages
		return $messagesDao->deleteMessagesByMessageIds($messageIds);
	}
	function manageMessageWithMessageIds($messageIds){
		//clear relations
		$relationsDao = $this->getRelationsDao();
		$relationsDao->deleteRelationsByMessageIds($messageIds);
		//clear replies
		$repliesDao = $this->getRepliesDao();
		$repliesDao->deleteRepliesByMessageIds($messageIds);
		//clear messages
		$messagesDao = $this->getMessagesDao();
		//clear attach
		$this->deleteAttachsByMessageIds($messageIds);
		//clear search
		$searchsDao = $this->getSearchsDao();
		$searchsDao->deleteByMessageIds($messageIds);
		$messagesDao->deleteMessagesByMessageIds($messageIds);
		return true;
	}
	function manageMessageWithCategory($category,$unRead,$isDelete,$page,$perpage){
		$categoryId = $this->getMap($category);
		if( 0 > $categoryId) return false;
		$relationsDao = $this->getRelationsDao();
		$sql = " WHERE categoryid = ".S::sqlEscape($categoryId);
		if($unRead){
			$sql .= " AND status in(0,4,5) ";
		}
		if(!$total = $relationsDao->countManageRelations($sql)){
			return array(0,false);
		}
		if($isDelete){
			return $relationsDao->deleteManageRelations($sql);
		}
		$totalPages = ceil($total/$perpage);
		$page    = intval($page) ? intval($page) : 1;
		$page = ($page < 0 ) ? 1 : (($page>$totalPages) ? $totalPages : $page);
		$start = ( $page -1 ) * $perpage;
		$sql .= "  ORDER BY modified_time DESC LIMIT ".$start.",".$perpage;
		$result = $relationsDao->getManageRelations($sql);
		return array($total,$this->_build($result));
	}
	/**
	 * 批量删除消息附件信息
	 * @param $messageIds
	 * @return unknown_type
	 */
	function deleteAttachsByMessageIds($messageIds){
		if(!$messageIds) return false;
		return $this->_deleteAttachsByMessageIds($messageIds);
	}
}