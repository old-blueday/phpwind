<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 云服务检测类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-3-25
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
define ( 'YUN_APPLY_RETRY', 0 );
define ( 'YUN_APPLY_TRUE', 1 );
define ( 'YUN_APPLY_FALSE', 0 );
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class Yun_CheckServer extends PW_YunBase {
	function getSiteInfo() {
		$settingService = $this->getYunSettingService ();
		return array ($GLOBALS ['db_bbsname'], $GLOBALS ['db_bbsurl'], WIND_VERSION, $settingService->getCloudWindVersion () );
	}
	function checkCloudWind() {
		if (YUN_APPLY_RETRY) {
			return YUN_APPLY_FALSE;
		}
		$setting = $this->getYunSetting ();
		if (isset ( $setting ['uniqueid'] ) && $setting ['uniqueid'] && isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_open) {
			return $this->_apply_result_success;
		}
		if (isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_fail) {
			return $this->_apply_result_verify;
		}
		if (isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_reset) {
			$settingService = $this->getYunSettingService ();
			$settingService->resetSetting ();
			return YUN_APPLY_FALSE;
		}
		return (isset ( $setting ['cloudstatus'] ) && $setting ['cloudstatus'] == $this->_status_apply) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE;
	}
	function checkHost() {
		$host = $this->getYunHost ();
		$result = $this->_sendPost ( $host, 'checksite', array ('siteurl' => $GLOBALS ['db_bbsurl'] ) );
		return (intval ( $result ) == YUN_APPLY_TRUE) ? true : false;
	}
	function getSiteScale() {
		$setting = $this->getYunSetting ();
		if ($setting ['sitescale'] == 101) {
			return true;
		}
		$count = $GLOBALS ['db']->get_one ( "SELECT count(*) as count FROM pw_threads " );
		return ($count ['count'] >= ((isset ( $setting ['sitelimit'] )) ? $setting ['sitelimit'] : 5000)) ? true : false;
	}
	function getDunDescribe() {
		$setting = $this->getYunSetting ();
		return isset ( $setting ['dundescribe'] ) ? html_entity_decode ( $setting ['dundescribe'] ) : '';
	}
	function getYunDescribe() {
		$setting = $this->getYunSetting ();
		return isset ( $setting ['yundescribe'] ) ? html_entity_decode ( $setting ['yundescribe'] ) : '';
	}
	function getBaseDescription() {
		$setting = $this->getYunSetting ();
		return isset ( $setting ['description'] ) ? html_entity_decode ( $setting ['description'] ) : '';
	}
	function identifySite($mark) {
		if (! $mark) {
			return false;
		}
		$setting = $this->getYunSetting ();
		return ($setting && isset ( $setting ['marksite'] ) && $setting ['marksite'] == $mark) ? YUN_APPLY_TRUE : YUN_APPLY_FALSE;
	}
	function markSite($isMark = true) {
		$settingService = $this->getYunSettingService ();
		$marksite = rand ( 10000000, 99999999 );
		$result = $settingService->updateSetting ( array ('marksite' => $marksite, 'cloudstatus' => (($isMark) ? $this->_status_apply : $this->_status_fail) ) );
		return ($result) ? $marksite : YUN_APPLY_FALSE;
	}
	function getYunSearchManageUrl() {
		$db_bbsurl = ($GLOBALS ['db_bbsurl']) ? $GLOBALS ['db_bbsurl'] : 'http://' . $_SERVER ['HTTP_HOST'];
		$params = array ('pw_sig' => $GLOBALS ['timestamp'], 'pw_bbsurl' => $GLOBALS ['db_bbsurl'], 'pw_bbsname' => $GLOBALS ['db_bbsname'], 'pw_charset' => $GLOBALS ['db_charset'], 'pw_sitehash' => $GLOBALS ['db_yunsearch_hash'] );
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
		$ip = gethostbyname ( $host );
		$port = $this->checkHostConnection ( $host );
		$ping = ($port) ? $this->getHostConnectTime ( $host ) : YUN_APPLY_FALSE;
		($ping > 2) && $this->setYunDefendModel ( $ping );
		return array ($host, $ip, (($show) ? $this->getPortStatus ( $port ) : $port), $ping );
	}
	function setYunDefendModel($speed) {
		$model = array ();
		$model ['userdefend_model'] = ($speed > 2) ? 100 : 0;
		$model ['postdefend_model'] = ($speed > 5) ? 100 : 0;
		$this->setYunMode ( array_merge ( ( array ) $GLOBALS ['db_yun_model'], $model ) );
		return true;
	}
	function setYunMode($model) {
		setConfig ( 'db_yun_model', $model );
		updatecache_c ();
		return true;
	}
	function checkHostConnection($host) {
		$fp = @fsockopen ( $host, 80, $errnum, $errstr, 5 );
		$result = (! $fp) ? false : true;
		@fclose ( $fp );
		return $result;
	}
	function getHostConnectTime($host) {
		$time_start = $this->getMicrotime ();
		$result = $this->_sendPost ( $host, 'testsite', array ('siteurl' => $GLOBALS ['db_bbsurl'] ) );
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
