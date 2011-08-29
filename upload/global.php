<?php
/**
 *
 *  Copyright (c) 2003-2010  phpwind.net. All rights reserved.
 *  Support : http://www.phpwind.net
 *  This software is the proprietary information of phpwind.com.
 *
 */

file_exists('install.php') && header('Location: ./install.php');

error_reporting(E_ERROR | E_PARSE);
function_exists('set_magic_quotes_runtime') && set_magic_quotes_runtime(0);
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');

define('R_P',getdirname(__FILE__));
define('D_P',R_P);
define('A_P', R_P.'apps/');
define('P_W','global');
defined('SCR') || define('SCR','other');

$P_S_T	 = pwMicrotime();
require_once(R_P.'require/common.php');
S::filter();
require_once (D_P.'data/bbscache/baseconfig.php');
require_once D_P.'data/sql_config.php';
pwCache::getData(D_P.'data/bbscache/config.php');

define('AREA_PATH', R_P . $db_htmdir . '/channel/');
define('PORTAL_PATH', R_P . $db_htmdir . '/portal/');
$db_userurl = ($db_htmifopen && $db_userurlopen) ? 'u/' : 'u.php?uid='; //url
define('USER_URL',$db_userurl);

$db_debug && error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
$timestamp = time();
$db_cvtime!=0 && $timestamp += $db_cvtime*60;
$wind_in = $SCR = SCR;

$onlineip = pwGetIp();
if ($db_forcecharset && !defined('W_P') && !defined('AJAX')) {
	@header("Content-Type:text/html; charset=$db_charset");
}
if ($db_loadavg && !defined('W_P') && pwLoadAvg($db_loadavg)) {
	$db_cc = 2;
}
if ($db_cc && !defined('COL')) {
	pwDefendCc($db_cc);
}

if ($db_htmifopen) {
	$_NGET = parseRewriteQueryString($pwServer['QUERY_STRING']);
	!empty($_NGET) && $_GET = $_NGET;
}

foreach ($_POST as $_key => $_value) {
	if (!in_array($_key,array('atc_content','atc_title','prosign','pwuser','pwpwd'))) {
		S::checkVar($_POST[$_key]);
	}
}
foreach ($_GET as $_key => $_value) {
	S::checkVar($_GET[$_key]);
}

list($wind_version,$wind_repair,$wind_from) = explode(',',WIND_VERSION);
$db_olsize    = 96;

if (false !== ($dirstrpos = strpos($pwServer['SCRIPT_NAME'],$db_dir))) {
	$tmp = substr($pwServer['SCRIPT_NAME'],0,$dirstrpos);
	$pwServer['PHP_SELF'] = "$tmp.php";
	unset($dirstrpos);
} else {
	$tmp = $pwServer['SCRIPT_NAME'];
}
$REQUEST_URI = $pwServer['PHP_SELF'].($pwServer['QUERY_STRING'] ? '?'.$pwServer['QUERY_STRING'] : '');

$_mainUrl = $index_url = $db_bbsurl;
$bbsurlArray = parse_url($db_bbsurl);
$R_url = $db_bbsurl = S::escapeChar(str_replace($bbsurlArray['host'],$pwServer['HTTP_HOST'],$db_bbsurl));
defined('SIMPLE') && SIMPLE && $db_bbsurl = substr($db_bbsurl,0,-7);
$defaultMode = empty($db_mode) ? 'bbs' : $db_mode;
$db_mode = 'bbs';

//二级域名绑定跳转
if (in_array(SCR, array('read', 'thread')) && $_mainUrl !== $db_bbsurl) {
	ObHeader($_mainUrl . substr($REQUEST_URI, strrpos($REQUEST_URI, '/')));
} elseif (SCR == 'u' && !empty($db_modedomain['o']) && $db_modedomain['o'] !== $pwServer['HTTP_HOST']) {
	ObHeader("http://" . $db_modedomain['o'] . substr($REQUEST_URI, strrpos($REQUEST_URI, '/')));
}

if ($cookie_lastvisit = GetCookie('lastvisit')) {
	list($c_oltime,$lastvisit,$lastpath) = explode("\t",$cookie_lastvisit);
	($onbbstime=$timestamp-$lastvisit)<$db_onlinetime && $c_oltime+=$onbbstime;
	unset($cookie_lastvisit);
} else {
	$lastvisit = $lastpath = '';
	$c_oltime = $onbbstime = 0;
	Cookie('lastvisit',$c_oltime."\t".$timestamp."\t".$REQUEST_URI);
}

S::gp(array('fid','tid'),'GP',2);
#$db = $ftp = $credit = null;
$ftp = $credit = null;//distributed

!is_array($manager) && $manager = array();
$newmanager = array();
foreach ($manager as $key => $value) {
	if (!empty($value) && !is_array($value)) {
		$newmanager[$key] = $value;
	}
}
$manager = $newmanager;
if ($database == 'mysqli' && Pwloaddl('mysqli') === false) {
	$database = 'mysql';
}
ObStart();//noizy

if ($db_http != 'N') {
	$imgpath = $db_http;
	if (D_P != R_P) {
		$R_url = substr($db_http,-1)=='/' ?  substr($db_http,0,-1) : $db_http;
		$R_url = substr($R_url,0,strrpos($R_url,'/'));
	}
} else {
	$imgpath = $db_picpath;
}
list($attachpath,$imgdir,$attachdir,$pw_posts,$pw_tmsgs,$runfc) = array($db_attachurl != 'N' ? $db_attachurl : $db_attachname, R_P.$db_picpath, R_P.$db_attachname, 'pw_posts', 'pw_tmsgs', 'N');
list($winduid,$windpwd,$safecv) = explode("\t",addslashes(StrCode(GetCookie('winduser'),'DECODE')));

$loginhash = GetVerify($onlineip,$db_pptkey);
if ($db_pptifopen && $db_ppttype == 'client') {
	if (strpos($db_pptloginurl,'?') === false) {
		$db_pptloginurl .= '?';
	} elseif (substr($db_pptloginurl,-1) != '&') {
		$db_pptloginurl .= '&';
	}
	if (strpos($db_pptregurl,'?') === false) {
		$db_pptregurl .= '?';
	} elseif (substr($db_pptregurl,-1) != '&') {
		$db_pptregurl .= '&';
	}
	$urlencode	= rawurlencode($db_bbsurl);
	$loginurl	= "$db_pptserverurl/{$db_pptloginurl}forward=$urlencode";
	$loginouturl= "$db_pptserverurl/$db_pptloginouturl&forward=$urlencode&verify=$loginhash";
	$regurl		= "$db_pptserverurl/{$db_pptregurl}forward=$urlencode";
} else {
	$loginurl	= 'login.php';
	$loginouturl= "login.php?action=quit&verify=$loginhash";
	$regurl		= $db_registerfile;
}

$ol_offset = (int)GetCookie('ol_offset');
$skinco	   = GetCookie('skinco');

if ($db_refreshtime && SCR != 'register' && str_replace("=",'',$REQUEST_URI) == $lastpath && $onbbstime < $db_refreshtime) {
	!GetCookie('winduser') && $groupid = 'guest';
	$skin = $skinco ? $skinco : $db_defaultstyle;
	Showmsg('refresh_limit');
}
if (!$db_bbsifopen && !defined('CK')) {
	require_once(R_P.'require/bbsclose.php');
}

$H_url =& $db_wwwurl;
$B_url =& $db_bbsurl;
$_time		= array('hours'=>get_date($timestamp,'G'),'day'=>get_date($timestamp,'j'),'week'=>get_date($timestamp,'w'));
$tdtime		= PwStrtoTime(get_date($timestamp,'Y-m-d'));
$montime	= PwStrtoTime(get_date($timestamp,'Y-m').'-1');

if (!defined('CK')) {
	switch (SCR) {
		case 'thread': $lastpos = "F$fid";break;
		case 'read': $lastpos = "T$tid";break;
		case 'cate': $lastpos = "C$fid";break;
		case 'index': $lastpos = 'index';break;
		case 'mode': $lastpos = $db_mode;break;
		default: $lastpos = 'other';
	}

	if ($timestamp-$lastvisit>$db_onlinetime || $lastpos != GetCookie('lastpos') || GetCookie('oltoken') == 'init') {
		$runfc = 'Y';
		Cookie('lastpos',$lastpos);
	}
}
if (is_numeric($winduid) && strlen($windpwd)>=16) {
	$winddb = User_info();
	list($winduid,$groupid,$userrvrc,$windid,$_datefm,$_timedf,$credit_pop) = array($winddb['uid'],$winddb['groupid'],floor($winddb['rvrc']/10),$winddb['username'],$winddb['datefm'],$winddb['timedf'],$winddb['creditpop']);

	if ($credit_pop && $db_ifcredit) {//Credit Changes Tips
		$credit_pop = str_replace(array('&lt;', '&quot;', '&gt;'), array('<', '"', '>'), $credit_pop);
		list($tmpCreditPop, $creditOuterData) = array('', array());
		$creditOuterData = explode(',', $credit_pop);
		foreach ($creditOuterData as $value) {
			$creditdb = explode('|', $value);
			$tmpCreditPop .= ($tmpCreditPop ? '<br/>' : '') .  S::escapeChar(GetCreditLang('creditpop', $creditdb['0']));
			unset($creditdb['0']);
			foreach ($creditdb as $val) {
				list($credit_1, $credit_2) = explode(':', $val);
				$tmpCreditPop .= '<span class="st2">'.pwCreditNames($credit_1).'&nbsp;<span class="f24">'.$credit_2.'</span></span>';
			}
		}
		$credit_pop = $tmpCreditPop;
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), array('creditpop' => ''));
	}

	list($winddb['style'],$ifcustomstyle) = explode('|',$winddb['style']);
	$skin	  = $winddb['style'] ? $winddb['style'] : $db_defaultstyle;
	list($winddb['onlineip']) = explode('|',$winddb['onlineip']);
	$groupid == '-1' && $groupid = $winddb['memberid'];
	$winddb['lastpost'] < $tdtime && $winddb['todaypost'] = 0;
	$curvalue = $db_signcurtype == 'rvrc' ? $userrvrc : $winddb[$db_signcurtype];
	if (getstatus($winddb['userstatus'], PW_USERSTATUS_SHOWSIGN) && (!$winddb['starttime'] && $db_signmoney && strpos($db_signgroup,",$groupid,") !== false && $curvalue > $db_signmoney || $winddb['starttime'] && $winddb['starttime'] != $tdtime)) {
		require_once(R_P.'require/Signfunc.php');
		Signfunc($winddb['starttime'],$curvalue);
	}
	unset($curvalue);
} else {
	$skin	 = $db_defaultstyle;
	$groupid = 'guest';
	$winddb  = $windid = $winduid = $_datefm = $_timedf = '';
}
$verifyhash = GetVerify($winduid);
if ($db_bbsifopen==2 && SCR!='login' && !defined('CK')) {
	require_once(R_P.'require/bbsclose.php');
}
if ($db_ifsafecv && !$safecv && !defined('PRO') && strpos($db_safegroup,",$groupid,") !== false ) {
	Showmsg('safecv_prompt');
}

pwCache::getData(D_P.'data/bbscache/inv_config.php'); 
if ($inv_linkopen && !$windid && (is_numeric($_GET['u']) || ($_GET['a'] && strlen(rawurldecode($_GET['a']))<16)) && strpos($pwServer['HTTP_REFERER'],$pwServer['HTTP_HOST']) === false) {
	S::gp(array('u','a'));
	if ($inv_linktype == 0) {
		$a = rawurldecode($a);
		require_once(R_P.'require/userads.php');
	} else {
		Cookie('userads',"$u\t$a\t".md5($pwServer['HTTP_REFERER']));
	}
}
unset($u,$a,$cookie_userads);

($_POST['skinco']) ? $skinco = $_POST['skinco'] : (($_GET['skinco']) ? $skinco = $_GET['skinco'] : '');
if ($skinco && strpos($skinco,'..')===false && file_exists(D_P."data/style/$skinco.php") ) {
	$skin = $skinco;
	Cookie('skinco',$skin);
}

if ($db_columns && !defined('W_P') && !defined('SIMPLE') && !defined('COL') && !defined('CK')) {
	$j_columns = GetCookie('columns');
	if (!$j_columns) {
		$db_columns==2 && $j_columns = 2;
		Cookie('columns',$j_columns);
	}
	if ($j_columns==2 && (strpos($pwServer['HTTP_REFERER'],$db_bbsurl)===false || strpos($pwServer['HTTP_REFERER'],$db_adminfile)!==false)) {
		strpos($REQUEST_URI,'index.php')===false ? Cookie('columns','1') : ObHeader('columns.php?action=columns');
	}
	unset($j_columns);
}
Ipban();

Cookie('lastvisit',$c_oltime."\t".$timestamp."\t".$REQUEST_URI);

if ($groupid == 'guest' && $db_guestdir && GetGcache()) {
	require_once(R_P.'require/guestfunc.php');
	getguestcache();
}
PwNewDB();
unset($db_whybbsclose,$db_whycmsclose,$db_ipban,$db_diy,$dbhost,$dbuser,$dbpw,$dbname,$pconnect,$manager_pwd,$newmanager);
if ($groupid == 'guest') {
	pwCache::getData(D_P.'data/groupdb/group_2.php');
} elseif (file_exists(D_P."data/groupdb/group_$groupid.php")) {
	pwCache::getData(S::escapePath(D_P."data/groupdb/group_$groupid.php"));
} else {
	pwCache::getData(D_P.'data/groupdb/group_1.php');
}
visitRightByGroup();
if ($_G['pwdlimitime'] && !defined('PRO') && !S::inArray($windid,$manager) && $timestamp-86400*$_G['pwdlimitime']>$winddb['pwdctime'] ) {
	Showmsg('pwdchange_prompt');
}
$cloud_information = CloudWind::getUserInfo();
CloudWind::sendUserInfo($cloud_information);
//响应

/**
 * 跳转
 *
 * @global string $db_ifjump
 * @param string $URL 跳转url
 * @param string $content 跳转提示信息
 * @param int $statime 几秒后跳转
 * @param bool $forcejump
 */
function refreshto($URL, $content, $statime = 1, $forcejump = false) {
	if (defined('AJAX')) Showmsg($content);
	global $db_ifjump,$db_htmifopen;

	if (!$forcejump && !($db_ifjump && $statime > 0)) {
		ObHeader($URL);
	} else {
		if ($db_htmifopen && strtolower(substr($URL,0,4))!=='http') {
			$URL = urlRewrite($URL);
		}
		ob_end_clean();
		global $expires, $db_charset, $tplpath, $fid, $imgpath, $db_obstart, $db_bbsname, $B_url, $forumname, $tpctitle, $db_bbsurl;
		$index_name = & $db_bbsname;
		$index_url = & $B_url;
		ObStart(); //noizy
		extract(L::style());
		//css file for showmsg
		require (L::style('', $skinco, true));
		if ("wind" != $tplpath && file_exists(D_P.'data/style/'.$tplpath.'_css.htm')) {
			$css_path = D_P.'data/style/'.$tplpath.'_css.htm';
		} else{
			$css_path = D_P.'data/style/wind_css.htm';
		}
		//end css file
		$content = getLangInfo('refreshto', $content);
		if (defined('AREA_PAGE') && function_exists('areaLoadFrontView')) {
			require_once areaLoadFrontView('area_manage_refreshto');
		} else {
			require PrintEot('refreshto');
		}
		$output = str_replace(array('<!--<!---->', '<!---->', "\r\n\r\n"), '', ob_get_contents());
		echo ObContents($output);
		exit();
	}
}

/**
 * 302跳转
 *
 * @param string $url
 */
function ObHeader($URL){
	global $db_obstart,$db_bbsurl,$db_htmifopen;
	if ($db_htmifopen && strtolower(substr($URL,0,4))!=='http') {
		$URL = urlRewrite($URL);
	}
	ob_end_clean();
	if (!$db_obstart) {
		ObStart();
		echo "<meta http-equiv='refresh' content='0;url=$URL'>";
		exit;
	}
	header("Location: $URL");
	exit;
}

/**
 * 显示系统提示信息
 *
 * @param string $msg_info 信息内容
 * @param int $dejump ?
 */

function Showmsg($msg_info, $dejump = 0) {
	@extract($GLOBALS, EXTR_SKIP);
	global $stylepath, $tablewidth, $mtablewidth, $tplpath, $db;
	define('PWERROR', 1);
	$msg_info = getLangInfo('msg', $msg_info);
	if (defined('AJAX')) {
		echo $msg_info;
		ajax_footer();
	}
	$showlogin = false;
	if ($dejump != '1' && $groupid == 'guest' && $REQUEST_URI == str_replace(array('register', 'login'), '', $REQUEST_URI) && (!$db_pptifopen || $db_ppttype != 'client')) {
		if (strpos($REQUEST_URI, 'post.php') !== false || strpos($REQUEST_URI, 'job.php?action=vote') !== false || strpos($REQUEST_URI, 'job.php?action=pcjoin') !== false) {
			$tmpTid = (int) S::getGP('tid', 'GP');
			$tmpTid && $REQUEST_URI = substr($REQUEST_URI, 0, strrpos($REQUEST_URI, '/')) . "/read.php?tid=$tmpTid&toread=1";
		}
		$jumpurl = "http://" . $pwServer['HTTP_HOST'] . $REQUEST_URI;
		//list(, $qcheck) = explode("\t", $db_qcheck);
		$qkey = $db_ckquestion & 2 && $db_question ? array_rand($db_question) : '';
		$showlogin = true;
	}
	extract(L::style());
	//css file for showmsg
	require (L::style('', $skinco, true));
	if ("wind" != $tplpath && file_exists(D_P.'data/style/'.$tplpath.'_css.htm')) {
		$css_path = D_P.'data/style/'.$tplpath.'_css.htm';
	} else{
		$css_path = D_P.'data/style/wind_css.htm';
	}
	//end css file
	list($_Navbar, $_LoginInfo) = pwNavBar();
	ob_end_clean();
	ObStart();
	/*
	if (defined('AREA_PAGE') && function_exists('areaLoadFrontView')) {
		require_once areaLoadFrontView('area_manage_showmsg');exit;
	}*/
	require_once PrintEot('showmsg');
	exit();
}

/**
 * 设置响应头
 *
 * @param int $num 响应状态码
 * @param bool $rtarr 是否返回响应头字符串
 * @return string
 */
function sendHeader($num, $rtarr = null) {
	static $sapi = null;
	if ($sapi === null) {
		$sapi = php_sapi_name();
	}
	$header_a = array('200' => 'OK', '206' => 'Partial Content', '304' => 'Not Modified', '404' => '404 Not Found',
		'416' => 'Requested Range Not Satisfiable');
	if ($header_a[$num]) {
		if ($sapi == 'cgi' || $sapi == 'cgi-fcgi') {
			$headermsg = "Status: $num " . $header_a[$num];
		} else {
			$headermsg = "HTTP/1.1: $num " . $header_a[$num];
		}
		if (empty($rtarr)) {
			header($headermsg);
		} else {
			return $headermsg;
		}
	}
	return '';
}

//全局业务
/**
 * 禁止ip
 *
 * @global string $db_ipban
 */
function Ipban() {
	global $db_ipban;
	if ($db_ipban) {
		global $onlineip, $imgpath, $stylepath;
		$baniparray = explode(',', $db_ipban);
		$ip = explode(".",$onlineip);
		if( in_array($ip[0],$baniparray) || in_array($ip[0].'.'.$ip[1],$baniparray) || in_array($ip[0].'.'.$ip[1].'.'.$ip[2],$baniparray) || in_array($ip[0].'.'.$ip[1].'.'.$ip[2].'.'.$ip[3],$baniparray)) {
			Showmsg('ip_ban');
		}
	}
}

//用户业务

/**
 * 获取用户信息
 */
function User_info() {
	global $db, $timestamp, $db_onlinetime, $winduid, $windpwd, $bday, $safecv, $db_ifonlinetime, $c_oltime, $onlineip, $db_ipcheck, $tdtime, $montime, $db_ifsafecv, $db_ifpwcache, $uc_server,$db_md_ifopen;
	PwNewDB();
	$detail = getUserByUid($winduid);
	if (empty($detail) && $uc_server) {
		require_once (R_P . 'require/ucuseradd.php');
	}
	$loginout = 0;
	if ($db_ipcheck && strpos($detail['onlineip'], $onlineip) === false) {
		$iparray = explode('.', $onlineip);
		strpos($detail['onlineip'], $iparray[0] . '.' . $iparray[1]) === false && $loginout = 1;
	}
	if (!$detail || PwdCode($detail['password']) != $windpwd || ($db_ifsafecv && $safecv != $detail['safecv']) || $loginout || $detail['yz'] > 1) {
		$GLOBALS['groupid'] = 'guest';
		require_once (R_P . 'require/checkpass.php');
		Loginout();
		if ($detail['yz'] > 1) {
			$GLOBALS['jihuo_uid'] = $detail['uid'];
			Showmsg('login_jihuo');
		}
		Showmsg('ip_change');
	} else {
		list($detail['shortcut'], $detail['appshortcut']) = explode("\t", $detail['shortcut']);
		unset($detail['password']);
		$detail['honor'] = substrs($detail['honor'], 90);
		$distime = $timestamp - $detail['lastvisit'];
		if ($distime > $db_onlinetime || $distime > 3600) {
			/*--- element update ---start*/
			if ($db_ifpwcache & 1 && SCR != 'post' && SCR != 'thread') {
				L::loadClass('elementupdate', '', false);
				$elementupdate = new ElementUpdate();
				$elementupdate->userSortUpdate($detail);
			}
			/*--- element update ---end*/
			if (!GetCookie('hideid')) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */

				$updateMemberData = $updateByIncrementMemberData = array();
				$updateMemberData['lastvisit'] = $timestamp;
				$updateMemberData['thisvisit'] = $timestamp;

				if ($db_ifonlinetime) {
					$c_oltime = intval($c_oltime);
					$c_oltime = $c_oltime <= 0 ? 0 : ($c_oltime > $db_onlinetime * 1.2 ? $db_onlinetime : $c_oltime);
					$updateByIncrementMemberData['onlinetime'] = $c_oltime;
					if ($detail['lastvisit'] > $montime) {
						$updateByIncrementMemberData['monoltime'] = $c_oltime;
					} else {
						$updateMemberData['monoltime'] = $c_oltime;
					}
					if ($c_oltime) {
						require_once (R_P . 'require/functions.php');
						updateDatanalyse($winduid, 'memberOnLine', $c_oltime);
					}
					$c_oltime = 0;
				}
				if(get_date($timestamp,'Y-m-d') > get_date($detail['lastvisit'],'Y-m-d')){
					/*更新今日登录数*/
					$stasticsService = L::loadClass('Statistics', 'datanalyse');
					$stasticsService->login($winduid);
					
					/*连续登录天数*/
					if ($db_md_ifopen) {
						require_once(R_P.'require/functions.php');
						doMedalBehavior($winduid,'continue_login');
					}
				}
				$userService->update($winduid, array(), $updateMemberData);
				$updateByIncrementMemberData && $userService->updateByIncrement($winduid, array(), $updateByIncrementMemberData);

				$detail['lastvisit'] = $detail['thisvisit'] = $timestamp;
			}
		}
	}
	return $detail;
}

/**
 * 检查用户是否为版块管理员
 *
 * @param string $forumAdmins 版块管理员
 * @param string $fupAdmins 父版块管理员
 * @param string $username 用户名
 * @return bool
 */
function admincheck($forumAdmins, $fupAdmins, $username) {
	if (!$username) {
		return false;
	}
	if ($forumAdmins && strpos($forumAdmins, ",$username,") !== false) {
		return true;
	}
	if ($fupAdmins && strpos($fupAdmins, ",$username,") !== false) {
		return true;
	}
	return false;
}

/**
 * 检查是否允许?
 *
 * @param string $allowGroups 允许的用户组
 * @param int $groupId 用户用户组
 * @param string $userGroups 用户的用户组
 * @param int|string $fid
 * @param string $allowForums 允许的论坛
 * @return bool
 */
function allowcheck($allowGroups, $groupId, $userGroups, $fid = '', $allowForums = '') {
	if ($allowGroups && strpos($allowGroups, ",$groupId,") !== false) {
		return true;
	}
	if ($allowGroups && $userGroups) {
		$groupIds = explode(',', substr($userGroups, 1, -1));
		foreach ($groupIds as $value) {
			if (strpos($allowGroups, ",$value,") !== false) {
				return true;
			}
		}
	}
	if ($fid && $allowForums && strpos(",$allowForums,", ",$fid,") !== false) {
		return true;
	}
	return false;
}

//在线用户业务

/**
 * 更新在线用户
 *
 * @global string $runfc
 * @global string $db_online
 */
function Update_ol() {
	global $runfc, $db_online;
	if ($runfc == 'Y') {
		if ($db_online) {
			Sql_ol();
		} else {
			Txt_ol();
		}
		$runfc = 'N';
	}
}

/**
 * 在线用户文本存储实现
 */
function Txt_ol() {
	global $ol_offset, $winduid, $db_ipstates, $isModify;
	require_once (R_P . 'require/userglobal.php');
	if ($winduid > 0) {
		list($alt_offset, $isModify) = addonlinefile($ol_offset, $winduid);
	} else {
		list($alt_offset, $isModify) = addguestfile($ol_offset);
	}
	$alt_offset != $ol_offset && Cookie('ol_offset', $alt_offset);
	$ipscookie = GetCookie('ipstate');
	if ($db_ipstates && ((!$ipscookie && $isModify === 1) || ($ipscookie && $ipscookie < $GLOBALS['tdtime']))) {
		require_once (R_P . 'require/ipstates.php');
	}
}

/**
 * 在线用户数据库存储实现
 */
function Sql_ol() {
	global $winduid, $timestamp, $db_onlinetime, $db_ipstates, $db_today, $lastvisit, $tdtime, $onlineip;
	$onlineService = L::loadClass('OnlineService', 'user');
	
	// 统计每日来访IP
	$ipscookie = GetCookie('ipstate');
	$guestInfo = $onlineService->getGuestInfo();
	if ($db_ipstates && (
	$ipscookie && $ipscookie < $GLOBALS['tdtime'] ||
	!$ipscookie && GetCookie('oltoken')=='init' && $onlineService->countOnlineGuestByIp($guestInfo['ip']) == 0 ||
	$guestInfo['ipchange'])) {
		require_once (R_P . 'require/ipstates.php');
	}
	
	// 统计每日来访会员
	if ($winduid && $db_today && $timestamp - $lastvisit > $db_onlinetime) {
		require_once (R_P . 'require/today.php');
	}
		
	// 更新在线信息
	if (!$_COOKIE || (GetCookie('oltoken') === null && !$winduid)){
		$onlineService->setGuestToken();
	}else {
		$winduid ? $onlineService->updateOnlineUser() : $onlineService->updateOnlineGuest();
	}
}

//论坛业务

/**
 * 判断
 *
 * @return bool
 */
function GetGcache() {
	global $db_fguestnum, $db_tguestnum, $db_guestindex,$defaultMode;
	$page = isset($GLOBALS['page']) ? (int)$GLOBALS['page'] : (int) $_GET['page'];
	if (SCR == 'thread' && $page < $db_fguestnum && !isset($_GET['type']) && !S::getGP('search')) {
		return true;
	} elseif (SCR == 'read' && $page < $db_tguestnum && !isset($_GET['uid'])) {
		return true;
	} elseif (SCR == 'index' && $db_guestindex && !isset($_GET['cateid']) && (($defaultMode=='bbs' && !$_GET['m']) || $_GET['m']=='bbs')) {
		return true;
	}
	return false;
}

/**
 * 获取版块短名
 *
 * @global array $winddb
 * @global array $forum
 * @global string $winduid
 * @global string $db_shortcutforum
 * @return array
 */
function pwGetShortcut() {
	static $sForumsShortcut = array();
	if (empty($sForumsShortcut)) {
		global $winduid, $db_shortcutforum;
		$sForumsShortcut = pwGetMyShortcut();
		if (empty($sForumsShortcut)) {
			if (!$db_shortcutforum && $winduid) {
				require_once (R_P . 'require/updateforum.php');
				updateshortcut();
				//$sForumsShortcut = updateshortcut();
			} 
		}
	}
	/*侧栏 等处因删除无权查看的隐藏板块*/
	global $winddb, $forum ,$groupid,$windid;
	extract(pwCache::getData(D_P . 'data/bbscache/forum_cache.php', false));
	foreach($sForumsShortcut as $k=>$v){
		if($forum[$k]['f_type'] == 'hidden'
			&& (!allowcheck($forum['allowvisit'], $groupid, $winddb['groups'], $forum['fid'], $winddb['visit']) && !S::inArray($windid, $manager))) {
				unset($sForumsShortcut[$k]);
		}
	}
	return $sForumsShortcut;
}

function pwGetMyShortcut(){
	static $sMyForumsShortcut = array();
	if (empty($sMyForumsShortcut)) {
		global $winddb, $forum;
		if (trim($winddb['shortcut'], ',')) {
			if (!isset($forum)) {
				extract(pwCache::getData(D_P . 'data/bbscache/forum_cache.php', false));
			}
			$shortcuts = explode(',', $winddb['shortcut']);
			foreach ($shortcuts as $value) {
				if ($value && isset($forum[$value])) {
					$sMyForumsShortcut[$value] = strip_tags($forum[$value]['name']);
				}
			}
		}
	}
	return $sMyForumsShortcut;
}

//任务调度业务

/**
 * 运行任务调度
 */
function runTask() {
	$taskClass = L::loadclass('task', 'task');
	$taskClass->run();
}

//任务系统业务

/**
 * 运行用户任务系统
 *
 * @global string $db_job_isopen
 * @global int $winduid
 * @global int $groupid
 */
function runJob() {
	global $db_job_isopen, $winduid, $groupid;
	if (!$db_job_isopen || !$winduid) {
		return;
	}
	$taskClass = L::loadclass('autojob', 'job');
	$taskClass->run($winduid, $groupid);
}

//模式

/**
 * 选择模式
 *
 * @param string $modeName 模式名
 */
function selectMode(&$modeName,$controll = '') {
	global $defaultMode, $db_mode, $db_modes, $db_modepages, $pwServer, $db_modedomain, $REQUEST_URI, $_mainUrl;
	if (defined('M_P'))
		return;
	if (in_array(SCR, array('index', 'mode'))) {
		$db_mode = $defaultMode;
		if (!$modeName && $db_modedomain) {
			$modeName = array_search($pwServer['HTTP_HOST'], $db_modedomain);
		}
		if ($db_modes && isset($db_modes[$modeName]) && is_array($db_modes[$modeName]) && ($db_modes[$modeName]['ifopen'] || ($modeName == 'area' && in_array($controll,array('manage','dialog'))))) {
			$db_mode = $modeName;
		}
		if (!empty($db_mode) && $db_mode != 'bbs' && file_exists(R_P . "mode/$db_mode/")) {
			define('M_P', R_P . "mode/$db_mode/");
			$db_modepages = $db_modepages[$db_mode];
			$GLOBALS['pwModeImg'] = "mode/$db_mode/images";
		}
		//二级域名绑定跳转
		if (defined('M_P') && !defined('HTML_CHANNEL') && !empty($db_modedomain[$db_mode]) && $db_modedomain[$db_mode] !== $pwServer['HTTP_HOST']) {
			ObHeader('http://' . $db_modedomain[$db_mode] . substr($REQUEST_URI, strrpos($REQUEST_URI, '/')));
		}
	}
}

/**
 * 获取二级域名 //TODO 没有被调用
 *
 * @param string $url
 * @param string $mainUrl
 * @return string
 */
function getSecDomain($url, $mainUrl = null) {
	global $pwServer;
	if ($mainUrl && $url == $mainUrl) {
		return '';
	}
	$dirname = substr($pwServer['HTTP_HOST'], 0, strpos($pwServer['HTTP_HOST'], '.'));
	if (preg_match('/[^\w]' . $dirname . '\./i', $mainUrl)) {
		return '';
	}
	return $dirname;
}

//语言包

/**
 * 获取语言包文件路径
 *
 * @param string $lang 语言文件包名
 * @param string $EXT 扩展名
 */
function GetLang($lang, $EXT = 'php') {
	global $tplpath;
	
	if (file_exists(R_P . "template/$tplpath/lang_$lang.$EXT")) {
		return R_P . "template/$tplpath/lang_$lang.$EXT";
	} elseif (file_exists(R_P . "template/wind/lang_$lang.$EXT")) {
		return R_P . "template/wind/lang_$lang.$EXT";
	} else {
		exit("Can not find lang_$lang.$EXT file");
	}
}

//模板

/**
 * 获取模板文件路径
 *
 * @global string $db_mode
 * @global array $db_modes //TODO 未使用
 * @global string $pwModeImg
 * @global string $db_tplstyle
 * @global string $appdir
 * @global array $tplapps
 * @global string $db_tplpath
 * @param string $template 模板文件名
 * @param string $EXT 扩展名
 * @return string
 */
function PrintEot($template, $EXT = 'htm') {
	!$template && $template = 'N';
	static $bbsTemplate = null;
	isset($bbsTemplate) || $bbsTemplate = new template(new bbsTemplate());
	return $bbsTemplate->printEot($template, $EXT);

	global $db_mode, $db_modes, $pwModeImg, $db_tplstyle, $appdir;
	!$template && $template = 'N';

	
	if (!defined('PWERROR')) { //apps template render
		//zhudong 通过判断模板名称为'm_'开头的调用apps目录下的模板
		if (defined('A_P') && $appdir && substr($template,0,2) == 'm_' && file_exists(A_P . "$appdir/template/$template.$EXT")) {
			return S::escapePath(A_P . "$appdir/template/$template.$EXT");
		}
		if (defined('F_M')/* || ($db_mode && $db_mode != 'bbs')*/) {
			$temp = modeEot($template, $EXT);
			if ($temp)
				return S::escapePath($temp);
		}
	}
	//if (defined('A_P') && !in_array($template,array('header','footer'))/* || ($db_mode && $db_mode != 'bbs')*/) {
	//	return A_P."$appdir/template/$template.$EXT";
	//}
	if (file_exists(R_P . "template/$tplpath/$template.$EXT")) {
		return S::escapePath(R_P . "template/$tplpath/$template.$EXT");
	} elseif (file_exists(R_P . "template/wind/$template.$EXT")) {
		return S::escapePath(R_P . "template/wind/$template.$EXT");
	} else {
		exit("Can not find $template.$EXT file");
	}
}

/**
 * 输出页脚，并处理输出缓存中的内容
 */
function footer() {
	global $db, $db_obstart, $db_footertime, $P_S_T, $mtablewidth, $db_ceoconnect, $wind_version, $imgpath, $stylepath, $footer_ad, $db_union, $timestamp, $db_icp, $db_icpurl, $db_advertdb, $groupid, $db_ystats_ifopen, $db_ystats_unit_id, $db_ystats_style, $pwServer, $db_ifcredit, $credit_pop, $db_foot, $db_mode, $db_modes, $shortcutforum, $_G, $winddb, $db_toolbar, $winduid, $db_menuinit, $db_appifopen, $db_job_ispop, $db_job_isopen, $db_siteappkey, $_Navbar,$db_statscode;

	defined('AJAX') && ajax_footer();

	$wind_spend = '';

	//$db_statscode = html_entity_decode($db_statscode);
	$ft_gzip = ($db_obstart ? 'Gzip enabled' : 'Gzip disabled') . $db_union[3];
	if ($db_footertime == 1) {
		$totaltime = number_format((pwMicrotime() - $P_S_T), 6);
		$qn = $db ? $db->query_num : 0;
		$wind_spend = "Total $totaltime(s) query $qn,";
		
	}
	$ft_time = get_date($timestamp, 'm-d H:i');
	$db_icp && $db_icp = "<a href=\"http://www.miibeian.gov.cn\" target=\"_blank\">$db_icp</a>";

	if ($db_toolbar) {
		if ($_COOKIE['toolbarhide']) {
			$toolbarstyle = 'style="display:none"';
			$openbarstyle = '';
			$closebarstyle = 'style="display:none"';
		} else {
			$toolbarstyle = '';
			$openbarstyle = 'style="display:none"';
			$closebarstyle = '';
			if ($db_appifopen) {
				$appshortcut = trim($winddb['appshortcut'], ',');
				if (!empty($appshortcut) && $db_siteappkey) {
					$appshortcut = explode(',', $appshortcut);
					$bottom_appshortcut = array();
					$appclient = L::loadClass('appclient');
					$bottom_appshortcut = $appclient->userApplist($winduid, $appshortcut, 1);
				}
			}
		}
	}
	$db_menuinit = trim($db_menuinit, ',');

	runJob();

	require PrintEot('footer');
	if ($db_advertdb['Site.PopupNotice'] || $db_advertdb['Site.FloatLeft'] || $db_advertdb['Site.FloatRight'] || $db_advertdb['Site.FloatRand']) {
		require PrintEot('advert');
	}
	pwOutPut();
}

function pwOutPut() {
	global $db_htmifopen, $db_redundancy, $SCR, $groupid;
	$masterDb = $GLOBALS['db']->getMastdb();
	if ($masterDb->arr_query) {
		writeover(D_P . "data/sqllist.txt", $masterDb->arr_query, 'wb');
	}
	Update_ol();
	$output = parseHtmlUrlRewrite(ob_get_contents(), $db_htmifopen);
	if ($db_redundancy && $SCR != 'post') {
		$output = str_replace(array("\r", '<!--<!---->-->', '<!---->-->', '<!--<!---->', "<!---->\n", '<!---->', '<!-- -->', "<!--\n-->", "\t\t", '    ', "\n\t", "\n\n"), array('', '', '', '', '', '', '', '', '', '',"\n", "\n"), $output);
	} else {
		$output = str_replace(array('<!--<!---->-->','<!---->-->', '<!--<!---->', "<!---->\r\n", '<!---->', '<!-- -->', "\t\t\t"), '', $output);
	}
	if ($SCR != 'post' && !defined('AJAX')) {
		$ceversion = defined('CE') ? 1 : 0;
		$output .= "<script type=\"text/javascript\">(function(d,t){
var url=\"http://init.phpwind.net/init.php?sitehash={$GLOBALS[db_sitehash]}&v={$GLOBALS[wind_version]}&c=$ceversion\";
var g=d.createElement(t);g.async=1;g.src=url;d.body.appendChild(g)}(document,\"script\"));</script>";
	}
	if ($groupid == 'guest' && !defined('MSG') && GetGcache()) {
		require_once (R_P . 'require/guestfunc.php');
		creatguestcache($output);
	}
	if (defined('SHOWLOG')) Error::writeLog();
	if (defined('PW_PACK_FILES')) pwPack::files();
	echo ObContents($output);
	unset($output);
	N_flush();
	exit();
}

/**
 * 获取目录路径
 *
 * @param string $path 文件路径
 * @return string
 */
function getdirname($path = null) {
	if (!empty($path)) {
		if (strpos($path, '\\') !== false) {
			return substr($path, 0, strrpos($path, '\\')) . '/';
		} elseif (strpos($path, '/') !== false) {
			return substr($path, 0, strrpos($path, '/')) . '/';
		}
	}
	return './';
}

/**
 * 设置状态
 *
 * @param int $status
 * @param int $b
 * @param string $setv
 */
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


//安全

/**
 * 获取客户端唯一hash
 *
 * @param string $str 附加信息
 * @param string $app
 * @return string
 */
function GetVerify($str, $app = null) {
	empty($app) && $app = $GLOBALS['db_siteid'];
	return substr(md5($str . $app . $GLOBALS['pwServer']['HTTP_USER_AGENT']), 8, 8);
}

/**
 * POST请求检查
 *
 * @global array $pwServer
 * @param int $checkHash 是否检查请求hash
 * @param int $checkGd 是否检查验证码
 * @param int $checkQuestion 是否检查安全问题
 * @param int $checkReferer 是否检查refer
 */
function PostCheck($checkHash = 1, $checkGd = 0, $checkQuestion = 0, $checkReferer = 1) {
	global $pwServer;
	$checkHash && checkVerify();
	if ($checkReferer && $pwServer['REQUEST_METHOD'] == 'POST') {
		$refererParsed = @parse_url($pwServer['HTTP_REFERER']);
		if ($refererParsed['host']) {
			list($httpHost) = explode(':', $pwServer['HTTP_HOST']);
			if ($refererParsed['host'] != $httpHost) {
				Showmsg('undefined_action');
			}
		}
	}
	$checkGd && GdConfirm($_POST['gdcode']);
	$checkQuestion && Qcheck($_POST['qanswer'], $_POST['qkey']);
}

/**
 * 校验请求的hash字符串
 *
 * @param string $hash 系统hash的key
 */
function checkVerify($hash = 'verifyhash') {
	S::getGP('verify') != $GLOBALS[$hash] && Showmsg('illegal_request');
}

/**
 * 校验验证码
 *
 * @param string $code
 */
function GdConfirm($code,$bool = null) {
	Cookie('cknum', '', 0);
	if (!$code || !SafeCheck(explode("\t", StrCode(GetCookie('cknum'), 'DECODE')), strtoupper($code), 'cknum', 1800)) {
		if($bool){
			return false;
		}else{
			Showmsg('check_error');
		}
	}
	return true;
}

/**
 * 随机机器问题1
 * @param boolean $setCookie
 */
function getMachineQuestion_1($setCookie = true){
	global $timestamp;
	$alg = mt_rand(0,1);//+-
	$num1 = mt_rand(1,100);
	switch($alg){
		case 0:
			$num2 = mt_rand(0,100-$num1);
			$symbol = '+';
			$answer = $num1 + $num2;
			break;
		case 1:
			$num2 = mt_rand(0,$num1);
			$symbol = '-';
			$answer = $num1 - $num2;
			break;
	}
	$setCookie && Cookie('ckquestion',StrCode($timestamp."\t\t".md5($answer.$timestamp . getHashSegment())));
	return sprintf('%s %s %s = ?',$num1,$symbol,$num2);
}
/**
 * 校验问题
 *
 * @global string $db_question
 * @global array $db_answer
 * @param string $answer 答案
 * @param string $qkey
 */
function Qcheck($answer, $qkey, $return = false) {
	global $db_question, $db_answer;
	$answer = trim($answer);
	if($qkey < 0){
		//机选问题
		//Cookie('ckquestion', '', 0);
		if(!is_string($answer) || $answer === '' || !SafeCheck(explode("\t", StrCode(GetCookie('ckquestion'), 'DECODE')), $answer, 'ckquestion', 1800 , false ,false)){
			if ($return) return false;
			Showmsg('qcheck_error');
		}
	}elseif($db_question && (!isset($db_answer[$qkey]) || $answer != $db_answer[$qkey])){
		if ($return) return false;
		Showmsg('qcheck_error');
	}
	if ($return) return true;
}

//数据库

//系统

/**
 * 加载扩展
 *
 * @param string $module 扩展模块名
 * @param string $checkFunction 检测函数
 * @return bool
 */
function Pwloaddl($module, $checkFunction = 'mysqli_get_client_info') {
	return extension_loaded($module) && $checkFunction && function_exists($checkFunction) ? true : false;
}

/**
 * 操作加锁
 *
 * @param string $action 操作名
 * @param int $uid
 * @return bool 是否成功
 */
function procLock($action, $uid = 0) {
	global $db, $timestamp;
	if ($db->query("INSERT INTO pw_proclock (uid,action,time) VALUES ('$uid','$action','$timestamp')", 'U', false)) {
		return true;
	}
	$db->update("DELETE FROM pw_proclock WHERE uid='$uid' AND action='$action' AND time < '$timestamp' - 30");
	return false;
}

/**
 * 操作解锁
 *
 * @param string $action 操作名
 * @param int $uid
 */
function procUnLock($action = '', $uid = 0) {
	$GLOBALS['db']->update("DELETE FROM pw_proclock WHERE uid='$uid' AND action='$action'");
}

/**
 * 获取微妙时间
 *
 * performance 2010-2-10
 * @return float
 */
function pwMicrotime() {
	$t_array = explode(' ', microtime());
	return $t_array[0] + $t_array[1];
}

/**
 * 生成导航条信息
 *
 * @return array
 */
function pwNavBar() {
	global $winduid, $db_mainnav, $db_menu, $groupid, $winddb, $SCR, $db_modes, $db_mode, $defaultMode, $db_menuinit;
	global $alias;

	$tmpLogin = $tmpNav = array();
	if ($groupid != 'guest') {
		require_once (R_P . 'require/showimg.php');
		list($tmpLogin['faceurl']) = showfacedesign($winddb['icon'], 1, 's');
		$tmpLogin['lastlodate'] = get_date($winddb['lastvisit'], 'Y-m-d');
	} else {
		global $db_question, $db_logintype, $db_qcheck,$db_ckquestion;
		if ($db_question) {
			list(,$tmpLogin['showq']) = explode("\t", $db_qcheck);
			$tmpLogin['qcheck'] = $db_ckquestion & 2;
			if ($tmpLogin['qcheck'])
				$tmpLogin['qkey'] = array_rand($db_question);
		}
		if ($db_logintype) {
			for ($i = 0; $i < 3; $i++) {
				if ($db_logintype & pow(2, $i))
					$tmpLogin['logintype'][] = $i;
			}
		} else {
			$tmpLogin['logintype'][0] = 0;
		}
	}
	$postion = $db_mode;

	if (defined('APP_GROUP')) $postion = 'group';  //群组定位特殊处理
	$currentPostion = array();
	$currentPostion['mode'] = $postion;
	if (in_array(SCR, array('index', 'cate', 'mode', 'read', 'thread')) || $SCR == 'm_home') {
		$currentPostion['mode'] = empty($postion) ? 'bbs' : $postion;
	}
	if ($currentPostion['mode'] == 'area' && $alias) $currentPostion['alias'] = $alias;
	$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
	$tmpNav[PW_NAV_TYPE_MAIN] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_MAIN, $postion, $currentPostion);
	$tmpNav[PW_NAV_TYPE_HEAD_LEFT] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_HEAD_LEFT, $postion);
	$tmpNav[PW_NAV_TYPE_HEAD_RIGHT] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_HEAD_RIGHT, $postion);
	$tmpNav[PW_NAV_TYPE_FOOT] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_FOOT, $postion);

	return array($tmpNav, $tmpLogin);
}

/**
 * 生成导航html
 *
 * @param array $navData 导航配置数据数组
 */
function buildNavLinkHtml($navData) {
	$title = strip_tags($navData['title']);
	$navData['style']['b'] && $title = "<b>$title</b>";
	$navData['style']['i'] && $title = "<span style=\"font-style:oblique\">$title</span>";
	$navData['style']['u'] && $title = "<u>$title</u>";
	$navData['style']['color'] && $title = "<font color=\"".$navData['style']['color']."\">$title</font>";

	$target = $navData['target'] ? 'target="_blank"' : '';
	return '<a id="nav_key_up_'.$navData['nid'].'" href="'.$navData['link'].'" title="'.$navData['alt'].'" '.$target.'>'.$title.'</a>';
}

/**
 * 根据用户组来判断站点访问权限
 */
function visitRightByGroup() {
	global $_G, $groupid, $manager, $windid, $pwServer;

	if (defined('CK') && CK == 1) return;
	if (S::inArray(SCR,array('sendpwd', 'login', 'register', 'job'))) {
		$action = S::getGP('action');
		if (SCR !== 'job' || $pwServer['HTTP_USER_AGENT'] == 'Shockwave Flash' && S::inArray($action, array('mutiupload', 'mutiuploadphoto', 'uploadicon'))) return;
	}

	if (empty($_G['allowvisit'])) {
		if (empty($groupid) || $groupid == 'guest') {
			if (defined('AJAX') && $_GET['action'] == 'pwschools') return;
			ObHeader('login.php');
		} elseif (!S::inArray($windid, $manager)) {
			@extract($GLOBALS, EXTR_SKIP);
			require_once (R_P.'header.php');
			require_once PrintEot('error');
			footer();
		}
	}
}

function parseRewriteQueryString($queryString){
	global $db_ext;
	$_NGET = array();
	$self_array = false !== strpos($queryString, '&') ? array() : explode('-',substr($queryString,0,strpos($queryString,$db_ext)));
	for ($i=0, $s_count=count($self_array); $i<$s_count-1;$i++) {
		$_key	= $self_array[$i];
		$_value	= rawurldecode($self_array[++$i]);
		$_NGET[$_key] = addslashes($_value);
	}
	return $_NGET;
}
class bbsTemplate {

	var $dir;

	function bbsTemplate() {
		$this->dir = R_P . 'template/';
	}

	function getpath($template, $EXT = 'htm') {
		if (!defined('PWERROR')) {
			global $appdir;
			if (defined('A_P') && $appdir && substr($template,0,2) == 'm_' && file_exists(A_P . "$appdir/template/$template.$EXT")) {
				return S::escapePath(A_P . "$appdir/template/$template.$EXT");
			}
			if (defined('F_M')/* || ($db_mode && $db_mode != 'bbs')*/) {
				$temp = modeEot($template, $EXT);
				if ($temp)
					return S::escapePath($temp);
			}
		}
		$tplpath = L::style('tplpath');
		if (file_exists($this->dir . "$tplpath/$template.$EXT")) {
			return $this->dir . "$tplpath/$template.$EXT";
		}
		if (file_exists($this->dir . "wind/$template.$EXT")) {
			return $this->dir . "wind/$template.$EXT";
		}
		return false;
	}

	function getDefaultDir() {
		return $this->dir . 'wind/';
	}
}
?>