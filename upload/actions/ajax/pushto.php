<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'fid',
	'seltid'
));
//* @include_once pwCache::getPath(S::escapePath(D_P . 'data/bbscache/mode_push_config.php'));
pwCache::getData(S::escapePath(D_P . 'data/bbscache/mode_push_config.php'));
$pushs = array();
if ($groupid == '3' || $groupid == '4' || S::inArray($windid, $manager)) {
	$pushs = $PUSH;
} elseif ($groupid == '5') {
	foreach ($PUSH as $key => $value) {
		if (in_array($value['scr'], array(
			'thread',
			'cate'
		))) {
			$pushs[] = $value;
		}
	}
}
if (!$pushs) {
	Showmsg('no_aim_to_push');
}
require_once PrintEot('ajax');
ajax_footer();
