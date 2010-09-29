<?php
!defined('P_W') && exit('Forbidden');

InitGP(array('friendid','ftid'),'P',2);
!$ftid && $ftid = 0;
if (!$friendid) Showmsg('undefined_action');
$db->update("UPDATE pw_friends SET ftid=".pwEscape($ftid)." WHERE uid=".pwEscape($winduid)." AND friendid=".pwEscape($friendid));
$str = $db->get_value("SELECT name FROM pw_friendtype WHERE uid =".pwEscape($winduid) ." AND ftid=".pwEscape($ftid));
echo "success\t$str";
ajax_footer();