<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 基础配置服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-3-25
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yuntoolkit.class.php';
class PW_YUNSetting {
	
	function updateSetting($fields) {
		$this->checkSetting ();
		$setting = $this->getSettingNoCache ();
		$fields = (is_array ( $fields )) ? $fields : $this->_arrayDecode ( $fields );
		$fields = (is_array ( $fields )) ? $this->_convert ( $fields ) : $fields;
		$fields = array_merge ( ((is_array ( $setting )) ? $setting : array ()), ((is_array ( $fields )) ? $fields : array ()) );
		$settingDao = $this->_getYunSettingDao ();
		$result = $settingDao->update ( array ('setting' => $this->_arrayEncode ( $fields ) ), 1 );
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
			$settingDao->replace ( 1, $this->_arrayEncode ( array () ) );
		}
		return true;
	}
	
	function _resetSettingCache() {
		$setting = $this->getSettingNoCache ();
		$setting = ($setting) ? $setting : array ();
		$output = "<?php\r\n! defined ( 'P_W' ) && exit ( 'Forbidden' );\r\n";
		$output .= "return " . pw_var_export ( $setting ) . ";\r\n?>";
		writeover ( $this->_getSettingPath (), $output, 'w' );
		return $setting;
	}
	
	function _getSettingPath() {
		return D_P . 'data/bbscache/yunbasesetting.php';
	}
	
	function getSettingNoCache() {
		$settingDao = $this->_getYunSettingDao ();
		$setting = $settingDao->get ( 1 );
		return ($setting && isset ( $setting ['setting'] )) ? $this->_arrayDecode ( $setting ['setting'] ) : array ();
	}
	
	function initSetting($setting) {
		$setting = ($setting) ? $setting : '';
		return $this->updateSetting ( $setting );
	}
	
	function getSetting() {
		$path = $this->_getSettingPath ();
		if (is_file ( $path )) {
			return include S::escapePath ( $path );
		}
		return $this->getSettingNoCache ();
	}
	
	function getYunHost() {
		return "cs11.phpwind.com";
	}
	
	function getSearchHost() {
		$setting = $this->getSetting ();
		return ($setting && isset ( $setting ['shost'] ) && $setting ['shost']) ? $setting ['shost'] : $this->getYunHost ();
	}
	
	function getDefendHost() {
		$setting = $this->getSetting ();
		return ($setting && isset ( $setting ['dhost'] ) && $setting ['dhost']) ? $setting ['dhost'] : 'dun10.phpwind.com';
	}
	
	function _arrayEncode($array) {
		return PW_YunToolKit::arrayToString ( $array );
	}
	
	function _arrayDecode($array) {
		return PW_YunToolKit::stringToArray ( $array );
	}
	
	function getCloudWindVersion() {
		return "cloudwind.v1.0";
	}
	
	function _convert($fields) {
		if (in_array ( $GLOBALS ['db_charset'], array ('utf8', 'utf-8' ) )) {
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
			require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
			$factory = new PW_YunExtendFactory ();
			$charset = $factory->getChineseService ( 'utf8', $GLOBALS ['db_charset'] );
		}
		return $charset->Convert ( $text );
	}
	
	function _getYunSettingDao() {
		static $sYunSettingDao;
		if (! $sYunSettingDao) {
			require_once R_P . 'lib/cloudwind/db/yun_settingdb.class.php';
			$sYunSettingDao = new PW_YUN_SettingDB ();
		}
		return $sYunSettingDao;
	}
}