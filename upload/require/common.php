<?php
defined('P_W') || exit('Forbidden');
define('WIND_VERSION', '8.7beta,20110817');
define('PW_USERSTATUS_BANUSER', 1); //是否禁言位 (版块禁言)
define('PW_USERSTATUS_CFGFRIEND', 3); //设置朋友位
//define('PW_USERSTATUS_NEWPM', 5); //是否有新短消息
define('PW_USERSTATUS_NEWRP', 6); //是否新回复通知
define('PW_USERSTATUS_PUBLICMAIL', 7); //是否公开邮箱
define('PW_USERSTATUS_RECEIVEMAIL', 8); //是否接受邮件
define('PW_USERSTATUS_SIGNCHANGE', 9); //签名是否需要转化
define('PW_USERSTATUS_SHOWSIGN', 10); //是否开启签名展示功能
define('PW_USERSTATUS_EDITOR', 11); //所见即所得
define('PW_USERSTATUS_USERBINDING', 12); //用户是否有绑定帐号
define('PW_USERSTATUS_SHOWWIDTHCFG', 13); //切换宽、窄版模式
define('PW_USERSTATUS_SHOWSIDEBAR', 14); //侧栏版块列表收缩切换
define('PW_USERSTATUS_BANSIGNATURE', 15);//是否禁止签名
define('PW_USERSTATUS_AUTHMOBILE', 16);//是否手机实名认证
define('PW_USERSTATUS_AUTHALIPAY', 17);//是否支付宝实名认证
define('PW_USERSTATUS_REPLYEMAIL', 18);//回复电子邮件通知
define('PW_USERSTATUS_REPLYSITEEMAIL', 19);//回复站内通知
define('PW_USERSTATUS_NOTICEVPICE', 20);//设置提示音
define('PW_USERSTATUS_AUTHCERTIFICATE', 21);//是否证件实名认证
define ('PW_COLUMN', 'column' ); //查询字段
define ('PW_EXPR', 'expr' ); //查询表达式
define ('PW_ORDERBY', 'orderby' ); //排序
define ('PW_GROUPBY', 'groupby' ); //分组
define ('PW_LIMIT', 'limit' ); //分页
define ('PW_ASC', 'asc' ); //升序
define ('PW_DESC', 'desc' ); //降序
define ('PW_CACHE_MEMCACHE', 'memcache' ); //内存缓存
define ('PW_CACHE_FILECACHE', 'filecache' ); //文件缓存
define ('PW_CACHE_DBCACHE', 'dbcache' ); //数据库缓存
define ('PW_OVERFLOW_NUM',2000000000);	//数据溢出大小临界数

define('PW_THREADSPECIALSORT_KMD',50);
define('PW_THREADSPECIALSORT_TOP1',101);
define('PW_THREADSPECIALSORT_TOP2',102);
define('PW_THREADSPECIALSORT_TOP3',103);

//* portal Start*//
define ('PW_PORTAL_MAIN', 'main.htm' ); //可视化结构文件
define ('PW_PORTAL_CONFIG', 'config.htm' ); //可视化配置文件
//* portal end*//



require_once(R_P.'require/security.php');


//请求相关

/**
 * 获取客户端IP
 *
 * @global array $pwServer 全局$_SERVER替代变量
 * @global int $db_xforwardip 是否检查代理的ip
 * @return string
 */
function pwGetIp() {
	global $pwServer, $db_xforwardip;
	if ($db_xforwardip) {
		if ($pwServer['HTTP_X_FORWARDED_FOR'] && $pwServer['REMOTE_ADDR']) {
			if (strstr($pwServer['HTTP_X_FORWARDED_FOR'], ',')) {
				$x = explode(',', $pwServer['HTTP_X_FORWARDED_FOR']);
				$pwServer['HTTP_X_FORWARDED_FOR'] = trim(end($x));
			}
			if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $pwServer['HTTP_X_FORWARDED_FOR'])) {return $pwServer['HTTP_X_FORWARDED_FOR'];}
		} elseif ($pwServer['HTTP_CLIENT_IP'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $pwServer['HTTP_CLIENT_IP'])) {return $pwServer['HTTP_CLIENT_IP'];}
	}
	$db_xforwardip = 0;
	if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $pwServer['REMOTE_ADDR'])) {return $pwServer['REMOTE_ADDR'];}
	return 'Unknown';
}

/**
 * 从请求中获取$_GET或$_POST变量，并以key为变量名注册为全局变量
 *
 * @param array|string $keys 单个key或多个key组成的数组，不能为GLOBALS
 * @param string $method P|G
 * @param int $cvtype 0表示不作处理，1表示用Char_cv处理字符串，2表示强制转换为int //TODO 定为常量
 */
function InitGP($keys, $method = null, $cvtype = 1) {
	S::gp($keys, $method, $cvtype);
}

/**
 * 从请求中获取$_GET或$_POST变量
 *
 * @param string $key 键名
 * @param string $method P|G
 * @return mixed
 */
function GetGP($key, $method = null) {
	return S::getGP($key, $method);
}

/**
 * 读取指定的$_SERVER变量
 *
 * @param string|array $keys 环境变量名，可数组或单值
 * @return string|array 根据参数个数返回指定环境变量值
 */
function GetServer($keys) {
	return S::getServer($keys);
}

/**
 * 从请求中获取cookie值
 *
 * @param string $cookieName cookie名
 * @return string
 */
function GetCookie($cookieName) {
	return $_COOKIE[CookiePre() . '_' . $cookieName];
}

//响应和内容处理


/**
 * 设置cookie
 *
 * @global string $db_ckpath
 * @global string $db_ckdomain
 * @global int $timestamp
 * @global array $pwServer
 * @param string $cookieName cookie名
 * @param string $cookieValue cookie值
 * @param int|string $expireTime cookie过期时间，为F表示1年后过期
 * @param bool $needPrefix cookie名是否加前缀
 * @return bool 是否设置成功
 */
function Cookie($cookieName, $cookieValue, $expireTime = 'F', $needPrefix = true) {
	global $db_ckpath, $db_ckdomain, $timestamp, $pwServer;
	static $sIsSecure = null;
	if ($sIsSecure === null) {
		if (!$pwServer['REQUEST_URI'] || ($parsed = @parse_url($pwServer['REQUEST_URI'])) === false) {
			$parsed = array();
		}
		if ($parsed['scheme'] == 'https' || (empty($parsed['scheme']) && ($pwServer['HTTP_SCHEME'] == 'https' || $pwServer['HTTPS'] && strtolower($pwServer['HTTPS']) != 'off'))) {
			$sIsSecure = true;
		} else {
			$sIsSecure = false;
		}
	}

	if (P_W != 'admincp') {
		$cookiePath = !$db_ckpath ? '/' : $db_ckpath;
		$cookieDomain = $db_ckdomain;
	} else {
		$cookiePath = '/';
		$cookieDomain = '';
	}
	$isHttponly = false;
	if ($cookieName == 'AdminUser' || $cookieName == 'winduser') {
		$agent = strtolower($pwServer['HTTP_USER_AGENT']);
		if (!($agent && preg_match('/msie ([0-9]\.[0-9]{1,2})/i', $agent) && strstr($agent, 'mac'))) {
			$isHttponly = true;
		}
	}
	$cookieValue = str_replace("=", '', $cookieValue);
	strlen($cookieValue) > 512 && $cookieValue = substr($cookieValue, 0, 512);
	$needPrefix && $cookieName = CookiePre() . '_' . $cookieName;
	if ($expireTime == 'F') {
		$expireTime = $timestamp + 31536000;
	} elseif ($cookieValue == '' && $expireTime == 0) {return setcookie($cookieName, '', $timestamp - 31536000, $cookiePath, $cookieDomain, $sIsSecure);}

	if (PHP_VERSION < 5.2) {
		return setcookie($cookieName, $cookieValue, $expireTime, $cookiePath . ($isHttponly ? '; HttpOnly' : ''), $cookieDomain, $sIsSecure);
	} else {
		return setcookie($cookieName, $cookieValue, $expireTime, $cookiePath, $cookieDomain, $sIsSecure, $isHttponly);
	}
}

/**
 * 压缩内容，并设置响应头为压缩格式
 *
 * @global string $db_obstart
 * @param string $output 要压缩的内容
 * @return string
 */
function ObContents($output) {
	ob_end_clean();
	$getHAE = S::getServer('HTTP_ACCEPT_ENCODING');
	if (!headers_sent() && $GLOBALS['db_obstart'] && $getHAE && N_output_zip() != 'ob_gzhandler') {
		$encoding = '';
		if (strpos($getHAE, 'x-gzip') !== false) {
			$encoding = 'x-gzip';
		} elseif (strpos($getHAE, 'gzip') !== false) {
			$encoding = 'gzip';
		}
		if ($encoding && function_exists('crc32') && function_exists('gzcompress')) {
			header('Content-Encoding: ' . $encoding);
			$outputLen = strlen($output);
			$outputZip = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			$outputZip .= substr(gzcompress($output, $GLOBALS['db_obstart']), 0, -4);
			$outputZip .= @pack('V', crc32($output));
			$output = $outputZip . @pack('V', $outputLen);
		} else {
			ObStart();
		}
	} else {
		ObStart();
	}
	return $output;
}

/**
 * 开启输出缓存
 *
 * @return bool
 */
function ObStart() {
	ObGetMode() == 1 ? ob_start('ob_gzhandler') : ob_start();
}

/**
 * 判断输出模式是否为可压缩
 *
 * @global string $db_obstart
 * @return int 1为可压缩
 */
function ObGetMode() {
	static $sOutputMode = null;
	if ($sOutputMode !== null) {return $sOutputMode;}
	$sOutputMode = 0;
	if ($GLOBALS['db_obstart'] && function_exists('ob_gzhandler') && N_output_zip() != 'ob_gzhandler' && (!function_exists('ob_get_level') || ob_get_level() < 1)) {
		$sOutputMode = 1;
	}
	return $sOutputMode;
}

/**
 * 将输出缓存中的内容刷出
 *
 * @param bool $ob 是否使用ob_flush
 */
function N_flush($ob = null) {
	if (php_sapi_name() != 'apache2handler' && php_sapi_name() != 'apache2filter') {
		if (N_output_zip() == 'ob_gzhandler') {return;}
		if ($ob && ob_get_length() !== false && ob_get_status() && !ObGetMode($GLOBALS['db_obstart'])) {
			@ob_flush();
		}
		flush();
	}
}

/**
 * 判断输出缓存输出处理者
 *
 * @return string
 */
function N_output_zip() {
	static $sOutputHandler = null;
	if ($sOutputHandler === null) {
		if (@ini_get('zlib.output_compression')) {
			$sOutputHandler = 'ob_gzhandler';
		} else {
			$sOutputHandler = @ini_get('output_handler');
		}
	}
	return $sOutputHandler;
}

/**
 * 将输出缓存中的内容以ajax格式输出，并中断程序
 *
 * @global string $db_charset
 */
function ajax_footer() {
	global $db_charset,$db_htmifopen;
	if (defined('SHOWLOG')) Error::writeLog();
	$output = str_replace(array('<!--<!--<!---->','<!--<!---->','<!---->-->','<!---->','<!-- -->'),'', ob_get_contents());
	if (P_W == 'admincp') {
		$output = preg_replace(
			"/\<form([^\<\>]*)\saction=['|\"]?([^\s\"'\<\>]+)['|\"]?([^\<\>]*)\>/ies",
			"FormCheck('\\1','\\2','\\3')",
			rtrim($output,'<!--')
		);
	} else {
		$output = parseHtmlUrlRewrite($output, $db_htmifopen);
	}
	header("Content-Type: text/xml;charset=$db_charset");
	echo ObContents("<?xml version=\"1.0\" encoding=\"$db_charset\"?><ajax><![CDATA[" . $output . "]]></ajax>");
	exit();
}

/**
 * 将数组格式化成json格式
 *
 * @param  $type
 * @return string
 */
function pwJsonEncode($var) {
	switch (gettype($var)) {
		case 'boolean' :
			return $var ? 'true' : 'false';
		case 'NULL' :
			return 'null';
		case 'integer' :
			return (int) $var;
		case 'double' :
		case 'float' :
			return (float) $var;
		case 'string' :
			return '"' . addslashes(str_replace(array("\n", "\r", "\t"), '', addcslashes($var, '\\"'))) . '"';
		case 'array' :
			if (count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
				$properties = array();
				foreach ($var as $name => $value) {
					$properties[] = pwJsonEncode(strval($name)) . ':' . pwJsonEncode($value);
				}
				return '{' . join(',', $properties) . '}';
			}
			$elements = array_map('pwJsonEncode', $var);
			return '[' . join(',', $elements) . ']';
	}
	return false;
}

//全局业务


/**
 * 分析服务器负载
 *
 * 只针对*unix服务器有效
 *
 * @param int $maxLoadAvg 负载最大值
 * @return boolean 是否超过最大负载
 */
function pwLoadAvg($maxLoadAvg) {
	$avgstats = 0;
	if (@file_exists('/proc/loadavg')) {
		if ($fp = @fopen('/proc/loadavg', 'r')) {
			$avgdata = @fread($fp, 6);
			@fclose($fp);
			list($avgstats) = explode(' ', $avgdata);
		}
	}
	if ($avgstats > $maxLoadAvg) {
		return true;
	} else {
		return false;
	}
}

/**
 * CC攻击处理
 *
 * CC攻击会导致服务器负载过大,对相关客户端请求进行处理并日志
 *
 * @global int $timestamp
 * @global string $onlineip
 * @global array $pwServer
 * @global string $db_xforwardip
 * @param int $ccLoad 服务器负载参数
 * @return void
 */
function pwDefendCc($ccLoad) {
	global $timestamp, $onlineip, $pwServer, $db_xforwardip;
	if ($ccLoad == 2 && !empty($pwServer['HTTP_USER_AGENT'])) {
		$userAgent = strtolower($pwServer['HTTP_USER_AGENT']);
		if (str_replace(array('spider', 'google', 'msn', 'yodao', 'yahoo', 'http:'), '', $userAgent) != $userAgent) {
			$ccLoad = 1;
		}
	}
	Cookie('c_stamp', $timestamp, 0);
	$ccTimestamp = GetCookie('c_stamp');
	$ccCrc32 = substr(md5($ccTimestamp . $pwServer['HTTP_REFERER']), 0, 10);
	$ccBanedIp = readover(D_P . 'data/ccbanip.txt');
	if ($ccBanedIp && $ipOffset = strpos("$ccBanedIp\n", "\t$onlineip\n")) {
		$ccLtt = substr($ccBanedIp, $ipOffset - 10, 10);
		$ccCrc32 == $ccLtt && exit('Forbidden, Please turn off CC');
		pwCache::writeover(D_P . 'data/ccbanip.txt', str_replace("\n$ccLtt\t$onlineip", '', $ccBanedIp));
	}
	if (($db_xforwardip || $ccLoad == 2) && ($timestamp - $ccTimestamp > 3 || $timestamp < $ccTimestamp)) {
		$isCc = false;
		if ($fp = @fopen(D_P . 'data/ccip.txt', 'rb')) {
			flock($fp, LOCK_SH);
			$size = 27 * 800;
			fseek($fp, -$size, SEEK_END);
			while (!feof($fp)) {
				$value = explode("\t", fgets($fp, 29));
				if (trim($value[1]) == $onlineip && $ccCrc32 == $value[0]) {
					$isCc = true;
					break;
				}
			}
			fclose($fp);
		}
		if ($isCc) {
			echo 'Forbidden, Please Refresh';
			$banIps = '';
			$ccBanedIp && $banIps .= implode("\n", array_slice(explode("\n", $ccBanedIp), -999));
			$banIps .= "\n" . $ccCrc32 . "\t" . $onlineip;
			pwCache::writeover(D_P . 'data/ccbanip.txt', $banIps);
			exit();
		}
		@filesize(D_P . 'data/ccip.txt') > 27 * 1000 && P_unlink(D_P . 'data/ccip.txt');
		pwCache::writeover(D_P . 'data/ccip.txt', "$ccCrc32\t$onlineip\n", 'ab');
	}
}

//全局处理


/**
 * 删除多余全局变量，对GPCF加转义
 *
 * 多余的全局变量,会对站点安全构成威胁.需要保留的变量在$allowed中说明
 *
 */
function pwInitGlobals() {
	S::filter();
}

/**
 * 生成cookie前缀
 *
 * @global string $cookiepre
 * @global string $db_sitehash
 * @return string
 */
function CookiePre() {
	return ($GLOBALS['db_cookiepre']) ? $GLOBALS['db_cookiepre'] : substr(md5($GLOBALS['db_sitehash']), 0, 5);
}

//文件处理


/**
 * 删除文件
 *
 * @param string $fileName 文件绝对路径
 * @return bool
 */
function P_unlink($fileName) {
	return @unlink(S::escapePath($fileName));
}

/**
 * 读取文件
 *
 * @param string $fileName 文件绝对路径
 * @param string $method 读取模式
 */
function readover($fileName, $method = 'rb') {
	$fileName = S::escapePath($fileName);
	$data = '';
	if ($handle = @fopen($fileName, $method)) {
		flock($handle, LOCK_SH);
		$data = @fread($handle, filesize($fileName));
		fclose($handle);
	}
	return $data;
}

/**
 * 写文件
 *
 * @param string $fileName 文件绝对路径
 * @param string $data 数据
 * @param string $method 读写模式
 * @param bool $ifLock 是否锁文件
 * @param bool $ifCheckPath 是否检查文件名中的“..”
 * @param bool $ifChmod 是否将文件属性改为可读写
 * @return bool 是否写入成功   :注意rb+创建新文件均返回的false,请用wb+
 */
function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
	$fileName = S::escapePath($fileName, $ifCheckPath);
	touch($fileName);
	$handle = fopen($fileName, $method);
	$ifLock && flock($handle, LOCK_EX);
	$writeCheck = fwrite($handle, $data);
	$method == 'rb+' && ftruncate($handle, strlen($data));
	fclose($handle);
	$ifChmod && @chmod($fileName, 0777);
	return $writeCheck;
}

/**
 * 服务器时间校正后的文件修改时间
 *
 * @global string $db_cvtime
 * @param string $file 文件路径
 * @return int 返回修改时间
 */
function pwFilemtime($file) {
	return file_exists($file) ? intval(filemtime($file) + $GLOBALS['db_cvtime'] * 60) : 0;
}

//字符串处理


/**
 * 加密、解密字符串
 *
 * @global string $db_hash
 * @global array $pwServer
 * @param $string 待处理字符串
 * @param $action 操作，ENCODE|DECODE
 * @return string
 */
function StrCode($string, $action = 'ENCODE') {
	$action != 'ENCODE' && $string = base64_decode($string);
	$code = '';
	$key = substr(md5($GLOBALS['pwServer']['HTTP_USER_AGENT'] . $GLOBALS['db_hash']), 8, 18);
	$keyLen = strlen($key);
	$strLen = strlen($string);
	for ($i = 0; $i < $strLen; $i++) {
		$k = $i % $keyLen;
		$code .= $string[$i] ^ $key[$k];
	}
	return ($action != 'DECODE' ? base64_encode($code) : $code);
}

/**
 * 截断字符串
 *
 * @global string $db_charset
 * @param string $content 内容
 * @param int $length 截取字节数
 * @param string $add 是否带省略号，Y|N
 * @return string
 */
function substrs($content, $length, $add = 'Y') {
	if (strlen($content) > $length) {
		if ($GLOBALS['db_charset'] != 'utf-8') {
			$cutStr = '';
			for ($i = 0; $i < $length - 1; $i++) {
				$cutStr .= ord($content[$i]) > 127 ? $content[$i] . $content[++$i] : $content[$i];
			}
			$i < $length && ord($content[$i]) <= 127 && $cutStr .= $content[$i];
			return $cutStr . ($add == 'Y' ? ' ..' : '');
		}
		return utf8_trim(substr($content, 0, $length)) . ($add == 'Y' ? ' ..' : '');
	}
	return $content;
}

/**
 * utf8字符串整齐化
 *
 * @param string $str
 * @return string
 */
function utf8_trim($str) {
	$hex = '';
	$len = strlen($str) - 1;
	for ($i = $len; $i >= 0; $i -= 1) {
		$ch = ord($str[$i]);
		$hex .= " $ch";
		if (($ch & 128) == 0 || ($ch & 192) == 192) {return substr($str, 0, $i);}
	}
	return $str . $hex;
}

/**
 * 获取随机字符串
 *
 * @param int $length 字符串长度
 * @return string
 */
function randstr($length) {
	return substr(md5(num_rand($length)), mt_rand(0, 32 - $length), $length);
}

/**
 * 获取随机数
 *
 * @param int $length 随机数字个数
 * @return string
 */
function num_rand($length) {
	mt_srand((double) microtime() * 1000000);
	$randVal = mt_rand(1, 9);
	for ($i = 1; $i < $length; $i++) {
		$randVal .= mt_rand(0, 9);
	}
	return $randVal;
}

function getAllowKeysFromArray($array, $allowKeys) {
	if (!is_array($array) || !is_array($allowKeys)) return array();
	$data = array();
	foreach ($array as $key => $value) {
		in_array($key, $allowKeys) && $data[$key] = $value;
	}
	return $data;
}
/**
 * 变量导出为字符串
 *
 * @param mixed $input 变量
 * @param string $indent 缩进
 * @return string
 */
function pw_var_export($input, $indent = '') {
	switch (gettype($input)) {
		case 'string' :
			return "'" . str_replace(array("\\", "'"), array("\\\\", "\'"), $input) . "'";
		case 'array' :
			$output = "array(\r\n";
			foreach ($input as $key => $value) {
				$output .= $indent . "\t" . pw_var_export($key, $indent . "\t") . ' => ' . pw_var_export($value, $indent . "\t");
				$output .= ",\r\n";
			}
			$output .= $indent . ')';
			return $output;
		case 'boolean' :
			return $input ? 'true' : 'false';
		case 'NULL' :
			return 'NULL';
		case 'integer' :
		case 'double' :
		case 'float' :
			return "'" . (string) $input . "'";
	}
	return 'NULL';
}

/**
 * 编码转换
 *
 * @uses Chinese
 * @param string $str 内容字符串
 * @param string $toEncoding 转为新编码
 * @param string $fromEncoding 原编码
 * @param bool $ifMb 是否使用mb函数
 * @return string
 */
function pwConvert($str, $toEncoding, $fromEncoding, $ifMb = true) {
	if (strtolower($toEncoding) == strtolower($fromEncoding)) {return $str;}
	is_object($str) && $str = get_object_vars($str);//fixed: object can't convert, by alacner 2010/09/15
	if (is_array($str)) {
		foreach ($str as $key => $value) {
			is_object($value) && $value = get_object_vars($value);
			$str[$key] = pwConvert($value, $toEncoding, $fromEncoding, $ifMb);
		}
		return $str;
	} else {
		if (function_exists('mb_convert_encoding') && $ifMb) {
			return mb_convert_encoding($str, $toEncoding, $fromEncoding);
		} else {
			static $sConvertor = null;
			!$toEncoding && $toEncoding = 'GBK';
			!$fromEncoding && $fromEncoding = 'GBK';
			if (!isset($sConvertor) && !is_object($sConvertor)) {
				L::loadClass('Chinese', 'utility/lang', false);
				$sConvertor = new Chinese();
			}
			return $sConvertor->Convert($str, $fromEncoding, $toEncoding, !$ifMb);
		}
	}
}

//数组处理


/**
 * 值是否在数组中
 *
 * @param $value 值
 * @param $stack 数组
 * @return bool
 */
function CkInArray($value, $stack) {
	return S::inArray($value, $stack);
}

//日期处理


/**
 * 格式化时间戳为日期字符串
 *
 * @global string $db_datefm
 * @global string $db_timedf
 * @global string $_datefm
 * @global string $_timedf
 * @param int $timestamp
 * @param string $format
 * @return string
 */
function get_date($timestamp, $format = null) {
	static $sDefaultFormat = null, $sOffset = null;
	if (!isset($sOffset)) {
		global $db_datefm, $db_timedf, $_datefm, $_timedf;
		$sDefaultFormat = $_datefm ? $_datefm : $db_datefm;
		if ($_timedf && $_timedf != '111') {
			$sOffset = $_timedf * 3600;
		} elseif ($db_timedf && $db_timedf != '111') {
			$sOffset = $db_timedf * 3600;
		} else {
			$sOffset = 0;
		}
	}
	empty($format) && $format = $sDefaultFormat;
	return gmdate($format, $timestamp + $sOffset);
}

/**
 * 日期字符串转为时间戳
 *
 * @global string $db_timedf
 * @param string $dateString
 * @return int
 */
function PwStrtoTime($dateString) {
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($dateString) - $db_timedf * 3600 : strtotime($dateString);
}

//附件业务


/**
 * 获取附件url
 *
 * @global string $attachdir
 * @global string $attachpath
 * @global string $db_ftpweb
 * @global string $attach_url
 * @param string $relativePath 附件相对地址
 * @param string|null $type 附件获取范围
 * @param string|null $isThumb 是否是缩略图
 * @return mixed
 */
function geturl($relativePath, $type = null, $isThumb = null) {
	global $attachdir, $attachpath, $db_ftpweb, $attach_url;
	if ($isThumb) {
		if (file_exists($attachdir . '/thumb/' . $relativePath)) {
			return array($attachpath . '/thumb/' . $relativePath, 'Local');
		} elseif (file_exists($attachdir . '/' . $relativePath)) {
			return array($attachpath . '/' . $relativePath, 'Local');
		} elseif ($db_ftpweb) {
			$relativePath = 'thumb/' . $relativePath;
		}
	}
	if (file_exists($attachdir . '/' . $relativePath)) {return array($attachpath . '/' . $relativePath, 'Local');}
	if ($db_ftpweb && !$attach_url || $type == 'lf') {return array($db_ftpweb . '/' . $relativePath, 'Ftp');}
	if (!$db_ftpweb && !is_array($attach_url)) {return array($attach_url . '/' . $relativePath, 'att');}
	if (!$db_ftpweb && count($attach_url) == 1) {return array($attach_url[0] . '/' . $relativePath, 'att');}
	if ($type == 'show') {return ($db_ftpweb || $attach_url) ? 'imgurl' : 'nopic';}
	if ($db_ftpweb && $fp = @fopen($db_ftpweb . '/' . $relativePath, 'rb')) {
		@fclose($fp);
		return array($db_ftpweb . '/' . $relativePath, 'Ftp');
	}
	if (!empty($attach_url)) {
		foreach ($attach_url as $value) {
			if ($value != $db_ftpweb && ($fp = @fopen($value . '/' . $relativePath, 'rb'))) {
				@fclose($fp);
				return array($value . '/' . $relativePath, 'att');
			}
		}
	}
	return false;
}

//帖子业务


/**
 * 剔除WindCode
 *
 * @param string $text
 * @return string
 */
function stripWindCode($text) {
	$pattern = array();
	if (strpos($text, "[post]") !== false && strpos($text, "[/post]") !== false) {
		$pattern[] = "/\[post\].+?\[\/post\]/is";
	}
	if (strpos($text, "[img]") !== false && strpos($text, "[/img]") !== false) {
		$pattern[] = "/\[img\].+?\[\/img\]/is";
	}
	if (strpos($text, "[hide=") !== false && strpos($text, "[/hide]") !== false) {
		$pattern[] = "/\[hide=.+?\].+?\[\/hide\]/is";
	}
	if (strpos($text, "[sell") !== false && strpos($text, "[/sell]") !== false) {
		$pattern[] = "/\[sell=.+?\].+?\[\/sell\]/is";
	}
	$pattern[] = "/\[[a-zA-Z]+[^]]*?\]/is";
	$pattern[] = "/\[\/[a-zA-Z]*[^]]\]/is";

	$text = preg_replace($pattern, '', $text);
	return trim($text);
}

//帖子业务，表名处理


/**
 * 获取帖子分表表名
 *
 * @global array $db_tlist
 * @param int $tid 帖子id
 * @return string
 */
function GetTtable($tid) {
	global $db_tlist;
	if ($db_tlist && is_array($db_tlist)) {
		foreach ($db_tlist as $key => $value) {
			if ($key > 0 && $tid > $value[1]) {return 'pw_tmsgs' . (int) $key;}
		}
	}
	return 'pw_tmsgs';
}

/**
 * 获取回帖分表表名
 *
 * @global array $db_plist
 * @global DB $db
 * @param int|string $postTableId 回复分表id，为N则自动取
 * @param int $tid 帖子id
 * @return string
 */
function GetPtable($postTableId, $tid = null) {
	if ($GLOBALS['db_plist'] && is_array($plistdb = $GLOBALS['db_plist'])) {
		if ($postTableId == 'N' && !empty($tid)) {
			$postTableId = $GLOBALS['db']->get_value('SELECT ptable FROM pw_threads WHERE tid=' . S::sqlEscape($tid, false));
		}
		if ((int) $postTableId > 0 && array_key_exists($postTableId, $plistdb)) {return 'pw_posts' . $postTableId;}
	}
	return 'pw_posts';
}

/**
 * 获取团购分表
 *
 * @global string $db_pcids
 * @param int $pcid 团购id
 * @return string
 */
function GetPcatetable($pcid) {
	global $db_pcids;
	$pcid = (int) $pcid;
	if ($pcid > 0 && trim($db_pcids, ',')) {
		if (strpos("," . $db_pcids . ",", "," . $pcid . ",") !== false) {return 'pw_pcvalue' . $pcid;}
	}
	Showmsg('undefined_action');
}

/**
 * 获取分类信息分表
 *
 * @global string $db_modelids
 * @param int $modelid 分类信息id
 * @return string
 */
function GetTopcitable($modelid) {
	global $db_modelids;
	$modelid = (int) $modelid;
	if ($modelid > 0 && trim($db_modelids, ',')) {
		if (strpos("," . $db_modelids . ",", "," . $modelid . ",") !== false) {return 'pw_topicvalue' . $modelid;}
	}
	Showmsg('undefined_action');
}

/**
 * 根据活动子分类ID获取存放其数据的数据库表名
 * @param int $actmid 活动子分类ID actmid
 * @param bool $checkTableExists 是否检查数据库表存在
 * @param bool $isUserDefinedField 是否为用户自定义（非默认）的字段
 * @return string 数据库表名
 */
function getActivityValueTableNameByActmid($actmid = '', $checkTableExists = 1, $isUserDefinedField = 1) {
	global $db_actmids;
	if ($actmid && $isUserDefinedField) { //用户自定义的字段
		$actmid = (int) $actmid;
		if (!$checkTableExists || ($actmid > 0 && trim($db_actmids, ',') && strpos("," . $db_actmids . ",", "," . $actmid . ",") !== false)) {return 'pw_activityvalue' . $actmid;}
		Showmsg('undefined_action');
	} else { //默认字段
		return 'pw_activitydefaultvalue';
	}
}

//版块业务


/**
 * 当前登录用户版块管理权限
 *
 * @global string $gp_gptype
 * @global int $winduid
 * @global int $groupid
 * @global int $fid
 * @global DB $db
 * @param string $isBM 用户是否为版主
 * @param string $rightKey 指定要获取的权限名
 * @param integer $fid 版块FID
 * @return mixed 返回指定权限值
 */
function pwRights($isBM = false, $rightKey = '', $fid = false) {
	static $sForumRights = null;

	if ($GLOBALS['gp_gptype'] != 'system' && $GLOBALS['gp_gptype'] != 'special') return false;

	$uid = (int) $GLOBALS['winduid'];
	$gid = (int) $GLOBALS['groupid'];
	$fid === false && $fid = (int) $GLOBALS['fid'];

	if (empty($uid) || empty($gid) || empty($fid)) return false;

	if (!isset($sForumRights[$fid])) {
		$sForumRights[$fid] = $forumRight = array();
		$isUser = false;

		$pwSQL = 'uid=' . S::sqlEscape($uid, false) . 'AND fid=' . S::sqlEscape($fid, false) . "AND gid='0'";
		if ($isBM && $gid != 5) { //获取版主权限
			$pwSQL .= " OR uid='0' AND fid=" . S::sqlEscape($fid, false) . "AND gid IN ('5'," . S::sqlEscape($gid, false) . ") OR uid='0' AND fid='0' AND gid='5'";
		} else {
			$pwSQL .= " OR uid='0' AND fid=" . S::sqlEscape($fid, false) . "AND gid=" . S::sqlEscape($gid, false);
		}
		$query = $GLOBALS['db']->query("SELECT uid,fid,gid,rkey,rvalue FROM pw_permission WHERE ($pwSQL) AND type='systemforum' ORDER BY uid DESC,fid");
		while ($rt = $GLOBALS['db']->fetch_array($query)) {
			if ($rt['uid'] == $uid) { //用户个人权限
				$sForumRights[$fid][$rt['rkey']] = $rt['rvalue'];
				$isUser = true;
			} elseif ($isUser) { //取得个人权限,结束
				break;
			} elseif ($isBM && $rt['gid'] && $gid != $rt['gid']) { //版主权限
				$forumRight[$rt['rkey']] = $rt['rvalue'];
			} else {
				$sForumRights[$fid][$rt['rkey']] = $rt['rvalue'];
			}
		}
		if (!$isUser) {
			empty($sForumRights[$fid]) && ($GLOBALS['SYSTEM']['superright'] || $isBM && $gid == 5) && $sForumRights[$fid] = $GLOBALS['SYSTEM'];
			if ($forumRight) { //版主权限加权
				foreach ($forumRight as $key => $value) {
					$sForumRights[$fid][$key] < $value && $sForumRights[$fid][$key] = $value;
				}
			}
		}
	}
	return empty($rightKey) ? $sForumRights[$fid] : $sForumRights[$fid][$rightKey];
}

/**
 * //TODO 使用难度很高
 *
 * @param $status
 * @param $b
 * @param $getv
 */
function getstatus($status, $b, $getv = 1) {
	return $status >> --$b & $getv;
}

/**
 * 是否是创始人
 *
 * @global array $manager
 * @param string $name 用户帐号
 * @return bool
 */
function isGM($name) {
	global $manager;
	return S::inArray($name, $manager);
}

/**
 * 判断用户所在用户组对版块的管理权限
 *
 * @param string $name 用户名
 * @param bool $isBM  是否为版主
 * @param string $type 例如：$pwSystem权限，deltpcs编辑权限
 * @return bool
 */
function userSystemRight($name, $isBM, $type) {
	$isGM = isGM($name);
	$pwSystem = pwRights($isBM);
	if ($isGM || $pwSystem[$type])  return true;
	return false;
}

/**
 * 获取用户信息
 *
 * @global DB $db
 * @param int $uid
 * @return array
 */
function getUserByUid($uid) {
	$uid = S::int($uid);
	if ($uid < 1) return false;
	if (perf::checkMemcache()){
		$_cacheService = Perf::getCacheService();
		$detail = $_cacheService->get('member_all_uid_' . $uid);
		if ($detail && in_array(SCR, array('index', 'read', 'thread', 'post'))){
			$_singleRight = $_cacheService->get('member_singleright_uid_' . $uid);
			$detail	= ($_singleRight === false) ? false : (array)$detail + (array)$_singleRight;
		}
		if ($detail){
			return $detail && $detail['groupid'] != 0 && isset($detail['md.uid']) ? $detail : false;
		}
		$cache = perf::gatherCache('pw_members');
		if (in_array(SCR, array('index', 'read', 'thread', 'post'))){
			$detail = $cache->getMembersAndMemberDataAndSingleRightByUserId($uid);
		} else {
			$detail = $cache->getAllByUserId($uid, true, true);
		}
		return $detail && $detail['groupid'] != 0 && isset($detail['md.uid']) ? $detail : false;
	}else {
		global $db;
		$sqladd = $sqltab = '';
		if (in_array(SCR, array('index', 'read', 'thread', 'post'))) {
			$sqladd = (SCR == 'post') ? ',md.postcheck,sr.visit,sr.post,sr.reply' : (SCR == 'read' ? ',sr.visit,sr.reply' : ',sr.visit');
			$sqltab = "LEFT JOIN pw_singleright sr ON m.uid=sr.uid";
		}
		$detail = $db->get_one("SELECT m.uid,m.username,m.password,m.safecv,m.email,m.bday,m.oicq,m.groupid,m.memberid,m.groups,m.icon,m.regdate,m.honor,m.timedf,m.style,m.datefm,m.t_num,m.p_num,m.yz,m.newpm,m.userstatus,m.shortcut,m.medals,md.lastmsg,md.postnum,md.rvrc,md.money,md.credit,md.currency,md.lastvisit,md.thisvisit,md.onlinetime,md.lastpost,md.todaypost,md.monthpost,md.onlineip,md.uploadtime,md.uploadnum,md.starttime,md.pwdctime,md.monoltime,md.digests,md.f_num,md.creditpop,md.jobnum,md.lastgrab,md.follows,md.fans,md.newfans,md.newreferto,md.newcomment,md.punch,md.bubble,md.newnotice,md.newrequest,md.shafa $sqladd FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid $sqltab WHERE m.uid=" . S::sqlEscape($uid) . " AND m.groupid<>'0' AND md.uid IS NOT NULL");
		return $detail;
	}
}


//积分业务


/**
 * 得到积分名称
 *
 * @global string $db_moneyname
 * @global string $db_rvrcname
 * @global string $db_creditname
 * @global string $db_currencyname
 * @global array $_CREDITDB
 * @param string $creditType 积分类型
 * @return mixed
 */
function pwCreditNames($creditType = null) {
	static $sCreditNames = null;
	if (!isset($sCreditNames)) {
		$sCreditNames = array('money' => $GLOBALS['db_moneyname'], 'rvrc' => $GLOBALS['db_rvrcname'],
			'credit' => $GLOBALS['db_creditname'], 'currency' => $GLOBALS['db_currencyname']);
		foreach ($GLOBALS['_CREDITDB'] as $key => $value) {
			$sCreditNames[$key] = $value[0];
		}
	}
	return isset($creditType) ? $sCreditNames[$creditType] : $sCreditNames;
}

/**
 * 获取积分单位
 *
 * @global string $db_moneyunit
 * @global string $db_rvrcunit
 * @global string $db_creditunit
 * @global string $db_currencyunit
 * @global string $_CREDITDB
 * @param string $creditType 积分类型
 * @return string
 */
function pwCreditUnits($creditType = null) {
	static $sCreditUnits = null;
	if (!isset($sCreditUnits)) {
		$sCreditUnits = array('money' => $GLOBALS['db_moneyunit'], 'rvrc' => $GLOBALS['db_rvrcunit'],
			'credit' => $GLOBALS['db_creditunit'], 'currency' => $GLOBALS['db_currencyunit']);
		foreach ($GLOBALS['_CREDITDB'] as $key => $value) {
			$sCreditUnits[$key] = $value[1];
		}
	}
	return isset($creditType) ? $sCreditUnits[$creditType] : $sCreditUnits;
}

//app业务


/**
 * 获取用户的唯一字符串
 *
 * @global string $db_hash
 * @param int $uid
 * @param string $app
 * @param string $add
 */
function appkey($uid, $app = false, $add = false) {
	global $db_hash;
	return substr(md5($uid . $db_hash . ($add ? $add : '') . ($app ? $app : '')), 8, 18);
}

//用户业务


/**
 * 加密密码
 *
 * @global array $pwServer
 * @global string $db_hash
 * @param string $pwd 密码
 * @return string
 */
function PwdCode($pwd) {
	return md5($GLOBALS['pwServer']['HTTP_USER_AGENT'] . $pwd . $GLOBALS['db_hash']);
}

/**
 * 检查cookie是否过期
 *
 * @global int $timestamp
 * @param array $cookieData cookie数据
 * @param string $pwdCode 用户私有信息
 * @param string $cookieName cookie名
 * @param int $expire 过期秒数
 * @param bool $clearCookie 验证错误是否清除cookie
 * @param bool $refreshCookie 是否刷新cookie
 * @return bool
 */
function SafeCheck($cookieData, $pwdCode, $cookieName = 'AdminUser', $expire = 1800, $clearCookie = true , $refreshCookie = true) {
	global $timestamp, $db_cloudgdcode, $keepCloudCaptchaCode,$db_hash;
	if (strtolower($cookieName) == 'cknum' && $db_cloudgdcode) {
		$cloudCaptchaService = L::loadClass('cloudcaptcha', 'utility/captcha');
		list($sessionid, $cloudckfailed) = array(getCookie('cloudcksessionid'), getCookie('cloudckfailed'));
		$cloudckfailed && Cookie('cloudckfailed', '', 0);
		$delflag = ($refreshCookie && !$keepCloudCaptchaCode) ? null : 0;
		if (!$cloudckfailed) return $cloudCaptchaService->checkCode($sessionid, $pwdCode, $delflag);
	}
	if($timestamp - $cookieData[0] > $expire) {
		Cookie($cookieName, '', 0);
		return false;
	} elseif ($cookieData[2] != md5($pwdCode . $cookieData[0] .getHashSegment())) {
		$clearCookie && Cookie($cookieName, '', 0);
		return false;
	}
	if ($refreshCookie) {
		$cookieData[0] = $timestamp;
		$cookieData[2] = md5($pwdCode . $cookieData[0] .getHashSegment());
		Cookie($cookieName, StrCode(implode("\t", $cookieData)));
	}
	return true;
}

/**
 * 检查是否在线
 *
 * @global int $db_onlinetime
 * @global int $timestamp
 * @param int $time 时间戳
 * @return bool
 */
function checkOnline($time) {
	global $db_onlinetime, $timestamp;
	if ($time + $db_onlinetime * 1.5 > $timestamp) {return true;}
	return false;
}

//sql安全、组装


/**
 * 针对SQL语句的变量进行反斜线过滤，并两边添加单引号
 *
 * @param mixed $var 过滤前变量
 * @param boolean $strip 数据是否经过stripslashes处理
 * @param boolean $isArray 变量是否为数组
 * @return mixed 过滤后的字符串
 */
function pwEscape($var, $strip = true, $isArray = false) {
	return S::sqlEscape($var, $strip, $isArray);
}

/**
 * 过滤数组每个元素值，用单引号括起，并用逗号连接
 *
 * @param array $array 源数组
 * @param boolean $strip 数据是否经过stripslashes处理
 * @return string 合并后字符串
 */
function pwImplode($array, $strip = true) {
	return S::sqlImplode($array, $strip);
}

/**
 * 构造单记录数据更新SQL语句
 * 格式: field='value',field='value'
 *
 * @param array $array 更新的数据,格式: array(field1=>'value1',field2=>'value2',field3=>'value3')
 * @param bool $strip 数据是否经过stripslashes处理
 * @return string SQL语句
 */
function pwSqlSingle($array, $strip = true) {
	return S::sqlSingle($array, $strip);
}

/**
 * 构造批量数据更新SQL语句
 * 格式: ('value1[1]','value1[2]','value1[3]'),('value2[1]','value2[2]','value2[3]')
 *
 * @param array $array 更新的数据,格式: array(array(value1[1],value1[2],value1[3]),array(value2[1],value2[2],value2[3]))
 * @param boolean $strip 数据是否经过stripslashes处理
 * @return string SQL语句
 */
function pwSqlMulti($array, $strip = true) {
	return S::sqlMulti($array, $strip);
}

/**
 * SQL查询中,构造LIMIT语句
 *
 * @param int $start 开始记录位置
 * @param int $num 读取记录数目
 * @return string SQL语句
 */
function pwLimit($start, $num = false) {
	return S::sqlLimit($start, $num);
}

//路径安全


/**
 * 过滤路径中的危险字符
 *
 * @param string $fileName 文件路径
 * @param bool $ifCheck 是否检查“..”
 * @return string
 */
function Pcv($fileName, $ifCheck = true) {
	return S::escapePath($fileName, $ifCheck);
}

/**
 * 过滤目录路径危险字符
 *
 * @param string $dir 目录路径
 * @return string
 */
function pwDirCv($dir) {
	return S::escapeDir($dir);
}

//数据安全


/**
 * 过滤数据，防xss攻击
 *
 * @param mixed $mixed
 * @param bool $isint 是否是数字
 * @param bool $istrim 是否需要整齐化
 * @return mixed
 */
function Char_cv($mixed, $isint = false, $istrim = false) {
	return S::escapeChar($mixed, $isint, $istrim);
}

/**
 * 检查变量
 *
 * @param mixed $var
 * @return mixed
 */
function CheckVar(&$var) {
	S::checkVar($var);
}

/**
 * 加转义
 *
 * @param mixed $array
 */
function Add_S(&$array) {
	S::slashes($array);
}

//语言包


/**
 * 获取指定语言包里的某一内容信息
 *
 * @param string $T 语言包文件名
 * @param string $I 指定语言信息
 * @param array $L 额外变量
 * @param bool $M 是否调用模式下的语言文件
 * @return string
 */
function getLangInfo($T, $I, $L = array(), $M = false) {
	static $lang;
	if (!isset($lang[$T])) {
		if ($M == false) {
			require S::escapePath(GetLang($T));
		} else {
			require S::escapePath(getModeLang($T));
		}
	}
	if (isset($lang[$T][$I])) {
		eval('$I="' . addcslashes($lang[$T][$I], '"') . '";');
	}
	return $I;
}

/**
 * 获取积分语言包信息
 *
 * @param string $T 语言包文件名
 * @param string $logtype 类型
 * @return string
 */
function GetCreditLang($T, $logtype) {
	static $lang;
	if (!isset($lang[$T])) {
		require S::escapePath(GetLang($T));
	}
	$pop = '';
	if (isset($lang[$T][$logtype])) {
		eval('$pop="' . addcslashes($lang[$T][$logtype], '"') . '";');
	}
	return $pop;
}

/**
 * 获取模式语言包信息
 *
 * @param string $lang 语言包文件名
 * @param string $EXT 语言包文件扩展名
 * @return string
 */
function getModeLang($lang, $EXT = 'php') {
	if (defined('M_P') && file_exists(M_P . "lang/lang_$lang.$EXT")) {
		return M_P . "lang/lang_$lang.$EXT";
	} else {
		return GetLang($lang);
	}
}

//模板处理

/**
 * 获取模式下的模板文件路径
 *
 * @global string $db_mode
 * @global string $db_tplpath
 * @param string $template 模板名
 * @param string $ext 扩展名
 * @return string
 */
function modeEot($template, $EXT = 'htm') {
	global $db_mode;
	if ($db_mode == 'area') {
		return areaEot($template, $EXT);
	} else {
		$srcTpl = M_P . "template/$template.$EXT";
		$tarTpl = D_P . "data/tplcache/" . $db_mode . '_' . $template . '.' . $EXT;
	}
	if (!file_exists($srcTpl)) {return false;}
	if (pwFilemtime($tarTpl) > pwFilemtime($srcTpl)) {
		return $tarTpl;
	} else {
		return modeTemplate($srcTpl, $tarTpl);
	}
}

function areaEot($template, $EXT = 'htm') {
	global $alias;
	$ifNeedParsePW = 0;
	$srcTpl = getAreaChannelTpl($template, $alias, $EXT);
	$tarTpl = D_P . "data/tplcache/area_" . $alias . '_' . $template . '.' . $EXT;
	if (!file_exists($srcTpl)) return false;
	$srcTplTime = pwFilemtime($srcTpl);
	if ($template == 'main') {
		$ifNeedParsePW = 1;
		$configFile = AREA_PATH . $alias . '/'.PW_PORTAL_CONFIG;
		$srcTplTime = max(pwFilemtime($configFile), $srcTplTime);
	}
	if (pwFilemtime($tarTpl) > $srcTplTime) {return $tarTpl;}
	if ($ifNeedParsePW) {return areaTemplate($alias, $srcTpl, $tarTpl);}
	return modeTemplate($srcTpl, $tarTpl);
}

function getAreaChannelTpl($template, $channel, $EXT = 'htm') {
	$srcTpl = S::escapePath(AREA_PATH . "$channel/$template.$EXT");
	if (!file_exists($srcTpl)) {
		
		$srcTpl = M_P . 'template/' . $template . '.' . $EXT;

		if (!file_exists($srcTpl)) {
			$srcTpl = R_P . 'template/wind/' . $template . '.' . $EXT;
			if (!file_exists($srcTpl)) {
				Showmsg('the template file is not exists');
			}
		}
	}
	return $srcTpl;
}
/**
 * 获取可视化模板文件
 * @param $sign
 */
function portalEot($sign) {
	$GLOBALS['__pwPortalEot'] = 1;
	$srcTpl = S::escapePath(PORTAL_PATH . $sign . '/'.PW_PORTAL_MAIN);
	$tarTpl = S::escapePath(D_P . "data/tplcache/portal_" . $sign . '.htm');

	$configFile = S::escapePath(PORTAL_PATH . $sign . '/'.PW_PORTAL_CONFIG);
	$srcTplTime = max(pwFilemtime($configFile), pwFilemtime($srcTpl));

	if (pwFilemtime($tarTpl) <= $srcTplTime) {
		$portalPageService = L::loadClass('portalpageservice', 'area');
		$portalPageService->updateInvokesByModuleConfig($sign);

		$file_str = readover($srcTpl);
		$parseTemplate = L::loadClass('parsetemplate', 'area');
		$file_str = $parseTemplate->execute('other', $sign, $file_str);

		pwCache::writeover($tarTpl, $file_str);
	}
	return $tarTpl;
}

/*
 * 输出可视化的内容
 */
function portalEcho($sign,$_viewer = '',$name='') {
	$GLOBALS['__pwPortalEot'] = 1;
	global $timestamp;
	extract(pwCache::getData(D_P.'data/bbscache/portal_config.php', false));
	extract(pwCache::getData(D_P.'data/bbscache/portalhtml_config.php', false));
	
	$staticPath = S::escapePath(PORTAL_PATH . $sign .'/index.html');
	$mainFile = S::escapePath(PORTAL_PATH . $sign . '/'.PW_PORTAL_MAIN);
	$configFile = S::escapePath(PORTAL_PATH . $sign . '/'.PW_PORTAL_CONFIG);

	//$staticFileMTime = pwFilemtime($staticPath);
	$staticFileMTime = isset($portalhtml_times[$sign]) ? $portalhtml_times[$sign] : 0;
	$tplFileMTime = max(pwFilemtime($mainFile),pwFilemtime($configFile));
	if (($tplFileMTime>$staticFileMTime || ($GLOBALS['db_portalstatictime'] && $staticFileMTime<$timestamp-$GLOBALS['db_portalstatictime']*60) || !filesize($staticPath) || $portal_staticstate[$sign])) {
		portalStatic($sign,$_viewer,$name);
	}
	$output .= pwCache::getData($staticPath,false,true);

	echo '-->'. $output . '<!--';
}
/**
 * 更新可视化页面的静态文件
 * @param $sign
 */
function portalStatic($sign,$_viewer = '',$name='') {
	$portalPageService = L::loadClass('portalpageservice', 'area');
	if (!$portalPageService->checkPortal($sign)) {
		if ($name) {
			$portalPageService->addPortalPage(array('sign'=>$sign,'title'=>$name));
		} else {
			Showmsg('函数portalEcho调用出错，请设置本函数的第三个参数，定义该调用页面的名称');
		}
	}
	$lockName = 'portal_'.$sign;
	if (!procLock($lockName)) return false;
	$staticPath = S::escapePath(PORTAL_PATH.$sign);
	if (!is_dir($staticPath)) return false;
	$staticPath = S::escapePath(PORTAL_PATH.$sign.'/index.html');
	$otherOutput = ob_get_contents();
	ob_clean();

	$invokeService = L::loadClass('invokeservice', 'area');
	$pageConfig = $invokeService->getEffectPageInvokePieces('other', $sign);
	$tplGetData = L::loadClass('tplgetdata', 'area');
	$tplGetData->init($pageConfig);
	require portalEot($sign);

	$temp = ob_get_contents();
	$temp = str_replace(array('<!--<!---->',"<!---->\r\n",'<!---->','<!-- -->',"\t\t\t"),'',$temp);
	//$success = pwCache::writeover($staticPath, $temp,'wb+');
	$success = pwCache::setData($staticPath, $temp,false,'wb+');
	procUnLock($lockName);
	if (!$success && !$GLOBALS ['db_distribute'] && !pwCache::writeover($staticPath, $temp)&& !is_writable($staticPath)) {	//写入二次尝试
		ob_end_clean();
		ObStart();
		Showmsg('请设置'.str_replace(R_P,'',$staticPath).'文件为可写，如果文件不存在，则新建一个空文件');
	}
	ob_clean();
	$portalPageService->setPortalStaticState($sign,0);
	updateCacheData();
	setPortalHtmlTime($sign);
	if ($otherOutput) echo $otherOutput;
}

function setPortalHtmlTime($sign) {
	global $timestamp;
	require_once(R_P.'admin/cache.php');
	extract(pwCache::getData(D_P.'data/bbscache/portalhtml_config.php', false));
	if (!$portalhtml_times) $portalhtml_times = array();
	$portalhtml_times[$sign] = $timestamp;
	setConfig('portalhtml_times', $portalhtml_times, null,true);
	updatecache_conf('portalhtml', true);
}

function modeTemplate($srcTpl, $tarTpl) {
	$file_str = readover($srcTpl);
	$file_str = tplParsePrint($file_str);
	pwCache::writeover($tarTpl, $file_str);
	return $tarTpl;
}

/**
 * 编译类似于<!--#$condition#-->的标签
 *
 */
function tplParsePrint($string) {
	$s = array('/<!--#\s*/', '/\s*#-->/', '/\s*print <<<EOT\s*EOT;\s*/', '/print <<<EOT\s*/', '/\s*EOT;/');
	$e = array("\r\nEOT;\r\n", "\r\nprint <<<EOT\r\n", "\r\n", "print <<<EOT\r\n", "\r\nEOT;");
	return preg_replace($s, $e, $string);
}

function areaTemplate($alias, $srcTpl, $tarTpl) {
	$portalPageService = L::loadClass('portalpageservice', 'area');
	$portalPageService->updateInvokesByModuleConfig($alias);

	$file_str = readover($srcTpl);

	$parseTemplate = L::loadClass('parsetemplate', 'area');
	$file_str = $parseTemplate->execute('channel', $alias, $file_str);

	pwCache::writeover($tarTpl, $file_str);
	return $tarTpl;
}

/**
 * validate page
 * @param int $page
 * @param int $pageCount
 * @return int $page
 */
function validatePage($page, $pageCount) {
	if (empty($page) || $page < 1) {
		$page = 1;
	} elseif ($page > $pageCount) {
		$page = $pageCount;
	}
	return $page;
}

//模板解析

/**
 * 获取模板数据
 *
 * @param string $invokeName
 * @param string $title
 * @param int $loopId
 * @return string
 */
function pwTplGetData($invokeName, $title) {
	$GLOBALS['__pwTplGetData'] = true;
	$tplgetdata = L::loadClass('tplgetdata', 'area');
	return $tplgetdata->getData($invokeName, $title);
}

/**
 * 更新模板缓存数据
 */
function updateCacheData() {
	if ($GLOBALS['__pwTplGetData']) {
		$pw_tplgetdata = L::loadClass('tplgetdata', 'area');
		if ($pw_tplgetdata->updates) {
			$pw_cachedata = L::loadDB('cachedata', 'area');
			$pw_cachedata->updates($pw_tplgetdata->updates);
		}
	}
}
//分页工具

function getHashSegment($s = 2){
	global $db_hash;
	$s = intval($s);
	$s > 3 && $s = 0;
	return substr(md5($db_hash), $s * 8,8);
}

/**
 * 生成分页html
 *
 * @param int $count 总记录数
 * @param int $page 当前页
 * @param int $numofpage 总页数
 * @param string $url
 * @param int $max 显示页数
 * @param string $ajaxCallBack
 * @return string
 */
function numofpage($count, $page, $numofpage, $url, $max = null, $ajaxCallBack = '') {
	list($count, $page, $numofpage, $max) = array(intval($count), intval($page), intval($numofpage), intval($max));
	if ($numofpage <= 1) return '';
	($max && $numofpage > $max) && $numofpage = $max;
	
	$ajaxurl = $ajaxCallBack ? " onclick=\"return $ajaxCallBack(this.href);\"" : '';
	list($url, $mao) = explode('#', $url);
	$mao && $mao = '#' . $mao;
	$pages = '<div class="pages">';
	$preArrow = $nextArrow = $firstPage = $lastPage = '';
	if ($numofpage > 7) {
		list($pre, $next) = array($page - 1, $page + 1);
		$page > 1 && $preArrow = "<a class=\"pages_pre\" href=\"{$url}page={$pre}$mao\"{$ajaxurl}>&#x4E0A;&#x4E00;&#x9875;</a>";
		$page < $numofpage && $nextArrow = "<a class=\"pages_next\" href=\"{$url}page={$next}$mao\"{$ajaxurl}>&#x4E0B;&#x4E00;&#x9875;</a>";		
	}
	$page != 1 && $firstPage = "<a href=\"{$url}page=1$mao\"{$ajaxurl}>" . (($numofpage > 7 && $page - 3 > 1) ? '1...</a>' : '1</a>');
	$page != $numofpage && $lastPage = "<a href=\"{$url}page={$numofpage}$mao\"{$ajaxurl}>" . (($numofpage > 7 && $page + 3 < $numofpage) ? "...$numofpage</a>" : "$numofpage</a>");
	
	list($tmpPages, $preFlag, $nextFlag) = array('', 0, 0);
	$leftStart = ($numofpage - $page >= 3) ? $page - 2 : $page - (5 - ($numofpage - $page));
	for ($i = $leftStart; $i < $page; $i++) {
		if ($i <= 1) continue;
		$tmpPages .= "<a href=\"{$url}page=$i$mao\"{$ajaxurl}>$i</a>";
		$preFlag++;
	}
	$tmpPages .= "<b>$page</b>";
	$nextFlag = 4 - $preFlag + (!$firstPage ? 1 : 0);
	if ($page < $numofpage) {
		for ($i = $page + 1; $i < $numofpage && $i <= $page + $nextFlag; $i++) {
			$tmpPages .= "<a href=\"{$url}page=$i$mao\"{$ajaxurl}>$i</a>";
		}
	}
	$pages .= $preArrow . $firstPage . $tmpPages . $lastPage . $nextArrow;
	$jsString = "var page=(value>$numofpage) ? $numofpage : value; " . ($ajaxurl ? "$ajaxCallBack('{$url}page='+page);" : " location='{$url}page='+page+'{$mao}';") . " return false;";
	$numofpage > 7 && $pages .= "<div class=\"fl\">&#x5230;&#x7B2C;</div><input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){var value = parseInt(this.value); $jsString}\"><div class=\"fl\">&#x9875;</div><button onclick=\"javascript:var value = parseInt(this.previousSibling.previousSibling.value); $jsString\">&#x786E;&#x8BA4;</button>";
	$pages .= '</div>';
	return $pages;
}

/**
 * 获取友好的时间信息
 *
 * @global int $timestamp
 * @global string $tdtime
 * @param int $time 时间戳
 * @param int $type 类型
 * @return array
 */
function getLastDate($time, $type = 1) {
	global $timestamp, $tdtime;
	static $timelang = false;
	if ($timelang == false) {
		$timelang = array('second' => getLangInfo('other', 'second'), 'yesterday' => getLangInfo('other', 'yesterday'),
			'hour' => getLangInfo('other', 'hour'), 'minute' => getLangInfo('other', 'minute'),
			'qiantian' => getLangInfo('other', 'qiantian'));
	}
	$decrease = $timestamp - $time;
	$thistime = PwStrtoTime(get_date($time, 'Y-m-d'));
	$thisyear = PwStrtoTime(get_date($time, 'Y'));
	$thistime_without_day = get_date($time, 'H:i');
	$yeartime = PwStrtoTime(get_date($timestamp, 'Y'));
	$result = get_date($time);
	if ($decrease <= 0) {
		if ($type == 1) {
			return array(get_date($time, 'Y-m-d'), $result);
		} else {
			return array(get_date($time, 'Y-m-d H:i'), $result);
		}
	}
	if ($thistime == $tdtime) {
		if ($type == 1) {
			if ($decrease <= 60) {return array($decrease . $timelang['second'], $result);}
			if ($decrease <= 3600) {
				return array(ceil($decrease / 60) . $timelang['minute'], $result);
			} else {
				return array(ceil($decrease / 3600) . $timelang['hour'], $result);
			}
		} else {
			return array(get_date($time, 'H:i'), $result);
		}
	} elseif ($thistime == $tdtime - 86400) {
		if ($type == 1) {
			return array($timelang['yesterday'] . " " . $thistime_without_day, $result);
		} else {
			return array(get_date($time, 'm-d H:i'), $result);
		}
	} elseif ($thistime == $tdtime - 172800) {
		if ($type == 1) {
			return array($timelang['qiantian'] . " " . $thistime_without_day, $result);
		} else {
			return array(get_date($time, 'm-d H:i'), $result);
		}
	} elseif ($thisyear == $yeartime) {
		if ($type == 1) {
			return array(get_date($time, 'm-d'), $result);
		} else {
			return array(get_date($time, 'm-d H:i'), $result);
		}
	} else {
		if ($type == 1) {
			return array(get_date($time, 'Y-m-d'), $result);
		} else {
			return array(get_date($time, 'Y-m-d H:i'), $result);
		}
	}
}

//缓存工厂

/**
 * 缓存实例化工厂
 *
 * @param string $datastore 缓存类型
 * @return PW_Memcache|PW_DBCache
 */
function getDatastore($datastore = null) {
	global $db_datastore;
	$datastore || $datastore = $db_datastore;
	switch (strtolower($datastore)) {
		case 'memcache' :
			$_cache = L::loadClass('Memcache', 'utility');
			break;
		case 'dbcache' :
			$_cache = L::loadClass('DBCache', 'utility');
			break;
		default :
			$_cache = L::loadClass('DBCache', 'utility');
			break;
	}
	return $_cache;
}

//广告业务

/**
 * 获取广告数据
 *
 * @global int $timestamp
 * @global string $db_advertdb
 * @global string $db_mode
 * @global array $_time
 * @param string $advKey 广告key
 * @param int $fid 版块id
 * @param int $lou 楼号
 * @param string $scr
 * @return array
 */
function pwAdvert($advKey, $fid = 0, $lou = -1, $scr = 0) {
	global $timestamp, $db_advertdb, $db_mode, $_time;
	if (empty($db_advertdb[$advKey])) return false;
	$hours = $_time['hours'] + 1;
	$fid || $fid = $GLOBALS['fid'];
	$scr || $scr = $GLOBALS['SCR'];
	$scr = strtolower($scr);
	$lou = (int) $lou;
	$tmpAdvert = $db_advertdb[$advKey];
	if ($db_advertdb['config'][$advKey] == 'rand') {
		shuffle($tmpAdvert);
	}
	$arrAdvert = array();
	$advert = '';
	foreach ($tmpAdvert as $key => $value) {
		if ($value['stime'] > $timestamp || $value['etime'] < $timestamp || ($value['dtime'] && strpos(",{$value['dtime']},", ",{$hours},") === false) || ($value['mode'] && strpos($value['mode'], $db_mode) === false) || ($value['page'] && (strpos($value['page'], ",$scr,") === false || ($scr == 'read' && $value['page'] == 'thread'))) || ($value['fid'] && $scr != 'index' && strpos(",{$value['fid']},", ",$fid,") === false) || ($value['lou'] && strpos(",{$value['lou']},", ",$lou,") === false)) {
			continue;
		}
		if ((!$value['ddate'] && !$value['dweek']) || ($value['ddate'] && strpos(",{$value['ddate']},", ",{$_time['day']},") !== false) || ($value['dweek'] && strpos(",{$value['dweek']},", ",{$_time['week']},") !== false)) {
			$arrAdvert[] = $value['code'];
			$advert .= is_array($value['code']) ? $value['code']['code'] : $value['code'];
			if ($db_advertdb['config'][$advKey] != 'all') break;
		}
	}
	return array($advert, $arrAdvert);
}

/**
 * 生成下拉选单html
 *
 * @param string $name
 * @param string $value 选中值
 * @param array $options 下拉选单选项数组
 * @param string $attrs
 * @param string $isempty 是否有空选项
 * @return string
 */
function formSelect($name, $value = null, $options = array(), $attrs = "", $isEmpty = "") {
	$html = '<select name="' . $name . '"';
	if ($name == "disabled") {
		$html .= ' disabled';
	}
	$html .= ' ' . $attrs . '>';
	if ($isEmpty != '') {
		$html .= '<option value="">' . $isEmpty . '</option>';
	}
	foreach ($options as $k => $v) {
		$html .= '<option value="' . $k . '"';
		if (null !== $value && $k == $value) {
			$html .= ' selected="selected"';
		}
		$html .= '>' . $v . '</option>';
	}
	$html .= '</select>';
	return $html;
}

/**
 * @param array $_define   默认seo配置
 * @param unknown_type $_values	对应 targets 的一组值
 * @param unknown_type $_targets
 * @return multitype:string
 */
function seoSettings($_define = array(), $_values = array(), $_default = array(), $_targets = array()) {
	global $db_bbsname, $webPageTitle, $metaDescription, $metaKeywords;

	if (empty($_targets)) $_targets = array('{wzmc}', '{bkmc}', '{flmc}', '{tzmc}', '{tmc}', '{wzgy}','{pdmc}');
	if (empty($_default)) $_default = array('title' => '{tzmc} | {bkmc} - {wzmc}', 'descp' => '{wzgy} | {wzmc}',
		'keywords' => '{flmc} , {tzmc} | {bkmc} - {wzmc}');

	if (!empty($_define)) {
		$cTitle = $_define['title'];
		$cDescription = $_define['metaDescription'];
		$cKeywords = $_define['metaKeywords'];
	}

	if (empty($_values[0])) $_values[0] = $db_bbsname;
	/* 过滤参数 */
	foreach ($_values as $key => $value) {
		$_values[$key] = empty($value) ? '' : trim(strip_tags($value));
	}

	/*设置默认值*/
	empty($cTitle) && $cTitle = $_default['title'];
	empty($cDescription) && $cDescription = $_default['descp'];
	empty($cKeywords) && $cKeywords = $_default['keywords'];

	/* 参数处理 */
	$webPageTitle = parseSeoTargets($cTitle, $_values, $_targets);
	$metaDescription = parseSeoTargets($cDescription, $_values, $_targets);
	$metaKeywords = parseSeoTargets($cKeywords, $_values, $_targets);

	return array($webPageTitle, $metaDescription, $metaKeywords);
}

/**
 * @param string $_page 当前页面信息
 * @param string $_definedSeo 自定义SEO配置信息
 * @param string $_fname 板块名称
 * @param string $_types 分类信息
 * @param string $_subject 帖子名称
 * @param string $_tags 标签
 * @param string $_summary 摘要
 */
function bbsSeoSettings($_page = 'index', $_definedSeo = array(), $_fname = '', $_types = '', $_subject = '', $_tags = '', $_summary = '') {
	global $db_bbsname, $db_seoset;
	/* 网站名称，板块名称，分类名称，帖子名称，标签名称，文章概要  */
	$_tags = substr($_tags, 0, strpos($_tags, "\t"));
	$_types = isset($_types) && is_array($_types) ? $_types['name'] : '';
	$_replace = array($db_bbsname, $_fname, $_types, $_subject, $_tags, $_summary);
	/*获取SEO配置信息  自定义->后台定义->默认*/
	empty($_definedSeo['title']) && $_definedSeo['title'] = $db_seoset['title'][$_page];
	empty($_definedSeo['metaDescription']) && $_definedSeo['metaDescription'] = $db_seoset['metaDescription'][$_page];
	empty($_definedSeo['metaKeywords']) && $_definedSeo['metaKeywords'] = $db_seoset['metaKeywords'][$_page];
	return seoSettings($_definedSeo, $_replace, $_default, $_targets);
}

/**
 * @param string $content
 * @param string $_replace
 * @param string $_targets
 * @return string
 */
function parseSeoTargets($content, $_replace, $_targets) {
	$content = str_replace($_targets, $_replace, $content);
	$content = trim(preg_replace(array('((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)'), array(
	',', '|', '', ''), $content), ' -,|');
	return $content;
}

/**
 * 判断用户是否有前台可视化管理权限
 */
function checkPortalRight() {
	global $db_portal_admins,$manager,$winduid,$windid;
	return S::inArray($windid,$manager) || ($winduid && in_array($winduid, $db_portal_admins));
}

function descriplog($message) {
	$message = str_replace(array("\n \n \n", "\n",'[b]','[/b]'),array('<br />','<br />','<b>','</b>'),$message);
	if (strpos($message,'[/URL]')!==false || strpos($message,'[/url]')!==false) {
		$message = preg_replace("/\[url=([^\[]+?)\](.*?)\[\/url\]/is","<a href=\"\\1\" target=\"_blank\">\\2</a>",$message);
	}
	return $message;
}

function parseHtmlUrlRewrite($html, $flag) {
	return $flag ? preg_replace("/\<a(\s*[^\>]+\s*)href\=([\"|\']?)((index|cate|thread|read|faq|rss)\.php\?[^\"\'>\s]+\s?)[\"|\']?/ies", "Htm_cv('\\3','<a\\1href=\"')", $html) : $html;
}

/**
 * url处理
 *
 * @param string $url
 * @param string $tag
 * @return string
 */
function Htm_cv($url, $tag) {
	return stripslashes($tag) . urlRewrite($url) . '"';
}

function urlRewrite($url) {
	global $db_htmifopen, $db_dir, $db_ext;
	if (!$db_htmifopen) return $url;
	$tmppos = strpos($url, '#');
	$add = $tmppos !== false ? substr($url, $tmppos) : '';
	$turl = str_replace(array('.php?', '=', '&amp;', '&', $add), array($db_dir, '-', '-', '-', ''), $url);
	$turl != $url && $turl .= $db_ext;
	return $turl . $add;
}

/**
 * 类似htmlspecialchars_decode函数，因为htmlspecialchars_decode只在PHP 5.1版本及以上才存在
 * @param $string
 */
function pwHtmlspecialchars_decode ($string,$decodeTags = true) {
	$string = str_replace('&amp;','&', $string);
	$string =  str_replace(array( '&quot;', '&#039;', '&nbsp;','&#160;'), array('"', "'", ' ',' '), $string);
	$decodeTags && $string = str_replace(array('&lt;', '&gt;','&#61;'),array( '<', '>','='),$string);
	return $string;
}

/*
 * 获取强制索引名称
 */
function getForceIndex($index) {
	$indexdb = array('idx_postdate' => 'idx_postdate', 'idx_digest' => 'idx_digest', 'idx_uid_categoryid_modifiedtime' => 'idx_uid_categoryid_modifiedtime', 'idx_tid' => 'idx_tid' , 'idx_fid_ifcheck_specialsort_lastpost'=>'idx_fid_ifcheck_specialsort_lastpost');
	return $indexdb[$index];
}

/**
 * 初始化数据库连接
 */
function PwNewDB() {
	if (!is_object($GLOBALS['db'])) {
		global $db, $database, $dbhost, $dbuser, $dbpw, $dbname, $PW, $charset, $pconnect;
		require_once S::escapePath(R_P . "require/db_$database.php");
		$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $PW, $charset, $pconnect);
	}
}

/**
 * 来自手机
 */
function checkFromWap() {
	static $fromMobile = array('nokia','sony','ericsson','motorola','samsung','java','philips','panasonic','ktouch','alcatel','lenovo','iphone','ipod','android','blackberry','meizu','netfront','symbian','ucweb','windowsce','palmsource','opera mini','opera mobi','cldc','midp','wap','mobile','benq', 'haier'); 
	$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
	foreach ($fromMobile as $v) {
		if (strrpos($userAgent,$v) !== false) return true;
	}
	return false;
}

//LOADER

class PW_BaseLoader {

	function _loadClass($className, $dir = '', $isGetInstance = true, $classPrefix = 'PW_') {
		static $classes = array();
		$dir = PW_BaseLoader::_formatDir($dir);

		$classToken = $isGetInstance ? $className : $dir . $className; //避免重名
		if (isset($classes[$classToken])) return $classes[$classToken];

		$classes[$classToken] = true; //默认值
		$fileDir = R_P . $dir . strtolower($className) . '.class.php';

		if (!$isGetInstance) return (@require_once S::escapePath($fileDir)); //未实例化的直接返回

		$class = $classPrefix . $className;
		if (!class_exists($class)) {

			# pack class start
			if($GLOBALS['db_classfile_compress']){
				$_directory = explode('/',$dir);
				if( isset($_directory[1]) && in_array( $_directory[1], array('framework','gather','forum','job','rate','site','user','utility') ) ){
					$fileDir = pwPack::classPath($fileDir,$class);
				}
			}
			# pack class end

			if (file_exists($fileDir)) require_once S::escapePath($fileDir);
			if (!class_exists($class)) { //再次验证是否存在class
				$GLOBALS['className'] = $class;
				Showmsg('load_class_error');
			}
		}
		$classes[$classToken] = &new $class(); //实例化
		return $classes[$classToken];
	}

	function _loadBaseDB() {
		if (!class_exists('BaseDB')) require_once (R_P . 'lib/base/basedb.php');
	}

	function _formatDir($dir) {
		$dir = trim($dir);
		if ($dir) $dir = trim($dir, "\\/") . '/';
		return $dir;
	}
}

/**
 * 加载类(包括通用类和通用配置文件的加载)
 */
class L extends PW_BaseLoader {

	/**
	 * 类文件的加载入口
	 *
	 * @param string $className 类的名称
	 * @param string $dir 目录：末尾不需要'/'
	 * @param boolean $isGetInstance 是否实例化
	 * @return mixed
	 */
	function loadClass($className, $dir = '', $isGetInstance = true) {
		return parent::_loadClass($className, 'lib/' . parent::_formatDir($dir), $isGetInstance);
	}

	/**
	 * dao文件加载入口
	 *
	 * @param string $dbName 数据库名称
	 * @param string $dir 目录
	 * @return mixed
	 */
	function loadDB($dbName, $dir = '') {
		parent::_loadBaseDB();
		return L::loadClass($dbName . 'DB', parent::_formatDir($dir) . 'db');
	}

	function config($var = null, $file = 'config', $dir = 'bbscache', $isStatic = true) {
		static $conf = array();
		$key = $dir . '_' . $file;
		if (!isset($conf[$key])) {
			if (file_exists(D_P . "data/$dir/{$file}.php")) {
				//* include S::escapePath(D_P . "data/$dir/{$file}.php");
				//* $arr = get_defined_vars();
				//* unset($arr['dir'], $arr['file'], $arr['var'], $arr['key'], $arr['conf'], $arr['isStatic']);
				$arr = pwCache::getData(S::escapePath(D_P . "data/$dir/{$file}.php"), false);
				if ($isStatic !== true) {return $var ? $arr[$var] : $arr;}
				$conf[$key] = $arr;
			} else {
				$conf[$key] = array();
			}
		}
		return $var ? $conf[$key][$var] : $conf[$key];
	}

	function reg($var = null) {
		return L::config($var, 'dbreg');
	}

	function style($var = null, $skinco = null, $ispath = false) {
		global $skin, $db_styledb, $db_defaultstyle;
		$skinco && isset($db_styledb[$skinco]) && $skin = $skinco;
		if ($skin && strpos($skin, '..') === false && file_exists(D_P . "data/style/$skin.php") && is_array($db_styledb[$skin]) && $db_styledb[$skin][1] == '1') {

		} elseif ($db_defaultstyle && strpos($db_defaultstyle, '..') === false && file_exists(D_P . "data/style/$db_defaultstyle.php")) {
			$skin = $db_defaultstyle;
		} else {
			$skin = 'wind';
		}
		return !$ispath ? L::config($var, $skin, 'style') : S::escapePath(D_P . 'data/style/' . $skin . '.php');
	}

	function forum($fid) {
		return L::config('foruminfo', 'fid_' . intval($fid), 'forums', false);
	}
}

class M {

	/*
	 * 发送单人/多人消息
	 */
	function sendMessage($userId, $usernames, $messageInfo, $shieldType = null, $typeName = null) {
		if ($shieldType) $usernames = M::_getUnShieldUsers($usernames, $shieldType);
		if (!$usernames) return false;

		$messageServer = L::loadClass("message", 'message');
		$typeName = ($typeName) ? $typeName : 'sms_message';
		$typeId = $messageServer->getConst($typeName);

		return $messageServer->sendMessage($userId, $usernames, $messageInfo, $typeId);
	}

	/*
	 * 发送通知 系统通知/团购通知/活动通知/应用通知
	 */
	function sendNotice($usernames, $messageInfo, $shieldType = 'notice_website', $typeName = null, $userId = '-1') {
		$usernames = M::_getUnShieldUsers($usernames, $shieldType);
		if (!$usernames) return false;

		$messageServer = L::loadClass("message", 'message');
		$typeName = ($typeName) ? $typeName : 'notice_system';

		$typeId = $messageServer->getConst($typeName);
		return $messageServer->sendNotice($userId, $usernames, $messageInfo, $typeId);
	}

	/*
	 * 发送请求 好友请求/群组请求/活动请求/应用请求
	 */
	function sendRequest($userId, $usernames, $messageInfo, $shieldType = 'notice_website', $typeName = null) {
		$usernames = M::_getUnShieldUsers($usernames, $shieldType);
		if (!$usernames) return false;

		$messageServer = L::loadClass("message", 'message');
		$typeName = ($typeName) ? $typeName : 'request_friend';
		$typeId = $messageServer->getConst($typeName);
		return $messageServer->sendRequest($userId, $usernames, $messageInfo, $typeId);
	}

	/*
	 * 发送群组消息
	 */
	function sendGroupMessage($userId, $groupId, $messageInfo, $userIds = array()) {
		$messageServer = L::loadClass("message", 'message');
		return $messageServer->sendGroupMessage($userId, $groupId, $messageInfo, '', $userIds);
	}

	function ifUnShieldThisType($user, $shieldType) {
		$messageServer = L::loadClass("message", 'message');
		return $messageServer->getMessageShield($user, $shieldType);
	}

	/*
	 * 获取没有屏蔽此类型消息的用户
	 */
	function _getUnShieldUsers($userNames, $shieldType) {
		if (!$shieldType) return $userNames;
		$messageServer = L::loadClass("message", 'message'); /* @var $messageServer PW_Message */
		$temp = array();
		foreach ($userNames as $user) {
			if (!$messageServer->getMessageShieldByUserName($user, $shieldType)) continue;
			$temp[] = $user;
		}
		return $temp;
	}
}

class template {

	var $bev;

	function template($bev = null) {
		$this->bev = $bev;
	}

	function printEot($template, $EXT = 'htm') {
		
		if (($filepath = $this->bev->getpath($template, $EXT)) !== false) {return S::escapePath($filepath);}
		exit('Can not find ' . $this->bev->getDefaultDir() . $template . '.' . $EXT . ' file');
	}
}
class Error {
	/**
	 * 添加一条错误信息
	 * @param $errorInfo	错误信息
	 */
	function addError($errorInfo) {
		$pwError = L::loadClass('errors','framework');
		$pwError->addError($errorInfo);
	}
	/**
	 * 添加一条提醒信息
	 * @param $logInfo
	 */
	function addLog($errorInfo) {
		$pwError = L::loadClass('errors','framework');
		$pwError->addLog($errorInfo);
	}
	/**
	 * 及时报错
	 * @param $error 错误信息
	 */
	function showError($error, $jumpurl = '') {
		$pwError = L::loadClass('errors','framework');
		$pwError->showError($error,$jumpurl);
	}
	/**
	 * 检查是否有错误信息，有的话及时报错
	 */
	function checkError($jumpurl = '') {
		$pwError = L::loadClass('errors','framework');
		$pwError->checkError($jumpurl);
	}
	/**
	 * 记录错误信息
	 */
	function writeLog() {
		$pwError = L::loadClass('errors','framework');
		$pwError->writeLog();
	}
}
/*
 * 结构化查询类库
 */
class pwQuery{
	/*
	 * 执行新增操作
	 */
	function insert($tableName, $col_names){
		$GLOBALS['db']->update(pwQuery::insertClause($tableName, $col_names));
		$insert_id =  $GLOBALS['db']->insert_id();
		$insert_id && Perf::gatherQuery ( 'insert', array($tableName), array_merge($col_names,array('insert_id'=>$insert_id)));
		return $insert_id;
	}
	/*
	 * 执行替换操作
	 */
	function replace($tableName, $col_names){
		$GLOBALS['db']->update(pwQuery::replaceClause($tableName, $col_names));
		return $GLOBALS['db']->affected_rows();
	}
	/*
	 * 执行更新操作
	 */
	function update($tableName, $where_statement = null, $where_conditions = null, $col_names, $expand = null) {
		$GLOBALS['db']->update(pwQuery::updateClause($tableName, $where_statement, $where_conditions, $col_names, $expand));
		return $GLOBALS['db']->affected_rows();
	}
	/*
	 * 执行删除操作
	 */
	function delete($tableName, $where_statement = null, $where_conditions = null, $expand = null) {
		$GLOBALS['db']->update(pwQuery::deleteClause($tableName, $where_statement, $where_conditions, $expand ));
		return $GLOBALS['db']->affected_rows();
	}
	/*
	 * 构造新增insert语句,仅返回数据新增语句,不执行数据库操作
	 */
	function insertClause($tableName, $col_names){
		$service = L::loadClass('querybuilder','utility');
		return $service->insertClause($tableName, $col_names);
	}
	/*
	 * 构造替换replace语句,仅返回数据替换语句,不执行数据库操作
	 */
	function replaceClause($tableName, $col_names){
		$service = L::loadClass('querybuilder','utility');
		return $service->replaceClause($tableName, $col_names);
	}
	/*
	 * 构造更新update语句,仅返回数据更新语句,不执行数据库操作
	 */
	function updateClause($tableName, $where_statement = null, $where_conditions = null, $col_names, $expand = null) {
		$service = L::loadClass('querybuilder','utility');
		return $service->updateClause($tableName, $where_statement, $where_conditions, $col_names, $expand);
	}
	/*
	 * 构造删除delete语句,仅返回数据删除语句,不执行数据库操作
	 */
	function deleteClause($tableName, $where_statement = null, $where_conditions = null, $expand = null) {
		$service = L::loadClass('querybuilder','utility');
		return $service->deleteClause($tableName, $where_statement, $where_conditions, $expand );
	}
	/*
	 * 构造查询语句,仅返回数据查询语句,不执行数据库操作
	 */
	function selectClause($tableName, $where_statement = null, $where_conditions = null, $expand = null) {
		$service = L::loadClass('querybuilder','utility');
		return $service->selectClause($tableName, $where_statement, $where_conditions, $expand);
	}
	/*
	 * 构造通用查询语句,仅返回数据查询语句,不执行数据库操作
	 */
	function buildClause($format, $parameters) {
		$service = L::loadClass('querybuilder','utility');
		return $service->buildClause($format, $parameters);
	}
}
/*
 * 全局聚合服务中心
 */
class Perf {
	/*
	 * 全局缓存聚合
	 */
	function gatherCache($cacheName){
		$gatherCache = L::loadClass ( 'gather', 'gather' );
		return $gatherCache->spreadCache ($cacheName);
	}
	/*
	 * 全局查询聚合
	 */
	function gatherQuery($operate, $tableNames, $fields, $expand = array()){
		$gatherQuery = L::loadClass('gather','gather');
		return $gatherQuery->spreadQuery($operate, $tableNames, $fields, $expand);
	}
	/*
	 * 全局信息聚合
	 */
	function gatherInfo($gatherName, $information, $defaultName = 'general'){
		$gatherInfo = L::loadClass ( 'gather', 'gather' );
		$gatherInfo->spreadInfo($gatherName, $information, $defaultName);
	}
	/*
	 * 检查是否开启或安装Memcache
	 */
	function checkMemcache(){
		static $isMemcache = null;
		if (! isset ( $isMemcache )) {
			$isMemcache = class_exists ( "Memcache" ) && strtolower ( $GLOBALS ['db_datastore'] ) == 'memcache';
		}
		return $isMemcache;
	}

	function getCacheService(){
		return L::loadClass('cacheservice','utility');
	}
}
/**
 * 全局通用读取缓存路径与写缓存数据服务
 */
class pwCache {
	/**
	 * 获取缓存路径函数
	 * @param string 	$filePath	文件名称
	 * @param boolean 	$isPack 	是否可选压缩文件
	 */
	function getPath($filePath, $isPack = false, $withCache = true) {
		if (! $withCache || !$isPack ) {
			return $filePath;
		}
		/**
		if( $GLOBALS['db_filecache_to_memcache'] && Perf::checkMemcache() && in_array ( SCR, array ('index', 'read', 'thread' ))){
			$_cacheService = perf::gatherCache('pw_filecache');
			return $_cacheService->getFileCache($filePath);
		}
		**/
		if ( $GLOBALS['db_cachefile_compress'] && in_array ( SCR, array ('index', 'read', 'thread' ) )) {
			return pwPack::cachePath ( $filePath );
		}
		return $filePath;
	}

	function readover($fileName, $method = 'rb'){
		return readover($fileName, $method);
	}

	function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true){
		return writeover($fileName, $data, $method, $ifLock, $ifCheckPath, $ifChmod);
	}

	function getData($filePath, $isRegister = true, $isReadOver= false ){
		$_service = L::loadClass('cachedistribute','utility');
		return $_service->getData($filePath,$isRegister,$isReadOver);
	}

	/**
	 * 写缓存通用函数
	 * @param string 		$filePath	文件名称
	 * @param string|array 	$data		数据
	 * @param boolean 		$isBuild	是否需要装组装
	 * @param string 		$method		读写模式
	 * @param boolean		$ifLock		是否锁文件
	 */
	function setData($filePath, $data, $isBuild = false, $method = 'rb+', $ifLock = true) {
		$_service = L::loadClass('cachedistribute','utility');
		return $_service->setData($filePath, $data, $isBuild , $method, $ifLock);
	}
	/**
	 * 删除文件缓存函数
	 * @param string 		$filePath	文件名称
	 */
	function deleteData($filePath) {
		$_service = L::loadClass('cachedistribute','utility');
		return $_service->deleteData($filePath);
	}
}
/*
 * 版本文件打包服务
 */
class pwPack {
	/*
	 * 通用缓存文件获取
	 */
	function cachePath($filePath) {
		if( !$GLOBALS['db_cachefile_compress'] || !in_array ( SCR, array ('index', 'read', 'thread' ) ) ){
			return $filePath;
		}
		$_packService = pwPack::getPackService ();
      	return $_packService->loadCachePath ( $filePath );
	}
	/*
	 * 通用类库文件获取
	 */
	function classPath($filePath, $className) {
		if( !$GLOBALS['db_classfile_compress'] || !in_array ( SCR, array ('index', 'read', 'thread' ) ) ){
			return $filePath;
		}
		static $_packClassFile = null;
		if (! isset ( $_packClassFile )) {
			$_packClassFile = D_P . 'data/package/pack.class.' . SCR . '.php';
			if (is_file ( $_packClassFile )) {
				(! class_exists ( 'BaseDB' )) && require_once (R_P . 'lib/base/basedb.php');
				require_once S::escapePath ( $_packClassFile );
			}
		}
		if ($_packClassFile && class_exists ( $className )) {
			return R_P . 'require/returns.php';
		}
		return $filePath;
	}
	/*
	 * 通用类库文件压缩
	 */
	function files() {
		if( !in_array ( SCR, array ('index', 'read', 'thread' ) ) ){
			return false;
		}
		$_packService = pwPack::getPackService ();
		if( $GLOBALS['db_cachefile_compress'] ){
			$_packService->packCacheFiles ();
		}
		return true;
	}
	/*
	 * 获取打包服务
	 */
	function getPackService() {
		static $packService = null;
		if (! isset ( $packService )) {
			require_once R_P.'require/packservice.php';
			$packService = new PW_packService ();
		}
		return $packService;
	}
}
/**
 * 
 * 论坛扩展机制
 *
 */
class pwHook{
	/**
	 * 添加不带返回值的的扩展
	 * pwHook::runHook('post',array('uid'=>11));
	 * @param string $hookName
	 * @param array $params
	 */
	function runHook($hookName,$params = array()) {
		if (!pwHook::checkHook($hookName)) return false;
		$pwHook = pwHook::_getHook($hookName);
		if ($params) $pwHook->setParams($params);
		$pwHook->runHook();
	}
	/**
	 * 添加一个带返回值的扩展
	 * pwHook::runFilter('filteruid',$winduid,array('uid'=>11));
	 * @param string $hookName
	 * @param unknown_type $result
	 * @param unknown_type $params
	 */
	function runFilter($hookName,$result,$params = array()) {
		if (!pwHook::checkHook($hookName)) return $result;
		$pwHook = pwHook::_getHook($hookName);
		if ($params) $pwHook->setParams($params);
		return $pwHook->runFilter($result);
	}
	/**
	 * 判断该hook是否开启
	 * @param string $name
	 * @return bool
	 */
	function checkHook($name) {
		global $db_hookset;
		return isset($db_hookset[$name]) || in_array($name,pwHook::getSystemHooks());
	}
	
	function getSystemHooks() {
		return array(
			'after_login',
			'after_post',
			'after_reply',
		);
	}
	
	function _getHook($name) {
		static $hooks = array();
		if (isset($hooks[$name])) return $hooks[$name];
		L::loadClass('hook','hook',false);
		$hooks[$name] = new PW_Hook($name);
		return $hooks[$name];
	}
}

class CloudWind{
	
	function yunPostDefend($authorid, $author, $groupid, $id, $title, $content, $type = 'thread', $expand = array()) {
		require_once R_P . 'lib/cloudwind/yundefend.php';
		return yunPostDefend( $authorid, $author, $groupid, $id, $title, $content, $type, $expand );
	}
	
	function yunUserDefend($operate, $uid, $username, $accesstime, $viewtime, $status = 0, $reason = "", $content = "", $behavior = array(), $expand = array()) {
		require_once R_P . 'lib/cloudwind/yundefend.php';
		YunUserDefend ( $operate, $uid, $username, $accesstime, $viewtime, $status, $reason, $content, $behavior, $expand  );
		Cookie("ci",'');
		return true;
	}
	function yunSetCookie($name,$tid='',$fid='') {
		global $timestamp;
		if (!$name) return false;
		Cookie("ci",$name."\t".$timestamp."\t".$tid."\t".$fid);
	}
	
	function checkSync(){
		if(!isset($GLOBALS['db_yunsearch_search']) && !isset($GLOBALS['db_yundefend_shield'])){
			return false;
		}
		if($GLOBALS ['db_yun_model'] ['search_model'] != 100 || $GLOBALS ['db_yun_model'] ['userdefend_model'] == 100){
			return true;
		}
		if($GLOBALS ['db_yun_model'] ['postdefend_model'] == 100 && SCR == 'read'){
			return true;
		}
		return false;
	}

	function getUserInfo() {
		$getCookie = GetCookie('ci');
		if (!$getCookie) return array();
		return explode("\t",$getCookie);
	}

	function sendUserInfo($cloud_information) {
		if (!S::isArray($cloud_information)) return false;
		list($operate,$leaveTime,$tid,$fid) = $cloud_information ? $cloud_information : array('','');

		if (!in_array($operate, array('index','read', 'thread')) || $operate == SCR) return false;
		$user = CloudWind::getOnlineUserInfo();
		$viewTime = $GLOBALS['timestamp'] - $leaveTime ? $GLOBALS['timestamp'] - $leaveTime : '';
		CloudWind::yunUserDefend('view'.$operate, $user['uid'], $user['username'], $leaveTime, $viewTime, 101,'','','',array('uniqueid'=>$tid.'-'.$fid));
		return true;
	}
	
	function getOnlineUserInfo() {
		if (!$GLOBALS['winduid'] && !GetCookie('cloudClientUid')) {
			Cookie("cloudClientUid",CloudWind::getNotLoginUid());
		}
		$cloudClientUid = GetCookie('cloudClientUid') ? GetCookie('cloudClientUid') : CloudWind::getNotLoginUid();
		return array(
			'uid'		=>	$GLOBALS['winduid'] ? $GLOBALS['winduid'] : $cloudClientUid,
			'username'	=> 	$GLOBALS['windid'] ? $GLOBALS['windid'] : '游客'
		);
	}
	
	function getNotLoginUid() {
		global $loginhash;
		$length = strlen($loginhash);
		for ($i=0;$i<$length;$i++) {
			if ($i%2 == 0) $odd .= ord($loginhash[$i]);
			if ($i%2 != 0) $even .= ord($loginhash[$i]);
		}
		return substrs("$odd+$even" , 8, 'N');
	}
	
	
}
?>