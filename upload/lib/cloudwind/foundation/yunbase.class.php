<?php
/**
 * 云服务基础服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
require_once R_P . 'lib/cloudwind/foundation/yuntoolkit.class.php';
class PW_YunBase {
	
	var $extendFactory = null;
	var $services = array ();
	
	function getRequest($array) {
		InitGP ( $array );
		$tmp = array ();
		foreach ( $array as $key ) {
			$tmp [] = $GLOBALS [$key];
		}
		return $tmp;
	}
	
	function buildQuery($params) {
		return PW_YunToolKit::buildQuery ( $params );
	}
	
	function sendPost($host, $data, $timeout = 10) {
		$factory = $this->getYunExtendFactory ();
		$httpClientService = $factory->getHttpClientService ();
		return $httpClientService->post ( $host, $data, $timeout );
	}
	
	function getYunHost() {
		$settingService = $this->getYunSettingService ();
		return $settingService->getYunHost ();
	}
	
	function getYunSearchHost() {
		$settingService = $this->getYunSettingService ();
		return $settingService->getSearchHost ();
	}
	
	function getYunDunHost() {
		$settingService = $this->getYunSettingService ();
		return $settingService->getDefendHost ();
	}
	
	function getYunSetting() {
		$settingService = $this->getYunSettingService ();
		return $settingService->getSetting ();
	}
	
	function getYunSettingService() {
		$factory = $this->getYunExtendFactory ();
		return $factory->getYunSettingService ();
	}
	
	function convertCharset($fields) {
		if (! $fields) {
			return array ();
		}
		if (in_array ( $GLOBALS ['db_charset'], array ('utf8', 'utf-8' ) )) {
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
			$factory = $this->getYunExtendFactory ();
			$charset = $factory->getChineseService ( $GLOBALS ['db_charset'], 'utf8' );
		}
		return $charset->Convert ( $text );
	}
	
	function getYunExtendFactory() {
		if (! $this->extendFactory) {
			require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
			$this->extendFactory = new PW_YunExtendFactory ();
		}
		return $this->extendFactory;
	}
	
	function getYunDefendService() {
		if (! isset ( $this->services ['YunDefendService'] ) || ! $this->services ['YunDefendService']) {
			require_once R_P . 'lib/cloudwind/defend/yundefend.class.php';
			$this->services ['YunDefendService'] = new PW_YunDefend ();
		}
		return $this->services ['YunDefendService'];
	}
}