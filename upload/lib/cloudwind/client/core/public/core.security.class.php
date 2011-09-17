<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CLOUDWIND_SECURITY_SERVICE {
	function isArray($params) {
		return (! is_array ( $params ) || ! count ( $params )) ? false : true;
	}
	function inArray($param, $params) {
		return (! in_array ( ( string ) $param, ( array ) $params )) ? false : true;
	}
	function htmlEscape($param) {
		return trim ( htmlspecialchars ( $param, ENT_QUOTES ) );
	}
	function stripTags($param) {
		return trim ( strip_tags ( $param ) );
	}
	function gp($keys, $method = null, $cvtype = 1, $istrim = true) {
		! is_array ( $keys ) && $keys = array ($keys );
		foreach ( $keys as $key ) {
			if ($key == 'GLOBALS')
				continue;
			$GLOBALS [$key] = NULL;
			if ($method != 'P' && isset ( $_GET [$key] )) {
				$GLOBALS [$key] = $_GET [$key];
			} elseif ($method != 'G' && isset ( $_POST [$key] )) {
				$GLOBALS [$key] = $_POST [$key];
			}
			if (isset ( $GLOBALS [$key] ) && ! empty ( $cvtype ) || $cvtype == 2) {
				$GLOBALS [$key] = CLOUDWIND_SECURITY_SERVICE::escapeChar ( $GLOBALS [$key], $cvtype == 2, $istrim );
			}
		}
	}
	function getGP($key, $method = null) {
		if ($method == 'G' || $method != 'P' && isset ( $_GET [$key] )) {
			return $_GET [$key];
		}
		return $_POST [$key];
	}
	function escapePath($fileName, $ifCheck = true) {
		if (! CLOUDWIND_SECURITY_SERVICE::_escapePath ( $fileName, $ifCheck )) {
			exit ( 'Forbidden' );
		}
		return $fileName;
	}
	function _escapePath($fileName, $ifCheck = true) {
		$tmpname = strtolower ( $fileName );
		$tmparray = array ('://', "\0" );
		$ifCheck && $tmparray [] = '..';
		if (str_replace ( $tmparray, '', $tmpname ) != $tmpname) {
			return false;
		}
		return true;
	}
	function escapeDir($dir) {
		$dir = str_replace ( array ("'", '#', '=', '`', '$', '%', '&', ';' ), '', $dir );
		return rtrim ( preg_replace ( '/(\/){2,}|(\\\){1,}/', '/', $dir ), '/' );
	}
	function escapeChar($mixed, $isint = false, $istrim = false) {
		if (is_array ( $mixed )) {
			foreach ( $mixed as $key => $value ) {
				$mixed [$key] = CLOUDWIND_SECURITY_SERVICE::escapeChar ( $value, $isint, $istrim );
			}
		} elseif ($isint) {
			$mixed = ( int ) $mixed;
		} elseif (! is_numeric ( $mixed ) && ($istrim ? $mixed = trim ( $mixed ) : $mixed) && $mixed) {
			$mixed = CLOUDWIND_SECURITY_SERVICE::escapeStr ( $mixed );
		}
		return $mixed;
	}
	function escapeStr($string) {
		$string = str_replace ( array ("\0", "%00", "\r" ), '', $string );
		$string = preg_replace ( array ('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '/&(?!(#[0-9]+|[a-z]+);)/is' ), array ('', '&amp;' ), $string );
		$string = str_replace ( array ("%3C", '<' ), '&lt;', $string );
		$string = str_replace ( array ("%3E", '>' ), '&gt;', $string );
		$string = str_replace ( array ('"', "'", "\t", '  ' ), array ('&quot;', '&#39;', '    ', '&nbsp;&nbsp;' ), $string );
		return $string;
	}
	function checkVar(&$var) {
		if (is_array ( $var )) {
			foreach ( $var as $key => $value ) {
				CLOUDWIND_SECURITY_SERVICE::checkVar ( $var [$key] );
			}
		} else {
			$var = str_replace ( array ('..', ')', '<', '=' ), array ('&#46;&#46;', '&#41;', '&#60;', '&#61;' ), $var );
		}
	}
	function slashes(&$array) {
		if (is_array ( $array )) {
			foreach ( $array as $key => $value ) {
				if (is_array ( $value )) {
					CLOUDWIND_SECURITY_SERVICE::slashes ( $array [$key] );
				} else {
					$array [$key] = addslashes ( $value );
				}
			}
		}
	}
	function getServer($keys) {
		$server = array ();
		$array = ( array ) $keys;
		foreach ( $array as $key ) {
			$server [$key] = NULL;
			if (isset ( $_SERVER [$key] )) {
				$server [$key] = str_replace ( array ('<', '>', '"', "'", '%3C', '%3E', '%22', '%27', '%3c', '%3e' ), '', $_SERVER [$key] );
			}
		}
		return is_array ( $keys ) ? $server : $server [$keys];
	}
	function sqlEscape($var, $strip = true, $isArray = false) {
		if (is_array ( $var )) {
			if (! $isArray)
				return " '' ";
			foreach ( $var as $key => $value ) {
				$var [$key] = trim ( CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $value, $strip ) );
			}
			return $var;
		} elseif (is_numeric ( $var )) {
			return " '" . $var . "' ";
		} else {
			return " '" . addslashes ( $strip ? stripslashes ( $var ) : $var ) . "' ";
		}
	}
	function sqlImplode($array, $strip = true) {
		return implode ( ',', CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $array, $strip, true ) );
	}
	function sqlSingle($array, $strip = true) {
		if (! CLOUDWIND_SECURITY_SERVICE::isArray ( $array ))
			return '';
		$array = CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $array, $strip, true );
		$str = '';
		foreach ( $array as $key => $val ) {
			$str .= ($str ? ', ' : ' ') . CLOUDWIND_SECURITY_SERVICE::sqlMetadata ( $key ) . '=' . $val;
		}
		return $str;
	}
	function sqlMulti($array, $strip = true) {
		if (! CLOUDWIND_SECURITY_SERVICE::isArray ( $array ))
			return '';
		$str = '';
		foreach ( $array as $val ) {
			if (! empty ( $val ) && CLOUDWIND_SECURITY_SERVICE::isArray ( $val )) {
				$str .= ($str ? ', ' : ' ') . '(' . CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $val, $strip ) . ') ';
			}
		}
		return $str;
	}
	function sqlLimit($start, $num = false) {
		return ' LIMIT ' . ($start <= 0 ? 0 : ( int ) $start) . ($num ? ',' . abs ( $num ) : '');
	}
	function sqlMetadata($data, $tlists = array()) {
		if (empty ( $tlists ) || ! CLOUDWIND_SECURITY_SERVICE::inArray ( $data, $tlists )) {
			$data = str_replace ( array ('`', ' ' ), '', $data );
		}
		return ' `' . $data . '` ';
	}
}