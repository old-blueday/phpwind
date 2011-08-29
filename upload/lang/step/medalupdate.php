<?php
require_once ('../../global.php');
$medalService = L::loadClass('medalservice','medal');
InitGP(array('type','step'));
$offset = 500;
if (!$type) {
	$medals = array();
	$db->query("TRUNCATE TABLE pw_medal_info");
	$db->query("TRUNCATE TABLE pw_medal_award");
	$query = $db->query("SELECT * FROM pw_medalinfo");
	while ($value = $db->fetch_array($query)) {
		$temp = array();
		$temp['medal_id'] = $value['id'];
		$temp['name'] = $value['name'];
		$temp['descrip'] = $value['intro'];
		$temp['image'] = 'teshugongxian.png';
		$temp['type'] = 2;
		$medalService->addMedal($temp);
	}
	urlJump('medaluser');
} elseif ($type=='medaluser') {
	$step = (int)$step;
	$limit = $step*$offset;
	$count = 0;
	$query = $db->query("SELECT * FROM pw_medaluser ".S::sqlLimit($limit,$offset));
	$awardMedalDb = $medalService->_getMedalAwardDb();
	while ($value = $db->fetch_array($query)) {
		$count++;
		$temp = array('uid'=>$value['uid'],'medal_id'=>$value['mid'],'timestamp'=>$timestamp,'type'=>2);
		$awardMedalDb->insert($temp);
	}
	if ($count==$offset) {
		urlJump('medaluser',$step+1);
	} else {
		urlJump('medaluserlimit');
	}
} elseif ($type=='medaluserlimit') {
	$step = (int)$step;
	$limit = $step*$offset;
	$count = 0;
	$awardMedalDb = $medalService->_getMedalAwardDb();
	$query = $db->query("SELECT id,awardee,awardtime,timelimit,level FROM pw_medalslogs WHERE action='1' AND state='0' AND timelimit>0 ".S::sqlLimit($limit,$offset));
	while ($value = $db->fetch_array($query)) {
		$count++;
		$uid = $db->get_value("SELECT uid FROM pw_members WHERE username=".S::sqlEscape($value['awardee']));
		if (!$uid) continue;
		$temp = array('timestamp'=>$value['awardtime']);
		if ($value['timelimit']) $temp['deadline'] = $value['awardtime']+$value['timelimit']*2592000;
		$awardMedalDb->updateByUidAndMedalId($temp,$uid,$value['level']);
	}
	if ($count==$offset) {
		urlJump('medaluserlimit',$step+1);
	} else {
		urlJump('finish');
	}
} elseif ($type=='finish') {
	echo "转换完毕<br />";
	echo "如发现有勋章图片无法显示，请将相应的图片在images/medal/big目录和images/medal/small各放一份";
}

function urlJump($type,$step = 0) {
	echo "升级中";
	$URL = $REQUEST_URI."?type=".$type.'&step='.$step;
	echo "<script>setTimeout(\"window.location='$URL'\",300)</script>";
}