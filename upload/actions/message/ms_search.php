<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
$normalUrl = $baseUrl . "?type=search";
!empty($winduid) && $userId = $winduid;

S::gp(array('page', '_usernames', 'smstype','usernames'), 'GP');
($usernames) && $_usernames = (is_array($usernames)) ? $usernames : array($usernames);
//empty($_usernames) && Showmsg("用户名不能为空");
(empty($smstype) || $smstype == 'all') && $smstype = '';
empty($page) && $page = 1;
if ($_usernames && in_array($windid,$_usernames)) {
	Showmsg('不能搜索自己消息');
}
list($countSmsSearch, $searchList) = $messageServer->searchMessages($userId, $_usernames[0], $smstype, $page, $perpage);
$pageCount = ceil($countSmsSearch / $perpage);
$page = validatePage($page, $pageCount);
$pages = numofpage($countSmsSearch, $page, $pageCount, "$normalUrl&usernames=".$_usernames[0]."&");

!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot('search');
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}
?>