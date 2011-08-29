<?php
/**
 * phpwind 店铺数据统计
 * 
 * @author phpwind team
 * @version 1.0
 * @package api
 */
 !defined('P_W') && exit('Forbidden');
 require_once(R_P . 'api/class_Statistics.php');

 class Statistics_Dianpu extends Statistics {
	
	/**
	 * 获取每天开启店铺的数量
	 * 
	 * @param string $day 某一天，格式“2010-10-09”
	 * @return int
	 */
	function getDianpuNumOfDay($day = null) {
		$this->_setTimestamp($day);
		$sql = "SELECT COUNT(a.createtime) FROM pw_dianpu_dianpubase as a LEFT JOIN pw_dianpu_dianpuextend  as b ON a.dianpuid = b.dianpuid   WHERE a.createtime BETWEEN $this->startTime AND $this->endTime";
		$posts = intval($this->db->get_value($sql));
 		return new ApiResponse($posts);
	}
	
	/**
	 * 获取总的店铺数量
	 * 
	 * @return int
	 */
	function getDianpu() {
		$sql = "SELECT COUNT(*) as num FROM pw_dianpu_dianpuextend";
		$num = intval($this->db->get_value($sql));
		return new ApiResponse($num);
	}

	/**
	 * 获取vip店铺数量
	 * 
	 * @return int
	 */
	function getVipDianpu() {
		$sql = "SELECT COUNT(*) as num FROM pw_dianpu_dianpuextend WHERE groupid = 2";
		$num = intval($this->db->get_value($sql));
		return new ApiResponse($num);
	}
	
	/**
	 * 各个行业分类店铺构成
	 * 
	 * @return array
	 */
	function getDianpuNumByCate() {
		$numArr = array();
		$cateArr = $this->_getCate();
		$query = $this->db->query("SELECT COUNT(*) as num , categoryid FROM pw_dianpu_dianpuextend group by categoryid");
		while($row = $this->db->fetch_array($query)){
			$numArr[$row['categoryid']] = $row['num'];
		}
		foreach($cateArr as $key => $val) {
			$cateArr[$key]['num'] = isset($numArr[$val['categoryid']]) ? $numArr[$val['categoryid']] : 0;
		}
		return new ApiResponse($cateArr);
	}
	
	/**
 	 * 获取各个地区的店铺构成
 	 * 
 	 * @return array
 	 */
	function getDianpuNumByArea() {
		$areaArr = array();
		$numArr = array();
		$areaArr =  $this->_getArea();
		$query = $this->db->query("SELECT COUNT(*) as num , areaid FROM pw_dianpu_dianpuextend group by areaid");
		while($row = $this->db->fetch_array($query)){
          $numArr[$row['areaid']] = $row['num'];
       	}
      	foreach($areaArr as $key => $val) {
          $areaArr[$key]['num'] = isset($numArr[$val['areaid']]) ? $numArr[$val['areaid']] : 0;
       	}
	   	return new ApiResponse($areaArr);
	}
	
	/**
	 * 获取分类数组
	 * 
	 * @return array
	 */
	function _getCate() {
		$temp = array();
		$query = $this->db->query("SELECT categoryid,parentid,name FROM pw_dianpu_categories WHERE parentid != 0");
		while($row = $this->db->fetch_array($query)){
			$temp[] = $row;
		}
		return $temp;
	}
	
	/**
	 * 获取地区数组
	 * 
 	 * @return array
 	 */
	function _getArea() {
		$areaArr = array();
		$query = $this->db->query("SELECT * FROM pw_dianpu_areas ");
		while($row = $this->db->fetch_array($query)){
			$areaArr[] = $row;
		}
		return $areaArr;
	}

 }
?>