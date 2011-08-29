<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索工具类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class YUN_Tools {
	
	function _filterString($output, $length = 4000) {
		static $filters = array ('\n', '\t', '\r', "\n", "\t", "\r", "	", '&amp;', '&#160;', '&#10084;', '&nbsp;', "%3C", '&lt;', '>', "%3C", '&gt;', '<', '&quot;', '&#39;', '&nbsp;&nbsp;' ), $replace = array (' ' );
		$output = preg_replace ( array ('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '/&(?!(#[0-9]+|[a-z]+);)/is' ), array ('' ), $output );
		$output = preg_replace ( '/\s+/', ' ', $output );
		$output = strip_tags ( stripWindCode ( ($output) ) );
		$output = $this->_convertCharset ( str_replace ( $filters, $replace, $output ) );
		return $this->_splitString ( $output, $length );
	}
	
	function _splitString($string, $length = 4000) {
		if (strlen ( $string ) > $length) {
			return substr ( $string, 0, $length );
		}
		return $string;
	}
	
	function _convertCharset($text) {
		static $charset = null;
		if (! in_array ( $GLOBALS ['db_charset'], array ('utf8', 'utf-8' ) )) {
			if (! $charset) {
				require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
				$factory = new PW_YunExtendFactory ();
				$charset = $factory->getChineseService ( $GLOBALS ['db_charset'], 'utf8' );
			}
			return $charset->Convert ( $text );
		}
		return $text;
	}

}