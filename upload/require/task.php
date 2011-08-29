<?php
!function_exists('readover') && exit('Forbidden');
@set_time_limit(600);
@ignore_user_abort(TRUE);

$query = $db->query("SELECT * FROM pw_plan WHERE ifopen='1' AND nexttime<".S::sqlEscape($timestamp));
while ($plan = $db->fetch_array($query)) {
	if (file_exists(R_P.'require/plan/'.$plan['filename'].'.php')) {
		$nexttime = nexttime($plan);
		require_once S::escapePath(R_P.'require/plan/'.$plan['filename'].'.php');
		$db->update("UPDATE pw_plan SET".S::sqlSingle(array('usetime' => $timestamp, 'nexttime' => $nexttime),false)."WHERE id=".S::sqlEscape($plan['id'],false));
	}
}
$db->free_result($query);
unset($plan);

require_once(R_P.'admin/cache.php');
updatecache_plan();

function nexttime($plan) {
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