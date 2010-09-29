<?php
/*
 * 更新个人资料
 */
!function_exists('readover') && exit('Forbidden');
class JOB_DoReply{
	
	var $step = 1;
	var $hour = 3600;
	var $_timestamp = null;
	
	function JOB_DoReply(){
		global $timestamp;
		$this->_timestamp = $timestamp;
	}
	/*
	 * 任务链接
	 */
	function getUrl($job){
		if(!$job){
			return "";
		}
		$factor = unserialize($job['factor']);
		if($factor['type'] == 1){
			return "read.php?tid=".$factor['tid'];
		}
		return "";
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
		$step = $jober['step']+1;
		if($factors['type'] == 2 && isset($factor['user']) && $factors['user'] == $factor['user']){
			return ($step == $factors['replynum']) ? 2 : 1;
		}
		if($factors['type'] == 1 && isset($factor['tid']) && $factors['tid'] == $factor['tid']){
			return ($step == $factors['replynum']) ? 2 : 1;
		}
		return 0;
	}
	
}