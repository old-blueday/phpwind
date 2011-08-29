<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 验证服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-3-25
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yuntoolkit.class.php';
class PW_VerifySetting {
	
	function initVerifySetting($vector, $cipher, $hash) {
		if (! $vector || ! $cipher || ! $hash) {
			return false;
		}
		$id = 1;
		$vector = trim ( $vector );
		$cipher = trim ( $cipher );
		$hash = trim ( $hash );
		$logSettingDao = $this->_getLogSettingDao ();
		$logSettingDao->replace ( $id, $vector, $cipher, $hash );
		$result = $this->_resetSettingCache ();
		return ($result) ? true : false;
	
	}
	function getVerifySetting() {
		return $this->_getVerifySettingCache ();
	}
	
	function _getVerifySettingCache() {
		$path = $this->_getVerifySettingPath ();
		if (! is_file ( $path ) || ! isset ( $GLOBALS ['db_yunsearch_hash'] ) || ! ($config = include ($path))) {
			$config = $this->_resetSettingCache ();
		}
		return $config;
	}
	
	function _resetSettingCache() {
		$config = $this->getVerifySettingNoCache ();
		if (! $config) {
			return array ();
		}
		$output = "<?php\r\n! defined ( 'P_W' ) && exit ( 'Forbidden' );\r\n";
		$output .= "return " . pw_var_export ( $config ) . ";\r\n?>";
		writeover ( $this->_getVerifySettingPath (), $output, 'w' );
		isset ( $config ['field1'] ) && $this->_setSearchHash ( $config ['field1'] );
		return $config;
	}
	
	public function clearSettingCache() {
		$this->_setSearchHash ( '' );
		$this->setSearchHook ( 0 );
		$this->setSearchDomain ( '' );
		$this->setSearchOpen ( 0 );
		$this->setYunModel ( array () );
		$this->setYunExpand ( array () );
		P_unlink ( $this->_getVerifySettingPath () );
		return true;
	}
	
	function _getVerifySettingPath() {
		return D_P . 'data/bbscache/yunverifysetting.php';
	}
	
	function _setSearchHash($hash) {
		$this->_setConfig ( 'db_yunsearch_hash', $hash );
	}
	
	function setSearchHook($hook) {
		$this->_setConfig ( 'db_yunsearch_hook', $hook );
		return true;
	}
	
	function setSearchDomain($domain) {
		$this->_setConfig ( 'db_yunsearch_domain', $domain );
		return true;
	}
	
	function setSearchOpen($isopen) {
		$this->_setConfig ( 'db_yunsearch_isopen', intval ( $isopen ) );
		return true;
	}
	
	function setYunModel($model) {
		$model = ($model) ? PW_YunToolKit::stringToArray ( $model ) : array ();
		if (! is_array ( $model )) {
			return false;
		}
		$this->_setConfig ( 'db_yun_model', array_merge ( ( array ) $GLOBALS ['db_yun_model'], $model ) );
		return true;
	}
	
	function setYunExpand($expand) {
		$expand = ($expand) ? PW_YunToolKit::stringToArray ( $expand ) : array ();
		if (! is_array ( $expand )) {
			return false;
		}
		$this->_setConfig ( 'db_yun_expand', array_merge ( ( array ) $GLOBALS ['db_yun_expand'], $expand ) );
		return true;
	}
	
	function _setConfig($key, $value) {
		require_once (R_P . 'admin/cache.php');
		setConfig ( $key, $value );
		updatecache_c ();
	}
	
	function getVerifySettingNoCache() {
		$logSettingDao = $this->_getLogSettingDao ();
		$setting = $logSettingDao->get ( 1 );
		return $setting;
	}
	
	function _getLogSettingDao() {
		static $sLogSettingDao;
		if (! $sLogSettingDao) {
			require_once R_P . 'lib/cloudwind/db/yun_logsettingdb.class.php';
			$sLogSettingDao = new PW_YUN_LogSettingDB ();
		}
		return $sLogSettingDao;
	}
}