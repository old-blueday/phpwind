<?php
!defined('W_P') && exit('Forbidden');
require_once (R_P . 'require/checkpass.php');
Loginout ();
wap_msg ( 'wap_quit', 'index.php' );
?>
