<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('keyword'));
L::loadClass('adminsearch', 'site', false);
$keyword = pwConvert(urldecode($keyword),$db_charset,'utf8');
$searchpurview = new AdminSearch($keyword);
$result = $searchpurview->search();
include PrintEot('search');exit;
?>