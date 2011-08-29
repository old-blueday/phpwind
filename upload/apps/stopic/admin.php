<?php
!defined('P_W') && exit('Forbidden');

$admin_jobs = array(
	'make',
	'advance',
	'initstopic',
	'editblock',

	'cgman',
	'bgman',
	'stman',

	'creatstopic',
	'preview',
	'checkfilename',
	'showconfirm',

	"ajax",
	'gettidcontent',
	'createcategory',
	'changestyle',
);
$job = in_array($job, $admin_jobs) ? $job : 'stman';

$stopic_admin_url = $basename;

$stopic_service	= L::loadClass('stopicservice','stopic');
include S::escapePath(A_P."/action/admin/$job.php");


function stopic_use_layout($layout) {
	return S::escapePath(A_P."/template/layout/$layout.php");
}
function stopic_load_view($action, $module='admin') {
	return S::escapePath(A_P."/template/$module/$action.htm");
}

function stopic_check_file_name($file_name) {
	return (bool) preg_match('/^[a-zA-Z0-9_\-]+$/i', $file_name);
}

?>