<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.toolkit.class.php';
class CloudWind_Platform_VerifySetting {
	
	function initVerifySetting($vector, $cipher, $hash) {
		if (! $vector || ! $cipher || ! $hash) {
			return false;
		}
		list ( $id, $vector, $cipher, $hash ) = array (1, trim ( $vector ), trim ( $cipher ), trim ( $hash ) );
		$logSettingDao = $this->_getLogSettingDao ();
		$logSettingDao->replace ( $id, $vector, $cipher, $hash );
		$result = $this->_resetSettingCache ();
		return ($result) ? true : false;
	
	}
	function getVerifySetting($isCache = true) {
		if (! $isCache) {
			return $this->getVerifySettingNoCache ();
		}
		$path = $this->_getVerifySettingPath ();
		if (! is_file ( $path ) || ! ($config = include CLOUDWIND_SECURITY_SERVICE::escapePath ( $path ))) {
			$config = $this->_resetSettingCache ();
		}
		return $config;
	}
	
	function _resetSettingCache() {
		$config = $this->_getVerifySettingNoCache ();
		if (! $config) {
			return array ();
		}
		$output = "<?php\r\n! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );\r\n";
		$output .= "return " . CloudWind_varExport ( $config ) . ";\r\n?>";
		CloudWind_writeover ( $this->_getVerifySettingPath (), $output, 'w' );
		return $config;
	}
	
	function _getVerifySettingNoCache() {
		$logSettingDao = $this->_getLogSettingDao ();
		$setting = $logSettingDao->get ( 1 );
		return $setting;
	}
	
	function _getVerifySettingPath() {
		return CLOUDWIND_SECURITY_SERVICE::escapePath ( CloudWind_getConfig ( 'g_cachedir' ) . 'cloudwind_logsettings.php' );
	}
	
	function _getLogSettingDao() {
		require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
		$factory = new CloudWind_Dao_Factory ();
		return $factory->getPlatformLogSettingDao ();
	}
}