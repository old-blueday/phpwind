<?php
!defined('P_W') && exit('Forbidden');
/*
 * 文件操作类
 */
class PW_FileOperate{
	function copyFiles($source,$dest) {
		$source = S::escapePath($source);
		$dest	= S::escapePath($dest);
		$folder = opendir($source);
		while($file = readdir($folder)) {
			if ($file == '.' || $file == '..' || strpos($file,'.')===0) continue;
			if(is_dir($source.'/'.$file)) {
				PW_FileOperate::createFolder($dest.'/'.$file);
				PW_FileOperate::copyFiles($source.'/'.$file,$dest.'/'.$file);
			} else {
				PW_FileOperate::copyfile($source.'/'.$file,$dest.'/'.$file);
			}
		}
		closedir($folder);
		return 1;
	}
	function createFolder($path) {
		if (!is_dir($path)) {
			PW_FileOperate::createFolder(dirname($path));
			@mkdir($path);
			@chmod($path,0777);
		}
	}
	function deleteDir($dir) {
		$dir=S::escapePath($dir);
		while(!rmdir($dir)) {
			if (is_dir($dir)) {
				if ($dp = opendir($dir)) {
					while (($file=readdir($dp)) != false) {
						if (is_dir($dir.'/'.$file) && $file!='.' && $file!='..') {
							PW_FileOperate::deleteDir($dir.'/'.$file);
						} else {
							if($file!='.' && $file!='..') {
								P_unlink($dir.'/'.$file);
							}
						}
					}
					closedir($dp);
				} else {
					return false;
				}
			}
		}
	}

	function copyFile($source,$dest) {
		if (@copy($source,$dest)) return true;
		if (is_readable($source)) {
			writeover($dest,readover($source));
			if (file_exists($dest)) return true;
		}

		return false;
	}
}