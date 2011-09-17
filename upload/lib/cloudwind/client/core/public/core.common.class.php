<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
function CloudWind_sqlConnect($config) {
	require_once CLOUDWIND . '/client/core/public/core.dbconnect.class.php';
	$db = new CloudWind_Core_DbConnect ( $config ['host'], $config ['user'], $config ['pwd'], $config ['database'], $config ['pre'], $config ['charset'], $config ['pconnect'] );
	return $db;
}

function CloudWind_getConfig($key) {
	if (! isset ( $GLOBALS ['CloudWind_Configs'] ) || ! $GLOBALS ['CloudWind_Configs']) {
		$GLOBALS ['CloudWind_Configs'] = CloudWind_getConfigs ();
	}
	return isset ( $GLOBALS ['CloudWind_Configs'] [$key] ) ? $GLOBALS ['CloudWind_Configs'] [$key] : '';
}

function CloudWind_ipControl() {
	require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
	$factory = new CloudWind_Platform_Factory ();
	$controlService = $factory->getControlService ();
	if (! $controlService->ipControl ()) {
		return exit ( 'IP Forbidden' );
	}
	return true;
}

function CloudWind_getConfigs() {
	$configs = include (CLOUDWIND . '/version/' . CLOUDWIND_CLIENT_VERSION . '_config.php');
	return ($configs) ? $configs : array ();
}

function CloudWind_writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
	$fileName = CLOUDWIND_SECURITY_SERVICE::escapePath ( $fileName, $ifCheckPath );
	touch ( $fileName );
	$handle = fopen ( $fileName, $method );
	$ifLock && flock ( $handle, LOCK_EX );
	$writeCheck = fwrite ( $handle, $data );
	$method == 'rb+' && ftruncate ( $handle, strlen ( $data ) );
	fclose ( $handle );
	$ifChmod && @chmod ( $fileName, 0777 );
	return $writeCheck;
}

function CloudWind_readover($fileName, $method = 'rb') {
	$fileName = CLOUDWIND_SECURITY_SERVICE::escapePath ( $fileName );
	$data = '';
	if ($handle = @fopen ( $fileName, $method )) {
		flock ( $handle, LOCK_SH );
		$data = @fread ( $handle, filesize ( $fileName ) );
		fclose ( $handle );
	}
	return $data;
}

function CloudWind_getIp() {
	static $ip = null;
	if (!$ip) {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) && $_SERVER ['HTTP_X_FORWARDED_FOR'] && $_SERVER ['REMOTE_ADDR']) {
			if (strstr ( $_SERVER ['HTTP_X_FORWARDED_FOR'], ',' )) {
				$x = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
				$_SERVER ['HTTP_X_FORWARDED_FOR'] = trim ( end ( $x ) );
			}
			if (preg_match ( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
				$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
			}
		} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] ) && $_SERVER ['HTTP_CLIENT_IP'] && preg_match ( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER ['HTTP_CLIENT_IP'] )) {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
		}
		if (!$ip && preg_match ( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		!$ip && $ip = 'Unknown';
	}
	return $ip;
}

function CloudWind_buildSecutiryCode($string, $action = 'ENCODE') {
	$action != 'ENCODE' && $string = base64_decode ( $string );
	$code = '';
	$key = substr ( md5 ( CloudWind_getConfig ( 'yun_hash' ) ), 8, 18 );
	$keyLen = strlen ( $key );
	$strLen = strlen ( $string );
	for($i = 0; $i < $strLen; $i ++) {
		$k = $i % $keyLen;
		$code .= $string [$i] ^ $key [$k];
	}
	return ($action != 'DECODE' ? base64_encode ( $code ) : $code);
}

function CloudWind_unlink($fileName) {
	return @unlink ( CLOUDWIND_SECURITY_SERVICE::escapePath ( $fileName ) );
}

function CloudWind_filemtime($file) {
	return file_exists ( $file ) ? intval ( filemtime ( $file ) + $GLOBALS ['db_cvtime'] * 60 ) : 0;
}

function CloudWind_varExport($input, $indent = '') {
	switch (gettype ( $input )) {
		case 'string' :
			return "'" . str_replace ( array ("\\", "'" ), array ("\\\\", "\'" ), $input ) . "'";
		case 'array' :
			$output = "array(\r\n";
			foreach ( $input as $key => $value ) {
				$output .= $indent . "\t" . CloudWind_varExport ( $key, $indent . "\t" ) . ' => ' . CloudWind_varExport ( $value, $indent . "\t" );
				$output .= ",\r\n";
			}
			$output .= $indent . ')';
			return $output;
		case 'boolean' :
			return $input ? 'true' : 'false';
		case 'NULL' :
			return 'NULL';
		case 'integer' :
		case 'double' :
		case 'float' :
			return "'" . ( string ) $input . "'";
	}
	return 'NULL';
}

function CloudWind_getDirName($path = null) {
	if (! empty ( $path )) {
		if (strpos ( $path, '\\' ) !== false) {
			return substr ( $path, 0, strrpos ( $path, '\\' ) ) . '/';
		} elseif (strpos ( $path, '/' ) !== false) {
			return substr ( $path, 0, strrpos ( $path, '/' ) ) . '/';
		}
	}
	return './';
}

function CloudWind_stripCode($text) {
	$pattern = array ();
	if (strpos ( $text, "[post]" ) !== false && strpos ( $text, "[/post]" ) !== false) {
		$pattern [] = "/\[post\].+?\[\/post\]/is";
	}
	if (strpos ( $text, "[img]" ) !== false && strpos ( $text, "[/img]" ) !== false) {
		$pattern [] = "/\[img\].+?\[\/img\]/is";
	}
	if (strpos ( $text, "[hide=" ) !== false && strpos ( $text, "[/hide]" ) !== false) {
		$pattern [] = "/\[hide=.+?\].+?\[\/hide\]/is";
	}
	if (strpos ( $text, "[sell" ) !== false && strpos ( $text, "[/sell]" ) !== false) {
		$pattern [] = "/\[sell=.+?\].+?\[\/sell\]/is";
	}
	$pattern [] = "/\[[a-zA-Z]+[^]]*?\]/is";
	$pattern [] = "/\[\/[a-zA-Z]*[^]]\]/is";
	
	$text = preg_replace ( $pattern, '', $text );
	return trim ( $text );
}