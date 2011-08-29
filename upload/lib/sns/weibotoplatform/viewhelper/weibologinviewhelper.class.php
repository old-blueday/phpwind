<?php
!defined('P_W') && exit('Forbidden');

function WeiboLoginViewHelper_WindowOpenScript($type, $windowHeight = 520, $windowWidth = 850) {
	global $db_bbsurl;
	return "window.open('{$db_bbsurl}/login.php?action=weibologin&type={$type}&from='+self.location.href, 'weiboLogin', 'height={$windowHeight}, width={$windowWidth}, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, status=no');";
}
