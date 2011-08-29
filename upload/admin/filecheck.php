<?php
!function_exists('adminmsg') && exit('Forbidden');
empty($adminitem) && $adminitem = 'chmod';
$jobUrl = "$admin_file?adminjob=filecheck";
$basename = "$admin_file?adminjob=filecheck&adminitem=$adminitem";

if ($adminitem == 'chmod') {
	if (!$db_attachname || $db_htmdir) {
		$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name IN ('db_attachname','db_htmdir')");
		while ($rt = $db->fetch_array($query)) {
			${$rt['db_name']} = $rt['db_value'];
		}
	}
	$filepath = array(
		D_P.'data/',
		D_P.'data/sql_config.php',
		D_P.'data/bbscache/',
		D_P.'data/forums/',
		D_P.'data/guestcache/',
		D_P.'data/groupdb/',
		D_P.'data/style/',
		D_P.'data/tmp/',
		D_P.'data/tplcache/',
		D_P.'data/package/',
		R_P."$db_attachname/",
		R_P."$db_attachname/cn_img/",
		R_P."$db_attachname/mini/",
		R_P."$db_attachname/mutiupload/",
		R_P."$db_attachname/photo/",
		R_P."$db_attachname/pushpic/",
		R_P."$db_attachname/thumb/",
		R_P."$db_attachname/upload/",
		R_P."$db_htmdir/",
		R_P."$db_htmdir/channel/",
	);
	$filemode = array();
	foreach($filepath as $key => $value){
		if (substr($value,-1)=='/') {
			$value = substr($value,0,strlen($value)-1);
			if (!file_exists($value)) {
				@mkdir($value,0777);
				@touch("$value/index.html");
			}
		}
		if(!file_exists($value)){
			$filemode[$key] = 1;
		} elseif(!pwWritable($value)){
			$filemode[$key] = 2;
		} else{
			$filemode[$key] = 0;
		}
	}
	
	
} elseif ($adminitem == 'searchcheck') {
	/*文件搜索检查*/
	if (empty($action)) {
		$dirlist = '';
		$fp = opendir('./');
		while($filename = readdir($fp)){
			if($filename!='.' && $filename!='..' && is_dir($filename)){
				$dirlist .= "<option value=\"$filename\">/$filename</option>";
			}
		}
	}
	if ($action == 'search') {
		S::gp(array('dir','keyword'));
	
		if(!$dir || !$keyword){
			adminmsg('safecheck_operate_error');
		}
		$check = $dirlist = array();
		foreach($dir as $key=>$value){
			$ifsub = $value == '.' ? 0 : 1;
			checkfile($keyword,$value.'/',$ifsub);
		}
		empty($check) && adminmsg('all_file_ok');
		foreach($check as $file=>$value){
			$dir = dirname($file);
			$filename = basename($file);
			$filemtime = get_date(pwFilemtime($file));
			$filesize  = filesize($file);
			$dirlist[$dir][] = array($filename,$filesize,$filemtime);
		}
		include PrintEot('filecheck');exit;
	}
} elseif ($adminitem == 'filecheck') {
	/*程序文件检查*/
	if(!$files = readover('admin/safefiles.md5')){
		adminmsg('safefiles_not_exists');
	}
	$files = explode("\n",$files);
	$md5_a = $md5_c = $md5_m = $md5_d = $dirlist = array();

	safefile('./','\.php',0);
	safefile('admin/','\.php');
	safefile('api/','\.php|\.html');
	safefile('apps/','\.php|\.htm');
	safefile('hack/','\.php|\.htm');
	safefile('js/','\.js',0);
	safefile('lib/','\.php|\.html');
	safefile('mode/','\.js|\.php|\.htm');
	safefile('require/','\.php');
	safefile('simple/','\.php');
	safefile('template/','\.php|\.htm');
	safefile('m/','\.php');

	foreach($files as $value){
		list($md5key,$file) = explode("\t",$value);
		$file = trim($file);
		if(!isset($md5_a[$file])){
			$md5_d[$file] = 1;
		} elseif($md5key != $md5_a[$file]){
			$md5_m[] = $file;
		} else{
			$md5_c[] = $file;
		}
	}
	$cklog = array('1'=>0,'2'=>0,'3'=>0);
	$md5_a = array_merge($md5_a,$md5_d);

	foreach($md5_a as $file=>$value){
		$dir = dirname($file);
		$filename = basename($file);
		if(isset($md5_d[$file])){
			$cklog[2]++;
			$dirlist[$dir][] = array($filename,'','','2');;
		} else{
			$filemtime = get_date(pwFilemtime($file));
			$filesize  = filesize($file);

			if(in_array($file,$md5_m)){
				$cklog[3]++;
				$dirlist[$dir][] = array($filename,$filesize,$filemtime,'3');
			} elseif(!in_array($file,$md5_c)){
				$cklog[1]++;
				$dirlist[$dir][] = array($filename,$filesize,$filemtime,'1');
			}
		}
	}
} elseif ($adminitem == 'cachecheck') {
	/*缓存目录检查*/
	$check = $dirlist = array();
	$cklog = array('1'=>0,'2'=>0,'3'=>0);
	cachefile(D_P.'data/');

	if(empty($check)){
		adminmsg('all_file_ok',"$jobUrl&adminitem=chmod");
	}
	foreach($check as $file=>$value){
		$dir = dirname($file);
		$filename = basename($file);
		$filemtime = get_date(pwFilemtime($file));
		$filesize  = filesize($file);
		$dirlist[$dir][] = array($filename,$filesize,$filemtime,$value);
	}
}

if (empty($action)) {
	include PrintEot('filecheck');exit;
} 


/*functions for safecheck*/
function checkfile($keyword,$dir,$sub){
	global $check;
	$fp = opendir($dir);
	while($filename = readdir($fp)){
		$path = $dir.$filename;
		if($filename!='.' && $filename!='..'){
			if(is_dir($path)){
				$sub && checkfile($keyword,$path.'/',$sub);
			} elseif(preg_match('/(\.php|\.php3|\.htm|\.js)$/i',$filename) && filesize($path)<1048576){
				$a = strtolower(readover($path));
				if(strpos($a,$keyword)!==false){
					$check[$path] = 1;
				}
			}
		}
	}
	closedir($fp);
}
function safefile($dir,$ext='',$sub=1){
	global $md5_a;
	$exts = '/('.$ext.')$/i';
	$fp = opendir($dir);
	while($filename = readdir($fp)){
		$path = $dir.$filename;
		if($filename!='.' && $filename!='..' && (preg_match($exts, $filename) || $sub && is_dir($path))){
			if($sub && is_dir($path)){
				safefile($path.'/',$ext);
			} else{
				$md5_a[$path] = md5_file($path);
			}
		}
	}
	closedir($fp);
}
function cachefile($dir){
	global $check,$cklog;
	$fp = opendir($dir);
	while($filename = readdir($fp)){
		$path = $dir.$filename;
		if($filename!='.' && $filename!='..'){
			if(is_dir($path)){
				cachefile($path.'/');
			} elseif(preg_match('/(\.php|\.php3|\.htm)$/i',$filename) && filesize($path)<1048576){
				$a = strtolower(readover($path));
				if(strpos($a,'shell_exec')!==false || strpos($a,'gzencode')!==false){
					$check[$path] = 1;
					$cklog[1]++;
				} elseif(strpos($a,'eval(')!==false || strpos($a,'move_uploaded_file($')!==false || strpos($a,'copy($')!==false || strpos($a,'chr(')!==false || strpos($a,'fopen(')!==false || strpos($a,'writeover(')!==false){
					$check[$path] = 2;
					$cklog[2]++;
				} elseif(preg_match("/\<iframe(.+?)\<\/iframe\>/is",$a)){
					$check[$path] = 3;
					$cklog[3]++;
				}
			}
		}
	}
	closedir($fp);
}
/*end functions for safecheck*/