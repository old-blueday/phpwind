<?php
!defined('P_W') && exit('Forbidden');

require_once(R_P . 'apps/groups/lib/colonys.class.php');
@include_once(D_P.'data/bbscache/o_config.php');
$colonyServer = new PW_Colony();

//随机群组
$randColonys = $colonyServer->getRandColonys(12);

//综合积分排行
$creditColonys = $colonyServer->getRankByColonyCredit(20);

//群组总数
$colonyNums = $colonyServer->getColonyNum();

//最新加入成员
$newmembers = $colonyServer->getNewMembers(12);
?>