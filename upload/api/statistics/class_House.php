<?php
/**
 * phpwind 房产数据统计
 *
 * @author phpwind team
 * @version 1.0
 * @package api
 */
!defined('P_W') && exit('Forbidden');
require_once(R_P . 'api/class_Statistics.php');

class Statistics_House extends Statistics {

	/**
	 *
	 * 每天发布出售房源数量
	 */
	function getSalesOfDay($day = null){
		$this->_setTimestamp( $day );
		$sales = intval($this->db->get_value(
 			"SELECT COUNT(*) FROM `pw_house_saleinfo` 
 			WHERE `posttime` BETWEEN $this->startTime AND $this->endTime"
		));
		return new ApiResponse($sales);
	}

	/**
	 *
	 * 每天发布出租房源数量
	 */
	function getHiresOfDay($day = null){
		$this->_setTimestamp( $day );
		$hires = intval($this->db->get_value(
 			"SELECT COUNT(*) FROM `pw_house_hireinfo` 
 			WHERE `posttime` BETWEEN $this->startTime AND $this->endTime"
		));
		return new ApiResponse($hires);
	}

	/**
	 *
	 * 出售房源总量
	 */
	function getSalesCount(){
		$sales = 0;
		$sales = intval($this->db->get_value("SELECT SUM(salenum) FROM `pw_house_secondpost` "));
		return new ApiResponse($sales);
	}

	/**
	 *
	 * 出租房源总量
	 */
	function getHireCount(){
		$hires = 0;
		$hires = intval($this->db->get_value("SELECT SUM(hirenum) FROM `pw_house_secondpost` "));
		return new ApiResponse($hires);
	}

	/**
	 *
	 * 新源房源总量
	 */
	function getNewHouseCount(){
		$hires = intval($this->db->get_value(
 			"SELECT COUNT(*) FROM `pw_house_info` "
 			));
 			return new ApiResponse($hires);
	}

	/**
	 *
	 * 获取出售top10
	 */
	function getTopSales($num = 10){
		require_once R_P.'require/showimg.php';
		$sales = array();
		$num = (int)$num;
		$query = $this->db->query(
			"SELECT m.`username`,m.`icon`,d.`uid`,d.`salenum` AS `count` FROM `pw_house_secondpost` AS d
				LEFT JOIN `pw_members` AS m ON d.`uid`=m.`uid`
				ORDER BY `count` DESC 
				LIMIT $num"
		);
		while ($rt = $this->db->fetch_array($query)) {
			$sales[] = array(
			$rt['uid'],
			$rt['username'],
			showfacedesign($rt['icon']),
			$rt['count']
			);
		}
		return new ApiResponse($sales);
	}

	/**
	 *
	 * 获取出租top10
	 */
	function getTopHires($num = 10){
		require_once R_P.'require/showimg.php';
		$hires = array();
		$num = (int)$num;
		$query = $this->db->query(
			"SELECT m.`username`,m.`icon`,d.`uid`,d.`hirenum` AS `count` FROM `pw_house_secondpost` AS d
				LEFT JOIN `pw_members` AS m ON d.`uid`=m.`uid`
				ORDER BY `count` DESC 
				LIMIT $num"
		);
		while ($rt = $this->db->fetch_array($query)) {
			$hires[] = array(
			$rt['uid'],
			$rt['username'],
			showfacedesign($rt['icon']),
			$rt['count']
			);
		}
		return new ApiResponse($hires);
	}

	/**
	 *
	 * 每天开通经纪人的数量
	 */
	function getBrokersOfDay($day = null){
		$this->_setTimestamp( $day );
		$brokers = intval($this->db->get_value(
 			"SELECT COUNT(*) FROM `pw_house_broker` 
 			WHERE `createtime` BETWEEN $this->startTime AND $this->endTime"
		));
		return new ApiResponse($brokers);
	}

	/**
	 *
	 * 经纪人发布出售源总量
	 */
	function getSalesOfBroker(){
		$sales = intval($this->db->get_value(
 			"SELECT COUNT(*) FROM `pw_house_saleinfo` WHERE isbroker = 1"
 			));
 			return new ApiResponse($sales);
	}

	/**
	 *
	 * 经纪人发布出租房源总量
	 */
	function getHiresOfBroker(){
		$hires = intval($this->db->get_value(
 			"SELECT COUNT(*) FROM `pw_house_hireinfo` WHERE isbroker = 1"
 			));
 		return new ApiResponse($hires);
	}
}