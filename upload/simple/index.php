<?php
define('SIMPLEDIR', getindexdir(__FILE__));
define('SIMPLE', 1);
require_once (SIMPLEDIR . '/global.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
pwCache::getData(D_P . 'data/bbscache/forum_cache.php');

/*
* phpwind 是一个免费开源软件，您不需要支付任何费用就可以无限制使用。
* 如果您觉得这个软件有价值，请不要修改此处设置，我们对您表示衷心的感谢。
* 我们相信，有众多用户的支持，phpwind 能做得更好。
* 填1关闭 phpwind 的赞助广告
*/
$db_adclose = 0;

$mod = 0; //填2为第二种模式 3为第3种模式，根据您服务器的支持程度设置
switch ($mod) {
	case 0 :
		$DIR = 'simple/?';
		break;
	case 1 :
		$DIR = 'simple/index.php?';
		break;
	case 2 :
		$DIR = 'simple/index.php/';
		break;
	case 3 :
		$DIR = 'simple/';
		break; //RewriteRule ^(.*)/simple/([a-z0-9\_]+\.html)$ $1/simple/index.php?$2
}

extract(L::style());
$yeyestyle == 'no' ? $i_table = "bgcolor=$tablecolor" : $i_table = 'class=i_table';
if (!is_array($db_union)) {
	$db_union = explode("\t", stripslashes($db_union));
}
$R_URL = substr(($cutchar = strrchr($REQUEST_URI, '?')) ? substr($cutchar, 1) : substr(strrchr($REQUEST_URI, '/'), 1), 0, -5);
if ($R_URL) {
	$R_URL_A = explode('_', $R_URL);
	$prog = substr($R_URL_A[0], 0, 1);
	$id = (int) substr($R_URL_A[0], 1);
	$page = (int) $R_URL_A[1];
} else {
	$prog = '';
}

//SEO setting
bbsSeoSettings('index');

switch ($prog) {
	case 'f' :
		$fid = & $id;
		include_once (R_P . 'simple/mod_thread.php');
		break;
	case 't' :
		$tid = & $id;
		include_once (R_P . 'simple/mod_read.php');
		break;
	default :
		include_once (R_P . 'simple/mod_index.php');
}
Update_ol();
if ($db) {
	$qn = $db->query_num;
}
$db_obstart ? $ft_gzip = "Gzip enabled" : $ft_gzip = "Gzip disabled";
if ($db_footertime == 1) {
	$totaltime = number_format((pwMicrotime() - $P_S_T), 6);
	$wind_spend = "Time $totaltime second(s),query:$qn";
}
include PrintEot('simple_footer');
pwOutPut();
/*
$ceversion = defined('CE') ? 1 : 0;
$output = str_replace(array('<!--<!---->', "<!---->\r\n", '<!---->'), '', ob_get_contents());
if ($db_htmifopen) {
	$output = preg_replace("/\<a(\s*[^\>]+\s*)href\=([\"|\']?)((index|cate|thread|read|faq|rss)\.php\?[^\"\'>\s]+\s?)[\"|\']?/ies", "Htm_cv('\\3','<a\\1href=\"')", $output);
}
$output .= "<script type=\"text/javascript\" src=\"http://init.phpwind.net/init.php?sitehash=$db_sitehash&v=$wind_version&c=$ceversion\"></script>";
ob_end_clean();
ObStart();
echo $output;
flush();
exit();
*/
function PageDiv($count, $page, $numofpage, $url, $max = null) {
	global $tablecolor, $db_bbsurl;
	$total = $numofpage;
	if (!empty($max)) {
		$max = (int) $max;
		$numofpage > $max && $numofpage = $max;
	}
	if ($numofpage <= 1) {
		return null;
	} else {
		$pages = "<a href=\"{$url}.html\">&lt;&lt; </a>";
		$flag = 0;
		for ($i = $page - 3; $i <= $page - 1; $i++) {
			if ($i == 1)
				$pages .= " <a href={$url}.html>&nbsp;$i&nbsp;</a>";
			elseif ($i > 1)
				$pages .= " <a href={$url}_$i.html>&nbsp;$i&nbsp;</a>";
		}
		$pages .= "&nbsp;&nbsp;<b>$page</b>&nbsp;";
		if ($page < $numofpage) {
			for ($i = $page + 1; $i <= $numofpage; $i++) {
				$pages .= " <a href={$url}_$i.html>&nbsp;$i&nbsp;</a>";
				$flag++;
				if ($flag == 4)
					break;
			}
		}
		$pages .= " <input type='text' size='2' style='height: 16px; border:1px solid $tablecolor' onkeydown=\"javascript: if((window.event||event).keyCode==13) window.location='$db_bbsurl/{$url}_'+this.value+'.html';\"> <a href=\"{$url}_$numofpage.html\"> &gt;&gt;</a> &nbsp;Pages: ( $total total )";
		return $pages;
	}
}

function getindexdir($path = null) {
	if (!empty($path)) {
		if (strpos($path, '\\') !== false) {
			return substr($path, 0, strrpos($path, '\\') - 7);
		} elseif (strpos($path, '/') !== false) {
			return substr($path, 0, strrpos($path, '/') - 7);
		}
	}
	return './..';
}
?>