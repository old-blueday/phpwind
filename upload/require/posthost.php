<?php
!function_exists('readover') && exit('Forbidden');

function PostHost($host,$data='',$method='GET',$showagent=null,$port=null,$timeout=30){
	//Copyright (c) 2003-2103 phpwind
	$parse = @parse_url($host);
	if (empty($parse)) return false;
	if ((int)$port>0) {
		$parse['port'] = $port;
	} elseif (!$parse['port']) {
		$parse['port'] = '80';
	}
	$parse['host'] = str_replace(array('http://','https://'),array('','ssl://'),"$parse[scheme]://").$parse['host'];
	if (!$fp=@fsockopen($parse['host'],$parse['port'],$errnum,$errstr,$timeout)) {
		return false;
	}
	$method = strtoupper($method);
	$wlength = $wdata = $responseText = '';
	$parse['path'] = str_replace(array('\\','//'),'/',$parse['path'])."?$parse[query]";
	if ($method=='GET') {
		$separator = $parse['query'] ? '&' : '';
		substr($data,0,1)=='&' && $data = substr($data,1);
		$parse['path'] .= $separator.$data;
	} elseif ($method=='POST') {
		$wlength = "Content-length: ".strlen($data)."\r\n";
		$wdata = $data;
	}
	$write = "$method $parse[path] HTTP/1.0\r\nHost: $parse[host]\r\nContent-type: application/x-www-form-urlencoded\r\n{$wlength}Connection: close\r\n\r\n$wdata";
	@fwrite($fp,$write);
	while ($data = @fread($fp, 4096)) {
		$responseText .= $data;
	}
	@fclose($fp);
	empty($showagent) && $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
	return $responseText;
}
?>