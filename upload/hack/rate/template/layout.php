<?php
!defined('P_W') && exit('Forbidden');

include_once PrintEot ( 'left' );
print <<<EOT
-->
EOT;
require_once S::escapePath($filepath);
include_once PrintEot ( 'adminbottom' );
?>