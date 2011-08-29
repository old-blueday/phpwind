<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 通用补丁包扩展函数与类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-04-12
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */

function pwFilemtime($file) {
	return file_exists ( $file ) ? intval ( filemtime ( $file ) + $GLOBALS ['db_cvtime'] * 60 ) : 0;
}

function stripWindCode($text) {
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

class L {
	function forum($fid) {
		include_once D_P . 'data/bbscache/forum_cache.php';
		return ($forum && isset ( $forum [$fid] )) ? $forum [$fid] : array ();
	}
}

function setConfig($key, $value, $decrip = null, $hk = false) {
	global $db;
	$vtype = 'string';
	$strip = true;
	if (is_array ( $value )) {
		$value = serialize ( $value );
		$vtype = 'array';
		$strip = false;
	}
	$pwSQL = array ();
	isset ( $decrip ) && $pwSQL ['decrip'] = $decrip;
	
	if ($hk) {
		$pwSQL ['hk_value'] = $value;
		$db->pw_update ( "SELECT * FROM pw_hack WHERE hk_name=" . pwEscape ( $key ), "UPDATE pw_hack SET " . pwSqlSingle ( $pwSQL, $strip ) . ' WHERE hk_name=' . pwEscape ( $key ), "INSERT INTO pw_hack SET " . pwSqlSingle ( array_merge ( array ('hk_name' => $key ), $pwSQL ), $strip ) );
	} else {
		$pwSQL ['db_value'] = $value;
		$db->pw_update ( "SELECT * FROM pw_config WHERE db_name=" . pwEscape ( $key ), "UPDATE pw_config SET " . pwSqlSingle ( $pwSQL, $strip ) . ' WHERE db_name=' . pwEscape ( $key ), "INSERT INTO pw_config SET " . pwSqlSingle ( array_merge ( array ('db_name' => $key ), $pwSQL ), $strip ) );
	}
}

?>