<?php
!defined('M_P') && exit('Forbidden');

//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
pwCache::getData(D_P.'data/bbscache/area_config.php');

if(!$area_channels) Showmsg('还未创建频道');
define('F_M',true);
if (!$alias) S::gp(array('alias'),'GP');

if (!$area_default_alias) {
	$currentAlias = current($area_channels);
	$area_default_alias = $currentAlias['alias'];
}

!$alias && $alias = $area_default_alias;

$chanelService = L::loadClass('channelService', 'area');
if (isset($area_channels[$alias])) {
	$channelInfo = $area_channels[$alias];
} else {
	$channelInfo = $chanelService->getChannelInfoByAlias($alias); 
}

!$channelInfo && Showmsg('频道不存在');

/*SEO*/
$_seo = array('title'=>$channelInfo['metatitle'],'metaDescription'=>$channelInfo['metadescrip'],'metaKeywords'=>$channelInfo['metakeywords']);
if(empty($channelInfo['metatitle'])) $_seo['title'] = $area_seoset['title']['index'];
if(empty($channelInfo['metadescrip'])) $_seo['metaDescription'] = $area_seoset['metaDescription']['index'];
if(empty($channelInfo['metakeywords'])) $_seo['metaKeywords'] = $area_seoset['metaKeywords']['index'];
$_replace = array($area_sitename, $_fname, $_types, $_subject, $_tags, $_summary);
$area_sitename = $area_sitename ? $area_sitename : $db_bbsname;
seoSettings($_seo,array($area_sitename,$channelInfo['name'],'','','','',$channelInfo['name']));
/*SEO*/

$channelImagePath = $db_htmdir . '/channel/'.$alias.'/images';
if (defined('HTML_CHANNEL')) {
	$htmlFile = S::escapePath(AREA_PATH.$alias.'/index.html');
	if (!file_exists($htmlFile)) Showmsg('channel_html_have_not_create');
	readfile($htmlFile);
	exit;
}
if (!defined('AREA_STATIC')) {
	$ifactive = 1;
}
$channelid = $channelInfo['id'];
$areaLevelService = L::loadClass('arealevel', 'area');
$ifEditAdmin = $areaLevelService->getAreaLevel($winduid,$channelInfo['id']);

//$ifEditAdmin= 1;

//logic_file
$logic_file_path = AREA_PATH.$alias."/";

extract(L::style('',$skinco));
//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
pwCache::getData(D_P.'data/bbscache/area_config.php');

include_once (M_P.'require/core.php');



$db_menuinit .= $db_menuinit ? ",'td_userinfomore' : 'menu_userinfomore'" : "'td_userinfomore' : 'menu_userinfomore'";
//头部统一为论坛头部添加
$db_menuinit .= ",'td_u' : 'menu_u'";
$db_menuinit .= ",'td_home' : 'menu_home'";

$db_menuinit .= ",'td_profile' : 'menu_profile'";
$db_menuinit .= ",'td_message' : 'menu_message'";

if ($db_menu) $db_menuinit .= ",'td_sort' : 'menu_sort'";
//头部统一为论坛头部结束

list($_Navbar,$_LoginInfo) = pwNavBar();

//数据调用初始化
$invokeService = L::loadClass('invokeservice', 'area');
$pageConfig = $invokeService->getEffectPageInvokePieces('channel',$alias);
$tplGetData = L::loadClass('tplgetdata', 'area');
$tplGetData->init($pageConfig);


require modeEot('main');
if (!defined('AREA_STATIC')) {
	areaFooter();
	exit;
}
?>