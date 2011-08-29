<?php
!defined('M_P') && exit('Forbidden');
!defined('USED_HEAD') && define('USED_HEAD', 1);
define('F_M',true);

extract(L::style('',$skinco));
//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
pwCache::getData(D_P.'data/bbscache/area_config.php');

include_once (M_P.'require/core.php');
$ifEditAdmin = 0;
/*SEO*/
//include_once (D_P.'data/seo.php');
$seo_key = substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.'));
$seo_title = $site_seo[$seo_key]['title'];
$seo_keywords = $site_seo[$seo_key]['keywords'];
$seo_description = $site_seo[$seo_key]['description'];
/*SEO*/


switch ($SCR) {
	case 'index' :
		$ifEditAdmin= checkEditAdmin($windid,'index');
		$cateid = 'index';

		$db_tplstyle= $area_indextpl ? $area_indextpl : 'default';
		$csspath	= $area_indexcss ? $area_indexcss : 'default';
		$db_tplpath = $db_mode.'_'.$db_tplstyle.'_';
		break;
	case 'thread' :
	case 'read' :
		$SCR = 'm_home';
		$db_tplstyle= 'default';
		$csspath	= 'default';
		$db_tplpath = $db_mode.'_'.$db_tplstyle.'_';
		break;
	case 'cate' :
		$ifEditAdmin	= checkEditAdmin($windid,$fid);
		$cateid = $fid;

		if (isset($area_cateinfo[$fid]['tpl'])) {
			$db_tplstyle= $area_cateinfo[$fid]['tpl'];
			$db_tplpath = $db_mode.'_'.$db_tplstyle.'_'.$fid.'_';
		} else {
			$db_tplstyle= $area_catetpl ? $area_catetpl : 'default';
			$db_tplpath = $db_mode.'_'.$db_tplstyle.'_';
		}
		$csspath	= $area_cateinfo[$fid]['css'] ? $area_cateinfo[$fid]['css'] : 'default';
		break;
	//{push_page
	case 'push' :
		//添加前台管理
		$ifEditAdmin= checkEditAdmin($windid,'push');
		$cateid = 'push';

		if (isset($area_cateinfo[$fid]['tpl'])) {
			$db_tplstyle= $area_cateinfo[$fid]['tpl'];
			$db_tplpath = $db_mode.'_'.$db_tplstyle.'_'.$fid.'_';
		} else {
			$db_tplstyle= $area_catetpl ? $area_catetpl : 'default';
			$db_tplpath = $db_mode.'_'.$db_tplstyle.'_';
		}
		$csspath	= $area_cateinfo[$fid]['css'] ? $area_cateinfo[$fid]['css'] : 'default';
		break;
	//}
	default :
		if($SCR=='bbs'){
			$cateid = 'bbs';
			$db_tplstyle= 'index';
			$csspath	= 'default';
			$ifEditAdmin= checkEditAdmin($windid,'bbs');
		} elseif ($SCR=='home') {
			$cateid = 'home';
		} elseif ($SCR=='groups') {
			$cateid = 'groups';
		}
}
//print_r(array($db_tplstyle,$db_tplpath,$csspath));
$db_menuinit .= ",'td_userinfomore' : 'menu_userinfomore'";
//头部统一为论坛头部添加
$db_menuinit .= ",'td_u' : 'menu_u'";
$db_menuinit .= ",'td_home' : 'menu_home'";

if ($db_menu) $db_menuinit .= ",'td_sort' : 'menu_sort'";
//头部统一为论坛头部结束

$pwModeImg = "mode/$db_mode/images/";

list($_Navbar,$_LoginInfo) = pwNavBar();

if(defined('SCR') && SCR == 'push') {
	require PrintEot('header_push');
} else {
	require_once PrintEot('header');
}

unset($pwModeCss);
?>