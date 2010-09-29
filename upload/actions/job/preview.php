<?php
!defined('P_W') && exit('Forbidden');

require_once (R_P . 'require/bbscode.php');

InitGP(array(
	'pcid',
	'modelid'
), 'P', 2);

$fielddb = array();
$data = array();
$atc_content = Char_cv(stripslashes(GetGP('atc_content', 'P')));
$pcinfo = Char_cv(stripslashes(GetGP('pcinfo', 'P')));

if ($modelid > 0) {
	$query = $db->query("SELECT fieldid,fieldname FROM pw_topicfield WHERE modelid=" . pwEscape($modelid));
	while ($rt = $db->fetch_array($query)) {
		$fielddb[$rt['fieldid']] = $rt['fieldname'];
	}
	
	$pcdb = getPcviewdata($pcinfo, 'topic');
	L::loadClass('posttopic', 'forum', false);
	$postTopic = new postTopic($data);
	$topicvalue = $postTopic->getTopicvalue($modelid, $pcdb);

} elseif ($pcid > 0) {
	$query = $db->query("SELECT fieldid,fieldname FROM pw_pcfield WHERE pcid=" . pwEscape($pcid));
	while ($rt = $db->fetch_array($query)) {
		$fielddb[$rt['fieldname']] = $rt['fieldid'];
	}
	
	$pcdb = getPcviewdata($pcinfo, 'postcate');
	L::loadClass('postcate', 'forum', false);
	$postCate = new postCate($data);
	list(, $topicvalue) = $postCate->getCatevalue($pcid, $pcdb);
}
$atc_content = wordsConvert($atc_content);
$atc_content = convert($atc_content, $db_windpost);
$preatc = str_replace("\n", "<br>", $atc_content);

require_once (R_P . 'require/header.php');
require_once PrintEot('preview');
footer();
