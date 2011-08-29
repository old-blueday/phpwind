<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'page',
	'tid',
	'jointype',
	'payway',
	'ifend',
	'pcid'
));

$isadminright = $jointype == 3 ? 0 : 1;
L::loadClass('postcate', 'forum', false);
$postCate = new postCate($data);
list(, $isviewright) = $postCate->getViewright($pcid, $tid);

$memberdb = array();
$count = $sum = $paysum = 0;
$query = $db->query("SELECT ifpay,nums FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid));
while ($rt = $db->fetch_array($query)) {
	$count++;
	if ($rt['ifpay']) {
		$paysum += $rt['nums'];
	}
	$sum += $rt['nums'];
}

$page < 1 && $page = 1;
$numofpage = ceil($count / $db_perpage);
if ($numofpage && $page > $numofpage) {
	$page = $numofpage;
}
$start = ($page - 1) * $db_perpage;
$limit = S::sqlLimit($start, $db_perpage);
$pages = numofpage($count, $page, $numofpage, "pw_ajax.php?action=$action&tid=$tid&jointype=$jointype&payway=$payway&", null, 'ajaxview');

$i = $pcid = 0;
$query = $db->query("SELECT pcmid,uid,pcid,username,nums,totalcash,phone,mobile,address,extra,ifpay,jointime FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid) . " ORDER BY (uid=" . S::sqlEscape($winduid) . ") DESC,ifpay ASC,pcmid DESC $limit");
while ($rt = $db->fetch_array($query)) {
	if ($i == 0) {
		$pcid = $rt['pcid'];
	}
	$i++;
	$memberdb[] = $rt;
}

require_once PrintEot('ajax');
ajax_footer();
