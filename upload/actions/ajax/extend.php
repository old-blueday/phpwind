<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('type'));
if ($type == 'pwcode') {
	$code = array();
	$query = $db->query("SELECT * FROM pw_windcode");
	while ($rt = $db->fetch_array($query)) {
		$rt['descrip'] = str_replace("\n", "|", $rt['descrip']);
		$code[] = $rt;
	}
} else {
	//* @include_once pwCache::getPath(D_P . 'data/bbscache/setform.php');
	pwCache::getData(D_P . 'data/bbscache/setform.php');
	S::gp(array('id'), 'GP', 2);
	$setform = array();
	if (isset($setformdb[$id])) {
		$setform = $setformdb[$id];
	}
}
require_once PrintEot('ajax');
ajax_footer();
