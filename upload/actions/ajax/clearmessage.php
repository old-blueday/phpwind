<?php
!defined('P_W') && exit('Forbidden');

$userService = L::loadClass('userservice','user');
$userService->clearUserMessage($winduid);
echo "success\t";
ajax_footer();
