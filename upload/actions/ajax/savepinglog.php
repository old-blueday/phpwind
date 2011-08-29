<?php
!defined('P_W') && exit('Forbidden');

InitGP(array('id'), null, 2);
InitGP(array('record'));

$rt = $db->get_one("SELECT tid,fid FROM pw_pinglog WHERE ifhide=0 AND id=" . S::sqlEscape($id));
if (empty($rt) || !$rt['fid']) {
	Showmsg('data_error');
}

L::loadClass('forum', 'forum', false);
$pwforum = new PwForum($rt['fid']);
$isGM = CkInArray($windid, $manager);
if (!$isGM && !pwRights($pwforum->isBM($windid), 'pingcp', $rt['fid'])) {
	Showmsg('mawhole_right');
}

//$db->update("UPDATE pw_pinglog SET record=" . S::sqlEscape($record) . " WHERE id=" . S::sqlEscape($id));
pwQuery::update('pw_pinglog', 'id=:id', array($id), array('record'=>$record));	

echo "success";
# memcache reflesh
if ($db_memcache) {
	//* $threads = L::loadClass('Threads', 'forum');
	//* $threads->delThreads($rt['tid']);
	Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$rt['tid']));
}
ajax_footer();