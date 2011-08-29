<?php
require_once ('global.php');
define('SCR', 'message');
define('MS', 'ms_');
define('MESSAGE', 'message');
define('DS', DIRECTORY_SEPARATOR);
S::gp(array('subtype','type', 'action'));
(!$winduid && $type != 'ajax') && Showmsg ( 'not_login' );
$USCR = 'msg_message';

//导航
$homenavigation = array();
$navConfigService = L::loadClass('navconfig', 'site');
$homenavigation = $navConfigService->userHomeNavigation(PW_NAV_TYPE_MAIN, 'o');

if (!$type) {
	if ($winddb['newpm'] > 0 && $_G['maxmsg']) {
		$messageServer = L::loadClass('message', 'message');
		list($messageNumber, $noticeNumber, $requestNumber, $groupsmsNumber) = $messageServer->getUserStatistics($winduid);
		if ($messageNumber) {
			$type = 'sms';
		} elseif ($noticeNumber) {
			$type = 'notice';
		} elseif ($requestNumber) {
			$type = 'request';
		} elseif ($groupsmsNumber) {
			$type = 'groupsms';
		} else {
			$type = 'sms';
		}
	} else {
		$type = 'sms';
	}
}

list($perpage, $filePath) = array(20, 'actions' . DS . MESSAGE . DS . MS);
$allowTypes = array(
	'sms'		=> $filePath . 'sms' . '.php', //短消息
	'post'		=> $filePath . 'post' . '.php',  //发消息
	'notice'	=> $filePath . 'notice' . '.php', //通知
	'chat'		=> $filePath . 'chat' . '.php',  //多人聊天
	'groupsms'	=> $filePath . 'groupsms' . '.php', //群组消息
	'history'	=> $filePath . 'history' . '.php',  //历史消息
	'request'	=> $filePath . 'request' . '.php', //请求
	'ajax'		=> $filePath . 'ajax' . '.php',  //ajax统一入口
	'shield'	=> $filePath . 'shield' . '.php', //屏蔽设置
	'clear'		=> $filePath . 'clear' . '.php',
	'search'	=> $filePath . 'search' . '.php'
); //消息清空

if (!empty($subtype) && !in_array($subtype, array_keys($allowTypes)))
	Showmsg('undefined_action');

if (!in_array($type, array_keys($allowTypes)))
	Showmsg('undefined_action');
$operateFile = R_P . $allowTypes[$type];

if (file_exists($operateFile)) {
	require_once(R_P.'require/functions.php');
	require_once(R_P.'require/bbscode.php');
	require_once R_P.'u/require/core.php';
	require_once(R_P.'require/showimg.php');
	require_once(R_P.'u/lib/space.class.php');
	$messageServer = L::loadClass('message', 'message');
	$baseUrl = "message.php";
	require $operateFile;
} else {
	Showmsg('undefined_action');
}

/**
 * @param unknown_type $template
 * @return string
 */
function messageEot($template) {
	return printEot(MESSAGE . DS . MS . $template);
}

/**
 * @param unknown_type $output
 */
function ajaxExport($output) {
	echo is_array($output) ? stripslashes(pwJsonEncode($output)) : $output;
	ajax_footer();
}

/**
 * 重置消息数
 */
function resetUserMsgCount($num){
	global $winduid,$winddb;
	$num = intval($num);
	$userService = L::loadclass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->update($winduid, array('newpm'=>$num));
}

/**
 * 页面按时间分栏显示
 * @param Array $allList
 * @return multitype:number Array
 */
function getSubListInfo($allList) {
	global $timestamp;
	$tTimes = $yTimes = $mTimes = 0;
	$today = PwStrtoTime(get_date($timestamp, 'Y-m-d'));
	$yesterday = $today - 24 * 60 * 60;
	foreach ((array) $allList as $key => $value) {
		if ($value['modified_time'] >= $today) {
			$tTimes++;
		} elseif ($value['modified_time'] >= $yesterday && $value['modified_time'] < $today) {
			$yTimes++;
		} elseif ($value['modified_time'] < $yesterday) {
			$mTimes++;
		}
	}
	return array($today, $yesterday, $tTimes, $yTimes, $mTimes);
}

/**
 * 按时间分栏显示前台模板输出
 * @param Array $value
 * @return string
 */
function getSubListHtml($value) {
	global $today, $tTimes, $yesterday, $yTimes, $mTimes, $groups;
	$result = '';
	if ($value['modified_time'] >= $today && $tTimes) {
		$result = "<tr class=\"tr2\"><td colspan=\"6\">今天<em>({$tTimes}条)</em></td></tr>";
		$tTimes = 0;
	} elseif ($value['modified_time'] >= $yesterday && $value['modified_time'] < $today && $yTimes) {
		$result = "<tr class=\"tr2\"><td colspan=\"6\">昨天<em>({$yTimes}条)</em></td></tr>";
		$yTimes = 0;
	} elseif ($value['modified_time'] < $yesterday && $mTimes) {
		$result = "<tr class=\"tr2\"><td colspan=\"6\">更早<em>({$mTimes}条)</em></td></tr>";
		$mTimes = 0;
	}
	return $result;
}

/**
 * @param Array $value 结果集
 * @return multitype:Array (图片 ，样式 ，提示信息)
 */
function getMessageIconByStatus($value) {
	global $winduid;
	$_b = $_img = $_tip = '';
	if ($value['status'] == '1') {
		$_img = $winduid == $value['create_uid'] ? 'sendun.png' : 'reun.png';
		$_b = 'class="b"';
		$_tip = '(未读)';
	} elseif ($value['status'] == 2) {
		$_img = $winduid == $value['create_uid'] ? 'sendun.png' : 'reun.png';
		$_b = 'class="b"';
		$_tip = '(未读)';
	} else {
		$_img = $winduid == $value['create_uid'] ? 'sendread.png' : 'reread.png';
		$_b = '';
		$_tip = '';
	}
	return array($_img, $_b, $_tip);
}

/**
 * @param Array $message
 * @return string
 */
function getAllUsersHtml($message, $type = ''){
	global $windid;
	$userList = (array)unserialize($message['extra']);
	if (!in_array($message['create_username'],$userList)) {
		$userList = array_merge(array($message['create_username']),$userList);
	}
	$userListHtml = "";
	for ($i = 0 ; $i < count($userList); $i++) {
		$_userName = $userList[$i] == $windid ? '我' : $userList[$i];
		if ($i == 0) {
			$userListHtml .= '<a href="u.php?username=' . urlencode($userList[$i]) . '">' . $_userName . '</a> 和 ';
		} else {
			$userListHtml .= '<a href="u.php?username=' . urlencode($userList[$i]) . '">' . $_userName . '</a>, ';
		}
	}
	$userListHtml = trim($userListHtml, ', ');
	return $userListHtml;
}

/**
 * @return string
 */
function getUnReadHtml(){
	global $notReadCount,$action;
	$html = "";
	if ($action == 'unread') {
		$html = "<tr class=\"tr2\"><td colspan=\"6\">未读消息<em>({$notReadCount}条)</em></td></tr>";
	}
	return $html;
}

/**
 * 取得请求操作返回的状态,只争对消息的状态
 * @param $typeid int 消息类别
 * @param $status int 消息状态
 * @param $check  int 是否是审核操作
 * @param $L	  string 语言包
 * @return string 返回请求处理状态
 */
function getRequestTypeDes($typeid,$status = 0,$check = 0,$L = 'message') {
	global $messageServer;
	$typeDes = array(
					 $messageServer->getConst('request_friend') => array('descript'=>getLangInfo($L,'request_friend'),0=>getLangInfo($L,'friend_request_agree'),4=>getLangInfo($L,'friend_add_ignore'),5=>getLangInfo($L,'friend_add_success')),
					 $messageServer->getConst('request_group') => array('descript'=>getLangInfo($L,'request_group'),
					 													 0=>getLangInfo($L,$tip = $check ? 'colony_check_agree' : 'colony_request_agree'),
					 													 4=>getLangInfo($L,$tip = $check ? 'colony_check_ignore' : 'colony_add_ignore'),
					 													 5=>getLangInfo($L,$tip = $check ? 'colony_check_success' : 'colony_joinsuccess')
					 											  ),
					 $messageServer->getConst('request_app') => array('descript'=>getLangInfo($L,'request_app'),0=>getLangInfo($L,'app_request_agree'),4=>getLangInfo($L,'app_add_ignore'),5=>getLangInfo($L,'app_add_success'))
			   );
	return $status ?  $typeDes[$typeid][$status] : $typeDes[$typeid];
}

?>