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
define('PW_TURN_UP83TO85BYCOMMAND',1);//命令行升级开关
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

require_once(R_P.'lang/install_lang.php');
echo strip_tags($lang['redirect_msg']);

if (!get_magic_quotes_gpc()) {
	Add_S($_POST);
	Add_S($_GET);
}

$_WIND			= 'upto';
$from_version	= '8.3';
$wind_version	= '8.5';
$wind_repair	= '';

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$selfrpos	= strrpos($_SERVER['PHP_SELF'],'/') ? strrpos($_SERVER['PHP_SELF'],'/') : strrpos($_SERVER['PHP_SELF'],'\\') ;

$basename	= substr($_SERVER['PHP_SELF'],$selfrpos+1);

$lockfile	= substr($basename,0,strlen($basename)-13);

$bbsurl	= 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,$selfrpos);

$backmsg	= $input = $stepmsg = $stepleft = $stepright = '';
$syslogo	= 'upto';
$crlf = GetCrlf();
$REQUEST_URI = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$jumpurl = "http://".$_SERVER['HTTP_HOST'].$REQUEST_URI;

ob_start();

InitGP(array('step','start'));
$systitle	= $lang['title_upto'];
$steptitle	= $step;
file_exists(D_P."data/$lockfile.lock") && PromptmsgBycommandError('have_upfile');
!N_writable(D_P.'data/sql_config.php') && PromptmsgBycommandError('sqlconfig_file');
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
$CK = array();
if (!empty($CK)) {
	$isManager = checkuptoadmin($CK);
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

$step = 1;

while(true){

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
		$filenames = implode("\n",$fileexist);
		PromptmsgBycommandError('error_unfinds');
	}
	if ($writable) {
		$gojs = false;
		$filenames = implode("\n",$writable);
		PromptmsgBycommandError('error_777s');
	}
	if ($db_adminfile != 'admin.php' && file_exists(R_P.'admin.php')) {
		$gojs = false;
		PromptmsgBycommandError('error_admin');
	}
	PromptmsgBycommand('redirect_msg',2,$step);
} elseif ($step=='2') {//Create table
	checkFields('pw_argument','tpcid');
	/*$array = array(
		'检测表名' => array('建表语句小括号内容','表类型，默认空为MyISAM')
	);*/
	$sqlarray = array(
		//8.3 to 8.5
		'pw_auth_certificate' => array("`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `uid` int(10) NOT NULL DEFAULT '0', `type` tinyint(3) NOT NULL DEFAULT '0', `number` char(32) NOT NULL DEFAULT '', `attach1` varchar(80) NOT NULL DEFAULT '', `attach2` varchar(80) NOT NULL DEFAULT '', `createtime` int(10) NOT NULL DEFAULT '0', `admintime` int(10) NOT NULL DEFAULT '0', `state` tinyint(3) NOT NULL DEFAULT '1', PRIMARY KEY (`id`), UNIQUE KEY `idx_uid` (`uid`), KEY `idx_state` (`state`)"),
		'pw_membertags' => array("`tagid` int(10) unsigned NOT NULL AUTO_INCREMENT, `tagname` varchar(32) NOT NULL DEFAULT '', `num` int(10) unsigned NOT NULL DEFAULT '0', `ifhot` tinyint(3) NOT NULL DEFAULT '1', PRIMARY KEY (`tagid`), UNIQUE KEY `idx_tagname` (`tagname`), KEY `idx_num` (`num`), KEY `idx_ifhot` (`ifhot`)"),
		'pw_membertags_relations' => array("`tagid` int(10) unsigned NOT NULL DEFAULT '0', `userid` int(10) unsigned NOT NULL DEFAULT '0', `crtime` int(10) unsigned NOT NULL DEFAULT '0', UNIQUE KEY `idx_tagid_userid` (`tagid`,`userid`), KEY `idx_userid` (`userid`), UNIQUE KEY `idx_crtime` (`crtime`)"),
		'pw_weibo_topicattention' => array("`userid` int(10) unsigned NOT NULL DEFAULT '0', `topicid` int(10) unsigned NOT NULL DEFAULT '0', `crtime` int(10) unsigned NOT NULL DEFAULT '0', `lasttime` int(10) unsigned NOT NULL DEFAULT '0', UNIQUE KEY `idx_userid_topicid` (`userid`,`topicid`)"),
		'pw_weibo_topicrelations' => array("`topicid` int(10) unsigned NOT NULL DEFAULT '0', `mid` int(10) unsigned NOT NULL DEFAULT '0', `crtime` int(10) unsigned NOT NULL DEFAULT '0', UNIQUE KEY `idx_topicid_mid` (`topicid`,`mid`), KEY `idx_mid` (`mid`), KEY `idx_crtime` (`crtime`)"),
		'pw_weibo_topics' => array("`topicid` int(10) unsigned NOT NULL AUTO_INCREMENT, `topicname` varchar(255) NOT NULL DEFAULT '', `num` int(10) unsigned NOT NULL DEFAULT '0', `ifhot` tinyint(3) NOT NULL DEFAULT '1', `crtime` int(10) NOT NULL DEFAULT '0', `lasttime` int(10) NOT NULL DEFAULT '0', PRIMARY KEY (`topicid`), UNIQUE KEY `idx_topicname` (`topicname`), KEY `idx_ifhot` (`ifhot`), KEY `idx_crtime` (`crtime`)"),
		'pw_areas' => array("`areaid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(50) NOT NULL DEFAULT '', `joinname` varchar(150) NOT NULL DEFAULT '', `parentid` mediumint(8) unsigned NOT NULL DEFAULT '0', `vieworder` smallint(6) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`areaid`), KEY `idx_name` (`name`), KEY `idx_parentid_vieworder` (`parentid`,`vieworder`)"),
		'pw_school' => array("`schoolid` INT NOT NULL AUTO_INCREMENT, `schoolname` VARCHAR(32) NOT NULL DEFAULT '', `areaid` INT NOT NULL DEFAULT '0', `type` TINYINT unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`schoolid`), KEY `idx_areaid_type` (`areaid`,`type`)"),
		'pw_user_education' => array("`educationid` INT NOT NULL AUTO_INCREMENT, `uid` INT UNSIGNED NOT NULL DEFAULT '0', `schoolid` INT UNSIGNED NOT NULL DEFAULT '0', `educationlevel` TINYINT UNSIGNED NOT NULL DEFAULT '0', `starttime` INT(11) NOT NULL DEFAULT '0', PRIMARY KEY (`educationid`), KEY `idx_uid_schoolid` (`uid`,`schoolid`)"),
		'pw_user_career' => array("`careerid` INT UNSIGNED NOT NULL AUTO_INCREMENT, `uid` INT UNSIGNED NOT NULL DEFAULT '0', `companyid` INT UNSIGNED NOT NULL DEFAULT '0', `starttime` INT NOT NULL DEFAULT '0', PRIMARY KEY (`careerid`), KEY `idx_uid_companyid` (`uid`,`companyid`)"),
		'pw_cache_distribute' => array("ckey char(32) not null default '',     cvalue text not null,     typeid tinyint(3) not null default '0',     expire int(10) unsigned not null default '0',     extra varchar(255) NOT NULL DEFAULT '',     primary key (ckey)"),
		'pw_company' => array("companyid int(11) unsigned  NOT NULL AUTO_INCREMENT ,    companyname VARCHAR(60) NOT NULL DEFAULT '',    PRIMARY KEY (companyid),    UNIQUE KEY idx_companyname (companyname)"),
		'pw_hits_threads ' => array("tid int(10) unsigned NOT NULL DEFAULT '0',    hits int(10) unsigned NOT NULL DEFAULT '0'"),
		'pw_online_statistics  ' => array("name char(30) NOT NULL DEFAULT '',    value int(10) unsigned NOT NULL DEFAULT '0',    lastupdate int(10) NOT NULL DEFAULT '0',    PRIMARY KEY  (name)"),
		'pw_searchhotwords' => array("id mediumint(8) unsigned not null auto_increment,    keyword varchar(32) not null default '',    vieworder tinyint(3) not null default '0',    fromtype enum('custom','auto') not null default 'custom',    posttime int(10) unsigned not null default '0',    expire int(10) unsigned not null default '0',    PRIMARY KEY (id)"),
		'pw_searchadvert' => array("id mediumint(8) unsigned not null auto_increment,    keyword varchar(32) not null default '',    code text not null,    starttime int(10) unsigned not null default '0',    endtime int(10) unsigned not null default '0',    ifshow tinyint(3) not null default '0',    orderby tinyint(3) not null default '0',    config text not null,    primary key(id),    KEY idx_keyword (keyword)"),
		'pw_searchfourm ' => array(" id smallint(6) unsigned not null auto_increment,    fid smallint(6) unsigned not null default '0',    vieworder smallint(6) not null default '0',    PRIMARY KEY (id)"),
		'pw_online_guest ' => array(" ip int(10) NOT NULL DEFAULT '0',   token tinyint unsigned NOT NULL DEFAULT '0',   lastvisit int(10) NOT NULL DEFAULT '0',   fid smallint(6) NOT NULL DEFAULT '0',   tid int(10) NOT NULL DEFAULT '0',   action tinyint(3) NOT NULL DEFAULT '0',   ifhide tinyint(1) NOT NULL DEFAULT '0',   primary key (ip, token),   KEY idx_ip(ip)","MEMORY"),
		'pw_online_user ' => array(" uid int(10) unsigned NOT NULL default '0',   username char(15) NOT NULL DEFAULT '',   lastvisit int(10) NOT NULL DEFAULT '0',   ip int(10) NOT NULL default '0',   fid smallint(6) unsigned NOT NULL DEFAULT '0',   tid int(10) unsigned NOT NULL DEFAULT '0',   groupid tinyint(3) NOT NULL DEFAULT '0',   action tinyint(3) NOT NULL DEFAULT '0',   ifhide tinyint(1) NOT NULL DEFAULT '0',   PRIMARY KEY  (uid),   KEY idx_fid (fid)","MEMORY"),
		'pw_log_forums ' => array("id int(10) unsigned not null auto_increment,      sid int(10) unsigned not null default '0',      operate tinyint(3) not null default '1',      modified_time int(10) unsigned not null default '0',      primary key(id),      unique key idx_sid_operate(sid,operate)"),
		'pw_log_setting ' => array("id int(10) unsigned not null auto_increment,     vector varchar(255) not null default '',     cipher varchar(255) not null default '',     field1 varchar(255) not null default '',     field2 varchar(255) not null default '',     field3 int(10) unsigned not null default '0',     field4 int(10) unsigned not null default '0',     primary key(id)"),
	);
	N_createtable($sqlarray,$start,5);
	PromptmsgBycommand('redirect_msg',3,$step);

} elseif ($step==3) {//Alter table field//Char_cv

	/*$array = array(
		array('检测表名','检测字段',"数据库语句")
	);*/
	empty($start) && $start = '0_L';
	list($stepnum,$steptype) = explode('_',$start);
	if ($steptype=='L') {
		$sqlarray_L = array(
			//array(.*) CHANGE (\w*)
			//8.3 to 8.5
			array('pw_members','authmobile',"ALTER TABLE `pw_members` ADD `authmobile` CHAR(16) NOT NULL DEFAULT ''"),
			array('pw_members','realname',"ALTER TABLE `pw_members` ADD `realname` VARCHAR(16) NOT NULL DEFAULT ''"),
			array('pw_members','apartment',"ALTER TABLE `pw_members` ADD `apartment` INT(10) UNSIGNED NOT NULL DEFAULT 0"),
			array('pw_members','home',"ALTER TABLE `pw_members` ADD `home` INT(10) UNSIGNED NOT NULL DEFAULT 0"),
			array('pw_invitecode','type',"ALTER TABLE pw_invitecode ADD `type` tinyint(3) unsigned not null default 0"),
			array('pw_threads_img','tpcnum',"ALTER TABLE `pw_threads_img` ADD `tpcnum` SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
			array('pw_threads_img','totalnum',"ALTER TABLE `pw_threads_img` ADD `totalnum` SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
			array('pw_threads_img','collectnum',"ALTER TABLE `pw_threads_img` ADD `collectnum` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0"),
			array('pw_threads_img','cover',"ALTER TABLE `pw_threads_img` ADD `cover` varchar(80) NOT NULL DEFAULT ''"),
			array('pw_threads_img','ifcheck',"ALTER TABLE `pw_threads_img` ADD `ifcheck` TINYINT(3) NOT NULL DEFAULT '1'"),
			array('pw_threads_img','topped',"ALTER TABLE `pw_threads_img` ADD `topped` SMALLINT(6) NOT NULL DEFAULT '0'"),
			array('pw_cnstyles','vieworder',"ALTER TABLE pw_cnstyles ADD vieworder tinyint(3) unsigned not null default '0'"),
			array('pw_customfield','category',"ALTER TABLE pw_customfield ADD category ENUM( 'basic', 'contact', 'education' , 'other' ) NOT NULL DEFAULT 'basic'"),
			array('pw_customfield','complement',"ALTER TABLE pw_customfield ADD complement tinyint unsigned NOT NULL default '0'"),
			array('pw_customfield','ifsys',"ALTER TABLE pw_customfield ADD ifsys tinyint unsigned NOT NULL default '0'"),
			array('pw_customfield','fieldname',"ALTER TABLE pw_customfield ADD fieldname varchar(32) NOT NULL default ''"),
			array('pw_job','isuserguide',"ALTER TABLE pw_job ADD isuserguide tinyint(3) unsigned NOT NULL default '0'"),
			array('pw_ms_relations','relation',"ALTER TABLE pw_ms_relations ADD relation tinyint(3) not null default '1'"),
			array('pw_space','spacestyle',"ALTER TABLE pw_space ADD spacestyle tinyint(3) unsigned not null default '2'"),
			array('pw_topictype','ifsys',"ALTER TABLE pw_topictype ADD ifsys tinyint(3) unsigned not null default '0'"),
			array('pw_attachdownload','cost',"ALTER TABLE pw_attachdownload CHANGE cost cost smallint(6) unsigned not null default '0'"),
			array('pw_cnphoto','pid',"ALTER TABLE pw_cnphoto CHANGE pid pid int(10) NOT NULL auto_increment"),
			array('pw_memberdata','follows',"ALTER TABLE pw_memberdata CHANGE follows follows mediumint(8) unsigned NOT NULL default '0'"),
			array('pw_memberdata','fans',"ALTER TABLE pw_memberdata CHANGE fans fans mediumint(8) unsigned NOT NULL default '0'"),
			array('pw_memberdata','newfans',"ALTER TABLE pw_memberdata CHANGE newfans newfans mediumint(8) unsigned NOT NULL default '0'"),
			array('pw_memberdata','newreferto',"ALTER TABLE pw_memberdata CHANGE newreferto newreferto mediumint(8) unsigned NOT NULL default '0'"),
			array('pw_threads_img','fid',"ALTER TABLE pw_threads_img CHANGE fid fid smallint(6) unsigned NOT NULL DEFAULT '0'"),
			array('pw_tradeorder','transportfee',"ALTER TABLE pw_tradeorder CHANGE transportfee transportfee decimal(6,2) NOT NULL default '0'"),
			array('pw_memberdata','postcheck',"ALTER TABLE pw_memberdata CHANGE postcheck postcheck varchar(255) NOT NULL DEFAULT ''"),
			
		);
		@set_time_limit(888);
		N_atfield(N_pointstable($sqlarray_L),$stepnum,1);
		if (!$times) {
			$record = 0; $start = '0_F'; $times = 1;
		}
	} elseif ($steptype=='F') {
		$sqlarray_F = array(
			//8.3 to 8.5
			array('pw_colonys','cname',"ALTER TABLE pw_colonys CHANGE cname cname varchar(20) NOT NULL default ''"),
			array('pw_invitecode','ifused',"ALTER TABLE pw_invitecode CHANGE ifused ifused tinyint(3) NOT NULL default '0'"),
			array('pw_nav','pos',"ALTER TABLE pw_nav CHANGE pos pos char(32) NOT NULL"),

		);
		@set_time_limit(888);
		N_atfield($sqlarray_F,$stepnum,10);
	}
	PromptmsgBycommand('redirect_msg',4,$step);
}elseif ($step == 4) {//更改索引

	//ALTER TABLE pw_([a-z]+) ADD INDEX (.*) \((.*)\);
	//array('pw_\1','\2','\3'),

	/*$array = array(
		array('检测表名','检测索引名称',"索引括号内容，为空则只删索引")
	);*/
	empty($start) && $start = '0_L';
	list($stepnum,$steptype) = explode('_',$start);
	if ($steptype=='L') {
		$sqlarray_L = array(
			//8.3 to 8.5
			array('pw_threads_img','idx_fid_topped_tid','fid,topped,tid'),
			array('pw_threads_img','idx_fid_topped_totalnum','fid,topped,totalnum'),
			array('pw_threads_img','fid',''),

		);
		@set_time_limit(888);
		N_atindex(N_pointstable($sqlarray_L),$stepnum,1);
		if (!$times) {
			$record = 0; $start = '0_F'; $times = 1;
		}
	} elseif ($steptype=='F') {
		$sqlarray_F = array(
			//8.3 to 8.5
		);
		@set_time_limit(888);
		N_atindex($sqlarray_F,$stepnum,5);
	}
	PromptmsgBycommand('redirect_msg',5,$step);

} elseif ($step=='5') {//
	//团队考核
	$query = $db->get_one("SELECT * FROM pw_hack WHERE hk_name = 'db_team'");
	$$teamValue = array();
	$teamValue['db_name'] = 'db_team';
	$teamValue['vtype'] = $query['vtype'];
	$teamValue['db_value'] = $query['hk_value'];
	$teamValue['decrip'] = $query['decrip'];
	$db->pw_update(
		"SELECT * FROM pw_config WHERE db_name='db_team'",
		"UPDATE pw_config SET " . S::sqlSingle($teamValue,true) ." WHERE db_name='db_team'",
		"INSERT INTO pw_config SET " . S::sqlSingle($teamValue,true)
	);
	$db->update("DELETE FROM pw_hack WHERE hk_name = 'db_team'");
	PromptmsgBycommand('redirect_msg','6',$step);

} elseif ($step == '6') {//
	//地区默认数据
	include_once (R_P . 'lang/step/pw_areas.php');
	PromptmsgBycommand('redirect_msg','7',$step);

} elseif ($step == 7) {//
	//学校默认数据
	include_once (R_P . 'lang/step/pw_school.php');
	PromptmsgBycommand('redirect_msg','8',$step);
} elseif ($step == 8) {//
	$areaService = L::LoadClass('areasservice','utility');
    $areaService->setAreaCache();
	PromptmsgBycommand('redirect_msg','9',$step);
} elseif ($step == 9) {//设置空间类型字段
	$db->update("UPDATE pw_space SET spacestyle=2 WHERE spacetype=1");
	$db->update("UPDATE pw_space SET spacestyle=3 WHERE spacetype=0");
	$db->update("UPDATE pw_space SET spacetype=0");
	PromptmsgBycommand('redirect_msg','10',$step);
} elseif ($step == 10) {
	$db->update("INSERT INTO `pw_customfield` (`category`, `title`, `maxlen`, `vieworder`, `type`, `state`, `required`, `viewinread`, `editable`, `descrip`, `viewright`, `options`, `complement`, `ifsys`, `fieldname`) VALUES ('basic', '性别', 0, 2, 3, 1, 0, 0, 1, '', '', '0=保密\r\n1=男\r\n2=女', 2, 1, 'gender'), ('basic', '生日', 0, 3, 6, 1, 0, 0, 1, '', '', 'a:2:{s:9:\"startdate\";s:4:\"1912\";s:7:\"enddate\";s:4:\"2011\";}', 2, 1, 'bday'), ('basic', '现居住地', 0, 4, 7, 1, 1, 0, 1, '', '', '', 1, 1, 'apartment'), ('basic', '家乡', 0, 4, 7, 1, 0, 0, 1, '', '', '', 2, 1, 'home'), ('basic', '支付宝账号', 60, 6, 1, 1, 0, 0, 1, '', '', '', 0, 1, 'alipay'), ('education', '教育经历', 0, 6, 8, 1, 0, 0, 1, '', '', '', 2, 1, 'education'), ('education', '工作经历', 0, 5, 9, 1, 0, 0, 1, '', '', '', 2, 1, 'career'), ('contact', 'QQ', 12, 1, 1, 1, 0, 0, 1, '', '', '', 2, 1, 'oicq'), ('contact', '阿里旺旺', 30, 1, 1, 1, 0, 0, 1, '', '', '', 0, 1, 'aliww'), ('contact', 'Yahoo', 35, 1, 1, 1, 0, 0, 1, '', '', '', 0, 1, 'yahoo'), ('contact', 'Msn', 35, 1, 1, 1, 0, 0, 1, '', '', '', 0, 1, 'msn')");
	
	$db->update("INSERT INTO pw_job (title,description,icon,starttime,endtime,period,reward,sequence,usergroup,prepose,number,member,auto,finish,display,type,job,factor,isopen,isuserguide) VALUES('实名认证-支付宝绑定', '支付宝：支付宝是现代电子商务信用环节中重要的一环绑定您的支付宝账号获得实名认证标识更能享有认证会员积分特权', '', 0, 0, 0, 'a:4:{s:4:\"type\";s:5:\"money\";s:3:\"num\";s:2:\"10\";s:8:\"category\";s:6:\"credit\";s:11:\"information\";s:12:\"可获得 铜币 \";}', 2, '', 0, 0, 0, 1, 0, 0, 0, 'doAuthAlipay', 'a:1:{s:5:\"limit\";s:0:\"\";}', 0, 0)");
   $db->update("INSERT INTO pw_job (title,description,icon,starttime,endtime,period,reward,sequence,usergroup,prepose,number,member,auto,finish,display,type,job,factor,isopen,isuserguide) VALUES('实名认证-手机绑定', '手机：绑定手机获得实名认证标识更能享有认证会员积分特权以及通过手机验证码找回密码功能让您的账号万无一失', '', 0, 0, 0, 'a:4:{s:4:\"type\";s:5:\"money\";s:3:\"num\";s:2:\"10\";s:8:\"category\";s:6:\"credit\";s:11:\"information\";s:12:\"可获得 铜币 \";}', 2, '', 0, 0, 0, 1, 0, 0, 0, 'doAuthMobile', 'a:1:{s:5:\"limit\";s:0:\"\";}', 0, 0)");
   $db->update("INSERT INTO pw_job (title,description,icon,starttime,endtime,period,reward,sequence,usergroup,prepose,number,member,auto,finish,display,type,job,factor,isopen,isuserguide) VALUES('新用户引导上传头像', '', '', 0, 0, 0, 'a:4:{s:4:\"type\";s:5:\"money\";s:3:\"num\";s:2:\"10\";s:8:\"category\";s:6:\"credit\";s:11:\"information\";s:12:\"可获得 铜币 \";}', 2, '', 0, 0, 0, 1, 0, 0, 0, 'doUpdateAvatar', '', 1, 1)");
   $db->update("INSERT INTO pw_job (title,description,icon,starttime,endtime,period,reward,sequence,usergroup,prepose,number,member,auto,finish,display,type,job,factor,isopen,isuserguide) VALUES('新用户引导完善资料', '', '', 0, 0, 0, 'a:4:{s:4:\"type\";s:4:\"rvrc\";s:3:\"num\";s:2:\"20\";s:8:\"category\";s:6:\"credit\";s:11:\"information\";s:12:\"可获得 威望 \";}', 1, '', 0, 0, 0, 1, 0, 0, 0, 'doUpdatedata', '', 1, 1)");
   $db->update("INSERT INTO pw_job (title,description,icon,starttime,endtime,period,reward,sequence,usergroup,prepose,number,member,auto,finish,display,type,job,factor,isopen,isuserguide) VALUES('新用户引导关注其他用户', '', '', 0, 0, 0, 'a:4:{s:4:\"type\";s:5:\"money\";s:3:\"num\";s:2:\"10\";s:8:\"category\";s:6:\"credit\";s:11:\"information\";s:12:\"可获得 铜币 \";}', 1, '', 0, 0, 0, 1, 0, 0, 0, 'doAddFriend', '', 1, 1)");

	$db->query("TRUNCATE TABLE `pw_threads_img`");

	if(!checkContentIfExist('pw_config',array('db_name'=>'db_shiftstyle'))) {
		$db->update("INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_shiftstyle', 'string', '1', '')");
	}

	//默认开启新鲜事输入框
	if(!checkContentIfExist('pw_hack',array('hk_name'=>'o_weibopost'))) {
		$db->update("INSERT INTO pw_hack (hk_name,vtype,hk_value) VALUES ('o_weibopost','string','1')");
	}

	if(!checkContentIfExist('pw_config',array('db_name'=>'db_openbuildattachs'))) {
		$db->update("INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_openbuildattachs', 'string', '1', '')");
	}

	if(!checkContentIfExist('pw_hack',array('hk_name'=>'o_punchopen'))) {
		$db->update("INSERT INTO pw_hack (hk_name,vtype,hk_value) VALUES ('o_punchopen','string','1')");
	}

	if(!checkContentIfExist('pw_hack',array('hk_name'=>'o_punch_reward'))) {
		$db->update("INSERT INTO pw_hack (hk_name,vtype,hk_value) VALUES ('o_punch_reward','string','a:4:{s:4:\"type\";s:5:\"money\";s:3:\"num\";s:1:\"5\";s:8:\"category\";s:6:\"credit\";s:11:\"information\";s:12:\"可获得 铜币 \";}')");
	}

	//头像的限制默认改为120*120
	list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize) = explode("\t",$db_upload);
	$db_upload = implode("\t",array($db_upload,120,120,$db_imgsize));
	setConfig('db_upload',$db_upload);
	
	if(!checkContentIfExist('pw_config',array('db_name'=>'rg_recommendcontent'))) {
		$db->update("INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('rg_recommendcontent', 'string', '&lt;div class=&quot;p10&quot;&gt;&lt;table width=&quot;100%&quot;&gt;&lt;tr&gt;&lt;td&gt;&lt;a href=&quot;html/channel/tucool/&quot;&gt;&lt;img src=&quot;images/register/thumb/tuku.jpg&quot; /&gt;&lt;/a&gt;&lt;/td&gt;&lt;td&gt;&lt;a href=&quot;apps.php?q=weibo&amp;do=topics&quot;&gt;&lt;img src=&quot;images/register/thumb/huati.jpg&quot; /&gt;&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;/div&gt;', '')");
	}

	if(!checkContentIfExist('pw_config',array('db_name'=>'db_sharesite'))) {
		$db->update("INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_sharesite', 'string', '1', '')");
	}

	$db->update("update `pw_nav` set title = '管理统计' where nkey = 'sort_admin'");

	//if(!checkContentIfExist('pw_config',array('db_name'=>'db_hotwords'))) {
		$db->update("REPLACE INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_hotwords', 'string', '结婚,母婴,phpwind', '')");
		$db->update("REPLACE INTO `pw_searchhotwords` (`id`, `keyword`, `vieworder`, `fromtype`, `posttime`, `expire`) VALUES(1, '结婚', 1, 'custom', 1300247428, 0),(2, '母婴', 2, 'custom', 1300247428, 0),(3, 'phpwind', 3, 'custom', 1300712565, 0)");
	//}

	$oskindb = unserialize($db->get_value("SELECT hk_value FROM pw_hack WHERE hk_name='o_uskin'"));
	$newskindb = array('default85' => '蒲公英','prayer' => '祈祷');
	$oskindb = $oskindb + $newskindb;
	$oskindb = serialize($oskindb);
	$db->update("REPLACE INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES ('o_uskin', 'array', '$oskindb', '')");

	PromptmsgBycommand('redirect_msg','11',$step);
} elseif ($step == '11') {
	//风格
	$styles = array('wind' => '蓝色天空','wind8gray'=>'水墨江南','wind8black'=>'黑色旋风','wind8green'=>'绿之印象','wind8purple'=>'紫色梦幻','wind85' => '春意盎然');
	$i = 1;$temp_styledb=array();
	foreach ($styles as $key => $value) {
		if (!file_exists(D_P.'data/style/'.$key.'.php')) continue;
		include Pcv(D_P.'data/style/'.$key.'.php');
		$true_value = $value;
		$temp_styledb[$key] = array($true_value,1);
		$db->update("REPLACE INTO pw_styles SET sid='$i',name='$value',customname='".$true_value."',ifopen=1,stylepath='$stylepath',tplpath='$tplpath',yeyestyle='$yeyestyle',bgcolor='$bgcolor',linkcolor='$linkcolor',tablecolor='$tablecolor',tdcolor='$tdcolor',tablewidth='$tablewidth',mtablewidth='$mtablewidth',headcolor='$headcolor',headborder='$headborder',headfontone='$headfontone',headfonttwo='$headfonttwo',cbgcolor='$cbgcolor',cbgborder='$cbgborder',cbgfont='$cbgfont',forumcolorone='$forumcolorone',forumcolortwo='$forumcolortwo'");
		$i++;
	}

	$temp_styledb = addslashes(serialize($temp_styledb));
	$db->update("REPLACE INTO pw_config(db_name,vtype,db_value) VALUES ('db_styledb','array','$temp_styledb')");
	$db->update("REPLACE INTO pw_config(db_name,vtype,db_value) VALUES ('db_defaultstyle','array','wind85')");
	PromptmsgBycommand('redirect_msg','12',$step);
}elseif($step == '12'){//up85to87 更新置所有站内信提醒为0
	$db->update("UPDATE `pw_members` SET `newpm`=0");
	PromptmsgBycommand('redirect_msg','13',$step);
} elseif ($step == '13'){//up85to87 更新打卡奖励设置
	$hkValue = unserialize($db->get_value("SELECT hk_value FROM pw_hack WHERE hk_name = 'o_punch_reward'"));
	if($hkValue){
		$hkValue['min'] = $hkValue['max'] = $hkValue['num'];
		$hkValue['step'] = 1;
		unset($hkValue['num']);
		$db->update('UPDATE pw_hack SET ' . pwSqlSingle(array('hk_value' => serialize($hkValue))) . ' WHERE hk_name = "o_punch_reward"');
	}
	PromptmsgBycommand('redirect_msg','14',$step);
} elseif($step == '14'){//up85to87 更新导航设置,默认右导航都改为左导航
	$db->update("UPDATE pw_nav SET ".pwSqlSingle(array('type' => 'head_left')) ." WHERE type = 'head_right'");	
	PromptmsgBycommand('redirect_msg','15',$step);
} elseif($step == '15'){//up85to87 版块管理中的社区门户模式转回到传统模式
	$db->update("UPDATE pw_forums SET ifcms = '0' WHERE type='forum' AND (ifcms = '1' OR ifcms = '2')");
	PromptmsgBycommand('redirect_msg','16',$step);
} elseif($step == '16'){//up85to87 门户升级更新 start就等于page
	$invokeService = L::loadClass('invokeservice', 'area');
    $pageInvokeDB = L::loadDB('PageInvoke', 'area');
    $count = $pageInvokeDB->searchCount(array());
    $perpage = 50;
    
    $start || (int)$start = 1;
    $maxPage = ceil($count/$perpage);
    if ($start < $maxPage) {

    	$pageInvokes = $pageInvokeDB->searchPageInvokes(array(),$start,$perpage);
	    foreach ($pageInvokes as $value) {
		$invokeName = $value['invokename'];
		unset($value['id'],$value['invokename']);
		$invokeService->updateInvokeByName($invokeName,$value);
	}
    $start++;
    
} 
    $GLOBALS['max'] = $maxPage;
    $GLOBALS['limit'] = $start;
	PromptmsgBycommand('redirect_msg','17',$step);
	
} elseif($step == '17'){//up85to87勋章升级更新

  $medalService = L::loadClass('medalservice','medal');
  $offset = 500;

  $start || (int)$start = 1;
  list($start, $stepMedal) = explode('_',$start);
  if ($start == 1) {
	$medals = array();
	$db->query("TRUNCATE TABLE pw_medal_info");
	$db->query("TRUNCATE TABLE pw_medal_award");
	$query = $db->query("SELECT * FROM pw_medalinfo");
	while ($value = $db->fetch_array($query)) {
		$temp = array();
		$temp['medal_id'] = $value['id'];
		$temp['name'] = $value['name'];
		$temp['descrip'] = $value['intro'];
		$temp['image'] = $value['picurl'];
		$temp['type'] = 2;
		$medalService->addMedal($temp);
	}
	$start = 2;
} elseif ($start == 2) {
    $stepMedal = (int)$stepMedal;
	$medalLimit = $stepMedal*$offset;
	$count = 0;
	$query = $db->query("SELECT * FROM pw_medaluser ".S::sqlLimit($medalLimit,$offset));
	$awardMedalDb = $medalService->_getMedalAwardDb();
	while ($value = $db->fetch_array($query)) {
		$count++;
		$temp = array('uid'=>$value['uid'],'medal_id'=>$value['mid'],'timestamp'=>$timestamp,'type'=>2);
		$awardMedalDb->insert($temp);
	}
	if ($count == $offset) {
		$start = '2_'.($stepMedal+1);
	} else {
		$start = 3;
	}
} elseif ($start == 3) {
    $stepMedal = (int)$stepMedal;
	$medalLimit = $stepMedal*$offset;
	$count = 0;
	$awardMedalDb = $medalService->_getMedalAwardDb();
	$query = $db->query("SELECT id,awardee,awardtime,timelimit,level FROM pw_medalslogs WHERE action='1' AND state='0' AND timelimit>0 ".S::sqlLimit($medalLimit,$offset));
	while ($value = $db->fetch_array($query)) {
		$count++;
		$uid = $db->get_value("SELECT uid FROM pw_members WHERE username=".S::sqlEscape($value['awardee']));
		if (!$uid) continue;
		$temp = array('timestamp'=>$value['awardtime']);
		if ($value['timelimit']) $temp['deadline'] = $value['awardtime']+$value['timelimit']*2592000;
		$awardMedalDb->updateByUidAndMedalId($temp,$uid,$value['level']);
	}
	if ($count==$offset) {
		$start = '3_'.($stepMedal+1);
	} else {
		$start = 4;
	}
} 
    $GLOBALS['max'] = 4;
    $GLOBALS['limit'] = $start;
    PromptmsgBycommand('redirect_msg','resources',$step);
} elseif ($step=='resources') {//Resources phpwind
	$steptitle = '!';
	InitGP(array('banners','atcbottoms','footers'));
	$banners = (int)$banners; $atcbottoms = (int)$atcbottoms; $footers = (int)$footers;
	if ($banners || $atcbottoms || $footers) {
		echo "<img src=\"http://init.phpwind.net/init_agent.php?sitehash=$db_sitehash&v=$wind_version&c=$ceversion&referer=$_SERVER[HTTP_HOST]&banner=$banners&atcbottom=$atcbottoms&footer=$footers\" width=\"0\" height=\"0\">";
	}
	$stepmsg = $lang['step_resources'];
	$stepright = $lang['success'];
    PromptmsgBycommand('redirect_msg','finish',$step);
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
	updatemedal_list();//medal
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
	writeover(D_P.'data/'.$lockfile.'.lock','LOCKED');
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
	@unlink(D_P.'data/error.txt');
	Cookie('AdminUser','',0);
	//@unlink($basename);
	unset($GLOBALS['turn_up83to85bycommand']);
	echo "恭喜您，升级成功!\n前后台访问地址不变!\n";
	exit;
} else {
	exit('Invalid action in input');
}
}
########################## FUNCTION ##########################
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