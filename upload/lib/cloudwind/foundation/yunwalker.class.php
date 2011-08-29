<?php
/**
 * 漫步云服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
define ( "YUN_STATE_FAIL", 0 );
define ( "YUN_STATE_SUCCESS", 1 );
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class PW_YunWalker extends PW_YunBase {
	function router() {
		list ( $doing ) = $this->getRequest ( array ('doing' ) );
		if (! $this->_checkServer ( $doing )) {
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
			$verifyService = $this->_getVerifySettingService ();
			$verifyService->setSearchHook ( 1 );
			$settingService = $this->getYunSettingService ();
			$result = $settingService->initSetting ( $result ['setting'] );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function clearWalker() {
		if ($this->_checkVerify ()) {
			$yunInstallService = $this->_getYunInstallService ();
			return $yunInstallService->clearTables ();
		}
		return YUN_STATE_FAIL;
	}
	function hookWalker() {
		$result = false;
		if ($this->_checkVerify ()) {
			list ( $hook ) = $this->getRequest ( array ('hook' ) );
			$verifyService = $this->_getVerifySettingService ();
			$result = $verifyService->setSearchHook ( intval ( $hook ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function domainWalker() {
		$result = false;
		if ($this->_checkLevel ()) {
			list ( $domain ) = $this->getRequest ( array ('domain' ) );
			$verifyService = $this->_getVerifySettingService ();
			$result = $verifyService->setSearchDomain ( $domain );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function settingWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $setting ) = $this->getRequest ( array ('setting' ) );
			$settingService = $this->getYunSettingService ();
			$result = $settingService->updateSetting ( $setting );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function openWalker() {
		$result = false;
		if ($this->_checkLevel ()) {
			list ( $isopen ) = $this->getRequest ( array ('isopen' ) );
			$verifyService = $this->_getVerifySettingService ();
			$result = $verifyService->setSearchOpen ( intval ( $isopen ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function modelWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $model ) = $this->getRequest ( array ('model' ) );
			$verifyService = $this->_getVerifySettingService ();
			$result = $verifyService->setYunModel ( trim ( $model ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	
	function expandWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $expand ) = $this->getRequest ( array ('expand' ) );
			$verifyService = $this->_getVerifySettingService ();
			$result = $verifyService->setYunExpand ( trim ( $expand ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	
	function defendWalker() {
		$result = false;
		if ($this->_syncCheck ()) {
			list ( $data ) = $this->getRequest ( array ('data' ) );
			$service = $this->getYunDefendService ();
			$result = $service->setPostDefend ( trim ( $data ) );
		}
		return ($result) ? YUN_STATE_SUCCESS : YUN_STATE_FAIL;
	}
	function checksettingWalker() {
		if ($this->_syncCheck ()) {
			$settings = array ();
			$settingService = $this->getYunSettingService ();
			$settings ['setting'] = $this->convertCharset ( $settingService->getSetting () );
			$settings ['search'] = array ('hook' => $GLOBALS ['db_yunsearch_hook'], 'isopen' => $GLOBALS ['db_yunsearch_isopen'], 'domain' => $GLOBALS ['db_yunsearch_domain'], 'search' => $GLOBALS ['db_yunsearch_search'], 'defend' => $GLOBALS ['db_yundefend_shield'], 'shieldpost' => $GLOBALS ['db_yundefend_shieldpost'], 'shielduser' => $GLOBALS ['db_yundefend_shielduser'], 'yun_model' => $GLOBALS ['db_yun_model'], 'yun_expand' => $GLOBALS ['db_yun_expand'] );
			$settings ['site'] = array ('db_bbsurl' => $GLOBALS ['db_bbsurl'] );
			$settings ['version'] = array ('yun_version' => $settingService->getCloudWindVersion (), 'wind_version' => WIND_VERSION );
			$yunIndexService = $this->_getYunIndexService ();
			$settings ['list'] = array ('full' => $yunIndexService->createFullAllLists ( rand ( 1, 100 ), false ) );
			return base64_encode ( serialize ( $settings ) );
		}
		return YUN_STATE_FAIL;
	}
	function checksiteWalker() {
		list ( $marksite, $step ) = $this->getRequest ( array ('marksite', 'step' ) );
		if (! $marksite || ! $step) {
			return YUN_STATE_FAIL;
		}
		$factory = $this->getYunExtendFactory ();
		$checkService = $factory->getYunCheckServerService ();
		return $checkService->identifySite ( $marksite );
	}
	function _getWalkers() {
		return array ('check', 'init', 'clear', 'hook', 'open', 'model', 'expand', 'defend', 'domain', 'setting', 'syncinit', 'synccheck', 'syncsetting', 'checksetting', 'checksite' );
	}
	function _checkServer($action) {
		list ( $verify, $hash, $step, $doing ) = $this->getRequest ( array ('verify', 'hash', 'step', 'doing' ) );
		if (! $verify || ! $hash || ! $step) {
			return YUN_STATE_FAIL;
		}
		$result = $this->_sendPost ( array ('doing' => 'checkserver', "hash" => $hash, "verify" => $verify, "step" => $step, "bbsurl" => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'], 'ip' => pwGetIp (), 'createdtime' => $GLOBALS ['timestamp'], 'action' => $action, 'url' => base64_encode ( $_SERVER ['REQUEST_URI'] ), 'useragent' => $_SERVER ['HTTP_USER_AGENT'], 'method' => $_SERVER ['REQUEST_METHOD'] ) );
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
		$result = $this->_sendPost ( array ('doing' => $doing, "hash" => $hash, "verify" => $verify, "id" => $id, "step" => $step, "install" => $install, "bbsurl" => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'] ) );
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
		if (isset ( $params ['install'] ) && $params ['install'] > 0) {
			$this->_installTables ();
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
		$result = $this->_sendPost ( array ('doing' => $doing, "hash" => $hash, "verify" => $verify, "id" => $id, "step" => $step, "bbsurl" => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'] ) );
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
		$result = $this->_sendPost ( array ('doing' => $doing, "hash" => $hash, "verify" => $verify, "id" => $id, "step" => $step, "bbsurl" => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'] ) );
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
	function _sendPost($data) {
		return $this->sendPost ( "http://" . trim ( $this->getYunHost (), "/" ) . "/index.php?c=verify", $data, 10 );
	}
	function _installTables() {
		$yunInstallService = $this->_getYunInstallService ();
		return $yunInstallService->installTables ();
	}
	function _getYunInstallService() {
		$factory = $this->getYunExtendFactory ();
		return $factory->getYunInstallService ();
	}
	function _getVerifySettingService() {
		$factory = $this->getYunExtendFactory ();
		return $factory->getVerifySettingService ();
	}
	function _getAesService() {
		$factory = $this->getYunExtendFactory ();
		return $factory->getAesService ();
	}
	function _getYunIndexService() {
		require_once R_P . 'lib/cloudwind/yunindex.class.php';
		return new PW_YunIndex ();
	}
}