<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=plantodo";
if(!$action){
	$plandb= array();
	$query = $db->query("SELECT id,subject,month,week,day,hour,usetime,nexttime,ifopen FROM pw_plan ORDER BY id");
	while($rt = $db->fetch_array($query)){
		$rt['usetime']  = $rt['usetime'] ? get_date($rt['usetime'],'Y-m-d H:i') : '-';
		$rt['nexttime'] = $rt['ifopen'] ? get_date($rt['nexttime'],'Y-m-d H:i') : 'Closed';
		if ($rt['month'] != '*') {
			$rt['todo'] = 'm';
		} elseif ($rt['week'] != '*') {
			$rt['todo'] = 'w';
		} elseif ($rt['day'] != '*') {
			$rt['todo'] = 'd';
		} else {
			$rt['todo'] = 'h';
		}

		$plandb[] = $rt;
	}
	include PrintEot('plantodo');exit;
} elseif($_POST['action']=="unsubmit"){
	S::gp(array('selid'),'P');
	!$selid && adminmsg("operate_error");
	$selids = '';
	foreach($selid as $key=>$value){
		if(is_numeric($value) && $value>7){
			$selids .= $selids ? ','.$value : $value;
		}
	}
	$selids && $db->update("DELETE FROM pw_plan WHERE id IN($selids)");
	adminmsg("operate_success");
} elseif($action=='planset'){
	S::gp(array('id'));
	!$id && adminmsg('operate_error');
	if($_POST['step']!=3){
		$rt		= $db->get_one("SELECT * FROM pw_plan WHERE id=".S::sqlEscape($id));
		$month	= str_replace("<option value=\"$rt[month]\">","<option value=\"$rt[month]\" selected=\"SELECTED\">",makeoption(1,31));
		$day	= str_replace("<option value=\"$rt[day]\">","<option value=\"$rt[day]\" selected=\"SELECTED\">",makeoption(0,23));
		$hour	= makeoption(0,59);
		$hourdb	= explode(',',$rt['hour']);
		$hours	= array();
		$hours[0]=$hours[1]=$hours[2]=$hours[3]=$hour;
		foreach($hourdb as $key=>$val){
			$hours[$key]=str_replace("<option value=\"$val\">","<option value=\"$val\" selected=\"SELECTED\">",$hour);
		}
		if($rt['week']!='*'){
			for($i=1;$i<8;$i++){
				${'week_'.$i}='';
			}
			${'week_'.$rt['week']} = 'selected="SELECTED"';
		}

		if ($rt['month'] != '*') {
			$todo = 'm';
		} elseif ($rt['week'] != '*') {
			$todo = 'w';
		} elseif ($rt['day'] != '*') {
			$todo = 'd';
		} else {
			$todo = 'h';
		}
		${'todo_'.$todo} = 'CHECKED';
		ifcheck($rt['ifopen'],'ifopen');
		include PrintEot('plantodo');exit;
	} else {
		S::gp(array('title','month','week','day','hours','ifopen','filename'),'P');
		if(is_numeric($month)){
			$month = $month<1 ? 1 : ($month>31 ? 31 : $month);
			$week  = '*';
		} elseif(is_numeric($week)){
			$week  = $week<1 ? 1 : ($week>7 ? 7 : $week);
			$month = '*';
		} else{
			$month = $week = '*';
		}
		if(is_numeric($day)){
			$day = $day<0 ? 0 : ($day>23 ? 23 : $day);
		} else{
			$day = '*';
		}
		if(is_array($hours)){
			$hours = array_unique($hours);
			asort($hours);
			$hour_w=$ex='';
			foreach($hours as $key=>$hour){
				if(is_numeric($hour)){
					$hour_t = $hour<0 ? 0 : ($hour>59 ? 59 : $hour);
					$hour_w .= $ex.$hour_t;
					$ex=',';
				}
			}
			$hour_w=='' && $hour_w='*';
		} else{
			$hour_w='*';
		}
		if($month=='*' && $week=='*' && $day=='*' && $hour_w=='*' && $ifopen==1){
			adminmsg('time_error');
		}
		strpos($filename,'..')!==false && adminmsg("undefined_action");
		$plan = array(
			'month'=>$month,
			'week'=>$week,
			'day'=>$day,
			'hour'=>$hour_w,
			'usetime'=>'0',
			'ifopen'=>$ifopen
		);
		$nexttime = nexttime($plan);
		$db->update("UPDATE pw_plan"
			. " SET " . S::sqlSingle(array(
					'subject'	=> $title,
					'month'		=> $month,
					'week'		=> $week,
					'day'		=> $day,
					'hour'		=> $hour_w,
					'nexttime'	=> $nexttime,
					'ifopen'	=> $ifopen,
					'filename'	=> $filename
				))
			. " WHERE id=".S::sqlEscape($id));
		updatecache_plan();
		adminmsg("operate_success");
	}
} elseif($action=='detail'){
	S::gp(array('id'));
	$rt = $db->get_one("SELECT * FROM pw_plan WHERE id=".S::sqlEscape($id));
	!$rt && adminmsg('operate_error');
	$filename = $rt['filename'];
	if(file_exists(R_P.'require/plan/'.$filename.'_set.php')){
		require_once S::escapePath(R_P.'require/plan/'.$filename.'_set.php');
		include PrintEot('plantodo');exit;
	} else{
		adminmsg('operate_error');
	}
} elseif ($action == 'add') {
	S::gp(array('step'));
	if(!$step){
		$month = makeoption(1,31);
		$day   = makeoption(0,23);
		$hour  = makeoption(0,59);
		include PrintEot('plantodo');exit;
	} elseif($step == '2'){
		S::gp(array('title','month','week','day','hours','ifopen','filename'),'P');
		if (!$title) {
			$basename = "javascript:history.go(-1)";
			adminmsg('operate_error');
		}
		if(is_numeric($month)){
			$month = $month<1 ? 1 : ($month>31 ? 31 : $month);
			$week  = '*';
		} elseif(is_numeric($week)){
			$week  = $week<1 ? 1 : ($week>7 ? 7 : $week);
			$month = '*';
		} else{
			$month = $week = '*';
		}
		if(is_numeric($day)){
			$day = $day<0 ? 0 : ($day>23 ? 23 : $day);
		} else{
			$day = '*';
		}
		if(is_array($hours)){
			$hours  = array_unique($hours);
			$hour_w = '';
			foreach($hours as $key=>$hour){
				is_numeric($hour) && $hour_t = $hour<0 ? 0 : ($hour>59 ? 59 : $hour);
				$hour_w .= $hour_w ? ','.$hour_t : $hour_t;
			}
			!$hour_w && $hour_w='*';
		} else{
			$hour_w = '*';
		}
		if($month=='*' && $week=='*' && $day=='*' && $hour_w=='*' && $ifopen==1){
			$basename = "javascript:history.go(-1)";
			adminmsg('time_error');
		}
		$plan  = array(
			'month'=>$month,
			'week'=>$week,
			'day'=>$day,
			'hour'=>$hour_w,
			'usetime'=>'0',
			'ifopen'=>$ifopen
		);
		$nexttime = nexttime($plan);
		if(strpos($filename,'..')!==false)adminmsg("undefined_action");
		$db->update("INSERT INTO pw_plan"
			. " SET " . S::sqlSingle(array(
				'subject'	=> $title,	'month'		=> $month,
				'week'		=> $week,	'day'		=> $day,
				'hour'		=> $hour_w,	'nexttime'	=> $nexttime,
				'ifsave'	=> 0,		'ifopen'	=> $ifopen,
				'filename'	=> $filename
		)));
		updatecache_plan();
		adminmsg("operate_success");
	}
}

function makeoption($start,$end){
	$option="<option value=\"*\">*</option>";
	for($i=$start;$i<=$end;$i++){
		$option.="<option value=\"$i\">$i</option>";
	}
	return $option;
}
function nexttime($plan) {
	if($plan['ifopen']==0) return 0;
	global $timestamp,$db_timedf;

	$t		= gmdate('G',$timestamp+$db_timedf*3600);
	$timenow= (int)(floor($timestamp/3600)-$t)*3600;
	$minute = (int)get_date($timestamp,'i');
	$hour   = get_date($timestamp,'G');
	$day    = get_date($timestamp,'j');
	$month  = get_date($timestamp,'n');
	$year   = get_date($timestamp,'Y');
	$week   = get_date($timestamp,'w');
	$week==0 && $week=7;

	if (is_numeric($plan['month'])) {
		$timenow += (min($plan['month'],DaysInMouth($month))-$day)*86400;
	} elseif (is_numeric($plan['week'])) {
		$timenow += ($plan['week']-$week)*86400;
	}
	if (is_numeric($plan['day'])) {
		$timenow += $plan['day']*3600;
	}
	if ($plan['hour']!='*') {
		$hours = explode(',',$plan['hour']);
		asort($hours);
		if (is_numeric($plan['month']) || is_numeric($plan['week']) || is_numeric($plan['day'])) {
			foreach ($hours as $key=>$value) {
				if ($timenow + $value*60 > $timestamp) {
					$timenow += $value*60;
					return $timenow;
				}
			}
			$timenow += $hours[0]*60;
		} else {
			$timenow += $hour*3600;
			for ($i=0;$i<2;$i++) {
				foreach ($hours as $key=>$value) {
					if ($timenow + $value*60 > $timestamp) {
						$timenow +=$value*60;
						return $timenow;
					}
				}
				$timenow += 3600;
			}
			return $timenow+$hours['0'];
		}
	} elseif ($timenow > $timestamp) {
		return $timenow;
	}
	if (is_numeric($plan['month'])) {
		$timenow -= ((min($plan['month'],DaysInMouth($month))) - DaysInMouth($month)-min($plan['month'],DaysInMouth($month+1)))*86400;
	} elseif (is_numeric($plan['week'])) {
		$timenow += 604800;
	} elseif (is_numeric($plan['day'])) {
		$timenow += 86400;
	}
	if ($timenow > $timestamp) {
		return $timenow;
	}
	return $timestamp+86400;
}
function DaysInMouth($month) {
	if (in_array($month,array('1','3','5','7','8','10','12','13'))) {
		$days = 31;
	} elseif ($month!=2) {
		$days = 30;
	} else {
		if (get_date($GLOBALS['timestamp'],'L')) {
			$days = 29;
		} else {
			$days = 28;
		}
	}
	return $days;
}
?>