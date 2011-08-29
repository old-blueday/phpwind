<?php
!defined('P_W') && exit('Forbidden');

!$winduid && Showmsg('not_login');
$isGM = S::inArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($winduid);
S::gp(array('skin'));
$pwSQL = array(
		'skin'		=> $skin
		);
set_time_limit(0);
$newSpace->updateInfo($pwSQL);
echo "ok";