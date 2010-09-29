<?php
!defined('P_W') && exit('Forbidden');
/**
 * 相册服务对象
 * @package PW_Photos
 * @author suqian
 * @access public
 */
class PW_Photos {
	
	function PW_Photos() {
	}
	
	function getPhotoByPid($pid) {
		$photoDao =  L::loadDB('CnPhoto','colony');
		return $photoDao->get($pid);
	}
}
