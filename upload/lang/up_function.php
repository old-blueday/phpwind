<?php
!defined('PW_UPLOAD') && exit('Forbidden');
function Pwloaddl($mod,$ckfunc='mysqli_get_client_info'){//20080714
	return extension_loaded($mod) && $ckfunc && function_exists($ckfunc) ? true : false;
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
	return substr(md5($str.time().$_SERVER["HTTP_USER_AGENT"].$GLOBALS['db_hash']),0,$len);
}
function N_insertinto($array,$start,$records=null){
	global $db,$times,$record;
	$record || $record = 800;
	$records && $record = $records;
	$array = is_array($array) ? array_slice($array,$start,$record) : array();
	foreach ($array as $value) {
		$value[1] && strtolower(substr($value[0],0,6)) == 'insert' && $value[0] = 'REPLACE'.substr($value[0],6);
		$db->query($value[0]);
		$times++;
	}
}
function N_atindex($array,$start,$records=null){
	global $db,$times,$record;
	$record || $record = 800;
	$records && $record = $records;
	$array = is_array($array) ? array_slice($array,$start,$record) : array();
	foreach ($array as $value) {
		$unique = 0;
		if ($value[3]=='PRIMARY') {
			$add = $drop = 'PRIMARY KEY';
		} elseif ($value[3]=='UNIQUE') {
			$add  = "UNIQUE $value[1]"; $drop = "INDEX $value[1]";
		} else {
			$add = $drop = "INDEX $value[1]";
			$unique = 1;
		}
		$indexkey = array();
		$query = $db->query("SHOW KEYS FROM $value[0]");
		while ($rt = $db->fetch_array($query)) {
			$indexkey[$rt['Key_name']][$rt['Column_name']] = $unique;
		}
		if ($indexkey[$value[1]]) {
			if ($value[2]) {
				$ifdo = false;
				$column = explode(',',$value[2]);
				if (count($indexkey[$value[1]])!=count($column)) {
					$ifdo = true;
				} else {
					foreach ($column as $v) {
						if (!$indexkey[$value[1]][$v]) {
							$ifdo = true; break;
						}
					}
				}
				$ifdo && $db->query("ALTER TABLE $value[0] DROP $drop,ADD $add ($value[2])");
			} elseif (empty($value[4]) || isset($indexkey[$value[1]][$value[4]])) {
				$db->query("ALTER TABLE $value[0] DROP $drop");
			}
		} elseif ($value[2]) {
			$db->query("ALTER TABLE $value[0] ADD $add ($value[2])");
		}
		$times++;
	}
}
function N_pointstable($array){
	global $db,$PW;
	!is_array($array) && $array = array();
	$returnarray = $array;
	foreach ($array as $value) {
		if (in_array($value[0],array('pw_tmsgs','pw_posts'))) {
			$replace = str_replace(array('pw_','_'),array($PW,'\_'),$value[0]);
			$query = $db->query("SHOW TABLE STATUS LIKE '{$replace}%'");
			while ($rt = $db->fetch_array($query)) {
				if (substr($rt['Name'],-5) == 'floor' || substr($rt['Name'],-6) == 'topped') continue;
				if ($replace!=$rt['Name']) {
					$rt['OldName'] = str_replace($PW,'pw_',$rt['Name']);
					$returnarray[] = array($rt['OldName'],$value[1],str_replace(" $value[0] "," $rt[OldName] ",$value[2]));
				}
			}
		}
	}
	return $returnarray;
}
function N_atfield($array,$start,$records=null){
	global $db,$times,$record;
	$record || $record = 800;
	$records && $record = $records;
	$array = is_array($array) ? array_slice($array,$start,$record) : array();
	foreach ($array as $value) {
		//检查表是否存在，以兼容论坛独立表某些表不存在的情况
		$ckTableIfExists = $db->get_one("SHOW TABLES LIKE '$value[0]'");
		if (empty($ckTableIfExists)) continue;
		$rt = $db->get_one("SHOW COLUMNS FROM $value[0] LIKE '$value[1]'");
		$lowersql = strtolower($value[2]);
		if ((strpos($lowersql,' add ')!==false && $rt['Field']!=$value[1]) || (str_replace(array(' drop ',' change '),'',$lowersql)!=$lowersql && $rt['Field']==$value[1])) {
			$db->query($value[2]);
		}
		$times++;
	}
}
function N_createtable($array,$start,$records=null){
	global $db,$charset,$times,$record;
	$record || $record = 800;
	$records && $record = $records;
	!is_array($array) && $array = array();
	$array = array_slice($array,$start,$record);
	foreach ($array as $key => $value) {
		!$value[1] && $value[1] = 'MyISAM';
		$value[0] = "CREATE TABLE IF NOT EXISTS $key ($value[0]) ";
		if ($db->server_info() > '4.1') {
			$value[0] .= "ENGINE=$value[1]".($charset ? " DEFAULT CHARSET=$charset" : '');
		} else {
			$value[0] .= "TYPE=$value[1]";
		}
		!empty($value[2]) && $value[0] .= "  AUTO_INCREMENT=$value[2]";
		$db->query($value[0]);
		$times++;
	}
}


function N_droptable($array,$start,$records=null) {
	global $db,$times,$record;
	$record || $record = 800;
	$records && $record = $records;
	!is_array($array) && $array = array();
	$array = array_slice($array,$start,$record);
	foreach ($array as $key => $value) {
		$db->query("DROP TABLE IF EXISTS $value");
		$times++;
	}
}

function Promptmsg($msg,$tostep=null,$fromstep=null,$lastid=null){
	@extract($GLOBALS, EXTR_SKIP);
	require(R_P.'lang/install_lang.php');
	$lang['showmsg'] = $lang['showmsg_upto'];
	$lang['welcome_msg'] = $lang['welcome_msgupto'];
	if (empty($tostep)) {
		$url = 'javascript:history.go(-1);';
		$lang['last'] = $lang['back'];
	} else {
		$url = "window.location.replace('$basename?step=$tostep');";
		$lang['last'] = $lang['redirect'];
	}
	$lang[$msg] && $msg = $lang[$msg];
	if (!empty($fromstep) && $times==$record && empty($lastid)) {
		list($stepnum,$steptype) = explode('_',$start);
		$end = (int)$stepnum+$record;
		strlen($steptype) && $end .= "_$steptype";
		$url = "window.location.replace('$basename?step=$fromstep&start=$end');";
	} elseif (!empty($fromstep) && $times==$record && $lastid) {
		list($stepnum,$steptype) = explode('_',$start);
		$end = (int)$lastid;
		strlen($steptype) && $end .= "_$steptype";
		$url = "window.location.replace('$basename?step=$fromstep&start=$end');";
	}
	if ($limit < $max) {
		$url = "window.location.replace('$basename?step=$fromstep&start=$limit');";
	}
	$msg = preg_replace("/{#(.+?)}/eis",'$\\1',$msg);
	require(R_P.'lang/promptmsg.htm');footer();
}

function ajaxRedirect($tostep,$fromstep=null,$ajaxResult = 'continue'){
	@extract($GLOBALS, EXTR_SKIP);
	$url = "$basename?step=$tostep";
	if (!empty($fromstep) && $times==$record && empty($lastid)) {
		list($stepnum,$steptype) = explode('_',$start);
		$end = (int)$stepnum+$record;
		strlen($steptype) && $end .= "_$steptype";
		$url = "$basename?step=$fromstep&start=$end";
	} elseif (!empty($fromstep) && $times==$record && $lastid) {
		list($stepnum,$steptype) = explode('_',$start);
		$end = (int)$lastid;
		strlen($steptype) && $end .= "_$steptype";
		$url = "$basename?step=$fromstep&start=$end";
	}
	if ($limit < $max) {
		$url = "$basename?step=$fromstep&start=$limit";
	}
	$msg = $steps[$step];
	switch ($fromstep) {
		case 2:
			$tableNames = array_slice(array_keys($sqlarray), $start,5);
			$tableNames && $msg .= '|' . implode('|',$tableNames);
			break;
		case 3:
			$tableNames = array();
			$sqlarray = ${"sqlarray_$steptype"};
			$tmpTables = array_slice($sqlarray, $stepnum,$record);
			foreach ($tmpTables as $v){
				$tableNames[] = $v[0];
			}
			$tableNames && $msg .= '|' . implode('|',$tableNames);
			break;
	}
	ob_end_clean();
	ob_start();
	echo "$ajaxResult\t$url\t$msg";
	ajax_footer();
}
function footer(){
	global $footer;
	require_once(R_P.'lang/footer.htm');
	$output = trim(str_replace(array('<!--<!---->','<!---->',"\r"),'',ob_get_contents()),"\n");
	ob_end_clean();
	ob_start();
	echo $output;unset($output);exit;
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

function change_array($array) {
	$reset = array();
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			foreach ($value as $k => $v) {
				$reset[$k][$key] = $v;
			}
		}
	}
	return addslashes(serialize($reset));
}

function checkuptoadmin($CK) {
	Add_S($CK);
	global $db,$manager;
	if (is_array($manager) && CkInArray($CK[1],$manager)) {
		global $manager_pwd;
		$v_key = array_search($CK[1],$manager);
		if (!SafeCheck($CK,PwdCode($manager_pwd[$v_key]))) {
			$rt = $db->get_one("SELECT uid,username,groupid,groups,password FROM pw_members WHERE username=".pwEscape($CK[1]));
			if (!SafeCheck($CK,PwdCode($rt['password']))) {
				return false;
			}
		}
		return true;
	} elseif ($CK[1] == $manager) {
	   	global $manager_pwd;
		if (!SafeCheck($CK,PwdCode($manager_pwd))) {
			$rt = $db->get_one("SELECT uid,username,groupid,groups,password FROM pw_members WHERE username=".pwEscape($CK[1]));
			if (!SafeCheck($CK,PwdCode($rt['password']))) {
				return false;
			}
		}
		return true;
	} else {
		return false;
	}
}

function admincheck($uid,$username,$groupid,$groups) {
	global $db;
	$rt = $db->get_one("SELECT username,groupid,groups FROM pw_administrators WHERE uid=".pwEscape($uid));
	if ($rt && $rt['username'] == $username && ($rt['groupid'] == $groupid || strpos($rt['groups'], ",$groupid,") !== false)) {
		return true;
	} else {
		return false;
	}
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
function pwSetVersion($do,$reason='') {//phpwind history
	global $wind_version,$from_version,$wind_repair,$reason,$timestamp,$db;
	$wind_version = strtoupper($wind_version);
	$from_version = strtoupper($from_version);
	$phpwind = $db->get_value("SELECT db_value FROM pw_config WHERE db_name='phpwind'");
	$phpwind = $phpwind ? unserialize($phpwind) : array();
	$phpwind['version'] && $from_version = $phpwind['version'];
	$reason || $reason = $from_version == $wind_version ? ($wind_repair ? 'Repair wind' : 'Re-do it again') : "";
	$phpwind['history'][] = "$do\t$timestamp\t$from_version\t$wind_version,$wind_repair\t$reason";
	$phpwind['version'] = $wind_version;
	$phpwind['repair'] = $wind_repair;
	$db->update("REPLACE INTO pw_config (db_name, db_value, decrip) VALUES ('phpwind',".pwEscape(serialize($phpwind)).",'phpwind')");
	//@unlink(D_P.'data/bbscache/version');
	return $phpwind['version'];
}
function pwGetVersion() {
	global $db,$PW;
	$version = readover(D_P.'data/bbscache/version');
	if (!$version) {
		$phpwind = $db->get_value("SELECT db_value FROM pw_config WHERE db_name='phpwind'");
		$phpwind = $phpwind ? unserialize($phpwind) : array();
		if ($phpwind['version']) {
			$version = $phpwind['version'];
		} else {
			$rt = $db->get_one("SHOW TABLE STATUS LIKE '".str_replace('_','\_',$PW)."permission'");
			$pw_table = $rt['Name'];
			if ($pw_table==$PW.'permission') {
				$version = '7.0rc';
			} else {
				$rt = $db->get_one("SHOW TABLE STATUS LIKE '".str_replace('_','\_',$PW)."cache'");
				$pw_table = $rt['Name'];
				if ($pw_table==$PW.'cache') {
					$version = '6.3.2';
				}
			}
		}
		writeover(D_P.'data/bbscache/version',$version);
	}
	return $version;
}

function arr_unique($array){
	if (is_array($array)) {
		$temp_array = array();
		foreach ($array as $key => $value) {
			$var_md5 = md5(is_array($value) ? serialize($value) : $value);
			if (in_array($var_md5,$temp_array)) {
				unset($array[$key]);
			} else {
				$temp_array[] = $var_md5;
			}
		}
	}
	return $array;
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
function GetLang($lang,$EXT='php'){
	global $tplpath;
	if (file_exists(R_P."template/$tplpath/lang_$lang.$EXT")) {
		return R_P."template/$tplpath/lang_$lang.$EXT";
	} elseif (file_exists(R_P."template/wind/lang_$lang.$EXT")) {
		return R_P."template/wind/lang_$lang.$EXT";
	} else {
		exit("Can not find lang_$lang.$EXT file");
	}
}

function getfavor($tids) {
	if(!is_array($tids)){
		return array(); 
	} else {
		unset($tids[0]);
	}
	$tiddb = array();
	foreach ($tids as $key => $t) {
		if ($t) {
			$v = explode(',',$t);
			foreach ($v as $k => $v1) {
				$key_temp = $key - 1;
				$tiddb[$key_temp][$v1] = $v1;
			}
		}
	}
	return $tiddb;
}

function checkContentIfExist($tableName,$filed=array()) {
	global $db;
	list($filedname,$filedvalue) = each($filed);
	if ($filedname && $filedvalue) {
		  $sqladd = ' WHERE '.$filedname.'="'.$filedvalue.'"';
	}
	$count = $db->get_value("SELECT COUNT(*) FROM ".$tableName.$sqladd);
	if ($count > 0) {
		return true;
	} else {
		return false;
	}
}

function checkTableIfExist($tableName) {
	global $db,$PW;
	$tableName = str_replace('pw_',$PW,$tableName);
	$table = $db->get_one("SHOW TABLE STATUS LIKE '$tableName'");
	if($table) {
		return true;
	}else{
		return false;
	}
}

function getAllSubForums($forums, $parentfid, &$data) {
	foreach ($forums as $forum) {
		if ($forum['fup'] && $forum['fup'] == $parentfid) {
			$data[] = $forum;
			getAllSubForums($forums, $forum['fid'], $data);
		}
	}
}

function checkFields($tableName,$fieldName){
	global $db;
	$isField = $db->get_one("show columns from ".$tableName." like '".$fieldName."'");
	if(!$isField){
		$db->query("ALTER TABLE pw_argument add tpcid smallint(6) NOT NULL default '0'");
	}
}

/**
 * get table size
 * return size
 */

function getTableSize(){
	global $db;
	$size = 0;
	$unit = 'B';
	$query = $db->query('SHOW TABLE STATUS');
	while ($rt = $db->fetch_array($query)) {
		$size += $rt['Index_length'];
		$size += $rt['Data_length'];
	}
	return $size;
}

function getBaseSteps(){
	return array(
		1=> '检查文件目录',
		2=> '创建数据表',
		3=> '更新数据表结构',
		4=> '更新数据表索引',
	);
}
function getMedalImage($name) {
	$image = '';
	switch ($name) {
		case '终身成就奖':
			return 'zhongshenchengjiu.png';
		case '优秀版主奖':
		case '优秀斑竹奖':
			return 'youxiubanzhu.png';
		case '宣传大使奖':
			return 'xuanchuandashi.png';
		case '特殊贡献奖':
			return 'teshugongxian.png';
		case '金点子奖':
			return 'jindianzi.png';
		case '原创先锋奖':
			return 'yuanchuangxianfeng.png';
		case '贴图大师奖':
			return 'tietudashi.png';
		case '灌水天才奖':
			return 'guanshuidashi.png';
		case '新人进步奖':
			return 'xinrenjinbu.png';
		case '幽默大师奖':
			return 'youmodashi.png';
		default:
			return 'teshugongxian.png';
	}
}

function updateTableStructureForMerge($sourceTable) {
	global $db;
	$posts = array();
	$query = $db->query("SHOW TABLE STATUS LIKE '$sourceTable%'");
	while($result = $db->fetch_array($query)){
		$key = $GLOBALS['PW'] ? substr(str_replace($GLOBALS['PW'], 'pw_', $result['Name']), strlen($sourceTable)) : substr($result['Name'],5);
		if (!$key || ($key && !is_numeric($key))) continue;
		$posts[] = $result['Name'];
	}
	if (count($posts) < 1) return array();
	$fieldName = $changeFields = array();
	$fieldName = buildTableFields($sourceTable);
	foreach ($posts as $table) {
		$crtFieldName = buildTableFields($table);
		foreach ($fieldName as $field => $createSql) {
			if (strtolower($createSql[0]) == strtolower($crtFieldName[$field][0])) continue;
			$operate = !$crtFieldName[$field][0] ? ($createSql[2] ? "ADD $createSql[0]" : "ADD $createSql[0] AFTER `$createSql[1]`") : "CHANGE `$field` $createSql[0]";
			$changeFields[] = array("$table", "$field", "ALTER TABLE `$table` $operate");
		}
	}
	return $changeFields;
}

function buildTableFields($table) {
	global $db;
	$masterTable = $db->get_one("SHOW CREATE TABLE `$table`");
	preg_match('/\((.+)\)/is', $masterTable['Create Table'], $master);
	$master = explode("\n", trim($master[1]));
	$fieldName = $keepPosition = array();
	foreach ($master as $key => $value) {
		$tmpFieldName = explode(' ', trim($value));
		$tmpFieldName[0] = strtolower(str_replace('`', '', $tmpFieldName[0]));
		$position = $tmpFieldName[0] == 'key' ? 1 : ($tmpFieldName[0] == 'unique' ? 2 : 0);
		$tempName = str_replace('`', '', $tmpFieldName[$position]);
		$keepPosition[$key] = $tempName;
		$fieldName[$tempName] = array(trim($value, ','), $keepPosition[$key-1], $position);
	}
	return $fieldName;
}

function createMergeTable($table) {
	global $db, $PW;
	$posts = array();
	$query = $db->query("SHOW TABLE STATUS LIKE 'pw_$table%'");
	while($result = $db->fetch_array($query)){
		$key = $PW ? substr(str_replace($GLOBALS['PW'], 'pw_', $result['Name']), strlen($table) + 3) : substr($result['Name'],5);
		if ($key && !is_numeric($key)) continue;
		$key = intval($key);
		$posts[$key] = $result['Name'];
	}
	if (count($posts) <= 1) return false;
	$mergeTable = 'pw_merge_' . $table;
	$tableCreated = $db->get_one("SHOW TABLES LIKE '$mergeTable'");
	if ($tableCreated) return false;

	ksort($posts);
	$createTableMatch = $charsetMatch = array();
	$engineType = $db->server_info() > '4.1' ? 'ENGINE=MERGE' : 'TYPE=MERGE';
	$creatTable = $db->get_one("SHOW CREATE TABLE `pw_$table`");
	preg_match('/\(.+\)/is', $creatTable['Create Table'], $createTableMatch);
	preg_match('/CHARSET=([^;\s]+)/is', $creatTable['Create Table'], $charsetMatch);
	$createTableSql = "CREATE TABLE `$mergeTable` " . $createTableMatch[0] . " $engineType UNION=(" . implode(',', $posts) . ') DEFAULT CHARSET=' . $charsetMatch[1] . ' INSERT_METHOD=LAST';
	$db->query($createTableSql);

	$success = $db->get_one("SHOW TABLE STATUS LIKE '$mergeTable'");
	$config = $success['Engine'] ? 1 : 0;
	setConfig("db_merge_$table", $config);
	return true;
}
?>