<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 请求服务层
 * @2010-4-6 liuhui
 */
class MS_Request extends MS_Base {
	
	function sendRequest($userId,$userNames,$messageInfo,$typeId){
		$userId = intval($userId);
		$typeId = intval($typeId);
		if( 1 > $typeId || "" == $userNames || !$messageInfo ){
			return false;
		}
		list($userNames,$categoryId,$typeId) = $this->_checkReceiver($userNames,$this->_request,$typeId);
		if(!$userNames) return false;
		list($userIds,$userNames) = $this->_getUserByUserNames($userNames,false);
		if(!$userIds){
			return false;
		}
		if(!($messageId = $this->_doSend($userId,$userIds,$categoryId,$typeId,$messageInfo,false))){
			return false;
		}
		$this->_updateStatisticsByUserNames($userIds,false,$this->_request,1);
		$this->_updateUserMessageNumbers($userIds,$this->_request);
		return $messageId;
	}
	function getAllRequests($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getAll($userId,$this->_request,$page,$perpage);
	}
	function getRequestsNotRead($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getsByStatus($userId,$this->_request,$this->_s_not_read,$page,$perpage);
	}
	function getRequests($userId,$typeId,$page,$perpage){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage || 1 > $typeId ){
			return false;
		}
		return $this->_getsByTypeId($userId,$this->_request,$typeId,$page,$perpage);
	}
	function getUpRequest($userId,$relationId,$typeId){
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if( 1 > $userId || 1 > $typeId || 1 > $relationId ){
			return false;
		}
		return $this->_upMessage($userId,$this->_request,$relationId,$typeId);
	}
	function getDownRequest($userId,$relationId,$typeId){
		$userId = intval($userId);
		$typeId = intval($typeId);
		$relationId = intval($relationId);
		if( 1 > $userId || 1 > $typeId || 1 > $relationId ){
			return false;
		}
		return $this->_downMessage($userId,$this->_request,$relationId,$typeId);
	}
	function countAllRequest($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countAll($userId,$this->_request);
	}
	function countRequestsNotRead($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countByStatus($userId,$this->_request,$this->_s_not_read,$page,$perpage);
	}
	function countRequest($userId,$typeId){
		$userId   = intval($userId);
		$typeId   = intval($typeId);
		if( 1 > $userId || 1 > $typeId ){
			return false;
		}
		return $this->_countByTypeId($userId,$this->_request,$typeId);
	}
	function deleteRequest($userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_deleteRelations($userId,array($relationId));
	}
	function deleteRequests($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_deleteRelations($userId,$relationIds);
	}
	function updateRequest($fieldData,$userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || !$fieldData || 1 > $relationId ){
			return false;
		}
		return $this->_update($fieldData,$userId,$relationId);
	}
	function markRequest($userId,$relationId){
		$userId   = intval($userId);
		$relationId   = intval($relationId);
		if( 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_mark($userId,array($relationId));
	}
	function markRequests($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_mark($userId,$relationIds);
	}
	function getRequest($messageId){
		$messageId  = intval($messageId);
		if( 1 > $messageId  ){
			return false;
		}
		return $this->_get($messageId);
	}
	function readRequests($userId,$messageId){
		$messageId  = intval($messageId);
		$userId  = intval($userId);
		if( 1 > $messageId || 1 > $userId ){
			return false;
		}
		return $this->_updateByMessageIds(array('status'=>$this->_s_have_read),$userId,array($messageId));
	}
	function overlookRequest($userId,$relationIds){
		$userId  = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->updateRelations(array('status'=>$this->_s_overlook),$userId,$relationIds);
	}
	function agreeRequests($userId,$relationIds){
		$userId  = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->updateRelations(array('status'=>$this->_s_agree),$userId,$relationIds);
	}
}