<?php
/**
 * @author Xie Jin <xiejin@alibaba-inc.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
!defined('P_W') && exit('Forbidden');
if (!$db_jsifopen) {
	$showmsg = getLangInfo('other','js_close');
	exit("document.write(\"$showmsg\");");
}

S::gp(array('type'));
S::gp(array('id'),'',2);
if (!$id) exit('document.write("id is null");');
$invokeService = L::loadClass('invokeservice', 'area');
if (!$type) {
	$invokeInfo = $invokeService->getInvokeById($id);
	if (!$invokeInfo) exit('document.write("id is null");');
	if (!$invokeInfo['ifapi']) exit('document.write("<div style=\"background:#ffffe3;border:1px solid #cccccc;color:#333;padding:5px 10px;\">模块api未开放</div>");');
	$pieces = $invokeService->getInvokePieces($invokeInfo['name']);
	if (!$pieces) exit('document.write("id is null");');
	
	$tplGetData = L::loadClass('tplgetdata', 'area');
	$tplGetData->init($pieces);
	$invokeFile = $invokeService->getInvokeApiFile($id);
	if (!file_exists($invokeFile)) {
		pwCache::writeover($invokeFile, "<?php\r\nprint <<<EOT\r\n".$invokeInfo['parsecode']."\r\nEOT;\r\n?>");
	}
	include_once Pcv($invokeFile);
	$result = preg_replace("/\<a(\s*[^\>]+\s*)href=([\"|\'])([^\"\'>\s]+)(\\2)/ies","encodeApiUrl('\\3','\\1')" ,ob_get_contents());
	$result = str_replace(array('"',"\r","\n"),array('\"',"",""),$result);
	$result = ObContents('document.write("'.$result.'");');
	echo $result;
} elseif ($type == 'data') {
	$pieceInfo = $invokeService->getInvokePieceByInvokeId($id);
	if (!$pieceInfo) exit('document.write("id is null");');
	$invokeInfo = $invokeService->getInvokeByName($pieceInfo['invokename']);
	if (!$invokeInfo) exit('document.write("id is null");');
	if (!$invokeInfo['ifapi']) exit('document.write("<div style=\"background:#ffffe3;border:1px solid #cccccc;color:#333;padding:5px 10px;\">模块api未开放</div>");');

	$pieces = array();
	$pieces[$id] = $pieceInfo['title'];
	$tplGetData = L::loadClass('tplgetdata', 'area');
	$tplGetData->init($pieces);
	$result = pwTplGetData($pieceInfo['invokename'], $pieceInfo['title']);
	echo pwJsonEncode($result);
}
function encodeApiUrl($url,$addtion) {
	global $_mainUrl;
	$url = strpos($url, 'http:')===false ? $_mainUrl.'/'.$url:$url;
	return '<a '.stripslashes($addtion).' href="'.$url.'"';
}