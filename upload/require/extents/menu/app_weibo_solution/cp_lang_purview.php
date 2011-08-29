<?php
!defined('P_W') && exit('Forbidden');

unset($purview['sinaweibo']); //for compatible
$purview['platformweiboapp'] = array('帐号通',"$admin_file?adminjob=app&admintype=platformweiboapp");
?>