<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('pctype'));
S::gp(array(
	'fieldid',
	'tid',
	'id'
), GP, 2);
if (!$tid || !$id || !$fieldid || !$pctype) {
	echo 'fail';ajax_footer();
}

$fieldTable = 'pw_topicfield';
if ($pctype == 'topic') {
	$tablename = GetTopcitable($id);
} elseif ($pctype == 'postcate') {
	$tablename = GetPcatetable($id);
	$fieldTable = 'pw_pcfield';
}

$fieldname = $db->get_value("SELECT fieldname FROM $fieldTable WHERE fieldid=" . S::sqlEscape($fieldid));
if (!$tablename || !$fieldname) {
	echo 'fail';ajax_footer();
}
$path = $db->get_value("SELECT ".S::sqlMetadata($fieldname)." FROM ".S::sqlMetadata($tablename)." WHERE tid=" . S::sqlEscape($tid));

if (strpos($path, '..') !== false) {
	echo 'fail';ajax_footer();
}
$lastpos = strrpos($path, '/') + 1;
$s_path = substr($path, 0, $lastpos) . 's_' . substr($path, $lastpos);

if (!file_exists("$attachpath/$path")) {
	if (pwFtpNew($ftp, $db_ifftp)) {
		$ftp->delete($path);
		$ftp->delete($s_path);
		pwFtpClose($ftp);
	}
} else {
	P_unlink("$attachdir/$path");
	if (file_exists("$attachdir/$s_path")) {
		P_unlink("$attachdir/$s_path");
	}
}

$db->update("UPDATE ".S::sqlMetadata($tablename)." SET ".S::sqlMetadata($fieldname)."='' WHERE tid=" . S::sqlEscape($tid));

echo 'success';

ajax_footer();
