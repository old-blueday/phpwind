<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
define ( "YUN_STATE_FAIL", 0 );
define ( "YUN_STATE_SUCCESS", 1 );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_Walker extends CloudWind_Core_Service {
	function router() {
		list ( $doing, $randcode ) = $this->getRequest ( array ('doing', 'randcode' ) );
		if (! $this->_checkServer ( $doing, $randcode )) {
			return YUN_STATE_FAIL;
		}
		$walker = ($doing) ? $doing . "Walker" : "";
		if ($walker && in_array ( $doing, $this->_getWalkers () ) && method_exists ( $this, $walker )) {
			return $this->$walker ();
		}
		return YUN_STATE_FAIL;
	}
	function syncinitWalker() {
		return $this->_init ( 'syncinit', 300 );
	}
	function synccheckWalker() {
		return $this->_syncCheck ();
	}
	function initWalker() {
		return $this->_init ( 'init', 201 );
	}
	function checkWalker() {
		return $this->_checkVerify ();
	}
	function syncsettingWalker() {
		$result = false;
		if (($result = $this->_sync ( 'syncsetting', 401 )) && isset ( $result ['setting'] )) {
			$settingService = $this->getPlatformSettingService ();
			$settingService->initSetting ( $result ['setting'] );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function hookWalker() {
		$result = false;
		if ($this->_checkVerify ()) {
			list ( $hook ) = $this->getRequest ( array ('hook' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setSearchHook ( intval ( $hook ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function domainWalker() {
		$result = false;
		if ($this->_checkLevel ()) {
			list ( $domain ) = $this->getRequest ( array ('domain' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setSearchDomain ( $domain );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function settingWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $setting ) = $this->getRequest ( array ('setting' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->updateSetting ( $setting );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function openWalker() {
		$result = false;
		if ($this->_checkLevel ()) {
			list ( $isopen ) = $this->getRequest ( array ('isopen' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setSearchOpen ( intval ( $isopen ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function uniqueWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $unique ) = $this->getRequest ( array ('unique' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setSearchUnique ( trim ( $unique ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function sethashWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $yunhash ) = $this->getRequest ( array ('yunhash' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setYunHash ( trim ( $yunhash ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function modelWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $model ) = $this->getRequest ( array ('model' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setYunModel ( trim ( $model ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	
	function expandWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $expand ) = $this->getRequest ( array ('expand' ) );
			$settingService = $this->getPlatformSettingService ();
			$result = $settingService->setYunExpand ( trim ( $expand ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	
	function defendWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $data ) = $this->getRequest ( array ('data' ) );
			$factory = $this->getDefendFactory ();
			$service = $factory->getDefendGeneralService ();
			$result = $service->setPostDefend ( trim ( $data ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function checksettingWalker() {
		if ($this->_syncCheck ()) {
			$settings = array ();
			$settingService = $this->getPlatformSettingService ();
			$settings ['setting'] = $this->convertCharset ( $settingService->getSetting () );
			$settings ['site'] = array ('db_bbsurl' => CloudWind_getConfig ( 'g_bbsurl' ) );
			$settings ['config'] = $this->convertCharset ( CloudWind_getConfigs () );
			$settings ['version'] = array ('yun_version' => $settingService->getCloudWindVersion (), 'wind_version' => CloudWind_getConfig ( 'g_windversion' ) );
			$yunIndexService = $this->_getYunIndexService ();
			$settings ['list'] = array ('full' => $yunIndexService->createFullAllLists ( rand ( 1, 100 ), false ) );
			return CloudWind_buildSecutiryCode ( base64_encode ( serialize ( $settings ) ) );
		}
		return YUN_STATE_FAIL;
	}
	function checksiteWalker() {
		list ( $marksite, $step ) = $this->getRequest ( array ('marksite', 'step' ) );
		if (! $marksite || ! $step) {
			return YUN_STATE_FAIL;
		}
		$checkService = $this->_getCheckServerService ();
		return $checkService->identifySite ( $marksite );
	}
	function _getWalkers() {
		return array ('check', 'init', 'hook', 'open', 'sethash', 'model', 'expand', 'defend', 'domain', 'unique', 'setting', 'syncinit', 'synccheck', 'syncsetting', 'checksetting', 'checksite' );
	}
	function _checkServer($action, $randcode) {
		list ( $verify, $hash, $step, $doing ) = $this->getRequest ( array ('verify', 'hash', 'step', 'doing' ) );
		if (! $verify || ! $hash || ! $step) {
			return YUN_STATE_FAIL;
		}
		$result = $this->_sendPost ( array ('doing' => 'checkserver', "hash" => $hash, "verify" => $verify, "step" => $step, "bbsurl" => CloudWind_getConfig ( 'g_bbsurl' ), 'bbsname' => CloudWind_getConfig ( 'g_bbsname' ), 'ip' => CloudWind_getIp (), 'createdtime' => CloudWind_getConfig ( 'g_timestamp' ), 'action' => $action, 'url' => base64_encode ( $_SERVER ['REQUEST_URI'] ), 'useragent' => $_SERVER ['HTTP_USER_AGENT'], 'method' => $_SERVER ['REQUEST_METHOD'], 'randcode' => $randcode ) );
		return (intval ( $result ) === 1) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function _checkVerify() {
		return $this->_check ( 'check', 202 );
	}
	function _checkLevel() {
		return $this->_check ( 'level', 203 );
	}
	function _syncCheck() {
		return $this->_check ( 'synccheck', 301 );
	}
	function _init($doing, $verifyCode) {
		list ( $verify, $hash, $id, $step, $install ) = $this->getRequest ( array ('verify', 'hash', 'id', 'step', 'install' ) );
		if (! $verify || ! $hash || ! $step) {
			return YUN_STATE_FAIL;
		}
		$hash = html_entity_decode ( $hash );
		$result = $this->_sendPost ( array ('doing' => $doing, "hash" => $hash, "verify" => $verify, "id" => $id, "step" => $step, "install" => $install, "bbsurl" => CloudWind_getConfig ( 'g_bbsurl' ), 'bbsname' => CloudWind_getConfig ( 'g_bbsname' ) ) );
		$result = trim ( $result );
		if (! $result || strlen ( $result ) > 200) {
			return YUN_STATE_FAIL;
		}
		$params = $this->_splitQuery ( $result );
		if (! $params || ! isset ( $params ['verify'] ) || ! isset ( $params ['vector'] ) || ! isset ( $params ['cipher'] ) || ! isset ( $params ['hash'] )) {
			return YUN_STATE_FAIL;
		}
		if ($params ['verify'] != $verifyCode) {
			return YUN_STATE_FAIL;
		}
		$verifyService = $this->_getVerifySettingService ();
		$result = $verifyService->initVerifySetting ( trim ( $params ['vector'] ), trim ( $params ['cipher'] ), trim ( $params ['hash'] ) );
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function _check($doing, $verifyCode) {
		list ( $verify, $hash, $step, $id ) = $this->getRequest ( array ('verify', 'hash', 'step', 'id' ) );
		if (! $verify || ! $hash || ! $step) {
			return YUN_STATE_FAIL;
		}
		$hash = html_entity_decode ( $hash );
		$result = $this->_sendPost ( array ('doing' => $doing, "hash" => $hash, "verify" => $verify, "id" => $id, "step" => $step, "bbsurl" => CloudWind_getConfig ( 'g_bbsurl' ), 'bbsname' => CloudWind_getConfig ( 'g_bbsname' ) ) );
		$result = trim ( $result );
		if (! $result || strlen ( $result ) > 200) {
			return YUN_STATE_FAIL;
		}
		$params = $this->_splitQuery ( $result );
		if (! $params || ! isset ( $params ['verify'] ) || ! isset ( $params ['hashid'] ) || ! isset ( $params ['rand'] )) {
			return YUN_STATE_FAIL;
		}
		if ($params ['verify'] != $verifyCode) {
			return YUN_STATE_FAIL;
		}
		$verifyService = $this->_getVerifySettingService ();
		$setting = $verifyService->getVerifySetting ();
		if (! $setting || ! isset ( $setting ['vector'] ) || ! $setting ['cipher']) {
			return YUN_STATE_FAIL;
		}
		$aesService = $this->_getAesService ();
		$key = $aesService->encrypt ( $setting ['vector'], $setting ['cipher'], 256 );
		if (! $key) {
			return YUN_STATE_FAIL;
		}
		if ($params ['rand'] == $aesService->strcode ( urldecode ( $params ['hashid'] ), $key, 'DECODE' )) {
			return YUN_STATE_SUCCESS;
		}
		return YUN_STATE_FAIL;
	}
	function _sync($doing, $verifyCode) {
		list ( $verify, $hash, $step, $id ) = $this->getRequest ( array ('verify', 'hash', 'step', 'id' ) );
		if (! $verify || ! $hash || ! $step) {
			return YUN_STATE_FAIL;
		}
		$hash = html_entity_decode ( $hash );
		$result = $this->_sendPost ( array ('doing' => $doing, "hash" => $hash, "verify" => $verify, "id" => $id, "step" => $step, "bbsurl" => CloudWind_getConfig ( 'g_bbsurl' ), 'bbsname' => CloudWind_getConfig ( 'g_bbsname' ) ) );
		$result = trim ( $result );
		if (! $result) {
			return YUN_STATE_FAIL;
		}
		$params = $this->_splitQuery ( $result );
		if (! $params || ! isset ( $params ['verify'] ) || ! isset ( $params ['hashid'] ) || ! isset ( $params ['setting'] )) {
			return YUN_STATE_FAIL;
		}
		if ($params ['verify'] != $verifyCode) {
			return YUN_STATE_FAIL;
		}
		$verifyService = $this->_getVerifySettingService ();
		$setting = $verifyService->getVerifySetting ();
		if (! $setting || ! isset ( $setting ['vector'] ) || ! $setting ['cipher']) {
			return YUN_STATE_FAIL;
		}
		$aesService = $this->_getAesService ();
		$key = $aesService->encrypt ( $setting ['vector'], $setting ['cipher'], 256 );
		if (! $key) {
			return YUN_STATE_FAIL;
		}
		if ($params ['identifier'] == $aesService->strcode ( urldecode ( $params ['hashid'] ), $key, 'DECODE' )) {
			return $params;
		}
		return YUN_STATE_FAIL;
	}
	function _splitQuery($query) {
		if (! $query) {
			return array ();
		}
		$query = explode ( "&", $query );
		$params = array ();
		foreach ( $query as $q ) {
			list ( $key, $value ) = explode ( "=", $q );
			$params [$key] = $value;
		}
		return $params;
	}
	
	function convertCharset($fields) {
		if (! $fields) {
			return array ();
		}
		if (in_array ( CloudWind_getConfig ( 'g_charset' ), array ('utf8', 'utf-8' ) )) {
			return $fields;
		}
		foreach ( $fields as $k => $v ) {
			$fields [$k] = (is_array ( $v )) ? $this->convertCharset ( $v ) : $this->convertToUtf8 ( $v );
		}
		return $fields;
	}
	
	function convertToUtf8($text) {
		static $charset = null;
		if (! $charset) {
			require_once CLOUDWIND . '/client/core/public/core.factory.class.php';
			$factory = new CloudWind_Core_Factory ();
			$charset = $factory->getChineseService ( CloudWind_getConfig ( 'g_charset' ), 'utf8' );
		}
		return $charset->Convert ( $text );
	}
	
	function _sendPost($data) {
		return $this->sendPost ( "http://" . trim ( $this->getYunHost (), "/" ) . "/index.php?c=verify", $data, 10 );
	}
	function _getVerifySettingService() {
		$factory = $this->getPlatformFactory ();
		return $factory->getVerifySettingService ();
	}
	function _getSettingService() {
		$factory = $this->getPlatformFactory ();
		return $factory->getSettingService ();
	}
	function _getCheckServerService() {
		$factory = $this->getPlatformFactory ();
		return $factory->getCheckServerService ();
	}
	function _getAesService() {
		$factory = $this->getCoreFactory ();
		return $factory->getAesService ();
	}
	function _getYunIndexService() {
		require_once CLOUDWIND . '/client/search/search.index.class.php';
		return new CloudWind_Search_Index ();
	}
}