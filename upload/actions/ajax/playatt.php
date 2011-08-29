<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('aid'));

$rt = $db->get_one("SELECT * FROM pw_attachs WHERE aid=" . S::sqlEscape($aid));
$a_url = geturl($rt['attachurl']);
if (empty($a_url) || $rt['needrvrc'] > 0) {
	Showmsg('job_attach_error');
}
$width = 314;
$ext = strtolower(substr(strrchr($rt['attachurl'], '.'), 1));
switch ($ext) {
	case 'mp3':
	case 'wma':
		$height = 53;
		$type = 'wmv';
		break;
	case 'wmv':
		$height = 256;
		$type = 'wmv';
		break;
	case 'swf':
		$height = 256;
		$type = 'flash';
		break;
	case 'rm':
		$height = 256;
		$type = 'rm';
		break;
	default:
		Showmsg('undefined_action');
}
echo "ok\t$a_url[0]\t$width\t$height\t$type";
ajax_footer();
