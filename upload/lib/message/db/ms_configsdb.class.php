<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_ConfigsDB extends BaseDB {
	var $_tableName = 'pw_ms_configs';
	var $_primaryKey = 'uid';
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$id){
		return $this->_update($fieldData,$id);
	}
	function delete($id){
		return $this->_delete($id);
	}
	function get($id){
		return $this->_get($id);
	}
	function count(){
		return $this->_count();
	}
	function gets($userIds){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE uid in( ".S::sqlImplode($userIds).")"  );
		return $this->_getAllResultFromQuery ( $query );
	}
	function inserts($fieldDatas){
		$this->_db->update("INSERT INTO ".$this->_tableName." (uid,blacklist,blackcolony,categories,statistics,shieldinfo,sms_num,notice_num,request_num,groupsms_num) VALUES  ".S::sqlMulti($fieldDatas,FALSE));
		return $this->_db->insert_id ();
	}
	function updateByUserIds($fieldData,$userIds){
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . " WHERE uid in(  " . S::sqlImplode($userIds).")" );
		return $this->_db->affected_rows ();
	}
	function updateSmsNumberByUserIds($userIds,$number){
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET sms_num = sms_num+" . $this->_addSlashes( $number ) . " WHERE uid in(  " . S::sqlImplode($userIds) .")" );
		return $this->_db->affected_rows ();
	}
	function updateRequestNumberByUserIds($userIds,$number){
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET request_num = request_num+" . $this->_addSlashes( $number ) . " WHERE uid in(  " .  S::sqlImplode($userIds) .")" );
		return $this->_db->affected_rows ();
	}
	function updateNoticeNumberByUserIds($userIds,$number){
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET notice_num = notice_num+" . $this->_addSlashes( $number ) . " WHERE uid in(  " .  S::sqlImplode($userIds) .")" );
		return $this->_db->affected_rows ();
	}
	function updateGroupSmsNumberByUserIds($userIds,$number){
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET groupsms_num = groupsms_num+" . $this->_addSlashes( $number ) . " WHERE uid in(  " . S::sqlImplode($userIds) .")" );
		return $this->_db->affected_rows ();
	}
	function updateSmsNumsByUserIds($eUserIds,$nUserIds,$number){
		if($eUserIds){
			$this->updateSmsNumberByUserIds($eUserIds,$number);
		}
		if($nUserIds){
			$this->insertConfigs(array('sms_num'=>$number),$nUserIds);
		}
		return true;
	}
	function updateNoticeNumsByUserIds($eUserIds,$nUserIds,$number){
		if($eUserIds){
			$this->updateNoticeNumberByUserIds($eUserIds,$number);
		}
		if($nUserIds){
			$this->insertConfigs(array('notice_num'=>$number),$nUserIds);
		}
		return true;
	}
	function updateRequestNumsByUserIds($eUserIds,$nUserIds,$number){
		if($eUserIds){
			$this->updateRequestNumberByUserIds($eUserIds,$number);
		}
		if($nUserIds){
			$this->insertConfigs(array('request_num'=>$number),$nUserIds);
		}
		return true;
	}
	function updateGroupsmsNumsByUserIds($eUserIds,$nUserIds,$number){
		if($eUserIds){
			$this->updateGroupSmsNumberByUserIds($eUserIds,$number);
		}
		if($nUserIds){
			$this->insertConfigs(array('groupsms_num'=>$number),$nUserIds);
		}
		return true;
	}
	function insertConfigs($fieldData,$userIds){
		if( !$userIds || !$fieldData ) return false;
		$fieldDatas = array();
		foreach($userIds as $userId){
			$fieldDatas[] = array(
				'uid'            => $userId,
				'blacklist'      => isset($fieldData['blacklist'])    ? $fieldData['blacklist']    : '',
				'blackcolony'    => isset($fieldData['blackcolony'])  ? $fieldData['blackcolony']  : '',
				'categories'     => isset($fieldData['categories'])   ? $fieldData['categories']   : '',
				'statistics'     => isset($fieldData['statistics'])   ? $fieldData['statistics']   : '',
				'shieldinfo'     => isset($fieldData['shieldinfo'])   ? $fieldData['shieldinfo']   : '',
				'sms_num'        => isset($fieldData['sms_num'])      ? $fieldData['sms_num']      : 0,
				'notice_num'     => isset($fieldData['notice_num'])   ? $fieldData['notice_num']   : 0,
				'request_num'    => isset($fieldData['request_num'])  ? $fieldData['request_num']  : 0,
				'groupsms_num'   => isset($fieldData['groupsms_num']) ? $fieldData['groupsms_num'] : 0,
			);
		}
		return $this->inserts($fieldDatas);
	}
}