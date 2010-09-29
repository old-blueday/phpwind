<?php
/*
 * 更新个人资料
 */
!function_exists('readover') && exit('Forbidden');
class JOB_DoAddFriend{
	
	var $step = 1;
	var $hour = 3600;
	var $_timestamp = null;
	
	function JOB_DoAddFriend(){
		global $timestamp;
		$this->_timestamp = $timestamp;
	}
	
	/*
	 * 任务链接
	 */
	function getUrl($job){
		return "u.php?a=friend&type=find";
	}
	
	function finish($job,$jober,$factor){
		if(!$job || !$factor){
			return 0;
		}
		$factors = unserialize($job['factor']);
		if(isset($factors['limit']) && $factors['limit'] > 0){
			if($jober['last']+$factors['limit'] * $this->hour < $this->_timestamp){
				return 5;/*失败*/
			}
		}
		//增加指定用户为好友
		if($factors['type'] == 1 && isset($factor['user'])){
			return ($factors['user'] == $factor['user']) ? 2 : 1;
		}
		//增加好友个数
		if($factors['type'] == 2){
			return ($jober['step']+1 == $factors['num']) ? 2 : 1;
		}
		return 0;
	}
}