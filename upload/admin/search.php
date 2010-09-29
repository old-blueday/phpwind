<?php
!defined('P_W') && exit('Forbidden');
InitGP(array('keyword'));
L::loadClass('adminsearch', 'site', false);
$searchpurview = new AdminSearch($keyword);
$result = $searchpurview->search();
include PrintEot('search');exit;
?>