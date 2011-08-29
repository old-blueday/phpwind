<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索路由服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class PW_YunRoute {
	function dispatch() {
		if ($this->_is_spider ()) {
			return exit ();
		}
		$this->_checkIP ();
		if (! $this->_verify ()) {
			return exit ( 'what do you want to do,not authority' );
		}
		list ( $page, $type, $doing, $hashid, $versionid, $starttime, $endtime, $minid, $maxid ) = $this->_getRequest ( array ('page', 'type', 'doing', 'hashid', 'versionid', 'starttime', 'endtime', 'minid', 'maxid' ) );
		$yunIndexService = $this->_getYunIndexService ();
		switch ($doing) {
			case 'add' :
				return $yunIndexService->getAddOutPut ( $type, $page, $starttime, $endtime );
				break;
			case 'addlist' :
				return $yunIndexService->createAddLists ( $type, $starttime, $endtime, $hashid );
				break;
			case 'delete' :
				return $yunIndexService->markLogs ( $type, $starttime, $endtime );
				break;
			case 'full' :
				return $yunIndexService->getFullOutPut ( $type, $page, $versionid );
				break;
			case 'list' :
				return $yunIndexService->createLists ( $type, $hashid );
				break;
			case 'detectid' :
				return $yunIndexService->detectIndex ( $type, $minid, $maxid );
				break;
			case 'fullalllist' :
				return $yunIndexService->createFullAllLists ( $hashid );
				break;
			case 'addalllist' :
				return $yunIndexService->createAllAddLists ( $starttime, $endtime, $hashid );
				break;
			case 'deleteall' :
				return $yunIndexService->markAllLogs ( $starttime, $endtime );
				break;
			default :
				return exit ( 'forbidden' );
				break;
		}
		return exit ( 'forbidden' );
	}
	function _verify() {
		list ( $hashid ) = $this->_getRequest ( array ('hashid' ) );
		if (! $hashid) {
			return false;
		}
		$verifyService = $this->_getVerifySettingService ();
		$setting = $verifyService->getVerifySetting ();
		if (! $setting || ! isset ( $setting ['vector'] ) || ! $setting ['cipher']) {
			return false;
		}
		$hashid = html_entity_decode ( $hashid );
		if (! $hashid) {
			return false;
		}
		$aesService = $this->_getAesService ();
		$key = $aesService->encrypt ( $setting ['vector'], $setting ['cipher'], 256 );
		if (! $key) {
			return false;
		}
		$plaintext = $aesService->strcode ( $hashid, $key, 'DECODE' );
		if (! $this->_checkRequestParams ( $plaintext )) {
			return false;
		}
		return true;
	}
	
	function _checkIP() {
		require_once R_P . "lib/cloudwind/yunhook.php";
		yun_hook_iphook ();
	}
	
	function _checkRequestParams($plaintext) {
		if (! $plaintext) {
			return false;
		}
		$prarms = explode ( "&", $plaintext );
		foreach ( $prarms as $param ) {
			list ( $key, $value ) = explode ( "=", $param );
			if (! isset ( $_GET [$key] ) || $_GET [$key] != $value) {
				return false;
			}
		}
		return true;
	}
	
	function _filterHashId($text) {
		return htmlspecialchars ( strip_tags ( $text ) );
	}
	
	function _is_spider() {
		$user_agent = strtolower ( $_SERVER ['HTTP_USER_AGENT'] );
		$allow_spiders = array ('Baiduspider', 'Googlebot' );
		foreach ( $allow_spiders as $spider ) {
			$spider = strtolower ( $spider );
			if (strpos ( $user_agent, $spider ) !== false) {
				return true;
			}
		}
		return false;
	}
	function _getYunIndexService() {
		require_once R_P . 'lib/cloudwind/yunindex.class.php';
		return new PW_YunIndex ();
	}
	function _getVerifySettingService() {
		$factory = $this->_getYunExtendFactory ();
		return $factory->getVerifySettingService ();
	}
	function _getAesService() {
		$factory = $this->_getYunExtendFactory ();
		return $factory->getAesService ();
	}
	function _getYunExtendFactory() {
		static $factory = null;
		if (! $factory) {
			require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
			$factory = new PW_YunExtendFactory ();
		}
		return $factory;
	}
	function _getRequest($array) {
		InitGP ( $array );
		$tmp = array ();
		foreach ( $array as $key ) {
			$tmp [] = $GLOBALS [$key];
		}
		return $tmp;
	}
}