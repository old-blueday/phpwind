<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_General_Tools {
	
	function _filterString($output, $length = 4000) {
		static $filters = array ('\n', '\t', '\r', "\n", "\t", "\r", "	", '&amp;', '&#160;', '&#10084;', '&nbsp;', "%3C", '&lt;', '>', "%3C", '&gt;', '<', '&quot;', '&#39;', '&nbsp;&nbsp;' ), $replace = array (' ' );
		$output = preg_replace ( array ('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '/&(?!(#[0-9]+|[a-z]+);)/is' ), array ('' ), $output );
		$output = preg_replace ( '/\s+/', ' ', $output );
		$output = strip_tags ( CloudWind_stripCode ( ($output) ) );
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
		if (! in_array ( CloudWind_getConfig ('g_charset'), array ('utf8', 'utf-8' ) )) {
			if (! $charset) {
				require_once CLOUDWIND . '/client/core/public/core.factory.class.php';
				$factory = new CloudWind_Core_Factory ();
				$charset = $factory->getChineseService ( CloudWind_getConfig ('g_charset'), 'utf8' );
			}
			return $charset->Convert ( $text );
		}
		return $text;
	}

}