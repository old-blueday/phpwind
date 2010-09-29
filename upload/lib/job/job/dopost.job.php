<?php
/*
 * 更新个人资料
 */
!function_exists('readover') && exit('Forbidden');
class JOB_DoPost{
	
	var $step = 1;
	var $hour = 3600;
	var $_timestamp = null;
	
	function JOB_DoPost(){
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
		return "thread.php?fid=".$factor['fid'];
	}
	
	function finish($job,$jober,$factor){
		if(!$job || !$factor || !isset($factor['fid'])){
			return 0;
		}
		$factors = unserialize($job['factor']);
		if(isset($factors['limit']) && $factors['limit'] > 0){
			if($jober['last']+$factors['limit'] * $this->hour < $this->_timestamp){
				return 5;/*失败*/
			}
		}
		$step = $jober['step']+1;
		if($factors['fid'] == $factor['fid'] ){
			return ($step >= $factors['num']) ? 2 : 1;
		}
		return 0;
	}
}