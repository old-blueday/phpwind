<?php
!defined('P_W') && exit('Forbidden');

require_once(R_P . 'apps/groups/lib/colonys.class.php');
$colonyServer = new PW_Colony();
S::gp(array('limit'));
$limit = (int) $limit;
$number = $limit ? $limit : 12;
//随机群组
$randColonys = $colonyServer->getRandColonys($number);
require_once PrintEot('ajax');
ajax_footer();
?>