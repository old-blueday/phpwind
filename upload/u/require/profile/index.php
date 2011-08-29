<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P.'require/functions.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/level.php');
pwCache::getData(D_P . 'data/bbscache/level.php');
list($m_faceurl) = showfacedesign($winddb['icon'], 1, 'm');

$uinfo = $db->get_one("SELECT m.bday,m.location,m.site,m.introduce,o.index_privacy FROM pw_members m LEFT JOIN pw_ouserdata o ON m.uid=o.uid WHERE m.uid=" . S::sqlEscape($winduid));
$winddb += $uinfo;

$regdate = get_date($winddb['regdate'], 'Y-m-d');
$lastvisit = get_date($winddb['lastvisit'], 'Y-m-d');
$onlinetime = floor($winddb['onlinetime'] / 3600);
$winddb['lastpost'] < $tdtime && $winddb['todaypost'] = 0;
$averagepost = round($winddb['postnum'] / (ceil(($timestamp - $winddb['regdate'])/(3600*24))));
$friendcheck = getstatus($winddb['userstatus'], PW_USERSTATUS_CFGFRIEND, 3);


$messageServer = L::loadClass("message", 'message');
list($messageNumber,$noticeNumber,$requestNumber,$groupsmsNumber) = $messageServer->getUserStatistics($winduid);

require_once(R_P . 'require/credit.php');
$usercredit = $credit->get($winduid);

$creditdb = $usercredit + array(
	'postnum'	=> $winddb['postnum'],
	'digests'	=> $winddb['digests'],
	'onlinetime'=> $winddb['onlinetime']
);
$creditdb['rvrc'] *= 10;

$upgradeset  = unserialize($db_upgrade);
$totalcredit = CalculateCredit($creditdb, $upgradeset);

require_once PrintEot('profile_index');
footer();

function sort_cmp($a, $b) {
	if ($a['mdate'] == $b['mdate']) return 0;
    return ($a['mdate'] > $b['mdate']) ? -1 : 1;
}
?>