<?php
/*
 * 手机认证
 */
!function_exists('readover') && exit('Forbidden');
class JOB_DoAuthMobile{
	var $step = 1;
	var $_timestamp = null;
	
	function JOB_DoAuthMobile(){
		global $timestamp;
		$this->_timestamp = $timestamp;
	}
	
	/*
	 * 任务链接
	 */
	function getUrl($job){
		return "profile.php?action=auth&check_step=mobile";
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
	}
}