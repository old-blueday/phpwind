<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 附件服务层
 * @2010-4-10 liuhui
 */
class MS_Attach extends MS_Base {
	function addAttachs($fieldDatas){
		if(!$fieldDatas || !is_array($fieldDatas)) return false;
		$tmp = $messageIds = array();
		foreach($fieldDatas as $v){
			$data = array();
			$data['uid'] = intval($v['uid']);
			$data['aid'] = intval($v['aid']);
			$data['mid'] = intval($v['mid']);
			$data['rid'] = intval($v['rid']);
			$data['status'] = intval($v['status']);
			if( 1 > $data['uid'] || 1 > $data['aid'] ||1 > $data['mid']){
				continue;
			}
			$data['created_time'] = $this->_timestamp;
			$messageIds[] = $v['mid'];
			$tmp[] = $data;
		}
		if(!$tmp) return false;
		$msAttachsDao =  $this->getMsAttachsDao();
		$result =  $msAttachsDao->addAttachs($tmp);
		if($result && $messageIds ){
			$messagesDao = $this->getMessagesDao();
			$messagesDao->updateMessagesByMessageIds(array('attach'=>1),$messageIds);
		}
		return $result;
	}
	function getAttachs($userId,$messageId){
		$userId = intval($userId);
		$messageId = intval($messageId);
		if( 1 > $userId || 1 > $messageId){
			return false;
		}
		$msAttachsDao =  $this->getMsAttachsDao();
		if(!($msAttachs =  $msAttachsDao->getAttachsByMessageId($userId,$messageId))){
			return false;
		}
		$aids = $tmpAttachs = array();
		foreach($msAttachs as $r){
			($r['aid']) ? $aids[] = $r['aid'] : 0;
			$tmpAttachs[$r['aid']] = $r;
		}
		if(!$aids) return false;
		
		$attachsDao = $this->getAttachsDao();
		if(!($attachs = $attachsDao->getsByAids($aids))){
			return false;
		}
		$result = array();
		foreach($attachs as $a){
			$result[$a['aid']] = $a+$tmpAttachs[$a['aid']];
		}
		return $result;
	}
	function removeAttach($userId,$id){
		$userId = intval($userId);
		$id = intval($id);
		if( 1 > $userId || 1 > $id){
			return false;
		}
		$msAttachsDao =  $this->getMsAttachsDao();
		if(!($msAttach = $msAttachsDao->get($id))){
			return false;
		}
		$attachsDao = $this->getAttachsDao();
		$attach = $attachsDao->get($msAttach['aid']);
		$file = $this->_attachmentPath.'/'.$attach['attachurl'];
		if(is_file($file)){
			P_unlink($file);
		}
		$attachsDao->delete(array($attach['aid']));
		$msAttachsDao->delete($id);
		return true;
	}
	function getAllAttachs($page,$perpage){
		$start   = ($page -1) * $perpage;
		$msAttachsDao = $this->getMsAttachsDao();
		if(!($msAttachs =  $msAttachsDao->getAllAttachs($start,$perpage))){
			return false;
		}
		$attachIds = $tmp = array();
		foreach($msAttachs as $attach){
			$attachIds[] = $attach['aid'];
			$tmp[$attach['aid']] = $attach;
		}
		$attachsDao = $this->getAttachsDao();
		$attachs = $attachsDao->getsByAids($attachIds);
		$result = array();
		foreach($attachs as $a){
			$result[$a['aid']] = $tmp[$a['aid']]+$a;
		}
		return $result;
	}
	function countAllAttachs(){
		$msAttachsDao = $this->getMsAttachsDao();
		return $msAttachsDao->countAllAttachs();
	}
}