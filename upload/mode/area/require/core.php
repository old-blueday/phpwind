<?php
!defined('M_P') && exit('Forbidden');

function checkEditAdmin($name,$cid,$pushtype='') {
	if (isGM($name)) return true;
	if (!$name) return false;
	if ($pushtype=='show') {
		$area_editadmin	= L::config('show_editadmin','show_config');
	} else {
		$area_editadmin	= L::config('area_editadmin','area_config');
	}

	if (!$area_editadmin) return false;
	$area_editadmin = explode(',',$area_editadmin[$cid]);
	return in_array($name,$area_editadmin);
}

function areaLoadFrontView($action) {
	
	if (file_exists(M_P."template/front/$action.htm")) {
		return S::escapePath(M_P."template/front/$action.htm");
	}
	return false;
}


function aliasStatic($alias) {
	$file = S::escapePath(AREA_PATH.$alias.'/index.html');
	$output = cookTemplate();
	pwCache::writeover($file, $output);
	ob_clean();
}

function areaFooter() {
	global $db_advertdb;
	if (!defined('AREA_PAGE') && ($db_advertdb['Site.PopupNotice'] || $db_advertdb['Site.FloatLeft'] || $db_advertdb['Site.FloatRight'] || $db_advertdb['Site.FloatRand'])) {
		require PrintEot('advert');
	}
	$output = cookTemplate();
	echo ObContents($output);
	unset($output);
	N_flush();
}
function cookTemplate() {
	global $db_htmifopen;
	$output = ob_get_contents();
	$output = str_replace(array('<!--<!---->',"<!---->\r\n",'<!---->','<!-- -->',"\t\t\t"),'',$output);
	if ($db_htmifopen) {
		$output = preg_replace("/\<a(\s*[^\>]+\s*)href\=([\"|\']?)((index|cate|thread|read|faq|rss)\.php\?[^\"\'>\s]+\s?)[\"|\']?/ies", "Htm_cv('\\3','<a\\1href=\"')", $output);
	}
	updateCacheData();
	return $output;
}

//static function创建目录
function createFolder($path) {
	if (!is_dir($path)) {
		createFolder(dirname($path));
		@mkdir($path);
		@chmod($path,0777);
	}
}
?>