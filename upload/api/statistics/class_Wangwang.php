<?php
/**
 * phpwind 阿里旺旺数据统计
 * 
 * @author phpwind team
 * @version 1.0
 * @package api
 */
!defined('P_W') && exit('Forbidden');
require_once(R_P . 'api/class_Statistics.php');

class Statistics_Wangwang extends Statistics {
	
	/**
	 * 每天绑定用户总数
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */

	function getBindOfDay($day = null) {
		$platformApiClient = $this->_getPlatformApiClient();
		$response = $this->_jsonDecode($platformApiClient->get('openim.statistics.bindcount', array('fromdate' => $day, 'todate' => $day)));
		if (isset($response['content']['bindcount'])) {
			return new ApiResponse($response['content']['bindcount']);
		}
		return new ApiResponse(0);
	}

	/**
	 * 每天解绑用户总数
	 * @param string $day 'Y-m-d'
	 * @return int 
	 */

	function getUnBindOfDay($day = null) {
		$platformApiClient = $this->_getPlatformApiClient();
		$response = $this->_jsonDecode($platformApiClient->get('openim.statistics.bindcount', array('fromdate' => $day, 'todate' => $day)));
		if (isset($response['content']['unbindcount'])) {
			return new ApiResponse($response['content']['unbindcount']);
		}
		return new ApiResponse(0);
	}
	
	/**
	 * 提供在前在线人数
	 * @return array 
	 */

	function getCurrentOnline() {
		$platformApiClient = $this->_getPlatformApiClient();
		$response = $this->_jsonDecode($platformApiClient->get('openim.statistics.currentonline', array()));
		if (isset($response['content']['currentOnlineCount'])) {
			return new ApiResponse($response['content']['currentOnlineCount']);
		}
		return new ApiResponse(0);
	}

	/**
	 * 提供同时在线人数
	 * @return array 
	 */
	function getMaxOnline() {
		$platformApiClient = $this->_getPlatformApiClient();
		$response = $this->_jsonDecode($platformApiClient->get('openim.statistics.maxonline', array()));
		if (isset($response['content']['maxcount'])) {
			return new ApiResponse(array('maxcount'=>$response['content']['maxcount'],'maxdate'=>$response['content']['maxdate']));
		}
		return new ApiResponse(array('maxcount' => 0,'maxdate' => ''));
	}

	/**
	 * @return PlatformApiClient
	 */
	function _getPlatformApiClient() {
		static $client = null;
		if (null === $client) {
			global $db_sitehash, $db_siteownerid;
			L::loadClass('client', 'utility/platformapisdk', false);
			$client = new PlatformApiClient($db_sitehash, $db_siteownerid);
		}
		return $client;
	}

	function _jsonDecode($response) {
		require_once(R_P . 'api/class_json.php');
		$json = new Services_JSON(true);
		return $json->decode($response);
	}
}