<?php
!function_exists('readover') && exit('Forbidden');
include_once(D_P.'data/bbscache/bg_config.php');

$groupid=='guest'  && Showmsg('not_login');
!$bg_ifopen && Showmsg('blog_close');
if (!$action){
	include PrintHack('index');footer();
}elseif ($action=='activation'){
	ObHeader("$bg_blogurl/login.php");
}
?>