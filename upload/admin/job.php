<?php
!defined('P_W') && exit('Forbidden');

$jobService = L::loadclass("job", 'job'); /* @var $jobService PW_Job */

if(empty($action)){
	S::gp(array("page",'step'));
	
	if($step == 2){
		S::gp(array('joblist'));
		$jobs = array();
		foreach($joblist as $id=>$job){
			$id = intval($id);
			if($id < 1){
				adminmsg($jobService->getLanguage("job_id_null"));
			}
			if( "" == $job['title'] ){
				adminmsg($jobService->getLanguage("job_title_null"));
			}
			$jobs[$id]['isopen'] = (isset($job['isopen'])) ? 1 : 0;
			$sequence = intval($job['sequence']);
			if($sequence < 0){
				adminmsg($jobService->getLanguage("job_sequence_null"));
			}
			$jobs[$id]['sequence'] = $sequence;
			$jobs[$id]['title'] = trim($job['title']);
		}
		foreach($jobs as $id=>$job){
			$jobService->updateJob($job,$id);
		}
		adminmsg('operate_success',"$basename&action=");
	}
	/*基础设置*/
	$db_job_isopen = empty($db_job_isopen) ? 0 : $db_job_isopen;
	$db_job_ispop  = empty($db_job_ispop) ? 0 : $db_job_ispop; 
	$isopen = $ispop = array("","");
	($db_job_isopen == 0) ? $isopen[0] = "checked" : $isopen[1] = "checked";
	($db_job_ispop == 0)  ? $ispop[0]  = "checked" : $ispop[1]  = "checked";
	$page = ($page) ? $page : 1;
	$prepage = 25;/*每页数*/
	
	$total = (int)$jobService->countJobs();
	$numofpage = ceil($total/$prepage);
	$page > $numofpage && $page = $numofpage;
	$jobs = $total ? $jobService->getJobs($page,$prepage) : array();
	$joblists = array();
	foreach($jobs as $job){
		$lists = array();
		$lists['id']       = $job['id'];
		$lists['sequence'] = $job['sequence'];
		$lists['title']    = $job['title'];
		$lists['isuserguide']    = $job['isuserguide'];
		$lists['jobtype']  = $jobService->getJobType($jobService->getJobTypes($job['job']));
		if(!$job['starttime'] && !$job['endtime']){
			$timetips = "不限制";
		}else{
			$timetips = ($job['starttime']) ? date("Y-m-d",$job['starttime']) : "不限制";
			$timetips .= " - ";
			$timetips .= ($job['endtime']) ? date("Y-m-d",$job['endtime']) : "不限制";
		}
		$lists['timetips']     = $timetips;/*开始到结束*/
		$lists['period']   = ($job['period']) ? $job['period']." 小时" : "不限制";
		if(isset($job['reward'])){
			$lists['reward'] = implode(' ', $jobService->getCategoryInfo($job['reward']));
		}
		$lists['isopen'] = $job['isopen'] == 1 ? "checked" : "";
		$joblists[] = $lists;
	}
	$pages = numofpage($total,$page,$numofpage, "$admin_file?adminjob=job&");
	$ajaxUrl = EncodeUrl($basename);
	include PrintEot('job');exit;
}elseif($action == 'add' || $action == 'edit'){
	S::gp(array('step','id'));
	if($step == 2){
		S::gp(array('title','description','starttime','endtime','period','reward','prepose','usergroup','member','number','isuserguide'));
		S::gp(array('auto','finish','display','factor','icon','id'));
		if($title == "" ){
			adminmsg($jobService->getLanguage("job_title_null"));
		}
		if($description == "" && !$isuserguide){
			adminmsg($jobService->getLanguage("job_description_null"));
		}
		if($factor['job'] == 'doPost'){
			//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
			pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
			if(isset($factor['doPost']['fid']) && isset($forum[$factor['doPost']['fid']]) && $forum[$factor['doPost']['fid']]['type'] == 'category'){
				adminmsg('不能使用版块分类作为指定版块!',"$admin_file?adminjob=job&action=add");
			}
		}
		$data['title'] = trim($title);
		$data['description'] = trim($description);
		if(!$id){/*新增*/
			$data['starttime'] = $starttime ? PwStrtoTime($starttime) : $timestamp;
			$endtime   && $data['endtime']   = PwStrtoTime($endtime);
			$period    && $data['period']    = intval($period);
			$prepose   && $data['prepose']   = intval($prepose);
			$number    && $data['number']    = intval($number);
			(isset($usergroup)) && $data['usergroup'] = implode(",",$usergroup);/*用户组*/
		}else{
			$job = $jobService->getJob($id);
			$data['starttime'] = $starttime ? ($job['starttime'] == strtotime($starttime) ? strtotime($starttime) : PwStrtoTime($starttime) ) : 0;
			$data['endtime']   = $endtime   ? ($job['endtime'] == strtotime($endtime) ? strtotime($endtime) : PwStrtoTime($endtime) )   : 0;
			$data['period']    = $period    ? intval($period)       : 0;
			$data['prepose']   = $prepose   ? intval($prepose)      : 0;
			$data['number']    = $number    ? intval($number)       : 0;
			$data['usergroup'] = $usergroup ? implode(",",$usergroup) : '';/*用户组*/
		}
		if($data['starttime'] && $data['endtime']){
			$data['endtime'] < $data['starttime'] && 	adminmsg($jobService->getLanguage("job_stime_r_etime"));
		}

		/*奖励规则*/
		$rCategory = isset($reward['category']) ? $reward['category'] : "none";
		if($rCategory != 'none'){/*如果为空则过滤*/
			$t_reward = array();
			foreach($reward[$rCategory] as $k =>$v){
				$t_reward[$k] = $v;
			}
			$t_reward['category'] = $rCategory;
			/*组装奖励任务信息*/
			$t_reward['information'] = $jobService->buildCategoryInfo($t_reward);
			$data['reward'] = serialize($t_reward);
		}else{
			$data['reward'] = '';
		}
		$data['member'] = intval($member);
		$data['auto'] = intval($auto);
		$data['finish'] = intval($finish);
		$data['display'] = intval($display);
		$job = $factor['job'];
		if($job){
			$job == "doSendMessage" && empty($factor[$job]['user']) && adminmsg($jobService->getLanguage("use_not_exists"));
		}
		if(isset($factor[$job])){
			$t_factor = array();
			foreach($factor[$job] as $k=>$v){
				if($k == "limit"){
					$v = $v > 65535 ? 65535 : $v;
				}
				$t_factor[$k] = $v;
			}
			$data['factor'] = serialize($t_factor);
		}
		/*编辑操作*/
		if($id){
			$data['factor'] = isset($data['factor']) ? $data['factor'] : '';
		}		
		$data['job'] = trim($job);/*任务*/
		/*上传图片*/
		if(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != "" && !$filename = $jobService->upload($_FILES)){
			adminmsg($jobService->getLanguage("upload_icon_fail"));
		}
		$filename && $data['icon'] = $filename;
		$data['type'] = strtolower($jobService->getJobTypes($job));
		if($id){
			$result = $jobService->updateJob($data,$id);/*更新*/
		}else{
			$result = $jobService->addJob($data);/*新增*/
		}
		adminmsg('operate_success',"$basename&action=");
	}
	/*是否开启道具、勋章和邀请码*/
	$medalIsOpen      = $db_md_ifopen      ? "" : "disabled";
	$inviteCodeIsOpen = $jobService->checkIsOpenInviteCode() ? "" : "disabled";
	$toolsIsOpen      = $db_toolifopen ? "" : "disabled"; 
	
	/*编辑*/
	$job = array();
	if(!empty($id)){
		$job = $jobService->getJob($id);
	}
	$title       = (isset($job['title']))       ? $job['title'] : '';
	$description = (isset($job['description'])) ? $job['description'] : '';
	$starttime   = (isset($job['starttime']) && $job['starttime'] >0  )   ? get_date($job['starttime'],'Y-m-d H:i') : '';
	$endtime     = (isset($job['endtime']) && $job['endtime'] >0 )        ? get_date($job['endtime'],'Y-m-d H:i') : '';
	$period      = (isset($job['period']) && $job['period'] > 0)            ? $job['period'] : '';
	$icon        = (isset($job['icon']))       ? $job['icon'] : '';
	$isuserguide = (isset($job['isuserguide'])) ? $job['isuserguide'] : '';
	/*@todo reward data start*/
	if($job){
		$reward = unserialize($job['reward']);
	}
	$category = isset($reward['category']) ? $reward['category'] : "none";
	foreach(array('none','credit','tools','medal','usergroup','invitecode') as $v){
		$r_reward[$v] = ($v ==$category ) ? "checked" : "";
	}
	$r_credit = $tools  = $medal = $usergroup = $invitecode = array();
	$r_credit['num']    = ($category == "credit") ? $reward['num'] : "";
	$r_tools['num']     = ($category == "tools") ? $reward['num'] : "";
	//$r_medal['day']     = ($category == "medal") ? $reward['day'] : "";
	$r_usergroup['day'] = ($category == "usergroup") ? $reward['day'] : "";
	if($category == "invitecode"){
		$r_invitecode['num'] = $reward['num'];
		$r_invitecode['day'] = $reward['day'];
	}
	$tools = ($reward && $reward['category'] == "tools") ? $reward['type'] : ''; 
	$medal = ($reward && $reward['category'] == "medal") ? $reward['type'] : ''; 
	$credit = ($reward && $reward['category'] == "credit") ? $reward['type'] : '';
	$usergroup = ($reward && $reward['category'] == "usergroup") ? $reward['type'] : '';
	
	$levelSelect = $jobService->getLevelSelect($usergroup,'reward[usergroup][type]','reward_usergroup','special');
	$creditSelect = $jobService->getCreditSelect($credit,'reward[credit][type]','reward_credit');
	$medalSelect = $jobService->getMedalSelect($medal,'reward[medal][type]','reward_medal');
	$toolsSelect = $jobService->getToolsSelect($tools,'reward[tools][type]','reward_tools');	
	/*@todo reward data end*/
	
	$usergroups = (isset($job['usergroup'])) ? explode(",",$job['usergroup']) : array();
	$prepose    = (isset($job['prepose']))   ? $job['prepose'] : '';/*前置任务*/
	$number     = (isset($job['number']))    ? $job['number']  : '';
	
	$member = $auto = $finish = $display = array("","");
	( isset($job['member']) && $job['member'] == 1) ? $member[1] = "checked" : $member[0] = "checked";
	( isset($job['auto']) && $job['auto'] == 1)     ? $auto[1] = "checked"   : $auto[0] = "checked";
	( isset($job['finish']) && $job['finish'] == 1) ? $finish[1] = "checked"   : $finish[0] = "checked";
	( isset($job['display']) && $job['display'] == 0) ? $display[0] = "checked"   : $display[1] = "checked";
	
	$levelCheckBox = $jobService->getLevelCheckbox($usergroups);/*用户组复选框*/
	$preposeSelect = $jobService->getJobsSelect($prepose,'prepose','prepose');
	
	/* factor start*/
	$jobName = (isset($job['job'])) ? $job['job'] : 'doUpdatedata';
	$jobType = $jobService->getJobTypes($jobName);
	list($jobHtml,$jobInfo) = $jobService->getJobLists($jobName);
	if($job){
		$factor = unserialize($job['factor']);
	}
	/*默认*/
	
	/**************会员信息类    start *********************************************************/
	$doUpdatedata = $doUpdateAvatar = $doSendMessage = $doAddFriend = array();
	/*更新资料*/
	$doUpdatedata['limit']   = ($job && $jobName == "doUpdatedata")    ? $factor['limit'] : "";
	/*上传头像*/
	$doUpdateAvatar['limit'] = ($job && $jobName == "doUpdateAvatar")  ? $factor['limit'] : "";
	/*发送消息*/
	$doSendMessage['user']   = ($job && $jobName == "doSendMessage")   ? $factor['user']  : "";
	$doSendMessage['limit']  = ($job && $jobName == "doSendMessage")   ? $factor['limit'] : "";
	/*加好友*/
	$doAddFriend['limit']    = ($job && $jobName == "doAddFriend")     ? $factor['limit'] : "";
	$doAddFriend['user']     = ($job && $jobName == "doAddFriend")     ? $factor['user'] : "";
	$doAddFriend['num']      = ($job && $jobName == "doAddFriend")     ? $factor['num'] : "1";
	$doAddFriend['type1']    = ($job && $jobName == "doAddFriend" && $factor['type'] == 1) ? "checked" : "";
	$doAddFriend['type2']    = ($job && $jobName == "doAddFriend" && $factor['type'] == 2) ? "checked" : "";
	$job && !$doAddFriend['type1'] && !$doAddFriend['type2'] && $doAddFriend['type2'] = 'checked';
	/**************会员信息类    end ************************************************************/
	
	/* factor end*/
	
	/**************论坛操作类    start *********************************************************/
	$doPost = $doReply = array();
	/*发帖*/
	$doPost['fid']   = ($job && $jobName == "doPost")   ? $factor['fid']  : "";
	$doPost['num']   = ($job && $jobName == "doPost")   ? $factor['num']  : "";
	$doPost['limit'] = ($job && $jobName == "doPost")   ? $factor['limit'] : "";
	$forumSelectHtml = getForumSelectHtml($doPost['fid']);
	
	$doReply['tid']        = ($job && $jobName == "doReply")   ? $factor['tid']  : "";
	$doReply['user']       = ($job && $jobName == "doReply")   ? $factor['user']  : "";
	$doReply['replynum']   = ($job && $jobName == "doReply")   ? $factor['replynum']  : "1";
	$doReply['limit']      = ($job && $jobName == "doReply")   ? $factor['limit'] : "";
	$doReply['type1']      = ($job && $jobName == "doReply" && $factor['type'] == 1) ? "checked" : "";
	$doReply['type2']      = ($job && $jobName == "doReply" && $factor['type'] == 2) ? "checked" : "";
	($doReply['type1'] == "" && $doReply['type2'] == "" ) ? $doReply['type1'] = "checked" : '';
	/**************论坛操作类   end    *********************************************************/
	
	
	//添加任务时默任操作
	if(empty($job)){
		$doAddFriend['type2'] = 'checked';
	}
	include PrintEot('jobhander');exit;
}elseif($action=="delete"){
	S::gp(array('id'));
	$id = intval($id);
	if($id < 1){
		adminmsg($jobService->getLanguage("job_id_null"));
	}
	$result = $jobService->deleteJob($id);
	$result && adminmsg('operate_success',"$basename&action=");	
}elseif($action=="setting"){
	S::gp(array('isopen','ispop'));
	if ($isopen === null || $ispop === null) adminmsg('operate_error',"$basename&action=");	
	setConfig ( 'db_job_isopen', $isopen );
	setConfig ( 'db_job_ispop', $ispop );
	updatecache_c ();
	adminmsg('operate_success',"$basename&action=");		
}else{
	
}

function getForumSelectHtml($fid){
    global $db;
   	$query	= $db->query("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid ORDER BY f.vieworder,f.fid");
	$fkeys = array('fid','fup','ifsub','childid','type','name','style','f_type','cms','ifhide');
	$catedb = $forumdb = $subdb1 = $subdb2 = $forum_cache = $fname= array();
	while ($forums = $db->fetch_array($query)) {
		$fname[$forums['fid']] = str_replace(array("\\","'",'<','>'),array("\\\\","\'",'&lt;','&gt;'), strip_tags($forums['name']));
		$forum = array();
		foreach ($fkeys as $k) {
			$forum[$k] = $forums[$k];
		}
		if ($forum['type'] == 'category') {
			$catedb[] = $forum;
		} elseif ($forum['type'] == 'forum') {
			$forumdb[$forum['fup']] || $forumdb[$forum['fup']] = array();
			$forumdb[$forum['fup']][] = $forum;
		} elseif ($forum['type'] == 'sub') {
			$subdb1[$forum['fup']] || $subdb1[$forum['fup']] = array();
			$subdb1[$forum['fup']][] = $forum;
		} else {
			$subdb2[$forum['fup']] || $subdb2[$forum['fup']] = array();
			$subdb2[$forum['fup']][] = $forum;
		}
	}
	$forumcache = '';
	foreach ($catedb as $cate) {
		if (!$cate) continue;
		$forum_cache[$cate['fid']] = $cate;
		$forumlist_cache[$cate['fid']]['name'] = strip_tags($cate['name']);
        $forumcache .= "<option value=\"$cate[fid]\" ".(($fid == $cate[fid]) ? "selected" : "").">&gt;&gt; {$fname[$cate[fid]]}</option>\r\n";
		if (!$forumdb[$cate['fid']]) continue;

		foreach ($forumdb[$cate['fid']] as $forum) {
			$forum_cache[$forum['fid']] = $forum;
            $forumlist_cache[$cate['fid']]['child'][$forum['fid']] = strip_tags($forum['name']);
            $forumcache .= "<option value=\"$forum[fid]\" ".(($fid == $forum[fid]) ? "selected" : "")."> &nbsp;|- {$fname[$forum[fid]]}</option>\r\n";
			if (!$subdb1[$forum['fid']]) continue;
			foreach ($subdb1[$forum['fid']] as $sub1) {
				$forum_cache[$sub1['fid']] = $sub1;
				$forumcache .= "<option value=\"$sub1[fid]\" ".(($fid == $sub1[fid]) ? "selected" : "")."> &nbsp; &nbsp;|-  {$fname[$sub1[fid]]}</option>\r\n";
				if (!$subdb2[$sub1['fid']]) continue;

				foreach ($subdb2[$sub1['fid']] as $sub2) {
					$forum_cache[$sub2['fid']] = $sub2;
					$forumcache .= "<option value=\"$sub2[fid]\" ".(($fid == $sub2[fid]) ? "selected" : "").">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$fname[$sub2[fid]]}</option>\r\n";
				}
			}
		}
	}
    return $forumcache;
}
































