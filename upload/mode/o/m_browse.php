<?php
!defined('M_P') && exit('Forbidden');
$USCR = 'square_weibo';

if (!$o_browseopen) {
	ObHeader('u.php');
}
$act = S::getGP('act');

require_once(R_P . 'u/require/core.php');
require_once(R_P . 'require/showimg.php');

$thisbase = $basename;
$squareService = C::loadClass('squareservice');
require_once(M_P.'require/header.php');

switch ($act){
	case 'postlast':
		$postlast=$squareService->getLastPostUser(6);
		require_once PrintEot('m_browse_ajax');
		ajax_footer();
	break;
	case 'maxfans':
		$maxfans=$squareService->getFansDescUser(6);
		require_once PrintEot('m_browse_ajax');
		ajax_footer();
	break;
	case 'thread':
		$threadList = $squareService->getLastThread(20);
		require_once PrintEot('m_browse_ajax');
		ajax_footer();
	break;
	case 'weibo':
		$weiboList = $squareService->getWeiboLives(20);
		require_once PrintEot('m_browse_ajax');
		ajax_footer();
	break;
	default:	
		$postlast=		$squareService->getLastPostUser(6);
		$maxfans=		$squareService->getFansDescUser(6);
		$threadList =	$squareService->getLastThread(20);
	break;
}
$brandlist = $squareService->getFansBrand(10,$clTime);
$upgradelist=$squareService->getLastUpgradeUser(10);


require_once PrintEot('header');
require_once PrintEot('m_browse');
require_once(uTemplate::printEot('footer'));
pwOutPut();
?>