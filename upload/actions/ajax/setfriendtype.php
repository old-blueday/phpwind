<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('friendid','ftid'),'P',2);
!$ftid && $ftid = 0;
if (!$friendid) Showmsg('undefined_action');
$db->update("UPDATE pw_friends SET ftid=" . S::sqlEscape($ftid) . " WHERE uid=" . S::sqlEscape($winduid) . " AND friendid=" . S::sqlEscape($friendid));
$str = $db->get_value("SELECT name FROM pw_friendtype WHERE uid =" . S::sqlEscape($winduid) . " AND ftid=" . S::sqlEscape($ftid));
echo "success\t$str";
ajax_footer();