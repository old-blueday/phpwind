<?php
!function_exists('readover') && exit('Forbidden');
!defined('USED_HEAD') && define('USED_HEAD', 1);
require (L::style('', $skinco, true));
/*app list*/
if ($db_siteappkey) {
	$app_array = array();
	$appclient = L::loadClass('appclient');
	$db_appsdb = $appclient->getApplist();
}
/*app list*/

list($_Navbar,$_LoginInfo) = pwNavBar();
foreach($_Navbar['main'] as $key => $value) {
	if($value['subs']){
		$ifHaveSubs=true;
		break;
	}
}
list(,$showq) = explode("\t", $db_qcheck);
if ($winduid) $db_menuinit .= ",'td_u' : 'menu_u'";
if ($db_menu) $db_menuinit .= ",'td_sort' : 'menu_sort'";
if (!is_array($db_union)) {
	$db_union = $db_union ? explode("\t",stripslashes($db_union)) : array();
}
$db_union[0] && $db_union[0] = unserialize($db_union[0]);
if (isset($db_navinfo['KEYhack']) && !empty($db_union[0])) {
	$db_navinfo['KEYhack']['child']['app_union'] = '<a href="hack.php?H_name='.$db_union[0][1].'" >'.$db_union[0][0].'</a>';
}
empty($db_navinfo) && $db_navinfo = array();
foreach ($db_navinfo as $key => $value) {
	if (isset($value['child']) || $key == 'KEYapp') {
		$db_menuinit .= ",'td_".$key."' : 'menu_".$key."'";
	}
}
if (!in_array(SCR,array('register','login'))) {
	$shortcutforum = pwGetShortcut();
}

$msgsound = $head_pop = '';
if ($groupid == 'guest' && $db_regpopup == '1') {
	$head_pop = 'head_pop';
} elseif ($winddb['newpm']>0 && $db_msgsound && $secondurl!='message.php' && $_G['maxmsg']>=0 && getstatus($winddb['userstatus'], PW_USERSTATUS_NOTICEVPICE)) {
	$msgsound = "<div style='overflow:hidden;width:0;float:left'><embed src='$imgpath/msg/msg.wav' width='0' height='0' AutoStart='true' type='application/x-mplayer2'></embed></div>";
}

$db_skindb = array();
$db_skinimagedb=array();
empty($db_styledb) && $db_styledb['wind'] = array('0' => 'wind','1' => '1','2' =>'wind');
if(is_array($db_styledb)){
	foreach ($db_styledb as $key => $value) {
		$cname = $value[0] ? $value[0] : $key;
		$tname = $value[2] ? $value[2] : $key;
		$value[1] === '1' && $db_skindb[$key] = array($cname,$tname);
	}
}
$skincount = count($db_skindb);
if ($skincount > 1) {
	$db_menuinit .= ",'td_skin' : 'menu_skin'";
}

$s_url = $pwServer['PHP_SELF'].'?';
foreach ($_GET as $key => $value) {
	$key != 'skinco' && $value && !S::isArray($value) && $s_url .= "$key=".rawurlencode($value).'&';
}
($_GET) && $s_url =  S::escapeChar($s_url);

if ("wind" != $tplpath && file_exists(D_P.'data/style/'.$tplpath.'_css.htm')) {
	$css_path = D_P.'data/style/'.$tplpath.'_css.htm';
} else{
	$css_path = D_P.'data/style/wind_css.htm';
}

$jobnum = ($db_job_isopen && $winddb['jobnum']>0) ? "(".$winddb['jobnum'].")" : "";

require PrintEot('header');
unset($css_path,$s_url,$pwModeCss);
?>