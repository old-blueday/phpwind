<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 历史消息服务层
 * @2010-4-6 liuhui
 */
class MS_History extends MS_Base {
	function getHistoryMessages($userId,$page,$perpage){
		$userId   = intval($userId);
		$page     = intval($page);
		$perpage  = intval($perpage);
		if( 1 > $userId || 1 > $page || 1 > $perpage ){
			return false;
		}
		return $this->_getAll($userId,$this->_history,$page,$perpage);
	}
	function countHistoryMessage($userId){
		$userId   = intval($userId);
		if( 1 > $userId ){
			return false;
		}
		return $this->_countAll($userId,$this->_history);
	}
	function deleteHistoryMessages($userId,$relationIds){
		$userId   = intval($userId);
		if( 1 > $userId || !$relationIds ){
			return false;
		}
		return $this->_deleteRelations($userId,$relationIds);
	}
	function getHistoryMessage($messageId){
		$messageId  = intval($messageId);
		if( 1 > $messageId  ){
			return false;
		}
		if(!($message =  $this->_get($messageId))){
			return false;
		}
		return $message;
	}
	function getHistoryReplies($userId,$messageId,$relationId){
		$messageId   = intval($messageId);
		$userId      = intval($userId);
		$relationId  = intval($relationId);
		if( 1 > $messageId || 1 > $userId || 1 > $relationId ){
			return false;
		}
		return $this->_getGroupReplies($userId,$messageId,$relationId);
	}
	function setHistorys($timeSegment){
		$timeSegment = intval($timeSegment);
		if( 1 > $timeSegment ){
			return false;
		}
		$relationsDao = $this->getRelationsDao();
		return $relationsDao->updateRelationsBySegmentTime(array('categoryid'=>$this->getMap($this->_history)),$timeSegment);
	}
}