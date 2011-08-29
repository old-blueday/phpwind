<?php
!defined('M_P') && exit('Forbidden');
S::gp(array('q'));
$cmsControllers = array(
	'download',
	'list',
	'post',
	'view',
);
!$m && $m = 'cms';
!$q && $q = 'list';

if (!S::inArray($q,$cmsControllers)) ObHeader("index.php?m=$m");

define("CMS_BASEURL", "index.php?m=$m&");
$cmsBaseUrl = CMS_BASEURL;
$baseUrl = "index.php?m=$m";
$basename = "index.php?m=$m&";
isset($o_navinfo['KEY'.$q]) && $webPageTitle = strip_tags($o_navinfo['KEY'.$q]['html']).' - '.$webPageTitle;
unset($tname);

require_once S::escapePath ( M_P . "m_{$q}.php" );
exit();