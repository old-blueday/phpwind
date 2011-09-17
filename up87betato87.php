<?php
/**
 * Copyright (c) 2003-10  phpwind.net. All rights reserved.
 *
 * @author: Noizy Sky_hold 0zz Fengyu Xiaolang Zhudong
 */
error_reporting(E_ERROR | E_PARSE);
@set_magic_quotes_runtime(0);
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');

define('PW_UPLOAD',1);
define('P_W',1);
$defined_vars = get_defined_vars();
foreach ($defined_vars as $_key => $_value) {
	if (!in_array($_key,array('GLOBALS','_POST','_GET','_COOKIE','_SERVER'))) {
		${$_key} = '';
		unset(${$_key});
	}
}

define('R_P',getdirname(__FILE__));
define('D_P',R_P);
require_once(R_P.'require/common.php');
require_once(R_P.'lang/up_function.php');
require_once(R_P.'admin/cache.php');

if (!get_magic_quotes_gpc()) {
	Add_S($_POST);
	Add_S($_GET);
}

$_WIND			= 'upto';
$from_version	= '8.7beta';
$wind_version	= '8.7';
$wind_repair	= '';

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$selfrpos	= strrpos($_SERVER['PHP_SELF'],'/');
$basename	= substr($_SERVER['PHP_SELF'],$selfrpos+1);
$lockfile	= substr($basename,0,strlen($basename)-4);
$bbsurl	= 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,$selfrpos);

$backmsg	= $input = $stepmsg = $stepleft = $stepright = '';
$syslogo	= 'upto';
$crlf = GetCrlf();
$REQUEST_URI = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$jumpurl = "http://".$_SERVER['HTTP_HOST'].$REQUEST_URI;

ob_start();

InitGP(array('step','start'));

require_once(R_P.'lang/install_lang.php');
$systitle	= $lang['title_upto'];
$steptitle	= $step;
require_once(R_P.'lang/header.htm');
file_exists(D_P."data/$lockfile.lock") && Promptmsg('have_upfile');
!N_writable(D_P.'data/sql_config.php') && Promptmsg('sqlconfig_file');
include_once(D_P.'data/bbscache/config.php');

$onlineip = 'Unknown';
if ($db_xforwardip) {
	if ($_SERVER['HTTP_X_FORWARDED_FOR'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ($_SERVER['HTTP_CLIENT_IP'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_CLIENT_IP'])) {
		$onlineip = $_SERVER['HTTP_CLIENT_IP'];
	}
}
if ($onlineip == 'Unknown' && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['REMOTE_ADDR'])) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}

$timestamp = time();
$bbsrecordfile = D_P.'data/bbscache/admin_record.php';
!file_exists($bbsrecordfile) && writeover($bbsrecordfile,"<?php die;?>\n");
$baseconfig = D_P.'data/bbscache/baseconfig.php';
!file_exists($baseconfig) && writeover($baseconfig,"<?php\n\r?>\n");
$F_count = F_L_count($bbsrecordfile,2000);
$L_T = 1200-($timestamp-filemtime($bbsrecordfile));
$L_left = 15-$F_count;

if ($F_count>15 && $L_T>0) {
	Cookie('AdminUser','',0);
	require(R_P.'lang/login.htm');footer();
}

$db_cvtime!=0 && $timestamp += $db_cvtime*60;
$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
$tddays = get_date($timestamp,'j');
$tdtime	= (floor($timestamp/3600)-$t['hours'])*3600;
$montime = $tdtime-($tddays-1)*86400;
$gojs = true;
$footer = false;
$times = 0;

(int)$record<1 && $record = 800;//default value

include_once(D_P.'data/sql_config.php');
if ($database=='mysqli' && Pwloaddl('mysqli')===false) {
	$database = 'mysql';
}
require_once Pcv(R_P."require/db_$database.php");
$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$PW,$charset,$pconnect);

$isManager = false;
$admin_name = '';
$CK = array();
if ($_POST['admin_pwd'] && $_POST['admin_name']) {
	$admin_name = stripcslashes($_POST['admin_name']);
	$CK = array($timestamp,$_POST['admin_name'],md5(PwdCode(md5($_POST['admin_pwd'])).$timestamp. getHashSegment()));
	if (checkuptoadmin($CK)) {
		Cookie("AdminUser",StrCode($timestamp."\t".$_POST['admin_name']."\t".md5(PwdCode(md5($_POST['admin_pwd'])).$timestamp. getHashSegment())));
	}
} else {
	$AdminUser = GetCookie('AdminUser');
	if ($AdminUser) {
		$CK = explode("\t",StrCode($AdminUser,'DECODE'));
		$admin_name = stripcslashes($CK[1]);
	}
}
if (!empty($CK)) {
	$isManager = checkuptoadmin($CK);
}

if ($isManager === false) {
	if ($_POST['admin_name'] && $_POST['admin_pwd']) {
		writeover($bbsrecordfile,'|'.str_replace('|','&#124;',Char_cv($_POST['admin_name'])).'|'.str_replace('|','&#124;',Char_cv($_POST['admin_pwd']))."|Logging Failed|$onlineip|$timestamp|\n",'ab');
		Promptmsg('login_error');
	}
	Cookie('AdminUser','',0);
	require(R_P.'lang/login.htm');footer();
}
if ($_POST['admin_pwd'] && $_POST['admin_name'] && $isManager !== false) {
	$jumpurl = GetGP('jumpurl','P');
	header("Location: $jumpurl");exit;
}
unset($manager,$manager_pwd);

//is CE start
$ceversion = '0';
if ($ceversion=='1') {
	$db_windmagic = $writemsg = 0;
} else {
	$db_windmagic = $writemsg = 1;
}
$writemsg = 0;
//is CE end
if (is_numeric($step) || $step == 'database') define('AJAX', 1);
if (!$step) {
	@unlink(D_P.'data/error.txt');
	$footer = true;
	$lang['log_upto'] = str_replace(array('{#basename}','{#from_version}'),array($basename,$from_version),$lang['log_upto']);
	//8.7数据库大小
	$databaseSize = getTableSize();
	$showBackup = true;
	$unit = 'B';
	if ($databaseSize > 20*1024*1024) {
		$showBackup = false;
	}
	if ($databaseSize > 1024) {
		$databaseSize /= 1024;
		$unit = 'KB';
	}
	if ($databaseSize > 1024) {
		$databaseSize /= 1024;
		$unit = 'MB';
	}
	if ($databaseSize > 1024) {
		$databaseSize /= 1024;
		$unit = 'GB';
	}
	$databaseSize = round($databaseSize,2);
	//end 8.7
	require(R_P.'lang/upto.htm');footer();
} elseif ($step=='database') {
	ob_clean();
	//8.7数据库备份
	S::gp(array('sizelimit', 'start', 'tableid', 'go'), 'gp', 2);
	S::gp(array('tabledb', 'insertmethod', 'dirname', 'tabledbname'));
	$compress = 1;
	$insertmethod = 'common';
	$sizelimit = 2048;
	if (!$go) {
		$query = $db->query("SHOW TABLES");
		while ($rt = $db->fetch_array($query, 2)) {
			$tabledb[] = trim($rt[0]);
		}
	}
	if (!$tabledb && $go) {
		$cachedTable = pwCache::readover(S::escapePath(D_P . 'data/tmp/' . $tabledbname . '.tmp'));
		$tabledb = explode("|", $cachedTable);
	}
	
	$backupService = L::loadClass('backup', 'site');
	!$dirname && $dirname = $backupService->getDirectoryName();
	if (!$go) {
		$backupTable = $backupService->backupTable($tabledb, $dirname, $compress);
		$tabledbTmpSaveDir = D_P . 'data/tmp/';
		if (!N_writable($tabledbTmpSaveDir)) {
			echo "error\t目录 {$tabledbTmpSaveDir}不可写";
			ajax_footer();
		}
		$tabledbname = 'cached_table_' . randstr(8);
		pwCache::writeover(S::escapePath(D_P . 'data/tmp/' . $tabledbname . '.tmp'), implode("|", $tabledb), 'wb');
	}
	$go = (!$go ? 1 : $go) + 1;
	$filename = $dirname . '/' . $dirname . '_' . ($go - 1) . '.sql';
	list($backupData, $tableid, $start, $totalRows)  = $backupService->backupData($tabledb, $tableid, $start, $sizelimit, $insertmethod, $filename);
	
	$continue = $tableid < count($tabledb) ? true : false;
	$backupService->saveData($filename, $backupData, $compress);
	
	if ($continue) {
		$currentTableName = $tabledb[$tableid];
		$currentPos = $start + 1;
		$createdFileNum = $go - 1;
		$j_url = "{$pwServer['SCRIPT_NAME']}&step=database&start=$start&tableid=$tableid&sizelimit=$sizelimit&go=$go&dirname=$dirname&tabledbname=$tabledbname&insertmethod=$insertmethod&compress=$compress";
		echo "continue\t$j_url";
		ajax_footer();
	} else {
		unlink(S::escapePath(D_P . 'data/tmp/' . $tabledbname . '.tmp'));
		echo "success";
		ajax_footer();
	}
} else {
$steps = getBaseSteps();
$steps[] = "升级门户";
$steps[] = "更新回复分表结构";
$steps[] = "更新主题内容分表结构";
$steps[] = "更新积分日志分表结构";
if ($step==1) {

	$stepmsg = $lang['step_1_upto'];
	$stepleft = $lang['step_1_left_upto'];
	$stepright = $lang['step_1_right_upto'];
	$lang['log_upto'] = str_replace(array('{#basename}','{#from_version}'),array($basename,$from_version),$lang['log_upto']);

	$w_check = array(
		'attachment/mini/',
		'data/tplcache/',
		'data/forums/',
		'data/package/',
		'html/',
		'html/js/',
		'html/read/',
		'html/channel/',
		'html/stopic/',
		'html/portal/bbsindex/',
		'html/portal/bbsindex/main.htm',
		'html/portal/bbsindex/config.htm',
		'html/portal/bbsindex/index.html',
		'html/portal/bbsradio/',
		'html/portal/bbsradio/main.htm',
		'html/portal/bbsradio/config.htm',
		'html/portal/bbsradio/index.html',
		'html/portal/oindex/',
		'html/portal/oindex/main.htm',
		'html/portal/oindex/config.htm',
		'html/portal/oindex/index.html',
		'html/portal/groupgatherleft/main.htm',
		'html/portal/groupgatherleft/config.htm',
		'html/portal/groupgatherleft/index.html',
		'html/portal/groupgatherright/main.htm',
		'html/portal/groupgatherright/config.htm',
		'html/portal/groupgatherright/index.html',
		'html/portal/userlist/main.htm',
		'html/portal/userlist/config.htm',
		'html/portal/userlist/index.html',
		'html/portal/usermix/main.htm',
		'html/portal/usermix/config.htm',
		'html/portal/usermix/index.html'
	);
	$writelog = '';
	$fileexist = $writable = array();
	foreach ($w_check as $filename) {
		!N_writable(R_P.$filename) && $writable[] = $filename;
		!file_exists(R_P.$filename) && $fileexist[] = $filename;
		$writelog .= str_replace('{#filename}',$filename,$lang['success_2'])."\n";
	}
	if ($fileexist) {
		$gojs = false;
		$filenames = implode('<br />',$fileexist);
		Promptmsg('error_unfinds');
	}
	if ($writable) {
		$gojs = false;
		$filenames = implode('<br />',$writable);
		Promptmsg('error_777s');
	}
	if ($db_adminfile != 'admin.php' && file_exists(R_P.'admin.php')) {
		$gojs = false;
		Promptmsg('error_admin');
	}
	ajaxRedirect(2,$step);
} elseif ($step=='2') {//Create table
	checkFields('pw_argument','tpcid');
	/*$array = array(
		'检测表名' => array('建表语句小括号内容','表类型，默认空为MyISAM')
	);*/
	$sqlarray = array(
	);
	N_createtable($sqlarray,$start,5);
	ajaxRedirect(3,$step);

} elseif ($step==3) {//Alter table field//Char_cv
	/*$array = array(
		array('检测表名','检测字段',"数据库语句")
	);*/
	empty($start) && $start = '0_L';
	list($stepnum,$steptype) = explode('_',$start);
	if ($steptype=='L') {
		$sqlarray_L = array(
			//array(.*) CHANGE (\w*)
			//87beta to 87
			array('pw_robbuild','status', "ALTER TABLE `pw_robbuild` ADD `status` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0'"),
			//mobile
			array('pw_threads','frommob', "ALTER TABLE `pw_threads` ADD `frommob` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0"),
			array('pw_posts','frommob', "ALTER TABLE `pw_posts` ADD `frommob` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0"),
		
		);
		@set_time_limit(888);
		N_atfield(N_pointstable($sqlarray_L),$stepnum,1);
		if (!$times) {
			$record = 0; $start = '0_F';
		}
	} elseif ($steptype=='F') {
		$sqlarray_F = array(
		);
		@set_time_limit(888);
		N_atfield($sqlarray_F,$stepnum,10);
	}

	ajaxRedirect(4,$step);
}elseif ($step == 4) {//更改索引
	empty($start) && $start = '0_L';
	list($stepnum,$steptype) = explode('_',$start);
	if ($steptype=='L') {
		$sqlarray_L = array(
			//87beta to 87
			array('pw_searchstatistic','idx_created_time','created_time'),
		);
		@set_time_limit(888);
		N_atindex(N_pointstable($sqlarray_L),$stepnum,1);
		if (!$times) {
			$record = 0; $start = '0_F';
		}
	} elseif ($steptype=='F') {
		$sqlarray_F = array(
		);
		@set_time_limit(888);
		N_atindex($sqlarray_F,$stepnum,5);
	}
	ajaxRedirect($step+1,$step);
} elseif($step == '5'){//up85to87 门户升级更新 start就等于page
	$invokeService = L::loadClass('invokeservice', 'area');
    $pageInvokeDB = L::loadDB('PageInvoke', 'area');
    $count = $pageInvokeDB->searchCount(array());
    $perpage = 50;
    
    $start || (int)$start = 1;
    $maxPage = ceil($count/$perpage);
    if ($start <= $maxPage) {
    	$pageInvokes = $pageInvokeDB->searchPageInvokes(array(),$start,$perpage);
	    foreach ($pageInvokes as $value) {
			$invokeName = $value['invokename'];
			unset($value['id'],$value['invokename']);
			$invokeService->updateInvokeByName($invokeName,$value);
		}
	    $start++;
	}
    $GLOBALS['max'] = $maxPage+1;
    $GLOBALS['limit'] = $start;
	ajaxRedirect($step+1,$step);
} elseif ($step == 6) { //分表结构跟主表不一样时，更新回复分表结构
	@set_time_limit(888);
	empty($start) && $start = '0_L';
	list($stepnum, $steptype) = explode('_', $start);
	$changeFields = array();
	if (!$stepnum) {
		$changeFields = updateTableStructureForMerge('pw_posts');
		pwCache::writeover(D_P . 'data/tmp/alterpost.tmp', serialize($changeFields), 'wb');
	} elseif ($steptype == 'L') {
		$changeFields = unserialize(pwCache::readover(D_P . 'data/tmp/alterpost.tmp'));
	}
	
	if ($steptype == 'L') {
		count($changeFields) > 0 && N_atfield($changeFields, $stepnum, 1);
		if (!$times) {
			$record = 0;
			$start = '1_F';
		}
	} elseif ($steptype == 'F') {
		unlink(D_P . 'data/tmp/alterpost.tmp');
		createMergeTable('posts');
	}
	ajaxRedirect($step+1,$step);
} elseif ($step == 7) { //门户频道静态页更新
	require_once (R_P.'admin/cache.php');
	$pwChannel = L::loadClass('channelservice', 'area');
	$pwChannel->updateAreaChannels();
	ajaxRedirect($step+1,$step);
} elseif ($step == 8) { //分表结构跟主表不一样时，更新主题内容分表结构
	@set_time_limit(888);
	empty($start) && $start = '0_L';
	list($stepnum, $steptype) = explode('_', $start);
	$changeFields = array();
	if (!$stepnum) {
		$changeFields = updateTableStructureForMerge('pw_tmsgs');
		pwCache::writeover(D_P . 'data/tmp/altertmsgs.tmp', serialize($changeFields), 'wb');
	} elseif ($steptype == 'L') {
		$changeFields = unserialize(pwCache::readover(D_P . 'data/tmp/altertmsgs.tmp'));
	}
	
	if ($steptype == 'L') {
		count($changeFields) > 0 && N_atfield($changeFields, $stepnum, 1);
		if (!$times) {
			$record = 0;
			$start = '1_F';
		}
	} elseif ($steptype == 'F') {
		unlink(D_P . 'data/tmp/altertmsgs.tmp');
		createMergeTable('tmsgs');
	}
	ajaxRedirect($step+1, $step);
} elseif ($step == 9) { //分表结构跟主表不一样时，更新积分日志分表结构
	@set_time_limit(888);
	empty($start) && $start = '0_L';
	list($stepnum, $steptype) = explode('_', $start);
	$changeFields = array();
	if (!$stepnum) {
		$changeFields = updateTableStructureForMerge('pw_creditlog');
		pwCache::writeover(D_P . 'data/tmp/altercreditlog.tmp', serialize($changeFields), 'wb');
	} elseif ($steptype == 'L') {
		$changeFields = unserialize(pwCache::readover(D_P . 'data/tmp/altercreditlog.tmp'));
	}
	
	if ($steptype == 'L') {
		count($changeFields) > 0 && N_atfield($changeFields, $stepnum, 1);
		if (!$times) {
			$record = 0;
			$start = '1_F';
		}
	} elseif ($steptype == 'F') {
		unlink(D_P . 'data/tmp/altercreditlog.tmp');
		createMergeTable('creditlog');
	}
	ajaxRedirect($step+1, $step);
} elseif ($step == 10) { //更新置顶帖数据
	$db->update("UPDATE pw_threads SET specialsort = topped +100 WHERE topped >0 AND topped<4");
	ajaxRedirect('finish', $step, 'success');
} elseif ($step=='finish') {//Finish
	@set_time_limit(120);
	$db_htmdir = 'html';
	$levelService = L::loadclass("AreaLevel", 'area');
	$levelService->_updateAreaUserConfig();
	require_once(R_P.'admin/cache.php');
	require(R_P.'lib/upload.class.php');
	PwUpload :: createFolder(D_P.'data/forums');
	$appsdb = array();
	@include_once(D_P."data/bbscache/apps_list_cache.php");
	setConfig('db_apps_list',$appsdb);
	setConfig('db_server_url','http://apps.phpwind.net');
	updatecache();//config
	updatecache_conf('area',true);
	updatecache_conf('o',true);
	//updatemedal_list();//medal
	require_once(R_P.'require/updateforum.php');
	updatetop();

	$steptitle = '!';
	$stepmsg = $lang['step_finish'];
	if ($writemsg) {
		$usernames = '';
		require_once(R_P.'require/msg.php');
		foreach ($manager as $value) {
			$usernames .= ",'$value'";
		}
		if ($usernames) {
			$query = $db->query('SELECT username FROM pw_members WHERE username IN ('.substr($usernames,1).')');
			while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
				writenewmsg(array($rt[0],'0',$lang['log_unionmsgt'],$timestamp,$lang['log_unionmsgc'],'N'),1);
			}
			$db->free_result($query);
		}
	}

	pwSetVersion('UPGRADE');

	writeover(D_P."data/$lockfile.lock",'LOCKED');
	@unlink(D_P.'data/bbscache/notice.txt');
	@unlink(D_P.'data/bbscache/index.php');
	@unlink(D_P.'data/index.php');
	@unlink(D_P.'data/type_cache.php');
	if ($db_attachname) {
		$fp = opendir(R_P.$db_attachname);
		while ($filename = readdir($fp)) {
			if (strpos($filename,'.php')!==false) {
				@unlink(R_P."$db_attachname/$filename");
			}
		}
		closedir($fp);
	}
	$fp = opendir(D_P.'data/bbscache/');
	while ($filename = readdir($fp)) {
		if($filename=='..' || $filename=='.') continue;
		if (strpos($filename,'mode_area') !==false) {
			P_unlink(Pcv(D_P.'data/bbscache/'.$filename));
		}
	}
	closedir($fp);
	$fp = opendir(D_P.'data/tplcache/');
	while ($filename = readdir($fp)) {
		if($filename=='..' || $filename=='.' || strpos($filename,'.htm')===false) continue;
		P_unlink(Pcv(D_P.'data/tplcache/'.$filename));
	}
	closedir($fp);
	$fp = opendir($attachdir.'/mini/');
	while ($filename = readdir($fp)) {
		if($filename=='..' || $filename=='.') continue;
		P_unlink(Pcv($attachdir.'/mini/'.$filename));
	}
	closedir($fp);
	$errormsg = readover(D_P.'data/error.txt');
	if ($errormsg) {
		$lang['success_upto'] .= '<br /><small>'.str_replace(array('  ',"\n"),array('&nbsp; ','<br />'),$errormsg).'</small>';
	}
	if (!N_writable($basename)) {
		$lang['success_upto'] .= "<br /><small><font color=\"red\">$lang[error_delinstall]</font></small>";
	}
	$lang['success_upto'] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_upto']);
	require(R_P.'lang/upto.htm');
	@unlink(D_P.'data/error.txt');
	Cookie('AdminUser','',0);
	//@unlink($basename);
	footer();
} else {
	exit('Invalid action in input');
}
}
########################## FUNCTION ##########################

function Showmsg(){}
function getdirname($path=null){
	if (!empty($path)) {
		if (strpos($path,'\\')!==false) {
			return substr($path,0,strrpos($path,'\\')).'/';
		} elseif (strpos($path,'/')!==false) {
			return substr($path,0,strrpos($path,'/')).'/';
		}
	}
	return './';
}

function insertDatas($tid,$uid,$username,$ctid) {
	global $db;
	$collectionService = L::loadClass('Collection', 'collection');
	$threads = $db->get_one("SELECT tid,subject,author,authorid,lastpost FROM pw_threads WHERE tid = ".S::sqlEscape($tid));
	if($threads) {
		$collection['uid'] = $threads['authorid'];
		$collection['link'] = $db_bbsurl.'/read.php?tid='.$threads['tid'];
		$collection['postfavor']['subject'] = $threads['subject'];
		$collection['lastpost'] = $threads['lastpost'];
		$collectionDate = array(
						'ctid'		=> 	$ctid,
						'uid'		=> 	$uid,
						'username'	=> 	$username,
						'typeid'	=> 	$threads['tid'],
						'type'		=> 	'postfavor',
						'content'	=>	serialize($collection),
						'postdate'	=>	$threads['lastpost']
					);
		if ($collectionService->insert($collectionDate)) {
			return true;
		}
	}
}

function updatecache_cnc_s() {
	global $db;
	$styledb = $style_relation = array();
	$query = $db->query('SELECT id,cname,upid FROM pw_cnstyles WHERE ifopen=1');
	while ($rt = $db->fetch_array($query)) {
		$styledb[$rt['id']] = array(
			'cname'	=> $rt['cname'],
			'upid'	=> $rt['upid']
		);
		if ($rt['upid']) {
			$style_relation[$rt['upid']][] = $rt['id'];
		} else {
			$style_relation[$rt['id']] = array();
		}
	}
	$styledb = serialize($styledb);
	$style_relation = serialize($style_relation);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_styledb'",
		'UPDATE pw_hack SET ' . pwSqlSingle(array('hk_value' => $styledb, 'vtype' => 'array')) . " WHERE hk_name='o_styledb'",
		'INSERT INTO pw_hack SET ' . pwSqlSingle(array('hk_name' => 'o_styledb', 'vtype' => 'array', 'hk_value' => $styledb))
	);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_style_relation'",
		'UPDATE pw_hack SET ' . pwSqlSingle(array('hk_value' => $style_relation, 'vtype' => 'array')) . " WHERE hk_name='o_style_relation'",
		'INSERT INTO pw_hack SET ' . pwSqlSingle(array('hk_name' => 'o_style_relation', 'vtype' => 'array', 'hk_value' => $style_relation))
	);
	updatecache_conf('o',true);
}
?>