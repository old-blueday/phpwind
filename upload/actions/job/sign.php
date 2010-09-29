<?php
!defined('P_W') && exit('Forbidden');

Cookie('newpic', $timestamp);
refreshto("$db_bfn", 'operate_success');
