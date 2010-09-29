<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename = "$admin_file?adminjob=guestdir";

if (!is_dir(D_P.$db_guestdir)) {
	adminmsg('gusetdir_not_exists');
}

if (empty($action)) {

	$f_num	= $f_size = $g_size = $g_num = 0;
	PwDir(D_P.$db_guestdir);

	$g_size = round($g_size/1048576,2);

	if ($g_num > 1000) {
		$g_num	= '>'.$g_num;
		$g_size = '>'.$g_size;
	}

	$fp = opendir(D_P.'data/bbscache');
	while ($file = readdir($fp)) {
		if ($file!='' && !in_array($file,array('.','..')) && preg_match('/^fcache\_\d+\_\d+\.php$/i',$file)) {
			++$f_num;
			$f_size += filesize(D_P.'data/bbscache/'.$file);
		}
		if ($f_num > 1000) break;
	}
	closedir($fp);

	$f_size = round($f_size/1048576,2);
	if ($f_num > 1000) {
		$f_num	= '>'.$f_num;
		$f_size = '>'.$f_size;
	}

	include PrintEot('guestdir');exit;

} elseif ($action == 'delete') {

	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('num','selid'));
	InitGP(array('step'),'GP',2);

	if (empty($selid)) {
		adminmsg('operate_error');
	}
	!is_numeric($num) && $num = 1000;
	$isnum	= 1;
	$path	= D_P.$db_guestdir;
	++$step;

	$fp = opendir($path);

	while ($file = readdir($fp)) {
		if ($file!='' && !in_array($file,array('.','..'))) {
			if (is_dir("$path/$file")) {
				if ($file[0]=='T' && $selid[2] || $file[0]=='R' && $selid[3]) {
					$fp1 = opendir("$path/$file");
					while ($file1 = readdir($fp1)) {
						if ($file1!='' && !in_array($file1,array('.','..'))) {
							++$isnum;
							P_unlink("$path/$file/$file1");
							if ($isnum > $num) break;
						}
					}
					closedir($fp1);
					rmdir("$path/$file");
				}
			} elseif ($selid[1]) {
				++$isnum;
				P_unlink("$path/$file");
			}
		}
		if ($isnum > $num) break;
	}
	closedir($fp);

	if ($isnum > $num) {
		$url = "$basename&action=delete&num=$num&step=$step";
		foreach ($selid as $key=>$value) {
			$url .= "&selid[$key]=$value";
		}
		$delnum = $num*$step;
		adminmsg('guestdir_delete',EncodeUrl($url),2);
	}

	adminmsg('operate_success');

} elseif ($action == 'delf') {

	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	InitGP(array('num'));
	InitGP(array('step'),'GP',2);

	!is_numeric($num) && $num = 1000;
	$step	= (int)$step;
	$isnum	= 1;
	$path	= D_P.'data/bbscache';
	++$step;

	$fp = opendir($path);

	while ($file = readdir($fp)) {
		if ($file!='' && !in_array($file,array('.','..')) && preg_match('/^fcache\_\d+\_\d+\.php$/i',$file)) {
			++$isnum;
			P_unlink("$path/$file");
		}
		if ($isnum > $num) break;
	}
	closedir($fp);

	if ($isnum > $num) {
		$url = "$basename&action=delf&num=$num&step=$step";
		$delnum = $num*$step;
		adminmsg('fcache_delete',EncodeUrl($url),2);
	}
	adminmsg('operate_success');
}

function PwDir($path) {
	global $g_num,$g_size;
	$fp = opendir($path);

	while ($file = readdir($fp)) {
		if ($file!='' && !in_array($file,array('.','..'))) {
			if (is_dir("$path/$file")) {
				PwDir("$path/$file");
			} else {
				++$g_num;
				$g_size += filesize("$path/$file");
			}
		}
		if ($g_num > 1000) break;
	}
	closedir($fp);
}
?>