<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
empty($subtype) && $subtype = 'history';
$normalUrl = $baseUrl . "?type=history";
!empty($winduid) && $userId = $winduid;
S::gp(array('page'),'GP');

$countHistory = $messageServer->countHistoryMessage($userId);
$pageCount = ceil($countHistory / $perpage);
$page = validatePage($page,$pageCount);
$historyList = $messageServer->getHistoryMessages($userId,$page,$perpage);
$pages = numofpage($countHistory, $page, $pageCount, "$normalUrl&");

!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot($subtype);
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}

?>