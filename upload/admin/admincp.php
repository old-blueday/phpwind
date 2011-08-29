<?php
/**
*
*  Copyright (c) 2003-09  phpwind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of phpwind.com.
*
*/
!defined('R_P') && exit('Forbidden');
define('P_W','admincp');
define('UC_CLIENT_ROOT', R_P . '/uc_client/');
(isset($_GET['ajax']) && $_GET['ajax'] == 1) && define('AJAX', 1);
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');

require_once(R_P.'require/common.php');
require_once(R_P.'require/functions.php');
//S::filter();
//modified@2010/7/7 S&P
S::filter();



//* include_once pwCache::getPath(D_P.'data/bbscache/config.php');
pwCache::getData(D_P.'data/bbscache/config.php');

define('AREA_PATH', R_P . $db_htmdir . '/channel/');
define('PORTAL_PATH', R_P . $db_htmdir . '/portal/');
$db_userurl = ($db_htmifopen && $db_userurlopen) ? 'u/' : 'u.php?uid='; //url
define('USER_URL',$db_userurl);

$timestamp = time();
$db_cvtime != 0 && $timestamp += $db_cvtime*60;
$onlineip = pwGetIp();
$db_cc && pwDefendCc($db_cc);
$ceversion = defined('CE') ? 1 : 0;
#phpwind version
list($wind_version,$wind_repair,$wind_from) = explode(',',WIND_VERSION);

S::gp(array('adminjob','admintype','adminitem','type','hackset','a_type','action','verify','adskin','job','ajax','admin_keyword'));

if (strpos($adminjob,'..') !== false || $admintype && strpos($admintype,'..') !== false) {
	exit('Forbidden');
}

if ($ajax) define('AJAX','1');
if ($db_forcecharset && !defined('AJAX')) {
	@header("Content-Type:text/html; charset=$db_charset");
}
ObStart();
file_exists('install.php') && adminmsg('installfile_exists');

$admin_file = $pwServer['PHP_SELF'];
$REQUEST_URI  = trim($pwServer['PHP_SELF'].'?'.$pwServer['QUERY_STRING'],'#');

if ($adminjob == 'quit') {
	Cookie('AdminUser','',0);
	ObHeader($admin_file);
}

$imgpath	= $db_http		!= 'N' ? $db_http		: $db_picpath;
$attachpath = $db_attachurl != 'N' ? $db_attachurl	: "$db_bbsurl/$db_attachname";
$imgdir 	= R_P.$db_picpath;
$attachdir	= R_P.$db_attachname;
$pw_posts	= 'pw_posts';
$pw_tmsgs	= 'pw_tmsgs';

if (D_P != R_P && $db_http != 'N') {
	$R_url = substr($db_http,-1)=='/' ? substr($db_http,0,-1) : $db_http;
	$R_url = substr($R_url,0,strrpos($R_url,'/'));
} else {
	$R_url = $db_bbsurl;
}

//* include_once pwCache::getPath(D_P."data/style/wind.php");
//* include_once pwCache::getPath(D_P.'data/sql_config.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P."data/style/wind.php");
require D_P.'data/sql_config.php';
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

require_once(R_P.'admin/cache.php');
!is_array($manager) && $manager = array();
!is_array($manager_pwd) && $manager_pwd = array();
$newmanager = $newmngpwd = array();
foreach ($manager as $key => $value) {
	if (!empty($value) && !is_array($value)) {
		$newmanager[$key]	= $value;
		$newmngpwd[$key]	= $manager_pwd[$key];
	}
}
$manager		= $newmanager;
$manager_pwd	= $newmngpwd;
$H_url			= $db_wwwurl;
$B_url			= $db_bbsurl;

if ($database=='mysqli' && Pwloaddl('mysqli')===false) {
	$database = 'mysql';
}

$bbsrecordfile = D_P.'data/bbscache/admin_record.php';
!file_exists($bbsrecordfile) && writeover($bbsrecordfile,"<?php die;?>\n");
/** !file_exists($bbsrecordfile) && pwCache::setData($bbsrecordfile,"<?php die;?>\n"); **/

$F_count	= F_L_count($bbsrecordfile,2000);
$L_T		= 1200-($timestamp-pwFilemtime($bbsrecordfile));
$L_left		= 15-$F_count;

if ($F_count>15 && $L_T>0) {
	$db_adminrecord	= 0;
	Cookie('AdminUser','',0);
	adminmsg('login_fail');
}
if (empty($manager)) {
	if (file_exists(D_P.'data/sql_config.php')) {
		adminmsg('managerinfo_error');
	} else {
		adminmsg('sql_config');
	}
}

$CK = array();$admin_name = '';
if ($_POST['admin_pwd'] && $_POST['admin_name']) {
	if ($db_gdcheck & 32) {
		GdConfirm($_POST['lg_num']);
	}
	$admin_name = stripcslashes($_POST['admin_name']);
	$safecv = $db_ifsafecv ? questcode($_POST['question'],$_POST['customquest'],$_POST['answer']) : '';
	$CK = array($timestamp,$_POST['admin_name'],md5(PwdCode(md5($_POST['admin_pwd'])).$timestamp.getHashSegment()),$safecv);
	Cookie('AdminUser',StrCode(implode("\t",$CK)));
} else {
	$AdminUser = GetCookie('AdminUser');
	if ($AdminUser) {
		$CK = explode("\t",StrCode($AdminUser,'DECODE'));
		$admin_name = stripcslashes($CK[1]);
	}
}

if (!empty($CK)) {
	PwNewDB();
	$rightset = checkpass($CK);
} else {
	$db = null;
	$rightset = array();
}
if (empty($rightset)) {
	if ($_POST['admin_name'] || $_POST['admin_pwd']) {
		writeover($bbsrecordfile,'|'.str_replace('|','&#124;',S::escapeChar($_POST['admin_name'])).'|'.str_replace('|','&#124;',S::escapeChar($_POST['admin_pwd']))."|Logging Failed|$onlineip|$timestamp|\n",'ab');
		/** pwCache::setData($bbsrecordfile,'|'.str_replace('|','&#124;',S::escapeChar($_POST['admin_name'])).'|'.str_replace('|','&#124;',S::escapeChar($_POST['admin_pwd']))."|Logging Failed|$onlineip|$timestamp|\n", false, 'ab'); **/
		
		$db_adminrecord	= 0;
		$REQUEST_URI	= $pwServer['PHP_SELF'];
		Cookie('AdminUser','',0);
		if ($L_left) {
			adminmsg('login_error');
		} else {
			adminmsg('login_fail');
		}
	}
	S::gp(array("ajax"));
	if ($ajax == 1) {
		define('AJAX',1);
		adminmsg('login_invalid');
	}
	Cookie('AdminUser','',0);
	include PrintEot('adminlogin');afooter(true);
} elseif ($_POST['admin_name']) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$uid = $userService->getUserIdByUserName($admin_name);
	$slog = $db->get_value("SELECT slog FROM pw_administrators WHERE uid=" . S::sqlEscape($uid,false));
	$slog = explode(";",$slog);
	!$slog && $slog = array();
	if (count($slog) >= 8) unset($slog[0]);
	array_push($slog,$timestamp.','.$onlineip);
	$slog = implode(";",$slog);
	$db->update("UPDATE pw_administrators SET slog=".S::sqlEscape($slog,false)."WHERE uid=" . S::sqlEscape($uid,false));
	$REQUEST_URI = trim($REQUEST_URI,'?#');
	ObHeader($REQUEST_URI);
}
$bubbleInfo = $rightset['bubble'];
$uidForBubble = $rightset['uid'];
$admin_gid = $rightset['gid'];
if ($db_ifsafecv && strpos($db_safegroup,",$admin_gid,")!==false && !$CK[3]) {
	Cookie('AdminUser','',0);
	adminmsg('safecv_prompt');
}
//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
pwCache::getData(D_P.'data/bbscache/level.php');
!defined('If_manager') && define('If_manager',0);
if (!If_manager) {
	Iplimit();
	$temp_a = array_merge($_POST,$_GET);
	foreach ($temp_a as $key => $value) {
		if ($key!='module') {
			S::checkVar($value);
		}
	}
	unset($temp_a);
	$admin_level = $ltitle[$admin_gid];
} else {
	$admin_level = getLangInfo('other','admin_level');//'manager';
}
$_postdata  = $_POST ? PostLog($_POST) : '';
$new_record = '|'.str_replace('|','&#124;',S::escapeChar($admin_name)).'||'.str_replace('|','&#124;',S::escapeChar($REQUEST_URI))."|$onlineip|$timestamp|$_postdata|\n";
writeover($bbsrecordfile,$new_record,"ab");
//* pwCache::setData($bbsrecordfile,$new_record, false, "ab");

if ($pwServer['REQUEST_METHOD'] == 'POST') {
	$referer_a = @parse_url($pwServer['HTTP_REFERER']);
	if ($referer_a['host']) {
		list($http_host) = explode(':',$pwServer['HTTP_HOST']);
		if ($referer_a['host']!=$http_host) {
			adminmsg('undefined_action');
		}
	}
	unset($referer_a);
	PostCheck($verify);
}
unset($_postdata,$new_record,$bbsrecordfile,$dbhost,$dbuser,$dbpw,$dbname,$pconnect,$newmanager,$newmngpwd);
$thisPWTabs = $_GET['tab'] ? S::escapeChar($_GET['tab']) : S::escapeChar($_COOKIE['thisPWTabs']);

function HtmlConvert(&$array) {
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$array[$key] = htmlspecialchars($value);
			} else {
				HtmlConvert($array[$key]);
			}
		}
	} else {
		$array = htmlspecialchars($array);
	}
}
function checkpass($CK) {
	S::slashes($CK);
	global $db,$manager,$db_ifsafecv;
	if (S::inArray($CK[1],$manager)) {
		global $manager_pwd;
		$v_key = array_search($CK[1],$manager);
		$ifQuery = true; // In order ot get bubble info
		if (!SafeCheck($CK,PwdCode($manager_pwd[$v_key]))) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$rt = $userService->getByUserName($CK[1], true, true);

			if (!SafeCheck($CK,PwdCode($rt['password'])) || $db_ifsafecv && $rt['safecv']!=$CK['3']) {
				return false;
			}
			if (!admincheck($rt['uid'],$rt['username'],$rt['groupid'],$rt['groups'],'check')) {
				return false;
			}
			$ifQuery = false;
		} elseif ($db_ifsafecv) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$rt = $userService->getByUserName($CK[1], true, true);
			if ($rt && $rt['safecv']!=$CK['3']) return false;
			$ifQuery = false;
		}
		if ($ifQuery) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$rt = $userService->getByUserName($CK[1], true, true);
		}
		define('If_manager',1);
		$rightset['gid'] = 3;
		$rightset['all'] = 1;
		$rightset['bubble'] = $rt['bubble'];
		require GetLang('purview');
		foreach ($purview as $key=>$value) {
			$rightset[$key] = 1;
		}
		foreach ($nav_manager['option'] as $key => $value) {
			$rightset[$key] = 1;
		}
	} else {
		$rt = $db->get_one("SELECT m.uid,m.username,m.groupid,m.groups,m.password,m.safecv,m.groupid,u.gptype,p.rvalue as allowadmincp,md.bubble FROM pw_members m LEFT JOIN pw_usergroups u ON u.gid=m.groupid LEFT JOIN pw_permission p ON p.uid='0' AND p.fid='0' AND p.gid=m.groupid AND p.rkey='allowadmincp' LEFT JOIN pw_memberdata md ON md.uid = m.uid WHERE m.username=".S::sqlEscape($CK[1]));
		if (!$rt['allowadmincp'] || ($rt['gptype']!='system' && $rt['gptype']!='special') || $db_ifsafecv && $rt['safecv'] != $CK['3']) {
			return false;
		}
		if (!SafeCheck($CK,PwdCode($rt['password'])) || !admincheck($rt['uid'],$CK[1],$rt['groupid'],$rt['groups'],'check')) {
			return false;
		}
		$rightset = $db->get_value('SELECT value FROM pw_adminset WHERE gid='.S::sqlEscape($rt['groupid']));
		if ($rightset) {
			if (!is_array($rightset = unserialize($rightset))) {
				$rightset = array();
			}
		} else {
			$rightset = array();
		}
		require GetLang('purview');
		foreach ($rightset as $key=>$value) {
			$rightset[$key] = (isset($purview[$key]) && $rightset[$key]==1) ? 1 : 0;
		}
		$rightset['gid'] = $rt['groupid'];
		$rightset['bubble'] = $rt['bubble'];
	}
	$rightset['uid'] = $rt['uid'];
	return $rightset;
}
function gets($filename,$value='4096') {
	$getcontent = '';
	if ($handle = @fopen($filename,'rb')) {
		flock($handle,LOCK_SH);
		$getcontent = fread($handle,$value);//fgets调试
		fclose($handle);
	}
	return $getcontent;
}
function Showmsg($msg,$jumpurl='',$t=2,$langtype='index') {
	adminmsg($msg,$jumpurl,$t,$langtype);
}
function adminmsg($msg,$jumpurl='',$t=2,$langtype='admin') {
	@extract($GLOBALS,EXTR_SKIP);
	if ($langtype == 'admin') {
		$msg = getLangInfo('cpmsg',$msg);
	} else {
		$msg = getLangInfo('msg',$msg);
	}
	if (defined('AJAX')) {
		echo $msg;ajax_footer();
	}
	if ($jumpurl != '') {
		$basename = $jumpurl;
		$ifjump	  = "<meta http-equiv='Refresh' content='$t; url=$jumpurl'>";
	} elseif (!$basename) {
		$basename = $REQUEST_URI;
	}
	if ($adminjob!='manager' && $db_adminrecord==1 && $basename!='javascript:history.go(-1);' && !$fromgd) {
		$adminmsg = 2;
	} else {
		$adminmsg = 1;
	}
	if (strpos($pwServer['HTTP_USER_AGENT'],'MSIE 7.0;') !== false) {
		list($basename) = explode('#',$basename);
		list($jumpurl) = explode('#',$jumpurl);
	}
	include PrintEot('message');
	$cachetime = $timestamp-3600*24;
	if (readover(D_P.'data/bbscache/none.txt')!='' || pwFilemtime(D_P.'data/bbscache/file_lock.txt')<$cachetime || pwFilemtime(D_P.'data/bbscache/info.txt')<$cachetime || pwFilemtime(D_P.'data/bbscache/userpay.txt')<$cachetime) {
		echo '<script type="text/javascript">if (parent.notice) {parent.notice.location.href = "'.$admin_file.'?adminjob=notice";}</script>';
	}
	afooter();
}
function ieconvert($msg) {
	if (is_array($msg)) {
		foreach ($msg as $key=>$value) {
			$msg[$key] = ieconvert($value);
		}
	} else {
		$msg = str_replace(array("\t","\r",'  '),array('','','&nbsp; '),$msg);
	}
	return $msg;
}
function Quot_cv($msg){
	return str_replace('"','&quot;',$msg);
}
function deldir($path){
	if (file_exists($path)) {
		if (is_file($path)) {
			P_unlink($path);
		} else {
			$handle = opendir($path);
			while ($file = readdir($handle)) {
				if ($file!='' && !in_array($file,array('.','..'))) {
					if (is_dir("$path/$file")) {
						deldir("$path/$file");
					} else {
						P_unlink("$path/$file");
					}
				}
			}
			closedir($handle);
			rmdir($path);
		}
	}
}
//phpwind
function ifadmin($username){
	global $db;
	$query=$db->query("SELECT forumadmin FROM pw_forums WHERE forumadmin!=''");
	while($forum=$db->fetch_array($query)){
		if($forum['forumadmin'] && strpos($forum['forumadmin'],",$username,")!==false){
			return true;
		}
	}
	return false;
}
function ifcheck($var,$out) {
	$GLOBALS[$out.'_Y'] = $GLOBALS[$out.'_N'] = '';
	$GLOBALS[$out.'_'.($var ? 'Y' : 'N')] = 'checked';
}
function F_L_count($filename,$offset){
	global $onlineip;
	$count=0;
	if($fp=@fopen($filename,"rb")){
		flock($fp,LOCK_SH);
		fseek($fp,-$offset,SEEK_END);
		$readb=fread($fp,$offset);
		fclose($fp);
		$readb=trim($readb);
		$readb=explode("\n",$readb);
		$count=count($readb);$count_F=0;
		for($i=$count-1;$i>0;$i--){
			if(strpos($readb[$i],"|Logging Failed|$onlineip|")===false){
				break;
			}
			$count_F++;
		}
	}
	return $count_F;
}
function GetLang($lang,$type='admin',$EXT='php'){
	global $tplpath;
	!in_array($lang,array('all','cpmsg','left','rightset','purview','search','dbtable')) && $type = 'index';
	if ($type <> 'admin') {
		
		if (file_exists(R_P."template/$tplpath/lang_$lang.$EXT")) {
			return R_P."template/$tplpath/lang_$lang.$EXT";
		} elseif (file_exists(R_P."template/wind/lang_$lang.$EXT")) {
			return R_P."template/wind/lang_$lang.$EXT";
		} else {
			exit("Can not find lang_$lang.$EXT file");
		}
	}
	
	if (file_exists(R_P."template/admin_$tplpath/cp_lang_$lang.$EXT")) {
		return R_P."template/admin_$tplpath/cp_lang_$lang.$EXT";
	} elseif (file_exists(R_P."template/admin/cp_lang_$lang.$EXT")) {
		return R_P."template/admin/cp_lang_$lang.$EXT";
	} else {
		exit("Can not find cp_lang_$lang.$EXT file");
	}
}
function PrintEot($template,$EXT='htm') {
	$tplpath = L::style('tplpath');
	!$template && $template = 'N';
	//cms
	if ($template=='bbscode' || in_array($template,array('wysiwyg_editor_common','c_header','c_footer'))) {
		if (file_exists(R_P."template/$tplpath/$template.$EXT")) {
			return R_P."template/$tplpath/$template.$EXT";
		} elseif (file_exists(R_P."template/wind/$template.$EXT")) {
			return R_P."template/wind/$template.$EXT";
		} else {
			exit("Can not find $template.$EXT file");
		}
	}
	//cms
	
	if (file_exists(R_P."template/admin/$template.$EXT")) {
		return R_P."template/admin/$template.$EXT";
	} else {
		exit("Can not find $template.$EXT file");
	}
}
function afooter($unfoot=null){
	static $showafooter;
	global $db_redundancy,$wind_version,$db,$db_debug,$admin_keyword;
	$showafooter = false;
	if (empty($unfoot)) {
		$showafooter = true;
		require PrintEot('adminbottom');
	}

	$output = ob_get_contents();
	$output = str_replace(array('<!--<!--<!---->','<!--<!---->','<!---->-->','<!---->'),'',$output);
	if ($admin_keyword) {
		$output = preg_replace('/('.preg_quote($admin_keyword, '/').')([^">;]*<)(?!\/script|\/textarea)/si','<font color="red"><u>\\1</u></font>\\2',$output);
	}
	$output = preg_replace(
		"/\<form([^\<\>]*)\saction=['|\"]?([^\s\"'\<\>]+)['|\"]?([^\<\>]*)\>/ies",
		"FormCheck('\\1','\\2','\\3')",
		rtrim($output,'<!--')
	);
	echo ObContents($output);
	unset($output);
	if (defined('SHOWLOG')) Error::writeLog();
	exit;
}
function readlog($filename,$offset=1024000) {
	$readb = array();
	if ($fp = @fopen($filename,"rb")) {
		flock($fp,LOCK_SH);
		$size = filesize($filename);
		$size > $offset ? fseek($fp,-$offset,SEEK_END): $offset = $size;
		$readb = fread($fp,$offset);
		fclose($fp);
		$readb = str_replace("\n","\n<:wind:>",$readb);
		$readb = explode("<:wind:>",$readb);
		$count = count($readb);
		if ($readb[$count-1] == '' || $readb[$count-1] == "\r") {unset($readb[$count-1]);}
		if (empty($readb)) {$readb[0] = "";}
	}
	return $readb;
}

function checkselid($selid) {
	if (is_array($selid)) {
		$ret = array();
		foreach ($selid as $key => $value) {
			if (!is_numeric($value)) {
				return false;
			}
			$ret[] = $value;
		}
		return S::sqlImplode($ret);
	} else {
		return '';
	}
}

function ObHeader($URL) {
	echo '<meta http-equiv="expires" content="0">';
	echo '<meta http-equiv="Pragma" content="no-cache">';
	echo '<meta http-equiv="Cache-Control" content="no-cache">';
	echo "<meta http-equiv='refresh' content='0;url=$URL'>";exit;
}

function GetAllowForum($username) {
	global $db;
	$allowfid = $forumoption = '';
	$query = $db->query("SELECT fid,name,forumadmin FROM pw_forums WHERE type!='category' AND (forumadmin LIKE ".S::sqlEscape("%,$username,%")."OR fupadmin LIKE ".S::sqlEscape("%,$username,%").')');
	while ($rt = $db->fetch_array($query)) {
		$allowfid    .= ','.$rt['fid'];
		$forumoption .= "<option value=\"$rt[fid]\"> >> $rt[name]</option>";
	}
	$allowfid = trim($allowfid,',');
	return array($allowfid,$forumoption);
}
function GetHiddenForum() {
	global $db;
	$forumoption = '<option></option>';
	$allowfid    = '';
	$query = $db->query("SELECT fid,name FROM pw_forums WHERE f_type='hidden'");
	while ($rt = $db->fetch_array($query)) {
		$allowfid    .= ','.$rt['fid'];
		$forumoption .= "<option value=\"$rt[fid]\"> &nbsp;|- $rt[name]</option>";
	}
	$allowfid = trim($allowfid,',');
	return array($allowfid,$forumoption);
}
function Iplimit() {
	global $db_iplimit;
	if ($db_iplimit) {
		global $onlineip;
		$allowip = false;
		$ip_a = explode(',',$db_iplimit);
		foreach ($ip_a as $value) {
			$value = trim($value);
			if ($value && strpos(",$onlineip.",",$value.") !== false) {
				$allowip = true;
				break;
			}
		}
		if (!$allowip) {
			Cookie('AdminUser','',0);
			adminmsg('ip_ban');
		}
	}
}
function PostLog($log) {
	foreach ($log as $key => $val) {
		$key = str_replace(array("\n","\r","|"),array('\n','\r','&#124;'),$key);
		if (is_array($val)) {
			$data .= "$key=array(".PostLog($val).")";
		} else {
			$val = str_replace(array("\n","\r","|"),array('\n','\r','&#124;'),$val);
			if ($key == 'password' || $key == 'check_pwd') {
				$data .= "$key=***, ";
			} else {
				$data .= "$key=$val, ";
			}
		}
	}
	return $data;
}
function GdConfirm($code,$t=1) {
	Cookie('cknum','',0);
	if (!$code || !SafeCheck(explode("\t",StrCode(GetCookie('cknum'),'DECODE')),strtoupper($code),'cknum',300)) {
		global $basename,$admin_file;
		$t && Cookie('AdminUser','',0);
		$basename = $admin_file;
		$GLOBALS['fromgd'] = 1;
		adminmsg('check_error');
	}
}
function EncodeUrl($url,$r=false) {
	global $db_hash,$admin_name,$admin_gid;
	$url_a = substr($url,strrpos($url,'?')+1);
	substr($url,-1) == '&' && $url = substr($url,0,-1);
	parse_str($url_a,$url_a);
	$source = '';
	foreach ($url_a as $key => $val) {
		if ($key != 'verify') $source .= $key.$val;
	}
	$posthash = substr(md5($source.$admin_name.$admin_gid.$db_hash),0,8);
	if ($r) {
		return $posthash;
	} else {
		$url .= "&verify=$posthash";
		return $url;
	}
}
function FormCheck($pre,$url,$add){
	$pre = stripslashes($pre);
	$add = stripslashes($add);
	return "<form{$pre} action=\"".EncodeUrl($url)."&\"{$add}>";
}
function PostCheck($verify){
	global $db_hash,$admin_name,$admin_gid;
	$source = '';
	foreach ($_GET as $key => $val) {
		if (!in_array($key,array('verify','nowtime'))) {
			$source .= $key.$val;
		}
	}
	if ($verify != substr(md5($source.$admin_name.$admin_gid.$db_hash),0,8)) {
		adminmsg('illegal_request');
	}
	return true;
}
function PrintHack($template,$EXT="htm") {
	return H_P.'template/'.$template.".$EXT";
}
function PrintMode($template,$EXT="htm") {
	
	return M_P.'template/admin/'.$template.".$EXT";
}
function PrintApp($template,$EXT="htm") {
	
	return A_P."template/$template.".$EXT;
}

function maxmin($id) {
	global $tlistdb;
	$tidmax = $tidmin = 0;
	foreach ($tlistdb as $key => $val) {
		if ($key == $id) {
			$tidmin = $val[1];
			break;
		}
		$tidmax = $val[1];
	}
	return array($tidmin,$tidmax);
}
function admincheck($uid,$username,$groupid,$groups,$action) {
	global $db;
	if ($action == 'check') {
		$rt = $db->get_one("SELECT username,groupid,groups FROM pw_administrators WHERE uid=".S::sqlEscape($uid));
		if ($rt && $rt['username'] == $username && ($rt['groupid'] == $groupid || strpos($rt['groups'], ",$groupid,") !== false)) {
			return true;
		} else {
			return false;
		}
	} elseif ($action == 'update') {
		$rt = $db->get_one("SELECT username,groupid,groups FROM pw_administrators WHERE uid=".S::sqlEscape($uid));
		if (empty($rt)) {
			$db->update("INSERT INTO pw_administrators SET " . S::sqlSingle(array(
				'uid'		=> $uid,
				'username'	=> $username,
				'groupid'	=> $groupid,
				'groups'	=> $groups
			)));
		} elseif ($rt['username'] != $username || $rt['groupid'] != $groupid || $rt['groups'] != $groups) {
			$db->update("UPDATE pw_administrators SET " . S::sqlSingle(array(
				'username'	=> $username,
				'groupid'	=> $groupid,
				'groups'	=> $groups
			)) . " WHERE uid=".S::sqlEscape($uid));
		}
	} elseif ($action == 'delete') {
		$db->update("DELETE FROM pw_administrators WHERE uid=".S::sqlEscape($uid));
	} else {
		return false;
	}
}
function questcode($question,$customquest,$answer) {
	$question = $question=='-1' ? $customquest : $question;
	return $question ? substr(md5(md5($question).md5($answer)),8,10) : '';
}
function Pwloaddl($mod,$ckfunc='mysqli_get_client_info') {
	static $isallowed = null;
	if (extension_loaded($mod)) {
		if ($ckfunc && !function_exists($ckfunc)) return false;
		return true;
	}
	return false;

	if ($isallowed===null) {
		if (!@ini_get('safe_mode') && @ini_get('enable_dl') && @function_exists('dl') && @function_exists('phpinfo')) {
			ob_start();
			@phpinfo(INFO_GENERAL);
			$infomsg = strip_tags(ob_get_contents());
			ob_end_clean();
			if (preg_match('/thread safety\s*enabled/i',$infomsg) && !preg_match('/server api\s*\(cgi\|cli\)/i',$infomsg)) {
				$isallowed = false;
			} else {
				$isallowed = true;
			}
		} else {
			$isallowed = false;
		}
	}
	if (!$isallowed) return false;
	if (strncasecmp(PHP_OS,'win',3) == 0) {
		$module = "php_$mod.dll";
	} elseif (PHP_OS=='HP-UX') {
		$module = "$mod.sl";
	} else {
		$module ="$mod.so";
	}
	@dl(S::escapePath($module));
	if ($ckfunc && !function_exists($ckfunc)) {
		return false;
	}
}
function pwConfirm($msg,$inputmsg=null) {
	@extract($GLOBALS,EXTR_SKIP);
	$adminmsg = 0;
	$msg = getLangInfo('cpmsg',$msg);
	include PrintEot('message');afooter();
}
function adminRightCheck($key){
	global $rightset;
	return isset($rightset[$key]) && $rightset[$key] == 1;
}


/**
 * 后台公共分页js
 * @param unknown_type $count
 * @param unknown_type $page
 * @param unknown_type $numofpage
 * @param unknown_type $event
 */
function pagerforjs($count, $page, $numofpage, $event) {
	global $tablecolor;
	$total = $numofpage;
	if (!empty($max)) {
		$max = (int) $max;
		$numofpage > $max && $numofpage = $max;
	}
	if ($numofpage <= 1 || !is_numeric($page)) {
		return '';
	} else {
		$pages = "<div class=\"pages\"><a href=\"javascript:;\" $event page=\"1\">&laquo;</a>";
		for ($i = $page - 3; $i <= $page - 1; $i++) {
			if ($i < 1) continue;
			$pages .= "<a page=\"$i\" href=\"javascript:;\" $event >$i</a>";
		}
		$pages .= "<b>$page</b>";
		if ($page < $numofpage) {
			$flag = 0;
			for ($i = $page + 1; $i <= $numofpage; $i++) {
				$pages .= "<a page=\"$i\" href=\"javascript:;\" $event >$i</a>";
				$flag++;
				if ($flag == 4) break;
			}
		}
		$pages .= "<a page=\"$numofpage\" href=\"javascript:;\" $event >&raquo;</a><div class=\"fl\">共{$total}页</div><span class=\"pagesone\"><input id=\"input_page\" type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){this.nextSibling.onclick();return false;}\"><button $event >Go</button></span></div>";
		return $pages;
	}
}
function updateadmin() {
	global $db;
	$f_admin = array();
	$query = $db->query("SELECT forumadmin FROM pw_forums");
	while ($forum = $db->fetch_array($query)) {
		$adminarray = explode(",",$forum['forumadmin']);
		foreach ($adminarray as $key => $value) {
			$value = trim($value);
			if ($value) {
				$f_admin[] = $value;
			}
		}
	}
	$f_admin = array_unique($f_admin);

	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$query = $db->query("SELECT uid,username,groupid,groups FROM pw_administrators WHERE groupid=5 OR groups LIKE '%,5,%'");
	while ($rt = $db->fetch_array($query)) {
		if (!in_array($rt['username'],$f_admin)) {
			if ($rt['groupid'] == '5') {
				$userService->update($rt['uid'], array('groupid'=>-1));
				$rt['groupid'] = -1;
			} else {
				$rt['groups'] = str_replace(',5,',',',$rt['groups']);
				$rt['groups'] == ',' && $rt['groups'] = '';
				$userService->update($rt['uid'], array('groups'=>$rt['groups']));
			}
			if ($rt['groupid'] == '-1' && $rt['groups'] == '') {
				admincheck($rt['uid'],$rt['username'],$rt['groupid'],$rt['groups'],'delete');
			} else {
				admincheck($rt['uid'],$rt['username'],$rt['groupid'],$rt['groups'],'update');
			}
		}
	}
	if ($f_admin) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$usernames = S::sqlImplode($f_admin);
		$pwSQL = array();
		$query = $db->query("SELECT m.uid,m.username,m.groupid,m.groups,a.groupid AS gid,a.groups AS gps FROM pw_members m LEFT JOIN pw_administrators a ON m.uid=a.uid WHERE m.username IN($usernames)");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['groupid'] == '-1') {
				$rt['groups'] = str_replace(',5,',',',$rt['groups']);
				$rt['groups'] == ',' && $rt['groups'] = '';
				//$rt['groups'] = $rt['groups'] ? $rt['groups'].'5,' : ",5,";
				$userService->update($rt['uid'], array('groupid'=>5, 'groups'=>$rt['groups']));
				$rt['groupid'] = 5;
			} elseif ($rt['groupid'] != '5' && strpos($rt['groups'],',5,') === false) {
				$rt['groups'] = $rt['groups'] ? $rt['groups'].'5,' : ",5,";
				$userService->update($rt['uid'], array('groups'=>$rt['groups']));
			}
			if ($rt['groupid'] <> $rt['gid'] || $rt['groups'] <> $rt['gps']) {
				$pwSQL[] = array($rt['uid'],$rt['username'],$rt['groupid'],$rt['groups']);
			}
		}
		if ($pwSQL) {
			$db->update("REPLACE INTO pw_administrators (uid,username,groupid,groups) VALUES ".S::sqlMulti($pwSQL));
		}
	}
}
function setstatus(&$status, $b, $setv = '1') {
	--$b;
	for ($i = strlen($setv) - 1; $i >= 0; $i--) {
		if ($setv[$i]) {
			$status |= 1 << $b;
		} else {
			$status &= ~(1 << $b);
		}
		++$b;
	}
	//return $status;
}
?>