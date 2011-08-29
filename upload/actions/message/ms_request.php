<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
empty($subtype) && $subtype = 'request';
$normalUrl = $baseUrl . "?type=$subtype";
!empty($winduid) && $userId = $winduid;
S::gp(array('page'), 'GP', 2);
!$page && $page = 1;
$requestCount = $requestAllCount = $messageServer->countAllRequest($userId);
$notReadCount = $messageServer->countRequestsNotRead($userId);
$selected = $action ? array($action=>'selected') : 'selected';
$nav = $action && $action != 'unread' ? array($action=>'class = current') : ' class = current';
if (empty($action) || $action == 'all') {
	$pageCount = ceil($requestCount / $perpage);
	$page = validatePage($page,$pageCount);
	$requestList = $messageServer->getAllRequests($userId, $page, $perpage);
	$url = $normalUrl . '&';
	!$requestCount && $emptyListTip = "<p class=\"tac p15 f14\">暂无任何请求，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧</p>";
} elseif ($action == 'friend') {
	$requestCount = $messageServer->countRequest($userId, $messageServer->getConst('request_friend'));
	$pageCount = ceil($requestCount / $perpage);
	$page = validatePage($page,$pageCount);
	$requestList = $messageServer->getRequests($userId, $messageServer->getConst('request_friend'), $page, $perpage);
	$url = $normalUrl . '&action=friend&';
	!$requestCount && $emptyListTip = "<p class=\"tac p15 f14\">暂无任何好友请求，赶快去<a href=\"u.php?a=friend&type=find\">找好友</a>吧</p>";
} elseif ($action == 'group') {
	$requestCount = $messageServer->countRequest($userId, $messageServer->getConst('request_group'));
	$pageCount = ceil($requestCount / $perpage);
	$page = validatePage($page,$pageCount);
	$requestList = $messageServer->getRequests($userId, $messageServer->getConst('request_group'), $page, $perpage);
	$url = $normalUrl . '&action=group&';
	!$requestCount && $emptyListTip = "<p class=\"tac p15 f14\">暂无任何群组请求，赶快去<a href=\"group.php?q=all\">加群组</a>吧</p>";
} elseif ($action == 'app') {
	$requestCount = $messageServer->countRequest($userId, $messageServer->getConst('request_apps'));
	$pageCount = ceil($requestCount / $perpage);
	$page = validatePage($page,$pageCount);
	$requestList = $messageServer->getRequests($userId, $messageServer->getConst('request_apps'), $page, $perpage);
	$url = $normalUrl . '&action=app&';
	!$requestCount && $emptyListTip = "<p class=\"tac p15 f14\">暂无任何应用请求</p>";
} elseif ($action == 'unread') {
	$requestCount = $notReadCount;
	$pageCount = ceil($requestCount / $perpage);
	$page = validatePage($page,$pageCount);
	$requestList = $messageServer->getRequestsNotRead($userId, $page, $perpage);
	$url = $normalUrl . '&action=unread&';
	!$notReadCount && $emptyListTip = "<p class=\"tac p15 f14\">暂无任何未读请求</p>";
}
$pages = numofpage($requestCount, $page, $pageCount, $url);

if($subtype == 'request'){
	$messageServer->resetStatistics(array($userId),'request_num');
}

!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot($subtype);
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}
?>