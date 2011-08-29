<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'tid',
	'pcid'
), G, 2);
$read = $db->get_one("SELECT authorid,subject,fid FROM pw_threads WHERE tid=" . S::sqlEscape($tid));
$foruminfo = $db->get_one('SELECT forumadmin,fupadmin FROM pw_forums WHERE fid=' . S::sqlEscape($read['fid']));
$isGM = S::inArray($windid, $manager);
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
L::loadClass('postcate', 'forum', false);
$post = array();
$postCate = new postCate($post);
$isadminright = $postCate->getAdminright($pcid, $read['authorid']);

if (!$isadminright) {
	Showmsg('pcexport_none');
}

$memberdb = array();
$query = $db->query("SELECT username,mobile,phone,address,nums,ifpay,totalcash,name,zip,message FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid));
while ($rt = $db->fetch_array($query)) {
	if ($rt['ifpay'] == 1) {
		$rt['ifpay'] = getLangInfo('other', 'pc_payed');
	} else {
		$rt['ifpay'] = getLangInfo('other', 'pc_paying');
	}
	if ($db_charset == 'utf-8' || $db_charset == 'big5') {
		foreach ($rt as $key => $value) {
			$rt[$key] = pwConvert($value, 'gbk', $db_charset);
		}
	}
	$memberdb[] = $rt;
}

$titledb = array(
	getLangInfo('other', 'pc_id') . "\t",
	getLangInfo('other', 'pc_username') . "\t",
	getLangInfo('other', 'pc_name') . "\t",
	getLangInfo('other', 'pc_mobile') . "\t",
	getLangInfo('other', 'pc_phone') . "\t",
	getLangInfo('other', 'pc_address') . "\t",
	getLangInfo('other', 'pc_zip') . "\t",
	getLangInfo('other', 'pc_nums') . "\t",
	getLangInfo('other', 'pc_totalcash') . "\t",
	getLangInfo('other', 'pc_message') . "\t",
	getLangInfo('other', 'pc_ifpay') . "\t\n"
);

header("Content-type:application/vnd.ms-excel");
header("Content-Disposition:attachment;filename=$read[subject].xls");
header("Pragma: no-cache");
header("Expires: 0");

foreach ($titledb as $key => $value) {
	
	if ($db_charset == 'utf-8' || $db_charset == 'big5') {
		$value = pwConvert($value, 'gbk', $db_charset);
	}
	echo $value;

}

$i = 0;
foreach ($memberdb as $val) {
	$i++;
	$val['message'] = str_replace("\n", "", $val['message']);
	echo "$i\t";
	echo "$val[username]\t";
	echo "$val[name]\t";
	echo "$val[mobile]\t";
	echo "$val[phone]\t";
	echo "$val[address]\t";
	echo "$val[zip]\t";
	echo "$val[nums]\t";
	echo "$val[totalcash]\t";
	echo "$val[message]\t";
	echo "$val[ifpay]\t\n";
}
exit();
