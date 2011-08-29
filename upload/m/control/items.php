<?php
!defined('W_P') && exit('Forbidden');
wap_header ();
$rg_config  = L::reg();
require_once PrintWAP ( 'items' );
wap_footer ();
?>
