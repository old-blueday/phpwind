<?php
!defined('P_W') && exit('Forbidden');
define('DOWNLOAD_TEMP_PATH', D_P . 'data/tmp');
define('DOWNLOAD_FILE_PREFIX', 'rp_');
//L::loadClass('upload');
class PwDownload {
	
	function getRemoteFiles($urls, &$bhv){
		$downloaddb = array();
		if (!S::isArray($urls)) return false;
		foreach ($urls as $k=>$v) {
			if(false == ($download = PwDownload::download($v,$bhv))) continue;
			$downloaddb[$k] = $download;
		}
		$bhv->update($downloaddb);
		return $downloaddb;
	}
	
	/**
	 * 下载一个远程文件
	 * @param string $url
	 */
	function download($url,$bhv){
		if (!$url || !PwDownload::checkUrl($url)) return false;
		$filetype = PwDownload::getFileExt($url);
		$filename = PwDownload::getFileName($url);
		if (!$filetype || !$filename) return false;
		//文件类型判定
		if (!$filetype || !isset($bhv->ftype[$filetype])) return false;
		$fileContent = PwDownload::getContents($url);
		//文件尺寸判定
		$fileSize = strlen($fileContent);
		if ($fileSize < 1 || $fileSize > $bhv->ftype[$filetype] * 1024) {
			unset($fileContent,$fileSize);
			return false;
		}
		//init
		$array = array(
			'id'	=>	0,
			'attname' => 'download',
			'name' => $filename,
			'size' => intval($fileSize),
			'type' => 'zip',
			'ifthumb' => 0,
			'fileuploadurl' => '',
			'ext'	=> $filetype
		);
		//保存
		list($saveFilename, $saveDir) = $bhv->getFilePath($array);
		$source = PwUpload::savePath($bhv->ifftp, $saveFilename, $saveDir);
		$tmpname = tempnam(DOWNLOAD_TEMP_PATH, DOWNLOAD_FILE_PREFIX);
		writeover($tmpname, $fileContent);
		if (!PwDownload::downloadMove($tmpname, $source)) {
			showUploadMsg('upload_error');
		}
		$array['fileuploadurl'] = $saveDir . $saveFilename;
		PwUpload::operateAttach($source, $saveFilename, $saveDir, $array, $bhv);
		return $array;
	}
	
	function getContents($url){
		$parseUrl = parse_url($url);
		if ($parseUrl['scheme'] == 'http' && function_exists('fsockopen')) {
			$responseText = ''; 
			if (!$fp=@fsockopen($parseUrl['host'],80,$errnum,$errstr,3)) {
				return $responseText;
			}
			$str = 'GET '.$parseUrl['path'];
			$parseUrl['query'] && $str .= "?{$parseUrl['query']}";
			$str .= " HTTP/1.1\r\n";
			$str .= "Host: {$parseUrl['host']}\r\n";
			$str .= "Referer: http://{$parseUrl['host']}\r\n";
			$ua = 'phpwind' . WIND_VERSION;
			$str .= "User-Agent: $ua\r\n";
			$str .= "Cache-Control: no-cache\r\n";
			$str .= "Connection: close\r\n\r\n";
			@fwrite($fp,$str);
			while ($data = @fread($fp, 4096)) {
				$responseText .= $data;
			}
			$responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
			return $responseText;
		} else {
			return file_get_contents($url);
		}
	}
	
	function checkUrl($url){
		global $db_bbsurl,$db_ftpweb;
		$parseUrl = parse_url(strtolower($url));
		if (!$parseUrl['host'] || !in_array($parseUrl['scheme'], array('http','https','ftp'))) return false;
		if ($db_ftpweb && false !== strpos($db_ftpweb,$parseUrl['host'])) return false;
		if (false !== strpos($parseUrl['host'],$db_bbsurl)) return false;
		return true;
	}
	function downloadMove($tmp_name, $filename){
		if (strpos($filename, '..') !== false || strpos($filename, '.php.') !== false || eregi("\.php$", $filename)) {
			exit('illegal file type!');
		}
		PwUpload::createFolder(dirname($filename));
		if (@rename($tmp_name, $filename)) {
			@chmod($filename, 0777);
			return true;
		} elseif (@copy($tmp_name, $filename)) {
			@chmod($filename, 0777);
			@unlink($tmp_name);
			return true;
		} elseif (is_readable($tmp_name)) {
			pwCache::writeover($filename, readover($tmp_name));
			if (file_exists($filename)) {
				@chmod($filename, 0777);
				@unlink($tmp_name);
				return true;
			}
		}
		return false;
	}
	
	function getFileExt($filename) {
		$filename = trim($filename);
		if (false !== ($pos = strpos($filename, '?')))
			$filename = substr($filename,0,$pos);
		return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 4)));
	}
	
	function getFileName($filename,$suffix = null) {
		return $suffix ? addslashes(basename($filename,$suffix)) : addslashes(basename($filename));
	}
}