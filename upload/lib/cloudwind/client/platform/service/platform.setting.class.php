<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.toolkit.class.php';
class CloudWind_Platform_Setting {
	
	function updateSetting($fields) {
		$this->checkSetting ();
		$setting = $this->getSettingNoCache ();
		$fields = (is_array ( $fields )) ? $fields : CloudWind_Core_ToolKit::stringToArray ( $fields );
		$fields = (is_array ( $fields )) ? $this->_convertCharsets ( $fields ) : $fields;
		$fields = array_merge ( ((is_array ( $setting )) ? $setting : array ()), ((is_array ( $fields )) ? $fields : array ()) );
		$settingDao = $this->_getYunSettingDao ();
		$result = $settingDao->update ( array ('setting' => CloudWind_Core_ToolKit::arrayToString ( $fields ) ), 1 );
		if ($result) {
			$this->_resetSettingCache ();
		}
		return $result;
	}
	
	function resetSetting() {
		$settingDao = $this->_getYunSettingDao ();
		$settingDao->update ( array ('setting' => array () ), 1 );
		$this->_resetSettingCache ();
		return true;
	}
	
	function checkSetting() {
		if (! ($this->getSettingNoCache ())) {
			$settingDao = $this->_getYunSettingDao ();
			$settingDao->replace ( 1, CloudWind_Core_ToolKit::arrayToString ( array () ) );
		}
		return true;
	}
	
	function _resetSettingCache() {
		$setting = $this->getSettingNoCache ();
		$setting = ($setting) ? $setting : array ();
		$output = "<?php\r\n! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );\r\n";
		$output .= "return " . CloudWind_varExport ( $setting ) . ";\r\n?>";
		CloudWind_writeover ( $this->_getSettingPath (), $output, 'w' );
		return $setting;
	}
	
	function _getSettingPath() {
		return CLOUDWIND_SECURITY_SERVICE::escapePath ( CloudWind_getConfig ( 'g_cachedir' ) . 'cloudwind_settings.php' );
	}
	
	function getSettingNoCache() {
		$settingDao = $this->_getYunSettingDao ();
		$setting = $settingDao->get ( 1 );
		return ($setting && isset ( $setting ['setting'] )) ? CloudWind_Core_ToolKit::stringToArray ( $setting ['setting'] ) : array ();
	}
	
	function initSetting($setting) {
		$setting = ($setting) ? $setting : '';
		if (! ($this->updateSetting ( $setting )) || ! ($this->setSearchHook ( 1 )) || ! ($this->setSearchOpen ( 1 ))) {
			return false;
		}
		return true;
	}
	
	function getSetting() {
		$path = $this->_getSettingPath ();
		if (is_file ( $path )) {
			return include CLOUDWIND_SECURITY_SERVICE::escapePath ( $path );
		}
		return $this->getSettingNoCache ();
	}
	
	function getYunHost() {
		require_once CLOUDWIND . '/client/core/public/core.define.class.php';
		return PLATFORM_HOST;
	}
	
	function getSearchHost() {
		$setting = $this->getSetting ();
		return ($setting && isset ( $setting ['shost'] ) && $setting ['shost']) ? $setting ['shost'] : $this->getYunHost ();
	}
	
	function getDefendHost() {
		require_once CLOUDWIND . '/client/core/public/core.define.class.php';
		$setting = $this->getSetting ();
		return ($setting && isset ( $setting ['dhost'] ) && $setting ['dhost']) ? $setting ['dhost'] : DEFEND_HOST;
	}
	
	function getCloudWindVersion() {
		require_once CLOUDWIND . '/client/core/public/core.define.class.php';
		return CLOUDWIND_VERSION;
	}
	
	function setYunHash($hash) {
		$this->_setSetting ( 'yun_hash', $hash );
		return true;
	}
	
	function setSearchHook($hook) {
		$this->_setSetting ( 'yunsearch_hook', $hook );
		return true;
	}
	
	function setSearchDomain($domain) {
		$this->_setSetting ( 'yunsearch_domain', $domain );
		return true;
	}
	
	function setSearchOpen($isopen) {
		$this->_setSetting ( 'yunsearch_isopen', intval ( $isopen ) );
		return true;
	}
	
	function setSearchUnique($unique) {
		$this->_setSetting ( 'yunsearch_unique', trim ( $unique ) );
		return true;
	}
	
	function setYunModel($model) {
		$model = ($model) ? CloudWind_Core_ToolKit::stringToArray ( $model ) : array ();
		if (! is_array ( $model )) {
			return false;
		}
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		$this->_setSetting ( 'yun_model', array_merge ( ( array ) $yunModel, $model ) );
		return true;
	}
	
	function setYunExpand($expand) {
		$expand = ($expand) ? CloudWind_Core_ToolKit::stringToArray ( $expand ) : array ();
		if (! is_array ( $expand )) {
			return false;
		}
		$yunExpand = CloudWind_getConfig ( 'yun_expand' );
		$this->_setSetting ( 'yun_expand', array_merge ( ( array ) $yunExpand, $expand ) );
		return true;
	}
	
	function _setSetting($key, $value) {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND_VERSION_DIR . '/service/platform.config.class.php';
			$service = new CloudWind_Platform_Config ();
		}
		return $service->setConfig ( $key, $value );
	}
	
	function _convertCharsets($fields) {
		if (in_array ( CloudWind_getConfig ( 'g_charset' ), array ('utf8', 'utf-8' ) )) {
			return $fields;
		}
		foreach ( $fields as $k => $v ) {
			$fields [$k] = $this->_convertCharset ( $v );
		}
		return $fields;
	}
	function _convertCharset($text) {
		static $charset = null;
		if (! $charset) {
			require_once CLOUDWIND . '/client/core/public/core.factory.class.php';
			$factory = new CloudWind_Core_Factory ();
			$charset = $factory->getChineseService ( 'utf8', CloudWind_getConfig ( 'g_charset' ) );
		}
		return $charset->Convert ( $text );
	}
	
	function _getYunSettingDao() {
		static $sYunSettingDao;
		if (! $sYunSettingDao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$factory = new CloudWind_Dao_Factory ();
			$sYunSettingDao = $factory->getPlatformSettingDao ();
		}
		return $sYunSettingDao;
	}
}