<?php
!function_exists('readover') && exit('Forbidden');

$cachefile = D_P.'data/bbscache/brith_cache.php';
if ((!file_exists($cachefile) || pwFilemtime($cachefile) <= $tdtime) && procLock('birth')) {
	list($nyear,$nmonth,$nday) = explode('-',get_date($timestamp,'Y-n-j'));
	$birthnum = 0;
	$query = $db->query("SELECT username,bday,gender FROM pw_members WHERE MONTH(bday)=".S::sqlEscape($nmonth)." AND DAYOFMONTH(bday)=".S::sqlEscape($nday)." LIMIT 200");
	while ($rt = $db->fetch_array($query)) {
		$birthnum++;
		if ($rt['gender']==1) {
			$rt['gender'] = getLangInfo('other','men');
		} elseif ($rt['gender']==2) {
			$rt['gender'] = getLangInfo('other','women');
		} else {
			$rt['gender'] = '';
		}
		$rt['username'] = S::escapeChar($rt['username']);
		$rt['age'] = $nyear - substr($rt['bday'],0,strpos($rt['bday'],'-'));
		$brithcache .= ' <span><a  target="_blank" class=" _cardshow" data-card-url="pw_ajax.php?action=smallcard&type=showcard&username='.rawurlencode($rt[username]).'" data-card-key='.$rt[username].' href="u.php?username='.rawurlencode($rt['username'])."\" title=\"$rt[username]$rt[gender]".getLangInfo('other','indexbirth',array('age'=>$rt['age']))."\">$rt[username]</a></span>";
	}
	pwCache::writeover($cachefile,"<?php\r\n\$birthnum=".pw_var_export($birthnum).";\r\n\$brithcache=".pw_var_export($brithcache).";\r\n?>");
	procUnLock('birth');
} else {
	include_once ($cachefile);
}
$db_bdayautohide && !$brithcache && $brithcache = 'empty';
?>