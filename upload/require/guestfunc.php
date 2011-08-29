<?php
!function_exists('readover') && exit('Forbidden');

function getguestcache() {
	global $fid,$tid,$timestamp,$db_guestdir,$db_guestindex,$db_guestthread,$db_guestread;
	$page = (int)$_GET['page'];
	switch (SCR) {
		case 'thread' :
			empty($page) && $page=1;
			if (file_exists(D_P."$db_guestdir/T_{$fid}/{$fid}_{$page}.html") && $timestamp-pwFilemtime(D_P."$db_guestdir/T_{$fid}/{$fid}_{$page}.html")<$db_guestthread) {
				readfile(D_P."$db_guestdir/T_{$fid}/{$fid}_{$page}.html");
				guestfooter();
			}
			break;
		case 'index' :
			$indexpath = getguestIndexpath();
			if (file_exists($indexpath) && $timestamp-pwFilemtime($indexpath)<$db_guestindex) {
				readfile($indexpath);
				guestfooter();
			}
			break;
		case 'read' :
			$tmp = 'R_'.intval($tid/500);
			!$page && $page=1;
			if (file_exists(D_P."$db_guestdir/$tmp/{$tid}_{$page}.html") && $timestamp-pwFilemtime(D_P."$db_guestdir/$tmp/{$tid}_{$page}.html")<$db_guestread) {
				readfile(D_P."$db_guestdir/$tmp/{$tid}_{$page}.html");
				echo "<script src=\"hitcache.php?tid=$tid\"></script>";
				guestfooter();
			}
			break;
	}
}
function creatguestcache($output) {
	global $fid,$tid,$timestamp,$db_guestdir,$page;

	switch (SCR) {
		case 'thread' :
			if (!is_dir(D_P."$db_guestdir/T_{$fid}")) {
				@mkdir(D_P."$db_guestdir/T_{$fid}");
				@chmod(D_P."$db_guestdir/T_{$fid}", 0777);
			}
			pwCache::writeover(D_P."$db_guestdir/T_{$fid}/{$fid}_{$page}.html",$output);
			break;
		case 'read' :
			$tmp = 'R_'.intval($tid/500);
			if (!is_dir(D_P."$db_guestdir/$tmp")) {
				@mkdir(D_P."$db_guestdir/$tmp");
				@chmod(D_P."$db_guestdir/$tmp", 0777);
			}
			pwCache::writeover(D_P."$db_guestdir/$tmp/{$tid}_{$page}.html",$output);
			break;
		case 'index' :
			$indexpath = getguestIndexpath();
			pwCache::writeover($indexpath,$output);
			break;
	}
}

function getguestIndexpath(){
	global $db_guestdir;
	$mode = S::getGP('m');
	$mode = ($mode && in_array($mode,array('bbs','area','o'))) ? $mode : '';
	return D_P."$db_guestdir/index".$mode.".html";
}

function clearguestcache($tid,$replies) {
	global $db_readperpage,$db_guestdir;
	$pages = ceil(($replies+1)/$db_readperpage);
	$tmp = 'R_'.intval($tid/500);
	for ($i=1;$i<=$pages;$i++) {
		if (file_exists(D_P."$db_guestdir/$tmp/{$tid}_{$i}.html")) {
			P_unlink(D_P."$db_guestdir/$tmp/{$tid}_{$i}.html");
		}
	}
}
function expireguestcache($expireSeconds = 86400) {
	global $timestamp, $db_guestdir;
	$dir = D_P . "$db_guestdir/";
	if ($dirHandler = opendir($dir)) {
		while (($file = readdir($dirHandler)) !== false) {
			$filePath = $dir . $file;
			if (is_file($filePath)) {
				if ($timestamp - pwFilemtime($filePath) > $expireSeconds) P_unlink($filePath);
			} elseif (is_dir($filePath) && false === strpos($filePath, ".")) {
				$subDir = $filePath . "/";
				$subDirHandler = opendir($subDir);
				while (($file = readdir($subDirHandler)) !== false) {
					$filePath = $subDir . $file;
					if (is_file($filePath)) {
						if ($timestamp - pwFilemtime($filePath) > $expireSeconds) P_unlink($filePath);
					}
				}
				closedir($subDirHandler);
			}
		}
		closedir($dirHandler);
	}
}

function guestfooter() {
	global $db_footertime,$db_obstart,$db_union,$P_S_T,$timestamp,$db;
	Update_ol();
	$wind_spend = '';
	if ($db_footertime == 1) {
		$totaltime	= number_format((pwMicrotime()-$P_S_T),6);
		$qn = $db ? $db->query_num : 0;
		$wind_spend	= "Total $totaltime(s) query $qn,";
	}
	$ft_time = get_date($timestamp,'m-d H:i');
	$ft_gzip = ($db_obstart ? 'Gzip enabled' : 'Gzip disabled').$db_union[3];
	$output	 = preg_replace("/<span id=\"windspend\"\>(.+?)<\/span>/is","<span id=\"windspend\">$wind_spend Time now is:$ft_time, $ft_gzip</span>",ob_get_contents());
	echo ObContents($output);
	unset($output);
	N_flush();
	exit;
}
?>