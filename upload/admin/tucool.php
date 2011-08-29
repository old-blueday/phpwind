<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = $baseurl = "$admin_file?adminjob=tucool";
/*
pwCache::getData(D_P.'data/bbscache/forumcache.php');
require_once(R_P.'require/updateforum.php');
list($hidefid,$hideforum) = GetHiddenForum();
$forumcache .= $hideforum;	
*/
$forumService = L::loadClass('forums','forum');
$tucoolForums = $forumService->getTucoolForums();
$tucoolForumsHtml = getTucoolForumsHtml($tucoolForums);

S::gp(array('action'));
if(empty($action)){
	S::gp(array('starttime','endtime','fids'));
	$starttime = $starttime ? $starttime : get_date(PwStrtoTime('-1 month'),'Y-m-d');
	$endtime = $endtime ? $endtime :  get_date($timestamp,'Y-m-d');
	include PrintEot('tucool');
	
}elseif($action == 'process'){
	@set_time_limit(300);
	S::gp(array('starttime','endtime','fids','step','totalNums','offset','haveBuild'));
	if (!$fids && !$step) {
		adminmsg('请先选择需要生成的图酷版块',$basename);
	}
	$startTime = $starttime && !is_numeric($starttime) ? PwStrtoTime($starttime) : $starttime;
	$endTime = $endtime && !is_numeric($endtime) ? PwStrtoTime($endtime) : $endtime;
	if(!$starttime || !$endtime || $startTime > $endTime){
		adminmsg('时间范围输入有误',$basename);
	}
	$stepSize = 2;
	if(!$step){
		$step = $offset = 0;
		$endTime = $endTime + 86400;
		foreach($fids as $fid){
			$fid = intval($fid);
			if($fid < 1) continue;
		}
	}else{
		$fids = trim($fids);
		$fids = explode(',',$fids);
		if(!$fids) include PrintEot('tucool');
	}

	$attachsService = L::loadClass('Attachs','forum');
	$tuCoolService = L::loadClass('Tucool','forum');	
	$totalNums = $totalNums ? intval($totalNums) : $attachsService->countTuCoolThreadNum(getSelectedTucoolForums(),$startTime,$endTime);
	$haveBuild = $haveBuild ? intval($haveBuild) : 0;

 	foreach($fids as $fid){
 		$fid = intval($fid);
		$tids = $attachsService->getTuCool($fid,$tucoolForums[$fid]['tucoolpic'],$startTime,$endTime,$offset,$stepSize);
		$offset += $stepSize;
		$count = count($tids);
		
		if($count < $stepSize){
			array_shift($fids);
			//更新不满足条件的图酷帖
			$tuCoolService->renewToolThreads($fid);
			if(count($fids) > 1) $offset = 0;
			$haveBuild = $haveBuild + $count;
		}else{
			$haveBuild = $haveBuild + $stepSize;
		}
 		foreach($tids as $tid){
			$tid = intval($tid);
			if($tid < 1) continue;
			$db_ifftp or $attachsService->reBuildAttachs($tid);
			$tuCoolService->updateTucoolImageNum($tid);
 		}
 		if(!$fids || $totalNums){
			$step++;
			if(!$fids) adminmsg("数据更新完成",$basename);
			$fids = trim(implode(",",$fids));
			$jumpUrl = EncodeUrl("$basename&action=$action&totalNums=$totalNums&step=$step&fids=$fids&starttime=$startTime&endtime=$endTime&offset=$offset&haveBuild=$haveBuild");
			include PrintEot('tucool');
		}
 	}
 	include PrintEot('tucool');
}

function getTucoolForumsHtml($tucoolForums){
	$html = '';
	if(S::isArray($tucoolForums)) {
		foreach ($tucoolForums as $k=>$v) {
			$html .= '<option value="'.$k.'">'.$v['name'].'</option>';
		}
	}
	return $html;
}

function getSelectedTucoolForums(){
	global $tucoolForums,$fids;
	$selectedForums = array();
	foreach($fids as $fid){
		$selectedForums[$fid] = $tucoolForums[$fid];
	}
	return $selectedForums;
}
?>