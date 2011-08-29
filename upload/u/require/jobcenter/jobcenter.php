<?php

!function_exists('readover') && exit('Forbidden');

if(!$db_job_isopen && $action != 'punch'){
	Showmsg('抱歉，用户任务系统还没有开启');
}

$pro_tab = "job";/*导航*/
$jobService = L::loadclass("job", 'job'); /* @var $jobService PW_Job */
$current = array('','','','');
S::gp(array("id","step","action"));
$q = "jobcenter";
$action = empty($action) ? 'applied' : $action;
if($action == "list"){
	$jobs = $jobService->jobDisplayController($winduid,$groupid,$action);
	$current[0] = "current";
	require_once uTemplate::PrintEot('jobcenter');pwOutPut();
} elseif ($action == "apply"){
	if($step == 2){
		$id = intval($id);
		list($bool,$message,$job) = $jobService->checkApply($id,$winduid,$groupid);
		if(!$bool){
			ajaxResponse($message,false);
		}
		if(!$result = $jobService->jobApplyController($winduid,$id)){
			$message = $jobService->getLanguage("job_apply_fail");
			ajaxResponse($message,false);
		}else{
			$message =  $jobService->getLanguage("job_apply_success");
			$appliedHTML = $jobService->buildApplieds($winduid,$groupid);
			ajaxResponse($message,true,$appliedHTML);
		}
	}
}elseif( empty($action) || $action == "applied"){	
	$joblists = $jobService->getAppliedJobs($winduid); 
	$jobs = $jobService->buildLists($joblists,$action,$winduid,$groupid);
	$jobsNum = (int)count($jobs);
	$winddb['jobnum'] = (int)$winddb['jobnum'];
	if ($winddb['jobnum'] !== $jobsNum) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), array('jobnum' => $jobsNum));
		$winddb['jobnum'] = $jobsNum;
	}
	$current[1] = "current";
	require_once uTemplate::PrintEot('jobcenter');pwOutPut();
}elseif($action == "finish"){	
	S::gp(array("jobid"));
	if($jobid){
		$jobid = intval($jobid);
		if($jobid<1){
			Showmsg('undefined_action');
		}
		$job = $jobService->getJob($jobid);
		if(!$job){
			Showmsg('任务不存在');
		}
		$jober = $jobService->getJoberByJobId($winduid,$jobid);
		$list = array();
		$list['id'] = $job['id'];
		$list['title'] = $job['title'];
		$list['description'] = html_entity_decode($job['description']);
		$list['period']   = ($job['period']) ? "每隔".$job['period']." 小时可以申请一次" : "一次性任务";
		$list['isperiod'] = ($job['period']>0) ? true : false;
		if(isset($job['reward'])){
			$rewardTotal = $jobService->buildCountCategoryInfo($job['reward'],$jober['total']);
			$reward = implode(' ', $jobService->getCategoryInfo($job['reward']));
		}
		$list['reward']      = $reward ? $reward : "无";
		$list['rewardtotal'] = ($rewardTotal) ? $rewardTotal : "";
		$list['number']      = ( isset($job['number']) && $job['number'] != 0 ) ? $job['number']."人" : "";
		$list['member']      = ($job['member'] == 0 ) ? "不限制" : "限制";
		if(isset($job['factor'])){
			$factor = unserialize($job['factor']);
		}
		$list['timelimit'] = (isset($factor['limit']) && $factor['limit'] != "" ) ? $factor['limit']."个小时之内" : "不限制";
		/*前置任务*/
		$prepost = '';
		if(isset($job['prepose']) && $job['prepose'] != 0 ){
			$prepost = $jobService->getJob($job['prepose']);
			$prepost = "(必须完成 ".$prepost['title']." 才能申请)";
		}
		$list['prepose'] = $prepost ? $prepost : "";
		$list['icon'] = (isset($job['icon']) && $job['icon'] != "" ) ? "attachment/job/".$job['icon'] : "images/job/".strtolower($job['job']).".gif";
		$list['condition'] = $jobService->getCondition($job);
		$list['usergroup'] = (isset($job['usergroup']) && $job['usergroup'] != '') ? $jobService->getUserGroup($job['usergroup']) : '';
		$list['total'] = $jober['total'];
		$list['last'] = get_date($jober['last'],"Y-m-d H:i");
		require_once(R_P.'require/showimg.php');
		list($list['face']) = showfacedesign($winddb['icon'],1,m);
		list($others,$total) = $jobService->jobDetailHandler($winduid,$job['id']);
		$show = "detail";
	}else{
		$joblists = $jobService->getFinishJobs($winduid);
		$jobs = $jobService->buildLists($joblists,$action,$winduid,$groupid);
	}
	$current[2] = "current";
	require_once uTemplate::PrintEot('jobcenter');pwOutPut();
}elseif($action == "quit"){	
	if($step == 2){
		list($bool,$message) = $jobService->jobQuitController($winduid,$id);
		ajaxResponse($message,$bool);
	}
	$joblists = $jobService->getQuitJobs($winduid);
	$jobs = $jobService->buildLists($joblists,$action,$winduid,$groupid);
	$current[3] = "current";
	require_once uTemplate::PrintEot('jobcenter');pwOutPut();
}elseif($action == "start"){		
	//获取任务开始链接
	list($bool,$message,$link) = $jobService->jobStartController($winduid,$id);
	if (S::getGP('ajax')) {
		ajax_footer();
	}
	if(!$bool){
		refreshto("jobcenter.php?action=applied",$message);
	}
	if($link == ""){
		refreshto("jobcenter.php?action=applied","任务开始");
	}else{
		ObHeader($link);
	}
	
}elseif($action == "gain"){	
	if($step == 2){
		$id = intval($id);
		list($bool,$message) = $jobService->jobGainController($winduid,$id);
		if($bool){
			$autojobService = L::loadclass("autojob", 'job'); /* @var $jobService PW_Job */
			$autojobService->jobAutoController($winduid,$groupid);/*自动申请*/
			$appliedHTML = $jobService->buildApplieds($winduid,$groupid);
			ajaxResponse($message,true,$appliedHTML);
		}else{
			ajaxResponse($message,false);
		}
	}
} elseif($action == 'punch'){
	//每日打卡
	if($step == 2){
		//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
		pwCache::getData(D_P . 'data/bbscache/o_config.php');
		if(!$o_punchopen){
			ajaxResponse('打卡功能已经关闭',false);
		}
		$usergroup = ($o_punch_usergroup) ? explode(",",$o_punch_usergroup) : array();
		if($usergroup && !in_array($groupid,$usergroup)){
			ajaxResponse('您所在用户组没有打卡的权限',false);
		}
		list($todayStart,$todayEnd) = array($tdtime,$tdtime+86400);
		if($winddb['punch'] > $todayStart && $winddb['punch'] < $todayEnd){
			ajaxResponse('你已经打卡,请明天再试',false);
		}
		if(procLock('punch_save', $winduid)){
			if(pwQuery::update('pw_memberdata','uid=:uid',array($winduid),array('punch'=>$timestamp))){
				list($bool,$information) = getPunchReward($o_punch_reward);
			}
		}
		procUnLock('punch_save', $winduid);
		if($bool){
			ajaxResponse($information,true);
		}else{
			ajaxResponse('打卡失败,请明天继续!',false);
		}
	}
}else{
	
}

function ajaxResponse($message,$flag,$html=''){
	echo '[{"message":\''.$message.'\',"flag":\''.$flag.'\',"html":\''.$html.'\'}]';ajax_footer();
}
/*
 * 打卡领取奖励
 */
function getPunchReward($reward){
	global $credit,$winduid,$windid;
	$reward = (S::isArray($reward)) ? $reward : unserialize($reward);
	if(!$reward){
		return array(false,'');
	}
	(!S::isObj($credit)) && require_once R_P . "require/credit.php";
	$credit->addLog('other_finishpunch', array(
		$reward['type'] => $reward['num']
	), array(
		'uid' => $winduid,
		'username' => $windid,
		'ip' => $GLOBALS['onlineip']
	));
	$credit->set($winduid, $reward['type'], $reward['num']);
	$unit = (isset($credit->cUnit[$reward['type']])) ? $credit->cUnit[$reward['type']] : '';
	return array(true,pwCreditNames($reward['type']).'+'.$reward['num']);
}
?>