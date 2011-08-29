<?php
!defined('W_P') && exit('Forbidden');
! $winduid && (wap_msg ( 'not_login' ) && exit ());
$returnUrl = "index.php";
$pwServer ['HTTP_ACCEPT_LANGUAGE'] = GetServer ( 'HTTP_ACCEPT_LANGUAGE' );
wap_header ();
require_once PrintWAP ( 'myphone' );
wap_footer ();
?>
