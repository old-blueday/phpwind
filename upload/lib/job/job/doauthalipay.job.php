<?php
/*
 * 支付宝认证
 */
!function_exists('readover') && exit('Forbidden');
class JOB_DoAuthAlipay{
	var $step = 1;
	var $_timestamp = null;
	
	function JOB_DoAuthAlipay(){
		global $timestamp;
		$this->_timestamp = $timestamp;
	}
	
	/*
	 * 任务链接
	 */
	function getUrl($job){
		return "profile.php?action=auth";
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