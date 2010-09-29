<?php
!defined('P_W') && exit('Forbidden');

require_once(R_P . 'apps/groups/lib/colonys.class.php');
$colonyServer = new PW_Colony();

//随机群组
$randColonys = $colonyServer->getRandColonys(12);
require_once PrintEot('ajax');
ajax_footer();
?>