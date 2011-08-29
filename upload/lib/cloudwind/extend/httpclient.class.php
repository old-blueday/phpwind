<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * httpClient服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class PW_HttpClient {
	function get($host, $data, $timeout = 5) {
		return PW_HttpClient::request ( $host, $data, 'GET', $timeout );
	}
	function post($host, $data, $timeout = 5) {
		return PW_HttpClient::request ( $host, $data, 'POST', $timeout );
	}
	function request($host, $data, $method = 'GET', $timeout = 5) {
		$data = PW_HttpClient::buildQuery ( $data );
		$parse = parse_url ( $host );
		$method = strtoupper ( $method );
		if (empty ( $parse ))
			return null;
		if (! isset ( $parse ['port'] ) || ! $parse ['port'])
			$parse ['port'] = '80';
		if (! in_array ( $method, array ('POST', 'GET' ) ))
			return null;
		
		$parse ['host'] = str_replace ( array ('http://', 'https://' ), array ('', 'ssl://' ), $parse ['scheme'] . "://" ) . $parse ['host'];
		if (! $fp = @fsockopen ( $parse ['host'], $parse ['port'], $errnum, $errstr, $timeout ))
			return null;
		
		$contentLength = '';
		$postContent = '';
		$query = isset ( $parse ['query'] ) ? $parse ['query'] : '';
		$parse ['path'] = str_replace ( array ('\\', '//' ), '/', $parse ['path'] ) . "?" . $query;
		if ($method == 'GET') {
			substr ( $data, 0, 1 ) == '&' && $data = substr ( $data, 1 );
			$parse ['path'] .= ($query ? '&' : '') . $data;
		} elseif ($method == 'POST') {
			$contentLength = "Content-length: " . strlen ( $data ) . "\r\n";
			$postContent = $data;
		}
		$write = $method . " " . $parse ['path'] . " HTTP/1.0\r\n";
		$write .= "Host: " . $parse ['host'] . "\r\n";
		$write .= "Content-type: application/x-www-form-urlencoded\r\n";
		$write .= $contentLength;
		$write .= "Connection: close\r\n\r\n";
		$write .= $postContent;
		@fwrite ( $fp, $write );
		
		$responseText = '';
		while ( $data = fread ( $fp, 4096 ) ) {
			$responseText .= $data;
		}
		@fclose ( $fp );
		$responseText = trim ( stristr ( $responseText, "\r\n\r\n" ), "\r\n" );
		return $responseText;
	}
	function buildQuery($params) {
		require_once R_P . 'lib/cloudwind/foundation/yuntoolkit.class.php';
		return PW_YunToolKit::buildQuery ( $params );
	}
}