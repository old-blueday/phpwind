<?php
!defined('P_W') && exit('Forbidden');

echo $verifyhash . "\t" . GetVerify($onlineip . $winddb['regdate'] . $fid . $tid);
ajax_footer();
