<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云服务同步类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class PW_SyncRoute extends PW_YunBase {
	
	function dispatch() {
		if (! $GLOBALS ['db_yunsearch_search'] && ! $GLOBALS ['db_yundefend_shield']) {
			exit ( '1' );
		}
		$this->syncPost ();
		$this->syncData ();
		$this->syncUser ();
		$this->syncUserDefinedData ();
		exit ( '1' );
	}
	
	function syncData() {
		if (! $GLOBALS ['db_yunsearch_search'] || $GLOBALS ['db_yun_model'] ['search_model'] == 100) {
			return true;
		}
		$yunSyncService = $this->_getYunSyncService ();
		if (($data = $yunSyncService->syncData ())) {
			$setting = $this->getYunSetting ();
			$this->_sendPost ( array ('data' => $data, 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'] ), 'search' );
		}
		return true;
	}
	
	function syncPost() {
		if (! $GLOBALS ['db_yundefend_shield'] || $GLOBALS ['db_yun_model'] ['postdefend_model'] != 100) {
			return true;
		}
		$service = $this->_getYunDefendService ();
		return $service->syncDefend ();
	}
	
	function syncUser() {
		if (! $GLOBALS ['db_yundefend_shield'] || $GLOBALS ['db_yun_model'] ['userdefend_model'] != 100) {
			return true;
		}
		$userdefendDao = $this->getUserDefendDao ();
		$defends = $userdefendDao->getAll ();
		if (! $defends) {
			return false;
		}
		$data = array ();
		foreach ( $defends as $defend ) {
			$data [] = $defend ['data'];
		}
		$setting = $this->getYunSetting ();
		$this->_sendPost ( array ('data' => $data, 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'] ), 'batch' );
		$userdefendDao->deleteAll ();
		return true;
	}
	
	function syncUserDefinedData() {
		list ( $typename ) = $this->getRequest ( array ('typename' ) );
		if (! $typename) {
			return false;
		}
		$userDefinedService = $this->getYunUserDefinedService ();
		return $userDefinedService->postUserDefinedData ( $typename );
	}
	
	function getYunUserDefinedService() {
		require_once R_P . 'lib/cloudwind/userdefined.class.php';
		return new PW_UserDefined ();
	}
	
	function getUserDefendDao() {
		static $sUserDefendDao;
		if (! $sUserDefendDao) {
			require_once R_P . 'lib/cloudwind/db/yun_userdefenddb.class.php';
			$sUserDefendDao = new PW_YUN_UserDefendDB ();
		}
		return $sUserDefendDao;
	}
	
	function _sendPost($data, $action, $timeout = 5) {
		return $this->sendPost ( "http://" . trim ( $this->getYunDunHost (), "/" ) . "/defend.php?a=" . $action, $data, $timeout );
	}
	
	function _getYunSyncService() {
		require_once R_P . 'lib/cloudwind/search/yunsearchsync.class.php';
		return new PW_YunSearchSync ();
	}
	
	function _getYunDefendService() {
		require_once R_P . 'lib/cloudwind/defend/yundefend.class.php';
		return new PW_YunDefend ();
	}

}