<?php
!defined('W_P') && exit('Forbidden');
InitGP ( array ('uid', 'username', 'action', 'fr', 'tid', 'fid' ) );
! $winduid && (wap_msg ( 'not_login' ) && exit ());
if ($tid) {
	$returnUrl = "index.php?a=read&tid=" . $tid;
} else if ($fid || $fr == 'f') {
	$returnUrl = "index.php?a=forum&fid=" . $fid;
} else {
	$returnUrl = "index.php?a=bbsinfo";
}
wap_header ();
require_once PrintWAP ( 'u' );
wap_footer ();
?>
