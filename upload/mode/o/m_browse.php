<?php
!defined('M_P') && exit('Forbidden');
$USCR = 'square_weibo';

if (!$o_browseopen) {
	ObHeader('u.php');
}

$tab = S::getGP('tab');
require_once(R_P . 'u/require/core.php');
require_once(R_P . 'require/showimg.php');

$thisbase = $basename;
//$basename = $basename.'space=1&';

$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
//$weiboList = $weiboService->getWeibos(1,20);
$weiboHotTransmit = $weiboService->getHotTransmit(20);
$weiboHotComment = $weiboService->getHotComment(20);
require_once(M_P.'require/header.php');
require_once PrintEot('m_browse');
require_once(uTemplate::printEot('footer'));
pwOutPut();
//footer();

?>