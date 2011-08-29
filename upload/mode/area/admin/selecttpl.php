<?php
!defined('P_W') && exit('Forbidden');



$tplLib = array();
$tplPath = R_P.'mode/area/themes/';
if ($fp1 = opendir($tplPath)) {
	while ($tpldir = readdir($fp1)) {
		if (in_array($tpldir,array('.','..','admin','bbsindex','default'))) continue;
		if ($fp2 = opendir($tplPath.$tpldir)) {
			while ($file = readdir($fp2)) {
				if ($file == "." && $file == "..") continue;
				if ($file == 'main.htm') {
					$tplLib[] = array('dir'=>$tpldir,'preview'=>getPreviewImage($tpldir));
				}
			}
		}
	}
	closedir($fp);
}
include PrintMode('selecttpl');

function getPreviewImage($dir) {
	global $tplPath;
	$imagePath = S::escapePath($tplPath.$dir.'/images/preview/demo.jpg');
	return file_exists($imagePath) ? 'mode/area/themes/'.$dir.'/images/preview/demo.jpg' : 'images/100.jpg';
}
?>