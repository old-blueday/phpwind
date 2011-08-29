<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
$normalUrl = $baseUrl."?type=clear";
!empty($winduid) && $userId = $winduid;
S::gp(array('action'), 'GP');
if(empty($action)){
	if($_POST['step'] == 2){
		PostCheck();	
		S::gp(array('clear'), 'GP');
		if(!$clear){
			refreshto($normalUrl,'您还没选择要清空的数据');
		}

		$messageServer = L::loadClass('message', 'message');
		$messageServer->clearMessages($userId,$clear);
		//Showmsg("operate_success");
		
	}

}
!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';

$numbers =$messageServer->statisticUsersNumbers(array($winduid));
$totalMessage = isset($numbers[$winduid]) ? $numbers[$winduid] : 0;
$tip = '您目前有消息'.$totalMessage.'条';
$tip .= $_G['maxsendmsg'] ? ',每日可发送消息'.$_G['maxsendmsg'].'条' : ',每日可发送消息20条' ;
$_G['maxmsg'] && $tip .= $percentTip;

require messageEot('clear');
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}
?>