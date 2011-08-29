<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
S::gp(array('mid'));
Showmsg('msg_error');