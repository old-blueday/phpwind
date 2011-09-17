<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
define ( "DEFEND_SUCCESS", 1 );
define ( "DEFEND_POST_DIARY", 0 );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Defend_General extends CloudWind_Core_Service {
	
	function postDefend($authorid, $author, $groupid, $id, $title, $content, $type = 'thread', $expand = array()) {
		if (0 == CloudWind_getConfig ( 'yundefend_shield' ) || ! ($setting = $this->getPlatformSettings ()) || $setting ['dunstatus'] == 100) {
			return DEFEND_SUCCESS;
		}
		$data = array ('id' => $id, 'localtime' => CloudWind_getConfig ( 'g_timestamp' ), 'bbsurl' => CloudWind_getConfig ( 'g_bbsurl' ), 'bbsname' => CloudWind_getConfig ( 'g_bbsname' ), 'charset' => CloudWind_getConfig ( 'g_charset' ), 'uid' => $authorid, 'username' => $author, 'groupid' => $groupid, 'title' => $title, 'content' => $content, 'type' => $type, 'ip' => CloudWind_getIp (), 'refere' => $_SERVER ['REQUEST_URI'], 'useragent' => $_SERVER ['HTTP_USER_AGENT'], 'method' => $_SERVER ['REQUEST_METHOD'], 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'], 'operate' => $type, 'operateid' => $id, 'expand' => $expand );
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		$defendOperateService = $this->getDefendGeneralOperateService ();
		if (isset ( $yunModel ['postdefend_model'] ) && $yunModel ['postdefend_model'] == 100) {
			return $defendOperateService->insertPostDefend ( $data );
		}
		$result = $this->_sendPost ( $data, 'post' );
		if (! $result || strlen ( $result ) > 10) {
			return DEFEND_SUCCESS;
		}
		list ( $userAudit, $postAudit ) = explode ( "\t", $result );
		$defendOperateService->userAudit ( $authorid, $userAudit, $expand );
		$defendOperateService->postAudit ( $id, $postAudit, $type, $expand );
		return DEFEND_SUCCESS;
	}
	
	function userDefend($operate, $uid, $username, $accesstime, $viewtime, $status = 0, $reason = "", $content = "", $behavior = array(), $expand = array()) {
		if (0 == CloudWind_getConfig ( 'yundefend_shield' ) || ! ($setting = $this->getPlatformSettings ()) || $setting ['dunstatus'] == 100) {
			return DEFEND_SUCCESS;
		}
		$data = $this->_dataEncode ( array ('operate' => $operate, 'status' => $status, 'uid' => $uid, 'username' => $username, 'localtime' => $GLOBALS ['timestamp'], 'bbsurl' => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'], 'charset' => $GLOBALS ['db_charset'], 'ip' => CloudWind_getIp (), 'refere' => $_SERVER ['REQUEST_URI'], 'useragent' => $_SERVER ['HTTP_USER_AGENT'], 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'], 'method' => $_SERVER ['REQUEST_METHOD'], 'accesstime' => $accesstime, 'viewtime' => $viewtime, 'reason' => $reason, 'content' => $content, 'expand' => $expand, 'behavior' => $behavior, 'version' => WIND_VERSION, 'os' => '' ) );
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		if (isset ( $yunModel ['userdefend_model'] ) && $yunModel ['userdefend_model'] == 100) {
			$defendOperateService = $this->getDefendGeneralOperateService ();
			$defendOperateService->insertUserDefend ( $data );
		} else {
			$this->_sendRequest ( $this->_buildRequestUrl ( 'user' ) . '&data=' . $data, '', 5 );
		}
		return DEFEND_SUCCESS;
	}
	
	function syncDefend() {
		$defendOperateService = $this->getDefendGeneralOperateService ();
		$post = $defendOperateService->getPostDefend ();
		if (! $post) {
			return false;
		}
		$data = CloudWind_Core_ToolKit::stringToArray ( $post ['data'] );
		$this->_syncDefend ( $data );
		$defendOperateService->deletePostDefend ( $post ['id'] );
		return DEFEND_SUCCESS;
	}
	
	function _syncDefend($data) {
		$result = $this->_sendPost ( $data, 'post' );
		if (! $result || strlen ( $result ) > 10) {
			return DEFEND_SUCCESS;
		}
		$defendOperateService = $this->getDefendGeneralOperateService ();
		list ( $userAudit, $postAudit ) = explode ( "\t", $result );
		$defendOperateService->userAudit ( $data ['uid'], $userAudit, $data ['expand'] );
		$defendOperateService->postAudit ( $data ['id'], $postAudit, $data ['type'], $data ['expand'] );
		return DEFEND_SUCCESS;
	}
	
	function setPostDefend($data) {
		$data = CloudWind_Core_ToolKit::stringToArray ( $data );
		if (! $data) {
			return false;
		}
		$defendOperateService = $this->getDefendGeneralOperateService ();
		$defendOperateService->userAudit ( $data ['uid'], $data ['ua'], $data ['ed'] );
		$defendOperateService->postAudit ( $data ['nid'], $data ['pa'], $this->_getTypes ( $data ['tp'] ), $data ['ed'] );
		return DEFEND_SUCCESS;
	}
	
	function getDefendGeneralOperateService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND_SECURITY_SERVICE::escapePath ( CLOUDWIND_VERSION_DIR . '/service/defend.generaloperate.class.php' );
			$service = new CloudWind_Defend_General_Operate ();
		}
		return $service;
	}
	
	function _getTypes($type) {
		$types = array (1 => 'thread', 2 => 'diary', 6 => 'reply' );
		return isset ( $types [$type] ) ? $types [$type] : '';
	}
	function _sendPost($data, $action, $timeout = 5) {
		return $this->sendPost ( $this->_buildRequestUrl ( $action ), $data, $timeout );
	}
	function _buildRequestUrl($action) {
		return "http://" . trim ( $this->getYunDunHost (), "/" ) . "/defend.php?a=" . $action;
	}
	function _sendRequest($host, $data, $timeout = 1) {
		$parse = parse_url ( $host );
		if (empty ( $parse ))
			return null;
		$parse ['path'] = str_replace ( array ('\\', '//' ), '/', $parse ['path'] ) . "?" . $parse ['query'];
		$content = "GET " . $parse ['path'] . " HTTP/1.1\r\n";
		$content .= "Host: " . $parse ['host'] . "\r\n";
		$content .= "Connection: close\r\n\r\n";
		if (! $fp = @fsockopen ( $parse ['host'], 80, $errnum, $errstr, $timeout ))
			return null;
		@fwrite ( $fp, $content );
		@fclose ( $fp );
		return true;
	}
	function _dataEncode($array) {
		return urlencode ( CloudWind_Core_ToolKit::arrayToString ( $array ) );
	}
}