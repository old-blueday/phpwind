<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('num'), 'GP', 2);
Cookie("clearm_".$winduid,$num);

//$userService = L::loadClass('userservice','user');
//$userService->clearUserMessage($winduid);
echo "success\t";
ajax_footer();
