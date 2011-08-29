<?php 
!defined('A_P') && exit('Forbidden');
/* 用户行为 */
define('AJAX', 1);
S::gp(array('id', 'atype'));      
$id = (int) $id;      
if (!$winduid || $id < 1 || !$db_md_ifopen) exit;
//获取勋章信息、用户已经获取的勋章信息、已经申请的勋章信息
$medalService = L::loadClass('MedalService', 'medal'); /* @var $medalService PW_MedalService */
$medalInfo = $medalService->getMedal($id);
$userMedal = $medalService->getUserMedals($winduid); //获取会员的勋章
$isUserApply = $medalService->getApplyByUidAndMedalId($winduid,$medalInfo['medal_id']); //用户已经申请
$userMedalInfo = $userMedalIdArr = array();
if (is_array($userMedal)) {
	foreach ($userMedal as $v) {
		$userMedalIdArr[] = $v['medal_id']; //存放用户勋章ID，判断用户是否申请成功
		$userMedalInfo[$v['medal_id']] = $v;
	}
}
//HTML组装
if ($medalInfo['type'] == 2) { //手动发放
	if ($db_md_ifapply) {
		$ifApply = (in_array($winddb['memberid'], (array)$medalInfo['allow_group']) || !$medalInfo['allow_group']) ? '<a href="javascript:;" onclick="sendmsg(\'apps.php?q=medal&a=apply&id='.$id.'\',\'\',null,function(){window.location.reload();})" class="medal_pop_btn fr" >申请勋章</a>' : '你所在用户组无法领取此勋章，请尽快升级。';
	} else {
		$ifApply = '';
	}
	
	$otherHtml = ($isUserApply) ? '<a href="javascript:" class="medal_pop_bt fr">已申请</a>' : $ifApply;
	if (in_array($id, $userMedalIdArr)) {
		$awardMedal = $medalService->getAwardMedalByUidAndMedalId($winduid, $id); //
		if ($medalInfo['confine'] == 0) {
			$otherHtml = '<p class="mb5">获得时间：' . date('Y-m-d', $awardMedal['timestamp']) . '</p><p>有效期：永久</p>';
		} else {
			$otherHtml = '<p class="mb5">获得时间：' . date('Y-m-d', $awardMedal['timestamp']) . '</p><p><span class="mr20">有效期：' . $medalInfo['confine'] . ' 天</span>'. '到期时间：' . date('Y-m-d', $awardMedal['deadline']) . '</p>';
		}
	}
} else { //自动发放
	$attention = $notice = $nowhave = '';
	if (in_array($medalInfo['associate'], array('continue_login', 'continue_post', 'continue_thread_post'))) {
		//获取用户行为信息
		$behaviorService = L::loadClass('behaviorService', 'user'); /* @var $medalService PW_MedalService */ 
		$behavior = $behaviorService->getBehaviorStatistic($winduid, $medalInfo['associate']);
		$num = ($behavior) ? $behavior['num'] : 0;
		$needNum = $medalInfo['confine'] - $num;
		if ($medalInfo['associate'] == 'continue_login') {
			$attention = '<p class="gray">注意：1天不登录，现有天数会减1</p>';
			$notice = ($needNum > 0) ? '你还需连续登录'.$needNum.'天' : '重新登录一次即可获得此勋章';
			$notice = $notice . '（现有天数：'.$num.'）';
			$nowhave = '（现有连续登录天数：'.$num.'）';
		} elseif ($medalInfo['associate'] == 'continue_thread_post') {
			$attention = '<p class="gray">注意：1天不登录，现有连续登录天数会减1</p>';
			$notice = ($needNum > 0) ? '你还需连续发主题'.$needNum.'天' : '再发1主题帖即可获得此勋章';
			$notice = $notice . '（现有天数：'.$num.'）';
			$nowhave = '（现有连续主题天数：'.$num.'）';
		} elseif ($medalInfo['associate'] == 'continue_post') {
			$attention = '<p class="gray">注意：1天不发帖，现有天数会减1</p>';
			$notice = ($needNum > 0) ? '你还需连续发帖'.$needNum.'天' : '再发1帖即可获得此勋章';
			$notice = $notice . '（现有天数：'.$num.'）';
			$nowhave = '（现有连续发帖天数：'.$num.'）';
		}
	} else {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService*/
		$userInfo = $userService->get($winduid, true, true, true);
		if ($medalInfo['associate'] == 'post') {
			$needNum = $medalInfo['confine'] -$userInfo['postnum'];
			$notice = ($needNum > 0) ? '你还需发'.$needNum.'个帖子' : '再发1帖即可获得此勋章';
			$notice = $notice . '（现有帖数：'.$userInfo['postnum'].'）';
		} elseif ($medalInfo['associate'] == 'fans') {
			$needNum = $medalInfo['confine'] -$userInfo['fans'];
			$notice = ($needNum > 0) ? '你还需增加'.$needNum.'个粉丝' : '再增加1个粉丝即可获得此勋章';
			$notice = $notice . '（现有粉丝数：'.$userInfo['fans'].'）';
		} elseif ($medalInfo['associate'] == 'shafa') {
			$needNum = $medalInfo['confine'] -$userInfo['shafa'];
			$notice = ($needNum > 0) ? '你还需抢'.$needNum.'个沙发' : '再抢1个沙发即可获得此勋章';
			$notice = $notice . '（现有沙发数：'.$userInfo['shafa'].'）';
		}
	}
	if (in_array($id, $userMedalIdArr)) {
		$notice = '<p class="medal_pop_tips mb5">'.'恭喜获得'.$medalInfo['name'].'勋章'.$nowhave.'</p>';
	} else {
		//没有有用户组权限的情况
		if ($medalInfo['allow_group'] && !in_array($winddb['memberid'], (array)$medalInfo['allow_group'])) {
			$notice = '<p class="gray">你所在用户组无法领取此勋章，请尽快升级。</p>';
		}
		$notice =  '<p class="medal_pop_tips mb5">'.$notice.'</p>';
	}
	$otherHtml = $notice . $attention;
}
$html = '<div class="medal_pop">
<span class="fl"><span class="medal_pop_angle"></span></span>
<div class="medal_pop_cont">
<div class="medal_pop_top">
<p class="mb10"><img src="'.$medalInfo['smallimage'].'" width="30" height="30" id="medal_img" align="absmiddle" class="mr10" /><span class="b s5 mr10">'.$medalInfo['name'].'</span><span class="s6">('.$typeArr[$medalInfo['type']].')</span></p>
<p class="s5">'.$medalInfo['descrip'].'</p>
</div>
<div class="medal_pop_bot cc" >
'.$otherHtml.'
</div>
</div>
</div>';
echo "success\t".$html;
ajax_footer();
