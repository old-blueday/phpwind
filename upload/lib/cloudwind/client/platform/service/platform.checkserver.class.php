<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
define ( 'YUN_APPLY_RETRY', 0 );
define ( 'YUN_APPLY_TRUE', 1 );
define ( 'YUN_APPLY_FALSE', 0 );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_CheckServer extends CloudWind_Core_Service {
	function getSiteInfo() {
		$settingService = $this->getPlatformSettingService ();
		return array (CloudWind_getConfig ( 'g_bbsname' ), CloudWind_getConfig ( 'g_bbsurl' ), CloudWind_getConfig ( 'g_windversion' ), $settingService->getCloudWindVersion () );
	}
	function checkCloudWind() {
		if (YUN_APPLY_RETRY) {
			return YUN_APPLY_FALSE;
		}
		CloudWind::createCloudWindTables();
		$setting = $this->getPlatformSettings ();
		if (isset ( $setting ['uniqueid'] ) && $setting ['uniqueid'] && isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_open) {
			return $this->_apply_result_success;
		}
		if (isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_fail) {
			return $this->_apply_result_verify;
		}
		if (isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_reset) {
			$settingService = $this->getPlatformSettingService ();
			$settingService->resetSetting ();
			return YUN_APPLY_FALSE;
		}
		return (isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_apply) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE;
	}
	function checkHost() {
		$host = $this->getYunHost ();
		$result = $this->_sendPost ( $host, 'checksite', array ('siteurl' => CloudWind_getConfig ( 'g_bbsurl' ) ) );
		return (intval ( $result ) == YUN_APPLY_TRUE) ? true : false;
	}
	function getSiteScale() {
		$setting = $this->getPlatformSettings ();
		if ($setting ['sitescale'] == 101) {
			return true;
		}
		require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
		$factory = new CloudWind_Service_Factory ();
		$threadService = $factory->getSearchThreadService ();
		$count = $threadService->countThreadsNum ();
		return ($count >= ((isset ( $setting ['sitelimit'] )) ? $setting ['sitelimit'] : 0)) ? true : false;
	}
	function getDunDescribe() {
		$setting = $this->getPlatformSettings ();
		return isset ( $setting ['dundescribe'] ) ? html_entity_decode ( $setting ['dundescribe'] ) : '';
	}
	function getYunDescribe() {
		$setting = $this->getPlatformSettings ();
		return isset ( $setting ['yundescribe'] ) ? html_entity_decode ( $setting ['yundescribe'] ) : '';
	}
	function getBaseDescription() {
		$setting = $this->getPlatformSettings ();
		return isset ( $setting ['description'] ) ? html_entity_decode ( $setting ['description'] ) : '';
	}
	function identifySite($mark) {
		if (! $mark) {
			return false;
		}
		$setting = $this->getPlatformSettings ();
		return ($setting && isset ( $setting ['marksite'] ) && $setting ['marksite'] == $mark) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE;
	}
	function markSite($isMark = true) {
		$settingService = $this->getPlatformSettingService ();
		$marksite = rand ( 100000000, 999999999 );
		$result = $settingService->updateSetting ( array ('marksite' => $marksite, 'cloudstatus' => (($isMark) ? $this->_status_apply : $this->_status_fail) ) );
		return ($result) ? $marksite : YUN_APPLY_FALSE;
	}
	function getYunSearchManageUrl() {
		$db_bbsurl = (CloudWind_getConfig ( 'g_bbsurl' )) ? CloudWind_getConfig ( 'g_bbsurl' ) : 'http://' . $_SERVER ['HTTP_HOST'];
		$verifySettingService = $this->getPlatformVerifySettingService ();
		$settings = $verifySettingService->getVerifySetting ();
		$params = array ('pw_sig' => CloudWind_getConfig ( 'g_timestamp' ), 'pw_bbsurl' => CloudWind_getConfig ( 'g_bbsurl' ), 'pw_bbsname' => CloudWind_getConfig ( 'g_bbsname' ), 'pw_charset' => CloudWind_getConfig ( 'g_charset' ), 'pw_sitehash' => $settings ['field1'] );
		return "http://" . trim ( $this->getYunSearchHost (), "/" ) . '/index.php?c=manage&' . $this->buildQuery ( $params );
	}
	function getServerStatus() {
		if (! function_exists ( 'fsockopen' ) || ! function_exists ( 'parse_url' )) {
			return false;
		}
		list ( $host, $ip, $port, $ping ) = $this->getSearchHostInfo ( false );
		return (! $port) ? false : true;
	}
	function getFunctionsInfo() {
		$fsockopen = $this->getTips ( (function_exists ( 'fsockopen' )) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE );
		$parse_url = $this->getTips ( (function_exists ( 'parse_url' )) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE );
		$isgethostbyname = function_exists ( 'gethostbyname' ) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE;
		$gethostbyname = $this->getTips ( $isgethostbyname );
		return array ($fsockopen, $parse_url, $isgethostbyname, $gethostbyname );
	}
	function getSearchHostInfo($show = true) {
		return $this->getHostInfo ( $this->getYunSearchHost (), $show );
	}
	function getDefendHostInfo($show = true) {
		return $this->getHostInfo ( $this->getYunDunHost (), $show );
	}
	function getHostInfo($host, $show = true) {
		$ip = function_exists ( 'gethostbyname' ) ? gethostbyname ( $host ) : '0.0.0.0';
		$port = $this->checkHostConnection ( $host );
		$ping = ($port) ? $this->getHostConnectTime ( $host ) : YUN_APPLY_FALSE;
		($ping > 2) && $this->setYunDefendModel ( $ping );
		return array ($host, $ip, (($show) ? $this->getPortStatus ( $port ) : $port), $ping );
	}
	function setYunDefendModel($speed) {
		$model = array ();
		$model ['userdefend_model'] = ($speed > 2) ? 100 : 0;
		$model ['postdefend_model'] = ($speed > 5) ? 100 : 0;
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		$this->setYunMode ( array_merge ( ( array ) $yunModel, $model ) );
		return true;
	}
	function setYunMode($model) {
		$settingService = $this->getPlatformSettingService ();
		return $settingService->setYunModel ( trim ( $model ) );
	}
	function checkHostConnection($host) {
		$fp = @fsockopen ( $host, 80, $errnum, $errstr, 5 );
		$result = (! $fp) ? false : true;
		@fclose ( $fp );
		return $result;
	}
	function getHostConnectTime($host) {
		$time_start = $this->getMicrotime ();
		$result = $this->_sendPost ( $host, 'testsite', array ('siteurl' => CloudWind_getConfig ( 'g_bbsurl' ) ) );
		$time_end = $this->getMicrotime ();
		return number_format ( $time_end - $time_start, 4 );
	}
	function getMicrotime() {
		$t_array = explode ( ' ', microtime () );
		return $t_array [0] + $t_array [1];
	}
	function _sendPost($host, $action, $data = '', $timeout = 5) {
		return $this->sendPost ( "http://" . trim ( $host, "/" ) . "/index.php?c=apply&a=" . $action, $data, $timeout );
	}
	function getTips($bool, $content = '') {
		if (! in_array ( $bool, array (YUN_APPLY_FALSE, YUN_APPLY_TRUE ) )) {
			return '';
		}
		return $this->_iconTips [$bool] . (($content) ? $content : $this->_textTips [$bool]);
	}
	function getPortStatus($status) {
		return ($status) ? '正常' : '失败';
	}
	var $_status_apply = 100;
	var $_status_open = 101;
	var $_status_fail = 102;
	var $_status_reset = 200;
	var $_apply_result_success = 9;
	var $_apply_result_verify = 3;
	var $_iconTips = array ('<span class="error_span">&times;</span>', '<span class="correct_span">&radic;</span>' );
	var $_textTips = array ('不可用', '可用' );
}
