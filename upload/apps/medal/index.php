<?php
!defined('A_P') && exit('Forbidden');
if (!$db_md_ifopen)  Showmsg('勋章功能未开启'); 
/* 勋章前台显示 */
S::gp(array('a')); 
!$winduid && Showmsg('not_login');
(!$a || !in_array($a, array('apply', 'my', 'all', 'behavior'))) && $a = 'all';
$basename =  'apps.php?q=' . $q;
$current[$a] = 'class="current"'; 
$typeArr = array('系统发放', '自动发放', '手动发放');
if ($a == 'my' || $a == 'all') {
	require_once S::escapePath($appEntryBasePath . 'action/my.php'); //我的勋章
} elseif ($a == 'apply') {
	require_once S::escapePath($appEntryBasePath . 'action/apply.php'); //我的勋章
} elseif ($a == 'behavior') {
	require_once S::escapePath($appEntryBasePath . 'action/behavior.php'); //用户行为AJAX提交
}    


 