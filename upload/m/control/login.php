<?php
!defined('W_P') && exit('Forbidden');
include_once (D_P . "data/bbscache/dbreg.php");
InitGP ( array ('lgt', 'pwuser', 'pwpwd', 'question', 'customquest', 'answer' ), 'P' );
if ($windid) {
	wap_msg ( 'login_have' );
} elseif ($pwuser && $pwpwd) {
	$safecv = $db_ifsafecv ? wap_quest ( $question, $customquest, $answer ) : '';
	wap_login ( $pwuser, md5 ( $pwpwd ), $safecv, $lgt );
}
$returnUrl = getReturnUrl();
wap_header ();
require_once PrintWAP ( 'login' );
wap_footer ();
?>
