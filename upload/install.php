<?php
/**
 * Copyright (c) 2003-2103  phpwind.net. All rights reserved.
 *
 * @filename: install.php
 * @author: Noizy Sky_hold cn0zz Fengyu Xiaolang Zhudong
 */
set_time_limit(0);
error_reporting(E_ERROR | E_PARSE);
function_exists('set_magic_quotes_runtime') && set_magic_quotes_runtime(0);
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');

define('PW_UPLOAD',1);
define('R_P',getdirname(__FILE__));
define('D_P',R_P);
define('P_W','global');

require_once(R_P.'require/security.php');
require (R_P.'require/common.php');
pwInitGlobals();

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$selfrpos = strrpos($_SERVER['PHP_SELF'],'/');
$basename = substr($_SERVER['PHP_SELF'],$selfrpos+1);
$bbsurl = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, $selfrpos);
$crlf = GetCrlf();
$onlineip = pwGetIp();
$timestamp = time();

require_once(R_P.'lang/install_lang.php');

/**
 * 安装信息配置
 */
$_WIND			= 'install';
$from_version	= '';
list($wind_version, $versionDate) = explode(',', WIND_VERSION); 
$wind_repair	= '';
$wind_from		= '';//Customized version
$_actionStep = array(/*'index',*/'readme', 'writable', 'database', 'createtable', 'hack',
					'view', 'initdata', 'other', 'mode', 'custom',/*'testdata',*/ 'static',
					/*'resources',*/'finish');

$lockfile = 'install.lock';
file_exists(D_P."data/$lockfile") && Promptmsg('have_file');

InitGP(array('action','step'));
$action || $action = 'readme';

/**
 * 安装首页

if ($action == 'index') {
	$footer = true;
	$lang['log_install'] = str_replace('{#basename}',$basename,$lang['log_install']);
	@unlink(D_P.'data/install_sys.sql');
	@unlink(D_P.'data/install_hack.sql');
	@unlink(D_P.'data/install_test.sql');
	@unlink(D_P."data/install.log");
	pwViewHtml($action);exit;
}
*/

/**
 * 版权阅读
 */
if ($action == 'readme') {
	$wind_licence = readover(R_P.'licence.txt');
	@unlink(D_P.'data/install_sys.sql');
	@unlink(D_P.'data/install_hack.sql');
	@unlink(D_P.'data/install_test.sql');
	@unlink(D_P."data/install.log");
	list($prev,$next) = getStepto($action);
	pwViewHtml($action);exit;
}

/**
 * 目录检测可写性和目录是否存在
 */
if ($action == 'writable') {
	$w_check = array(
		'attachment/',
		'attachment/cn_img/',
		'attachment/photo/',
		'attachment/pushpic/',
		'attachment/thumb/',
		'attachment/upload/',
		'attachment/upload/middle/',
		'attachment/upload/small/',
		'attachment/upload/tmp/',
		'attachment/mini/',
		'attachment/mutiupload/',
		'data/',
		'data/bbscache/',
		'data/forums/',
		'data/groupdb/',
		'data/guestcache/',
		'data/tplcache/',
		'data/style/',
		'data/tmp/',
		'html/',
		'html/js/',
		'html/stopic/',
		'html/read/',
		'html/channel/',
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
	$writable = array();
	foreach ($w_check as $filename) {
		!N_writable(R_P . $filename) && $writable[] = $filename;
	}
	if (file_exists(D_P.'data/sql_config.php') && !N_writable(D_P.'data/sql_config.php')) {
		$writable[] = 'data/sql_config.php';
	}

	$recommendEnvironment = array(
		'os' => $lang['env_os'],
		'version' => '>= 5.0',
		'upload' => '2M',
		'space' => $lang['unlimited']
	);
	$lowestEnvironment = array(
		'os' => $lang['unlimited'],
		'version' => '4.3',
		'upload' => $lang['unlimited'],
		'space' => '50M'
	);
	$currentEnvironment = getCurrentEnvironment();
	
	list($prev,$next) = getStepto($action);
	pwViewHtml($action);exit;
}

/**
 * 填写数据库信息，创始人信息
 */
if ($action == 'database') {
	if ($step != 2) {
		$phplinfo = PHP_VERSION;
		$mysqlichecked = $phplinfo >= '5.1.0' ? 'CHECKED' : '';
		pwViewHtml($action);exit;
	} else {
		InitGP(array('dbhost','dbuser','dbpw','dbname','database','PW','manager','manager_pwd','manager_ckpwd','manager_email'));
		!$manager_email && Promptmsg('error_nothing');
		$mysqlimsg = '';
		$input = "<input type=\"hidden\" name=\"manager_email\" value=\"$manager_email\">";
		$input .= "<input type=\"hidden\" name=\"step\" value=\"2\">";
		if (!file_exists(D_P.'data/sql_config.php') || $_POST['from'] != 'prompt') {
			if (!N_writable(D_P . 'data/sql_config.php')) {
				$filename = 'data/sql_config.php';
				Promptmsg('error_777');
			}
			if (!$dbhost || !$dbuser || !$dbname || !$PW || !$manager || !$manager_pwd) {
				Promptmsg('error_nothing');
			}
			if ($manager_pwd !== $manager_ckpwd) {
				Promptmsg('error_ckpwd');
			}
			$manager_pwd = md5($manager_pwd);
			$charset = str_replace('-', '', $lang['db_charset']);
			require R_P.'require/db_mysql.php';
			$db = new DB($dbhost, $dbuser, $dbpw, '', $pconnect);
			$masterDb = $db->getMastdb();
			$mysqlinfo = mysql_get_server_info($masterDb->sql);
			if ($database == 'mysqli') {
				if ($mysqlinfo < '4.1.3') {
					$database = 'mysql';
					$mysqlimsg = $lang['error_mysqli'];
				} else {
					ob_end_clean();
					if (Pwloaddl('mysqli') === false) {
						$database = 'mysql';
						$mysqlimsg = $lang['error_mysqli'];
					}
					ob_start();
					$steptitle = $step;
				}
			}

			$manager = array($manager);
			$manager_pwd = array($manager_pwd);
			$newconfig = array(
				'dbhost' => $dbhost,
				'dbuser' => $dbuser,
				'dbpw' => $dbpw,
				'dbname' => $dbname,
				'database' => $database,
				'PW' => $PW,
				'pconnect' => 0,
				'charset' => $charset,
				'manager' => $manager,
				'manager_pwd' => $manager_pwd,
				'db_hostweb' => 1,
				'attach_url' => array()
			);
			$tplpath = 'wind';
			require_once(R_P.'require/updateset.php');
			write_config($newconfig);
			unset($newconfig);

			if ($mysqlinfo > '4.1') {
				$db->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET $charset");
			} else {
				$db->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
			}
			if (mysql_errno()) {
				Promptmsg('error_nodatabase');
			} elseif ($query = mysql_query("SELECT COUNT(*) FROM `$dbname`.{$PW}members")) {
				Promptmsg('have_install', $action);
			}
			mysql_select_db($dbname);
		}

		list($prev, $next) = getStepto($action);
		pwHeader("$basename?action=$next");exit;
	}
}

/**
 * 创建数据库表
 */
if ($action == 'createtable') {
	if (!file_exists(D_P.'data/sql_config.php')) {
		Promptmsg('config_noexists','database');
	} else {
		$db = pwNewDBForInstall();
	}
	@set_time_limit(200);
	@unlink(D_P."data/install.log");
	require (D_P.'data/sql_config.php');
	$t		 = getdate($timestamp + 8 * 3600);
	$tdtime  = (floor($timestamp / 3600) - $t['hours']) * 3600;
	$lang['db_bbsurl'] = $bbsurl;
	$lang['db_manager'] = $manager[0];
	$content = str_replace(array("\r", "\n\n", ";\n"), array('', "\n", ";<wind>\n"), trim(readover(R_P . 'lang/install_wind.sql'), " \n"));

	$db_floorname = array($lang['db_floorname_1'], $lang['db_floorname_2'], $lang['db_floorname_3'], $lang['db_floorname_4']);
	$db_sitemsg = array(
		"reg"	=> array($lang['db_sitemsg_1'], $lang['db_sitemsg_2']),
		"login"	=> array($lang['db_sitemsg_3']),
		"post"	=> array($lang['db_sitemsg_4'], $lang['db_sitemsg_5'], $lang['db_sitemsg_6']),
		"reply"	=> array($lang['db_sitemsg_4'], $lang['db_sitemsg_5'], $lang['db_sitemsg_6']),
	);
	$lang['db_floorname'] = addslashes(serialize($db_floorname));
	$lang['db_sitemsg'] = addslashes(serialize($db_sitemsg));
	$content = preg_replace("/{#(.+?)}/eis", '$lang[\'\\1\']', $content) . '<wind>';

	$content = explode("\n", $content);
	$writearray = array($lang['success_3']);
	$writearray = SQLCreate($content);

	$userstatus = 0;
	setstatus($userstatus, 7);
	setstatus($userstatus, 8);

	$db->update("REPLACE INTO pw_members (username,password,icon,email,groupid,memberid,regdate,userstatus,shortcut) VALUES ('$manager[0]','$manager_pwd[0]','none.gif|1|||','$manager_email','3','8','$timestamp','$userstatus',',article,write,diary,share,groups,photos,')");
	$uid = $db->insert_id();
	$db->update("REPLACE INTO pw_memberdata (uid,lastvisit,thisvisit) VALUES ('$uid','$timestamp','$timestamp')");
	$db->update("REPLACE INTO pw_bbsinfo (id,newmember,totalmember,tdtcontrol) VALUES ('1','$manager[0]','1','$tdtime')");
	$db->update("REPLACE INTO pw_administrators(uid,username,groupid,groups) VALUES('$uid','$manager[0]','3','')");

	writeover(D_P . "data/install.log", implode("\n", $writearray) . "\n$lang[success_3_2]", 'ab+');
	list($prev, $next) = getStepto($action);
	pwHeader("$basename?action=$next");exit;
}

/**
 * 初始化数据库数据
 */
if ($action == 'initdata') {
	if ($step != 2) {
		$sqlcache = readover(D_P . 'data/install_sys.sql');
		if (trim($sqlcache)) {
			$input = "<input type=\"hidden\" name=\"step\" value=\"2\">";
			Promptmsg('install_initdata', $action, true);
		} else {
			@unlink(D_P . 'data/install_sys.sql');
			@unlink(D_P . 'data/lock');
			list($prev, $next) = getStepto($action);
			pwHeader("$basename?action=$next");exit;
		}
	} else {
		$input = "<input type=\"hidden\" name=\"step\" value=\"2\">";
		@set_time_limit(200);
		if (!file_exists(D_P . 'data/sql_config.php')) {
			Promptmsg('config_noexists', 'database');
		} else {
			$db = pwNewDBForInstall();
		}
		$lockfile = D_P . 'data/lock';
		$fp = fopen($lockfile, 'wb+');
		flock($fp, LOCK_EX);
		$sqlcache = readover(D_P . 'data/install_sys.sql');
		$update = SQLUpdate($sqlcache);
		fclose($fp);
		if ($update) {
			Promptmsg('install_initdata', $action, true);
		} else {
			@unlink(D_P . 'data/install_sys.sql');
			@unlink(D_P . 'data/lock');
			list($prev, $next) = getStepto($action);
			pwHeader("$basename?action=$next");exit;
		}
	}
}

/**
 * 自定义安装插件
 */
if ($action == 'hack') {
	if (!file_exists(D_P.'data/sql_config.php')) {
		Promptmsg('config_noexists','database');
	} else {
		$db = pwNewDBForInstall();
	}
	if (0 && $step != 2) {
		$uninstalldb = (array)HackList();
		pwViewHtml($action);exit;
	} else {
		InitGP(array('hackdb'));
		$uninstalldb = (array)HackList();
		foreach ($uninstalldb as $value) {
			$hackdb[] = $value[1];
		}
		$createcache = '';
		$db_hackdb = array();
		if (is_array($hackdb)) {
			foreach ($hackdb as $value) {
				if ($uninstalldb[$value][0]) {
					$db_hackdb[$value] = $uninstalldb[$value];
					$createcache .= trim(readover(R_P . "hack/$value/sql.txt")," \n") . "\n";
				}
			}
			if ($createcache) {
				$createcache = explode("\n", str_replace(array("\r", "\n\n", ";\n"), array('', "\n", ";<wind>\n"), $createcache . '<wind>'));
				if ($createcache) {
					$writearray = SQLCreate($createcache, 'hack');
					$db_hackdb = addslashes(serialize($db_hackdb));
				}
			}
			$db->update("REPLACE INTO pw_config(db_name,vtype,db_value) VALUES ('db_hackdb','array','$db_hackdb')");
		}
		$sqlcache = readover(D_P.'data/install_hack.sql');
		$sqlcache && SQLUpdate($sqlcache, 800, 'hack');
		writeover(D_P . "data/install.log", $lang['success_4'], 'ab+');

		list($prev, $next) = getStepto($action);
		pwHeader("$basename?action=$next");exit;
		//Promptmsg('action_success',$next);
	}
}

/**
 * 显示安装日志
 */
if ($action == 'view') {
	include (D_P . 'data/sql_config.php');
	unset($dbhost, $dbuser, $dbpw, $dbname, $manager, $manager_pwd);
	$log = readover(D_P . "data/install.log") . "\n";
	$log = str_replace(array("\n", 'pw_'), array('<wind>', $PW), $log) . $lang['success_4_2'];
	list($prev, $next) = getStepto($action);
	pwViewHtml($action);exit;
}

/**
 * 设置默认模式及模式初始化信息
 */
if ($action == 'mode') {
	if (0 && $step != 2) {
		pwViewHtml($action);exit;
	} else {
		if (!file_exists(D_P . 'data/sql_config.php')) {
			Promptmsg('config_noexists', 'database');
		} else {
			$db = pwNewDBForInstall();
		}

		require(R_P . 'lang/step/modeset.php');
		require(R_P . 'lang/step/tpl.php');
		writeover(D_P . "data/install.log", "$lang[success_5]");

		list($prev, $next) = getStepto($action);
		pwHeader("$basename?action=$next");exit;
	}
}

/**
 * 默认添加数据
 */
if ($action == 'custom') {
	if (!file_exists(D_P . 'data/sql_config.php')) {
		Promptmsg('config_noexists', 'database');
	} else {
		$db = pwNewDBForInstall();
	}

	require(R_P . 'lang/step/updatead.php');
	require(R_P . 'lang/step/topic.php');
	require(R_P . 'lang/step/pcfield.php');
	require(R_P . 'lang/step/nav.php');
	require(R_P . 'lang/step/pw_areas.php');
	require(R_P . 'lang/step/pw_school.php');
    $areaService = L::LoadClass('areasservice', 'utility');
    $areaService->setAreaCache();

	list($prev, $next) = getStepto($action);
	pwHeader("$basename?action=$next");exit;
}

/**
 * 生成站点各种信息，添加默认风格,更新缓存
 */
if ($action == 'other') {
	if (!file_exists(D_P . 'data/sql_config.php')) {
		Promptmsg('config_noexists', 'database');
	} else {
		$db = pwNewDBForInstall();
	}

	$writeinto = str_pad('<?php die;?>', 96) . "\r\n";
	writeover(D_P.'data/bbscache/online.php', $writeinto);
	writeover(D_P.'data/bbscache/guest.php', $writeinto);
	writeover(D_P.'data/bbscache/olcache.php', "<?php\r\n\$userinbbs=1;\r\n\$guestinbbs=0;\r\n?>");

	mt_srand((double)microtime() * 1000000);

	$rand = '0123%^&*45ICV%^&*B6789qazw~!@#$sxedcrikolpQWER%^&*TYUNM';
	$randlen = strlen($rand);
	for ($i=0; $i<10; $i++) {
		$db_hash .= $rand[mt_rand(0, $randlen)];
	}
	$db_siteid      = generatestr(32);
	$db_siteownerid = generatestr(32);
	$db_sitehash = '10' . SitStrCode(md5($db_siteid . $db_siteownerid), md5($db_siteownerid . $db_siteid));

	$db_windmagic = 0;
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_hash','$db_hash')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_windmagic','$db_windmagic')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_siteid','$db_siteid')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_siteownerid','$db_siteownerid')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_sitehash','$db_sitehash')");

	$db->update("REPLACE INTO pw_config SET db_name='db_ifpwcache',db_value= '567'");

	//风格
	$styles = array('wind' => '蓝色天空', 'wind8gray'=>'水墨江南', 'wind8black'=>'黑色旋风', 'wind8green'=>'绿之印象', 'wind8purple'=>'紫色梦幻', 'wind85' => '春意盎然');
	$i = 1; $temp_styledb = array();
	foreach ($styles as $key => $value) {
		if (!file_exists(D_P . 'data/style/' . $key . '.php')) continue;
		include Pcv(D_P . 'data/style/' . $key . '.php');
		$true_value = $value;
		$temp_styledb[$key] = array($true_value, 1);
		$db->update("REPLACE INTO pw_styles SET sid='$i',name='$key',customname='".$true_value."',ifopen=1,stylepath='$stylepath',tplpath='$tplpath',yeyestyle='$yeyestyle',bgcolor='$bgcolor',linkcolor='$linkcolor',tablecolor='$tablecolor',tdcolor='$tdcolor',tablewidth='$tablewidth',mtablewidth='$mtablewidth',headcolor='$headcolor',headborder='$headborder',headfontone='$headfontone',headfonttwo='$headfonttwo',cbgcolor='$cbgcolor',cbgborder='$cbgborder',cbgfont='$cbgfont',forumcolorone='$forumcolorone',forumcolortwo='$forumcolortwo'");
		$i++;
	}

	$temp_styledb = addslashes(serialize($temp_styledb));
	$db->update("REPLACE INTO pw_config(db_name,vtype,db_value) VALUES ('db_styledb','array','$temp_styledb')");
	$db->update("REPLACE INTO pw_config(db_name,vtype,db_value) VALUES ('db_defaultstyle','string','wind85')");

	$o_classdb = addslashes(serialize(array(1 => '默认分类')));
	$db->update("REPLACE INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_classdb', 'array','$o_classdb' , '')");

	require_once(R_P.'admin/cache.php');
	updatecache_c();
	require(R_P.'lang/step/writesmile.php');

	//设置门户首页为默认首页
	$update	= array('area_default_alias', 'string', 'home85', '');
	$db->update("REPLACE INTO pw_hack VALUES (" . pwImplode($update) . ')');

	//87默认数据
	$query = $db->query("SELECT gid,gptype FROM pw_usergroups");
	while ($value = $db->fetch_array($query)) {
		switch ($value['gptype']) {
			case 'system':
			case 'special':
			case 'member':
				$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'allowat','basic','1')");
				$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'allowreplyreward','basic','1')");
				$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'allowremotepic','basic','1')");
				break;
		}
		if ($value['gptype'] == 'system') {
			$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'atnum','basic','10')");
			$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'robbuild','basic','1')");
		} else if($value['gptype'] == 'special') {
			$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'atnum','basic','10')");
		} else if($value['gptype'] == 'member') {
			$db->update("REPLACE INTO pw_permission (uid ,fid ,gid,rkey,type,rvalue) VALUES (0,0,{$value['gid']},'atnum','basic','3')");
		}
	}
	
	$query = $db->query("SELECT fid,forumset FROM pw_forumsextra");
	while ($value = $db->fetch_array($query)) {
		$forumset = unserialize($value['forumset']);
		$forumset['allowrob'] = 1;
		$forumset['ifkmd'] = 1;
		$forumset['kmdnumber'] = 3;
		$forumset = serialize($forumset);
		$db->update("UPDATE pw_forumsextra SET forumset='$forumset' WHERE fid={$value['fid']}");
	}

	@unlink(D_P . 'data/type_cache.php');
	list($prev, $next) = getStepto($action);
	pwHeader("$basename?action=$next");exit;
}

/**
 * 导入测试数据
 */
if ($action == 'testdata') {
	if ($step < 2) {
		$content = str_replace(array("\r","\n\n",";\n"),array('',"\n",";<wind>\n"),trim(readover(R_P.'lang/example.sql')," \n"));
		//$content = preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content).'<wind>';
		$content = explode("\n",$content);
		$writearray = SQLCreate($content);
	}
	@set_time_limit(200);
	if (!file_exists(D_P.'data/sql_config.php')) {
		Promptmsg('config_noexists','database');
	} else {
		$db = pwNewDBForInstall();
	}
	$sqlcache = readover(D_P.'data/install_sys.sql');
	$update = SQLUpdate($sqlcache,500);
	if ($update) {
		$step = $step ? $step + 1 : 2;
		$stepstring = str_pad('..',$step);
		$input = "<input type=\"hidden\" name=\"step\" value=\"$step\">";
		Promptmsg('install_initdata',$action,true);
	} else {
		require_once (R_P.'admin/cache.php');
		$pwChannel = L::loadClass('channelservice', 'area');
		$pwChannel->updateAreaChannels();
		require(R_P.'lang/step/nav_tiyan.php');

		//设置门户首页为默认首页
		$update	= array('area_default_alias','string','finance','');
		$db->update("REPLACE INTO pw_hack VALUES (".pwImplode($update).')');

		//更新关联版块信息
		updatecache_cnc();

		@unlink(D_P.'data/install_sys.sql');
		list($prev,$next) = getStepto($action);
		pwHeader("$basename?action=$next");exit;
	}
}

/**
 * 站点资源导航信息
 */
if ($action == 'resources') {
	require_once(R_P.'require/posthost.php');
	$log_resources = PostHost('http://u.phpwind.net/install/partner.php',"step=$step&url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]",'GET',null,80,10);
	if (!$log_resources || strpos($log_resources,'<pw_log_resources>') === false) {
		$log_resources = $lang['log_resources'];
	}

	$stepright = $lang['success'];
	pwViewHtml($action);exit;
}

if ($action == 'static') {
	if (!file_exists(D_P . 'data/sql_config.php')) {
		Promptmsg('config_noexists', 'database');
	} else {
		$db = pwNewDBForInstall();
	}
	require_once(R_P . 'admin/cache.php');
	updatecache_conf('area', true);
	updatecache();
	include_once(D_P . 'data/bbscache/config.php');
	$pwModeImg = "mode/area/images";
	$imgpath = $db_http != 'N' ? $db_http : $db_picpath;
	ObStart();
	define('M_P', R_P . "mode/area/");
	define('AREA_PATH', R_P . 'html/channel/');
	include_once(M_P . 'require/core.php');
	define('AREA_STATIC', '1');
	$db_mode = 'area';
	$ChannelService = L::loadClass('channelService', 'area');
	$channelsArray=$ChannelService->getChannels();
	$ChannelService->updateAreaChannels();

	require_once(R_P . 'require/nav.php');

	$alias = array('baby', 'decoration', 'auto', 'delicious', 'home85', 'tucool');
	foreach ($alias as $value) {
		$alias = $value;
		require M_P . 'index.php';
		aliasStatic($alias);
	}

	list($prev, $next) = getStepto($action);
	pwHeader("$basename?action=$next");exit;
}

if ($action == 'finish') {
	@set_time_limit(120);
	if (!file_exists(D_P . 'data/sql_config.php')) {
		Promptmsg('config_noexists', 'database');
	} else {
		$db = pwNewDBForInstall();
	}
	$db_htmdir = 'html';
	if ($_GET['app'] == '1') {
		M::sendNotice(
			array($manager['0']),
			array(
				'title' => $lang['app_subject'],
				'content' => $lang['app_content2'],
			)
		);
	}

	$steptitle = '!';
	if (!is_writeable($basename)) {
		$lang['success_install'] .= "<br /><small><font color=\"red\">$lang[error_delinstall]</font></small>";
	}
	$lang['success_install'] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_install']);

	$ceversion = defined('CE') ? 1 : 0;
	require_once(R_P.'admin/cache.php');
	if (defined('CE')) {
		M::sendNotice(
			$manager,
			array(
				'title' => $lang['log_unionmsgt'],
				'content' => $lang['log_unionmsgc'],
			)
		);
	}

	setConfig('db_server_url', 'http://apps.phpwind.net');
	//updatemedal_list();
	updatecache();
	updatecache_cnc_s();
	updatecache_conf('area', true);
	updatecache_conf('o', true);

//	$ipindex = L::loadClass('iptable', 'utility');
//	$ipindex->createIpIndex();

	$db_htmdir = 'html';
	$db_bbsurl = $bbsurl;
	
	ob_start();
	define('A_P', R_P . "apps/stopic/");
	$stopic_service = L::loadClass('stopicservice', 'stopic');
	$stopic_service->creatStopicHtml(1);
	ob_end_clean();

	pwSetVersion('Install', 'Welcome to phpwind');

	writeover(D_P . "data/$lockfile",'LOCKED');
	@unlink(D_P . 'data/install_sys.sql');
	@unlink(D_P . 'data/install_hack.sql');
	@unlink(D_P . 'data/install_test.sql');
	@unlink(D_P . 'data/install.log');
	@unlink($basename);

	list($db_bbsname, $db_charset) = array('phpwind', $lang['db_charset']);
	applyCloudWind($db_bbsname, $db_bbsurl);
	
	pwViewHtml($action);exit;
}
########################## FUNCTION ##########################

function GetLang($lang,$EXT="php"){
	global $tplpath;
	if (file_exists(R_P."template/$tplpath/lang_$lang.$EXT")) {
		return R_P."template/$tplpath/lang_$lang.$EXT";
	} elseif (file_exists(R_P."template/wind/lang_$lang.$EXT")) {
		return R_P."template/wind/lang_$lang.$EXT";
	} elseif (file_exists(R_P."template/admin/cp_lang_$lang.$EXT")) {
		return R_P."template/admin/cp_lang_$lang.$EXT";
	} else {
		exit("Can not find lang_$lang.$EXT file");
	}
}
function adminmsg(){}
function Showmsg(){}

function HackList(){
	$hackdb = array();
	if ($fp = opendir(R_P.'hack')) {
		$infodb = array();
		while (($hackdir = readdir($fp))) {
			if (strpos($hackdir,'.')===false) {
				$hackopen = 0;
				$hackname = $hackdir;
				$filedata = readover(R_P."hack/$hackdir/info.xml");
				if (preg_match('/\<hackname\>(.+?)\<\/hackname\>\s+\<ifopen\>(.+?)\<\/ifopen\>/is',$filedata,$infodb)) {
					$infodb[1] && $hackname = Char_cv(str_replace(array("\n"),'',$infodb[1]));
					$hackopen = (int)$infodb[2];
				}
				$hackdb[$hackdir] = array($hackname,$hackdir,$hackopen);
			}
		}
		closedir($fp);
	}
	return $hackdb;
}
function SitStrCode($string,$key,$action='ENCODE'){
	$string	= $action == 'ENCODE' ? $string : base64_decode($string);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($string); $i++){
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$code = $action == 'DECODE' ? $code : str_replace('=','',base64_encode($code));
	return $code;
}
function generatestr($len) {
	mt_srand((double)microtime() * 1000000);
    $keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars)-1;
	$str = '';
	for ($i=0;$i<$len;$i++){
		$str .= $keychars[mt_rand(0,$maxlen)];
	}
	return substr(md5($str.microtime().$_SERVER['HTTP_HOST'].$_SERVER['HTTP_USER_AGENT'].$GLOBALS['db_hash']),0,$len);
}
function SQLUpdate($sqlcache,$cutnum,$hack = null) {
	global $db;
	if (!trim($sqlcache)) return false;
	$cachename = empty($hack) ? 'sys' : $hack;
	$sqlarray = explode(';<wind>',$sqlcache);
	$count = count($sqlarray);
	if ($count>0) {
		if ($count <= $cutnum) {
			$cutnum = 0;
		}
		$num = 0; $cutcache = '';
		foreach ($sqlarray as $value) {
			if ($value) {
				$lowquery = strtolower(substr($value,0,5));
				if (in_array($lowquery,array('alter','inser','repla','updat'))) {
					if ($cutnum) {
						$num++;
						if ($num>$cutnum) break;
						$cutcache .= "$value;<wind>";
					}
					$db->query($value);
				}
			}
		}
		if ($cutcache) {
			$writedata = str_replace($cutcache,'',$sqlcache);
			writeover(D_P."data/install_$cachename.sql",$writedata);
		} elseif ($cutnum == 0) {
			writeover(D_P."data/install_$cachename.sql","");
		}
		return true;
	}
	return false;
}
function SQLCreate($sqlarray,$hack = null) {
	global $db, $charset, $writearray, $lang;
	$query = $updatesql = '';
	$cachename = empty($hack) ? 'sys' : $hack;
	foreach ($sqlarray as $value) {
		$value = trim($value, " \t");
		if ($value && $value[0] != '#') {
			$query .= $value;
			if (substr($value, -7) == ';<wind>') {
				$lowquery = strtolower(substr($query, 0, 5));
				$checkdrop = CheckDrop($value);
				if (in_array($lowquery, array('drop ', 'creat'))) {
					if ($cachename == 'sys' || $checkdrop) {
						if ($lowquery == 'creat') {
							$tablename = trim(substr($query, 0, strpos($query,'(')));
							$tablename = substr($tablename, strrpos($tablename, ' ') + 1);
							$writearray[] = str_replace('{#tablename}', $tablename, $lang['success_3_1']);
							$search = trim(substr(strrchr($value, ')'), 1));
							$tabtype = substr(strchr($search, '='), 1);
							$tabtype = substr($tabtype, 0, strpos($tabtype, strpos($tabtype, ' ') ? ' ' : ';'));
							if ($db->server_info() >= '4.1') {
								$replace = "ENGINE=$tabtype" . ($charset ? " DEFAULT CHARSET=$charset" : '') . ';';
							} else {
								$replace = "TYPE=$tabtype;";
							}
							$query = str_replace(array($search, '<wind>'), array($replace, ''), $query);
						} else {
							$query = str_replace('<wind>', '', $query);
						}
						$db->query($query);
					}
				} elseif ((in_array($lowquery, array('inser', 'repla')) && ($cachename == 'sys' || $checkdrop)) || ($lowquery == 'alter' && $cachename != 'sys' && $checkdrop && strpos(strtolower($query), 'drop') === false)) {
					$lowquery == 'inser' && $query = 'REPLACE ' . substr($query, 6);
					$updatesql .= $query;
				}
				$query = '';
			}
		}
	}
	$updatesql && writeover(D_P . "data/install_$cachename.sql", $updatesql);
	return $writearray;
}
function getStepto($action) {
	global $_actionStep;
	$key = array_search($action,$_actionStep);
	$prev = isset($_actionStep[$key-1]) ? $_actionStep[$key-1] : 'null';
	$next = isset($_actionStep[$key+1]) ? $_actionStep[$key+1] : 'null';
	return array($prev,$next);
}
function pwViewHtml($action) {
	@extract($GLOBALS, EXTR_SKIP);
	list($prev,$next) = getStepto($action);
	$stepmsg = $lang["step_$action"];
	$stepleft = $lang["step_{$action}_left"] ? $lang["step_{$action}_left"] : $lang["step_prev"].' '.$lang["step_$prev"];
	$stepright = $lang["step_{$action}_right"] ? $lang["step_{$action}_right"] : $lang["step_next"].' '.$lang["step_$next"];
	ob_start();
	require_once(R_P.'lang/header.htm');
	require(R_P.'lang/install.htm');footer();
}
function Promptmsg($msg, $toaction = null, $jump = false){
	@extract($GLOBALS, EXTR_SKIP);
	$lang[$msg] && $msg = $lang[$msg];
	$msg = preg_replace("/{#(.+?)}/eis", '$\\1', $msg);
	$url = $backurl = 'javascript:history.go(-1);';
	if (!$toaction) {
		$lang['last'] = $lang['back'];
		@unlink("log$step.txt");
	} else {
		$url = "document.getElementById('install').submit();";
	}

	ob_start();
	require_once(R_P.'lang/header.htm');
	require(R_P.'lang/promptmsg.htm');
	footer();
}
function footer(){
	global $footer;
	require_once(R_P.'lang/footer.htm');
	$output = str_replace(array('<!--<!---->-->','<!---->-->', '<!--<!---->', "<!---->\r\n", '<!---->', '<!-- -->', "\t\t\t"), '', ob_get_contents());
	ob_end_clean();
	ob_start();
	echo $output;unset($output);exit;
}
function CheckDrop($query){
	global $db,$tabledb,$db_modelids,$db_plist,$db_tlist;
	require_once(R_P.'admin/table.php');
	list($pwdb) = N_getTabledb();
	$next = true;
	foreach ($pwdb as $value) {
		if (strpos(strtolower($query),strtolower($value))!==false) {
			$next = false;
			break;
		}
	}
	return $next;
}
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
function GetCrlf(){
	return GetPlatform()=='win' ? "\r\n" : "\n";
}
function GetPlatform(){
	if (strpos($_SERVER['HTTP_USER_AGENT'],'Win')!==false) {
		return 'win';
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'],'Mac')!==false) {
		return 'mac';
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'],'Linux')!==false) {
		return 'linux';
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'],'Unix')!==false) {
		return 'unix';
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'],'OS/2')!==false) {
		return 'os2';
	} else {
		return '';
	}
}
function UserAgentMsg(){
	static $agent,$return;
	if ($agent===$_SERVER['HTTP_USER_AGENT']) {
		return $return;
	}
	$return = array();
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if ($agent) {
		if (preg_match('/Opera(\/| )([0-9]\.[0-9]{1,2})/',$agent,$ver)) {
			$return = array('opera',$ver[2]);
		} elseif (preg_match('/MSIE ([0-9]\.[0-9]{1,2})/',$agent,$ver)) {
			$return = array('ie',$ver[1]);
		} elseif (preg_match('/OmniWeb\/([0-9]\.[0-9]{1,2})/',$agent,$ver)) {
			$return = array('omniweb',$ver[1]);
		} elseif (preg_match('/(Konqueror\/)(.*)(;)/',$agent,$ver)) {
			$return = array('konqueror',$ver[2]);
		} elseif (preg_match('/Firefox\/([0-9]\.[0-9]{1,2})/',$agent,$ver)) {
			$return = array('firefox',$ver[1]);
		} elseif (preg_match('/Chrome\/([0-9]\.[0-9]{1,2})/',$agent,$ver)) {
			$return = array('chrome',$ver[1]);
		} elseif (preg_match('/Safari\/([0-9]*)/',$agent,$ver)) {
			$return = array('safari',$ver[1]);
		} elseif (preg_match('/Mozilla\/([0-9]\.[0-9]{1,2})/',$agent,$ver1)) {
			if (preg_match('/Safari\/([0-9]*)/',$agent,$ver2)) {
				$return = array('safari',$ver1[1].'.'.$ver2[1]);
			} elseif (preg_match('/rv:1\.9(.*)Gecko/',$agent)) {
				$return = array('gecko','1.9');
			} else {
				$return = array('mozilla',$ver[1]);
			}
		} elseif (preg_match('/rv:1\.9(.*)Gecko/',$agent)) {
			$return = array('gecko','1.9');
		} else {
			$return = array('unknown',0);
		}
	}
	return $return;
}
function N_writable($pathfile) {
	//Copyright (c) 2003-2103 phpwind
	//fix windows acls bug
	$isDir = substr($pathfile,-1)=='/' ? true : false;
	if ($isDir) {
		if (is_dir($pathfile)) {
			mt_srand((double)microtime()*1000000);
			$pathfile = $pathfile.'pw_'.uniqid(mt_rand()).'.tmp';
		} elseif (@mkdir($pathfile)) {
			return N_writable($pathfile);
		} else {
			return false;
		}
	}
	@chmod($pathfile,0777);
	$fp = @fopen($pathfile,'ab');
	if ($fp===false) return false;
	fclose($fp);
	$isDir && @unlink($pathfile);
	return true;
}
function Pwloaddl($mod,$ckfunc='mysqli_get_client_info'){//20080714
	return extension_loaded($mod) && $ckfunc && function_exists($ckfunc) ? true : false;
}

function setstatus(&$status,$b,$setv = '1') {
	--$b;
	for ($i = strlen($setv)-1; $i >= 0 ; $i--) {
		if ($setv[$i]) {
			$status |= 1 << $b;
		} else {
			$status &= ~(1 << $b);
		}
		++$b;
	}
}
function pwSetVersion($do,$reason='') {//phpwind history
	global $wind_version,$from_version,$wind_repair,$reason,$timestamp,$db;
	$wind_version = strtoupper($wind_version);
	$from_version = strtoupper($from_version);
	$PHPWind = $db->get_value("SELECT db_value FROM pw_config WHERE db_name='PHPWind'");
	$PHPWind = $PHPWind ? unserialize($PHPWind) : array();
	$PHPWind['version'] && $from_version = $PHPWind['version'];
	$reason || $reason = $from_version == $wind_version ? ($wind_repair ? 'Repair wind' : 'Re-do it again') : "";
	$PHPWind['history'][] = "$do\t$timestamp\t$from_version\t$wind_version,$wind_repair\t$reason";
	$PHPWind['version'] = $wind_version;
	$PHPWind['repair'] = $wind_repair;
	$db->update("REPLACE INTO pw_config (db_name, db_value, decrip) VALUES ('phpwind',".pwEscape(serialize($PHPWind)).",'phpwind')");
	//@unlink(D_P.'data/bbscache/version');
	return $PHPWind['version'];
}
function updatemedal_list(){
	global $db;
	$query = $db->query("SELECT uid FROM pw_medaluser GROUP BY uid");
	$medaldb = '<?php die;?>0';
	while ($rt = $db->fetch_array($query)) {
		$medaldb .= ','.$rt['uid'];
	}
	writeover(D_P.'data/bbscache/medals_list.php',$medaldb);
}
function &pwNewDBForInstall() {
	if (!is_object($GLOBALS['db'])) {
		global $charset,$manager,$PW;
		include (D_P.'data/sql_config.php');
		require_once Pcv(R_P."require/db_$database.php");
		$GLOBALS['db'] = new DB($dbhost,$dbuser,$dbpw,$dbname,$PW,$charset,$pconnect);
	}
	return $GLOBALS['db'];
}
function pwHeader($URL){
	ob_end_clean();
	header("Location: $URL");
	echo "<meta http-equiv='refresh' content='0;url=$URL'>";exit;
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
}

function updatecache_cnc() {
	global $db;
	$classdb = array();
	$query = $db->query('SELECT fid,cname FROM pw_cnclass WHERE ifopen=1');
	while ($rt = $db->fetch_array($query)) {
		$classdb[$rt['fid']] = $rt['cname'];
	}
	$classdb = serialize($classdb);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_classdb'",
		'UPDATE pw_hack SET ' . pwSqlSingle(array('hk_value' => $classdb, 'vtype' => 'array')) . " WHERE hk_name='o_classdb'",
		'INSERT INTO pw_hack SET ' . pwSqlSingle(array('hk_name' => 'o_classdb', 'vtype' => 'array', 'hk_value' => $classdb))
	);
	updatecache_conf('o',true);
}

function getCurrentEnvironment() {
	global $lowestEnvironment;
	$space = floor(disk_free_space(R_P) / (1024 * 1024)) . 'M';
	$currentVersion = explode('.', PHP_VERSION);
	$lowestVersion = explode('.', $lowestEnvironment['version']);
	$version = '<span class="error_span">&times;</span>' . PHP_VERSION;
	$currentUpload = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';

	if ($currentVersion[0] > $lowestVersion[0] || ($currentVersion[0] == $lowestVersion[0] && $currentVersion[1] > $lowestVersion[1]) || ($currentVersion[0] == $lowestVersion[0] && $currentVersion[1] == $lowestVersion[1] && $currentVersion[2] >= $lowestVersion[2])) {
		$version = '<span class="correct_span">&radic;</span>' . PHP_VERSION;
	}
	$upload = intval($currentUpload) >= intval($lowestEnvironment['upload']) ? '<span class="correct_span">&radic;</span>' . $currentUpload : '<span class="error_span">&times;</span>' . $currentUpload;
	$space = intval($space) >= intval($lowestEnvironment['space']) ? '<span class="correct_span">&radic;</span>' . $space : '<span class="error_span">&times;</span>' . $space;
	return array(
		'os' => '<span class="correct_span">&radic;</span>' . PHP_OS,
		'version' => $version,
		'upload' => $upload,
		'space' => $space
	);
}

function applyCloudWind($siteName, $siteUrl) {
	unset($GLOBALS['CloudWind_Configs']);
	require_once R_P . 'lib/cloudwind/cloudwind.class.php';
	$checkService = CloudWind::getPlatformCheckServerService ();
	if (!$checkService->checkHost() || !$checkService->getServerStatus() || !($marksite = $checkService->markSite())) return false;
	list($adminName, $adminPhone) = array('siteAdmin', '13888888888');
	$applyStatus = CloudWind::yunApplyPlatform($siteUrl, $siteName, $adminName, $adminPhone, $marksite);
	if (!$applyStatus) {
		$checkService->markSite(false);
		return false;
	}
	return true;
}
?>