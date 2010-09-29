<?php
!defined('P_W') && exit('Forbidden');

extract(L::style());

$openbarstyle = 'style="display:none"';
$closebarstyle = '';

if ($db_appifopen) {
	$appshortcut = trim($winddb['appshortcut'], ',');
	if (!empty($appshortcut) && $db_siteappkey) {
		$appshortcut = explode(',', $appshortcut);
		$bottom_appshortcut = array();
		$appclient = L::loadClass('appclient');
		$bottom_appshortcut = $appclient->userApplist($winduid, $appshortcut, 1);
	}
}
echo '<!--';
require_once PrintEot('bottom');
echo '-->';
ajax_footer();
