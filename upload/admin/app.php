<?php
!defined('P_W') && exit('Forbidden');

require_once(R_P.'require/posthost.php');
@include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');

!$admintype && $admintype = 'appset';

$host = $pwServer['HTTP_HOST'];
$appclient = L::loadClass('AppClient');
$islocalhost = $appclient->isLocalhost($host);

if ($islocalhost && !in_array($admintype,array('appset','appslist','modify','showinfo'))) {//本地判断
	adminmsg('localhost_error',"$basename&admintype=appset");
}

if (!$db_siteappkey && !in_array($admintype,array('appset','registerapp','appslist','modify'))) {//是否绑定APP平台判断
	$admintype = 'showinfo';
}

if ($admintype == 'appset') {
	/*version check*/

    $pw_query = $appclient->getApicode();
	list($app_version,$app_time) = explode('|',$db_app_version);
	if (!$app_version || $timestamp - $app_time > 86400) {

		$partner = md5($db_siteid.$db_siteownerid);
		$verify = md5($db_sitehash.$partner.$db_appid.'appversion');
		$app_version = PostHost("http://app.phpwind.net/pwbbsapi.php?", "m=appversion&sitehash=$db_sitehash&appid=$db_appid&verify=$verify&", "POST");
		list($app_version) = explode("\r\n",substr($app_version,strpos($app_version,'$backdata=')+10));
		if (strpos($app_version,'Not Found') !== false || strpos($app_version,'Error 405 Method Not Allowed') !== false) {
			adminmsg('The Interface is not exist');
		}
		$app_version && setConfig('db_app_version', $app_version.'|'.$timestamp);
	}
	/*version check*/

	/*Personal version check*/
	require_once(R_P.'api/class_Other.php');
	$api = new Other();
	$personal = $api->appver;
	/*Personal version check*/

	/*sitehash check*/
	$updatecache = false;
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name='db_siteid' OR db_name='db_siteownerid' OR db_name='db_sitehash'");
	while ($rt = $db->fetch_array($query)) {
		if (($rt['db_name'] == 'db_siteid' && $rt['db_value'] != $db_siteid) || ($rt['db_name'] == 'db_siteownerid' && $rt['db_value'] != $db_siteownerid) || ($rt['db_name'] == 'db_sitehash' && $rt['db_value'] != $db_sitehash)) {
			${$rt['db_name']} = preg_replace('/[^\d\w\_]/is','',$rt['db_value']);
			$updatecache = true;
		}
	}
	$db->free_result($query);
	if (!$db_siteid) {
		$db_siteid = generatestr(32);
		setConfig('db_siteid', $db_siteid);

		$db_siteownerid = generatestr(32);
		setConfig('db_siteownerid', $db_siteownerid);

		$db_sitehash = '10'.SitStrCode(md5($db_siteid.$db_siteownerid),md5($db_siteownerid.$db_siteid));
		setConfig('db_sitehash', $db_sitehash);
		$updatecache = true;
	}


	if ($app_version || $updatecache) {
		updatecache_c();
	}
	/*sitehash check*/

	/*file check*/
	if(!$files = readover('api/safefiles.md5')){
		adminmsg('safefiles_not_exists');
	}
	$files = explode("\n",$files);
	$md5_a = $md5_c = $md5_m = $md5_d = $dirlist = array();

	safefile('./','\.php',0);
	safefile('api/','\.php|\.html');

	foreach($files as $value){

		if (strpos($value,'./pw_api.php') !== false || strpos($value,'api/') !== false || strpos($value,'./apps.php') !== false) {

			list($md5key,$file) = explode("\t",$value);
			$file = trim($file);
			if (!isset($md5_a[$file])) {
				$md5_d[$file] = 1;
			} elseif ($md5key != $md5_a[$file]) {
				$md5_m[] = $file;
			} else {
				$md5_c[] = $file;
			}
		}
	}
	$cklog = array('1'=>0,'2'=>0,'3'=>0);
	$md5_a = array_merge($md5_a,$md5_d);

	foreach ($md5_a as $file => $value) {
		$dir = dirname($file);
		$filename = basename($file);
		if (isset($md5_d[$file])) {
			$cklog[2]++;
			$dirlist[$dir][] = array($filename,'','','2');
		} else {
			$filemtime = get_date(pwFilemtime($file));
			$filesize  = filesize($file);

			if(in_array($file,$md5_m)){
				$cklog[3]++;
				$dirlist[$dir][] = array($filename,$filesize,$filemtime,'3');
			} if(in_array($file,$md5_c)){
				$dirlist[$dir][] = array($filename,$filesize,$filemtime,'4');
			} elseif(!in_array($file,$md5_c) && !in_array($file,$md5_m)){
				$cklog[1]++;
				$dirlist[$dir][] = array($filename,$filesize,$filemtime,'1');
			}
		}
	}
	/*file check*/
} elseif ($admintype == 'onlineapp') {

	$appurl = $appclient->getOnlineApp();

} elseif ($admintype == 'open') {

	S::gp(array('open_app','updatelist'));

	$sqlarray = file_exists(R_P."api/sql.txt") ? FileArray('api') : array();
	!empty($sqlarray) && SQLCreate($sqlarray);

	$str = $appclient->alertAppState('open');

	$app_set = $db_server_url.'/appset.php';
	if ($response = PostHost($app_set, $str, 'POST')) {
		$response = unserialize($response);
	} else {
		$response = array('result' => 'error', 'error' => 3);
	}

	if (empty($response['error']) && $updatelist != 1) {

		setConfig('db_appifopen', 1);
		updatecache_c();
	}

	adminmsg($response['result'],"$basename&admintype=onlineapp");

} elseif ($admintype == 'close') {

	$str = $appclient->alertAppState('close');

	$app_set = $db_server_url.'/appset.php';
	if ($response = PostHost($app_set, $str, 'POST')) {
		$response = unserialize($response);
	} else {
		$response = array('result' => 'error', 'error' => 3);
	}
	if (empty($response['error'])) {
		setConfig('db_appifopen', 0);
		updatecache_c();
	}

	adminmsg($response['result'],"$basename&admintype=onlineapp");

} elseif ($admintype == 'blooming') {

	$appurl = $appclient->getThreadsUrl('admin', 'blooming', 'index');

}elseif($admintype == 'taolianjie'){

	$appurl = $appclient->getTaojinUrl('admin', 'taoke', 'index');

} elseif ($admintype == 'i9p') {

	if (empty($_POST['step'])) {

		$appurl = $appclient->getAppIframe('17');
	} elseif ($_POST['step'] == 2) {
		S::gp(array('open_app'));

		$sqlarray = file_exists(R_P."api/sql.txt") ? FileArray('api') : array();
		!empty($sqlarray) && SQLCreate($sqlarray);

		$str = $appclient->alertAppState('open');

		$app_set = $db_server_url.'/appset.php';
		if ($response = PostHost($app_set, $str, 'POST')) {
			$response = unserialize($response);
		} else {
			$response = array('result' => 'error', 'error' => 3);
		}

		if (empty($response['error'])) {

			setConfig('db_appifopen', 1);

			updatecache_c();
		}

		adminmsg($response['result'],"$basename&admintype=$admintype");
	}

} elseif ($admintype == 'sinaweibo') {
	$bindService = L::loadClass('weibobindservice', 'sns/weibotoplatform'); /* @var $bindService PW_WeiboBindService */
	$appurl = $bindService->getAppConfigUrl();
} elseif ($admintype == 'registerapp') {
    if (If_manager) {
        $host = $pwServer['HTTP_HOST'];
		$status = $appclient->RegisterApp($host);
		if (!$status) adminmsg('app_register_error',"$basename&admintype=appset");
    }

	adminmsg('operate_success',"$basename&admintype=appset");
}
include PrintEot('app');exit;

function generatestr($len) {
	mt_srand((double)microtime()*1000000);
    $keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars)-1;
	$str = '';
	for ($i=0;$i<$len;$i++){
		$str .= $keychars[mt_rand(0,$maxlen)];
	}
	return substr(md5($str.microtime().$GLOBALS['HTTP_HOST'].$GLOBALS['pwServer']["HTTP_USER_AGENT"].$GLOBALS['db_hash']),0,$len);
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
function SQLCreate($sqlarray) {
	global $db,$charset;
	$query = '';
	foreach ($sqlarray as $value) {
		if ($value[0]!='#') {
			$query .= $value;
			if (substr($value,-1)==';' && !in_array(strtolower(substr($query,0,5)),array('drop ','delet','updat'))) {
				$lowquery = strtolower(substr($query,0,5));
				if (in_array($lowquery,array('creat','alter','inser','repla'))) {
					$next = CheckDrop($query);
					if ($lowquery == 'creat') {
						if (!$next) continue;
						strpos($query,'IF NOT EXISTS')===false && $query = str_replace('TABLE','TABLE IF NOT EXISTS',$query);
						$extra1 = trim(substr(strrchr($value,')'),1));
						$tabtype = substr(strchr($extra1,'='),1);
						$tabtype = substr($tabtype,0,strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
						if ($db->server_info() >= '4.1') {
							$extra2 = "ENGINE=$tabtype".($charset ? " DEFAULT CHARSET=$charset" : '');
						} else {
							$extra2 = "TYPE=$tabtype";
						}
						$query = str_replace($extra1,$extra2.';',$query);
					} elseif (in_array($lowquery,array('inser','repla'))) {
						if (!$next) continue;
						$lowquery == 'inser' && $query = 'REPLACE '.substr($query,6);
					} elseif ($lowquery == 'alter' && !$next && strpos(strtolower($query),'drop')!==false) {
						continue;
					}
					$db->query($query);
					$query = '';
				}
			}
		}
	}
}

function FileArray($hackdir){
	if (function_exists('file_get_contents')) {
		$filedata = @file_get_contents(S::escapePath(R_P."$hackdir/sql.txt"));
	} else {
		$filedata = readover(R_P."$hackdir/sql.txt");
	}
	$filedata = trim(str_replace(array("\t","\r","\n\n",';'),array('','','',";\n"),$filedata));
	$sqlarray = $filedata ? explode("\n",$filedata) : array();
	return $sqlarray;
}
function CheckDrop($query){
	global $db;
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
				if (strpos($path,'./pw_api.php') !== false || strpos($path,'api/') !== false || strpos($path,'mode/o/m_app.php') !== false || strpos($path,'mode/o/m_myapp.php') !== false || strpos($path,'mode/o/template/m_app.htm') !== false || strpos($path,'mode/o/template/m_myapp.htm') !== false || strpos($path,'./apps.php') !== false || strpos($path,'template/wind/apps.htm') !== false) {
					$md5_a[$path] = md5_file($path).md5('app');
				}
			}
		}
	}
	closedir($fp);
}

function UpdateClassCache($classdb=array(),$flag=false) {
	global $info_class;
	$classcache = "<?php\r\n\$info_class=array(\r\n";

	foreach ($classdb as $key => $class) {

		!$class['ifshow'] && $class['ifshow'] = '0';
		$flag && $info_class[$class['cid']]['ifshow'] && $class['ifshow'] = '1';

		$class['name'] = str_replace(array('"',"'"),array("&quot;","&#39;"),$class['name']);
		$classcache .= "'$class[cid]'=>".pw_var_export($class).",\r\n\r\n";
	}
	$classcache .= ");\r\n?>";
	//* writeover(D_P."data/bbscache/info_class.php",$classcache);
	pwCache::setData(D_P."data/bbscache/info_class.php",$classcache);
}
?>