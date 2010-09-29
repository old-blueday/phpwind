<?php
!defined('P_W') && exit('Forbidden');

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($winduid);
$space = $newSpace->getInfo();
pwDelatt($space['banner'], $db_ifftp);
$newSpace->updateInfo(array('banner' => ''));

echo 'ok';
ajax_footer();

?>