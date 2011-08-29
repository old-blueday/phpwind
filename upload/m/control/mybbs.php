<?php
!defined('W_P') && exit('Forbidden');
InitGP ( array ('uid', 'username', 'action', 'fr', 'tid' ) );
! $winduid && wap_msg ( 'not_login' );
$returnUrl = "index.php";

wap_header ();
require_once PrintWAP ( 'mybbs' );
wap_footer ();
?>
