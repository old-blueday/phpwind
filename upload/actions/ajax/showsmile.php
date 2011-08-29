<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'subjectid',
	'page',
	'type'
));
$u = "http://dm.phpwind.net/misc";
$subjectid = (int) $subjectid;
(!is_numeric($page) || $page < 1) && $page = 1;
$s = '300.xml';
if ($type == 'general') {
	$s = $subjectid ? $subjectid . '_' . $page . '.xml' : '300.xml';
} elseif ($type == 'magic') {
	$s = $subjectid ? $subjectid . '_' . $page . '.xml' : '200.xml';
}
$cachefile = D_P . "data/bbscache/myshow_{$s}";
if (!file_exists($cachefile) || $timestamp - pwFilemtime($cachefile) > 43200) {
	$data = '';
	if ($subjectid) {
		$url = "$u/list/$s?$timestamp";
	} else {
		$url = "$u/menu/$s?$timestamp";
	}
	require_once (R_P . 'require/posthost.php');
	$data = PostHost($url);
	if ($data && strpos($data, '<?xml') !== false) {
		//* writeover($cachefile, $data);
		pwCache::writeover($cachefile, $data);
	}
}
header("Content-Type: text/xml; charset=UTF-8");
$data = pwCache::readover($cachefile);
echo $data;
exit();
