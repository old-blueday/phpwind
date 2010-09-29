<?php
!function_exists('readover') && exit('Forbidden');

InitGP(array('uid','username'));
ObHeader('u.php?'.($username ? 'username='.$username : 'uid='.$uid));

?>