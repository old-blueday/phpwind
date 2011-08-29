<?php
/*
 * 更新个人资料
 */
!function_exists('readover') && exit('Forbidden');
class JOB_DoSendMessage{
	
	var $step = 1;
	var $hour = 3600;
	var $_timestamp = null;
	
	function JOB_DoSendMessage(){
		global $timestamp;
		$this->_timestamp = $timestamp;
	}
	
	/*
	 * 任务链接
	 */
	function getUrl($job){
		$factor = unserialize($job['factor']);
		return "message.php?type=post&username=$factor[user]";
	}
	
	function finish($job,$jober,$factor){
		if(!$job || !$factor || !isset($factor['user'])){
			return 0;
		}
		$factors = unserialize($job['factor']);
		if(isset($factors['limit']) && $factors['limit'] > 0){
			if($jober['last']+$factors['limit'] * $this->hour < $this->_timestamp){
				return 5;/*失败*/
			}
		}
		$users = $factor['user'];
		return (in_array($factors['user'], $users)) ? 2 : 1;
	}
	
}