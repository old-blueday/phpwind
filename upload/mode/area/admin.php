<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp('p',null,'1');

if ($p=='setpage') {
	include(M_P.'/admin/setpage.php');
} elseif ($p=='forumtype') {
	include(M_P.'/admin/forumtype.php');
} else {
	include(M_P.'/admin/forumtype.php');
}
?>