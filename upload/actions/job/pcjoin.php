<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('tid'), G, 2);

if (!$winduid){
	define(AJAX,1);
	Showmsg('not_login');
} 
