<?php
!function_exists('adminmsg') && exit('Forbidden');

$cachetime = $timestamp-3600*24;
if (pwFilemtime(D_P.'data/bbscache/file_lock.txt')<$cachetime) {
	set_time_limit(600);
	require_once(R_P.'require/posthost.php');
	$wget = PostHost('http://nt.phpwind.net/src/pw7/union2.php',"url=".$pwServer['HTTP_HOST'].$pwServer['PHP_SELF']."&repair=$wind_repair&charset=$db_charset&ce=$ceversion");
	$wget = str_replace(array('<pwret>','</pwret>'),'',$wget);
	$wget = explode("\t<windtag>\t",$wget);
	$wget_a = explode("\t",$wget[0]);
	$wget_b = explode("\t",$wget[1]);
	$higholnum = $db->get_value('SELECT higholnum FROM pw_bbsinfo WHERE id=1');
	foreach ($wget_a as $key => $value) {
		$higholnum<(int)$wget_b[$key] && $wget_a[$key] = '';
	}
	$wget = implode("\t",$wget_a);
	if ($db_union != $wget) {
		setConfig('db_union', $wget);
		updatecache_c();
	}
	writeover(D_P.'data/bbscache/file_lock.txt','');

} elseif (pwFilemtime(D_P.'data/bbscache/file_lock1.txt')<$timestamp-3600) {//stat.

	set_time_limit(600);
	require_once(R_P.'require/posthost.php');
	$offset = 1024000;
	$lasttime = pwFilemtime(D_P.'data/bbscache/file_lock1.txt');
	$filename = D_P."data/bbscache/admin_record.php";
	$fp = @fopen($filename,"rb");
	if (!$fp) exit;
	flock($fp,LOCK_SH);
	$size = filesize($filename);
	$size > $offset ? fseek($fp,-$offset,SEEK_END) : $offset = $size;
	$logs = fread($fp,$offset);
	fclose($fp);

	$statadmin = '';$PHPWind_StatAdmin = array();
	require GetLang('purview');
	$pattern = "/adminjob=([a-z_]+)((&amp;admintype|&amp;hackset)=([a-z_]+))?\|.*\|([0-9]+)\|/i";
	preg_match_all($pattern,$logs,$match);
	foreach ($match[5] as $key=>$value) {
		if ($value < $lasttime) continue;
		$right = $match[1][$key] != 'hack' ? ($match[4][$key] ? $match[4][$key] : $match[1][$key]) : 'hack';
		if (!isset($purview[$right])) continue;
		if ($right == 'hack') $right .= '_'.$match[4][$key];
		$PHPWind_StatAdmin[$right] = $PHPWind_StatAdmin[$right] ? $PHPWind_StatAdmin[$right]+1 : 1;
	}
	$statadmin = serialize($PHPWind_StatAdmin);
	$statadmin = base64_encode($statadmin);
	$s_url = rawurlencode($pwServer['HTTP_HOST']);
	$rawbbsname = rawurlencode($db_bbsname);
	PostHost('http://nt.phpwind.net/pwstat.php',"bbsname=$rawbbsname&url=$s_url&type=admin&data=$statadmin",'POST');
	writeover(D_P.'data/bbscache/file_lock1.txt','');
} elseif (pwFilemtime(D_P.'data/bbscache/info.txt')<$cachetime) {
	require_once(R_P.'require/posthost.php');
	$wget = PostHost('http://nt.phpwind.net/src/pw7/info1.php',"charset=$db_charset&ce=$ceversion");
	$wget = str_replace(array('<pwret>','</pwret>'),'',$wget);
	writeover(D_P.'data/bbscache/info.txt',$wget);
} elseif (pwFilemtime(D_P."data/bbscache/myshow_default.php")<$cachetime) {
	require_once(R_P.'require/posthost.php');
	$url = "http://dm.phpwind.net/misc/custom/recommend_2.xml?$timestamp";
	$data = PostHost($url);
	if ($data && strpos($data,'<?xml')!==false) {
		$name   = array();
		$id     = array();
		$T      = '';
		$maxnum = 8;
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser,$data,$arr_vals);
		xml_parser_free($xml_parser);
		foreach ($arr_vals as $v) {
			if ($v['tag'] == 'ITEM' && $v['attributes']) {
				$name[] = $v['attributes']['NAME'];
			} elseif ($v['tag'] == 'CODE') {
				$id[] = $v['value'];
			}
		}
		if ($db_charset!='utf-8') {
			L::loadClass('Chinese', 'utility/lang', false);
			$chs = new Chinese('UTF-8',$db_charset);
			foreach ($name as $k=>$v) {
				$name[$k] = $chs->Convert($v);
			}
		}
		foreach ($id as $k=>$v) {
			$T .= $T ? ",$v : '$name[$k]'" : "$v : '$name[$k]'";
			if(!$maxnum--)break;
		}
		pwCache::setData(D_P."data/bbscache/myshow_default.php","<?php\r\n\t\$mDef = \"$T\";\r\n?>");
	}
}
exit();
?>