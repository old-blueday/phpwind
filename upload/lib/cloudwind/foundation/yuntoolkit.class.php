<?php
/**
 * 云服务工具箱 静态服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class PW_YunToolKit {
	function buildQuery($params) {
		if (! $params || ! is_array ( $params )) {
			return '';
		}
		$query = '';
		foreach ( $params as $key => $value ) {
			$query .= "$key=" . urlencode ( trim ( (is_array ( $value ) ? base64_encode ( serialize ( $value ) ) : $value) ) ) . '&';
		}
		return $query;
	}
	
	function arrayToString($array) {
		return base64_encode ( serialize ( $array ) );
	}
	
	function stringToArray($array) {
		return unserialize ( base64_decode ( $array ) );
	}
}