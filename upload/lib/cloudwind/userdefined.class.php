<?php
/**
 * 用户自定义云服务入口
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class PW_UserDefined {
	
	function postUserDefinedData($typeName) {
		$this->_checkIP ();
		if (! $typeName) {
			return false;
		}
		$filePath = R_P . 'lib/cloudwind/userdefined/' . $typeName . '.userdefined.php';
		if (! is_file ( $filePath )) {
			return false;
		}
		require_once $this->_getPath ( $filePath );
		$className = 'PW_' . ucfirst ( $typeName ) . '_UserDefined';
		if (! class_exists ( $className )) {
			return false;
		}
		$service = new $className ();
		if (! method_exists ( $service, 'sync' )) {
			return false;
		}
		return $service->sync ();
	}
	
	function _getPath($filepath) {
		if (str_replace ( array ('://', "\0" ), '', strtolower ( $filepath ) ) != strtolower ( $filepath )) {
			exit ( 'Forbidden' );
		}
		return $filepath;
	}
	
	function _checkIP() {
		require_once R_P . "lib/cloudwind/yunhook.php";
		yun_hook_iphook ();
	}
}