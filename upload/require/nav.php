<?php
!defined('P_W') && exit('Forbidden');
/**
 * 生成导航条信息
 *
 * @return array
 */
function pwNavBar() {
	global $winduid, $db_mainnav, $db_menu, $groupid, $winddb, $SCR, $db_modes, $db_mode, $defaultMode, $db_menuinit;
	global $alias;
	
	$tmpLogin = $tmpNav = array();
	if ($groupid != 'guest') {
		require_once (R_P . 'require/showimg.php');
		list($tmpLogin['faceurl']) = showfacedesign($winddb['icon'], 1, 's');
		$tmpLogin['lastlodate'] = get_date($winddb['lastvisit'], 'Y-m-d');
	} else {
		global $db_question, $db_logintype, $db_qcheck ,$db_ckquestion;
		if ($db_question) {
			//list(, $tmpLogin['qcheck'],,,$tmpLogin['showq']) = explode("\t", $db_qcheck);
			$tmpLogin['qcheck'] = $db_ckquestion & 2;
			list(,$tmpLogin['showq']) = explode("\t", $db_qcheck);
			if ($tmpLogin['qcheck'])
				$tmpLogin['qkey'] = array_rand($db_question);
		}
		if ($db_logintype) {
			for ($i = 0; $i < 3; $i++) {
				if ($db_logintype & pow(2, $i))
					$tmpLogin['logintype'][] = $i;
			}
		} else {
			$tmpLogin['logintype'][0] = 0;
		}
	}
	
	$currentPostion = array();
	$currentPostion['mode'] = $db_mode;
	if (in_array(SCR, array('index', 'cate', 'mode', 'read', 'thread')) || $SCR == 'm_home') {
		$currentPostion['mode'] = empty($db_mode) ? 'bbs' : $db_mode;
	}
	if ($currentPostion['mode'] == 'area' && $alias) $currentPostion['alias'] = $alias;

	$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
	$tmpNav[PW_NAV_TYPE_MAIN] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_MAIN, $db_mode, $currentPostion);
	$tmpNav[PW_NAV_TYPE_HEAD_LEFT] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_HEAD_LEFT, $db_mode);
	$tmpNav[PW_NAV_TYPE_HEAD_RIGHT] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_HEAD_RIGHT, $db_mode);
	$tmpNav[PW_NAV_TYPE_FOOT] = $navConfigService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_FOOT, $db_mode);

	return array($tmpNav, $tmpLogin);
}

/**
 * 生成导航html
 * 
 * @param array $navData 导航配置数据数组
 */
function buildNavLinkHtml($navData) {
	$title = strip_tags($navData['title']);
	$navData['style']['b'] && $title = "<b>$title</b>";
	$navData['style']['i'] && $title = "<i>$title</i>";
	$navData['style']['u'] && $title = "<u>$title</u>";
	$navData['style']['color'] && $title = "<font color=\"".$navData['style']['color']."\">$title</font>";

	$target = $navData['target'] ? 'target="_blank"' : '';
	return '<a id="nav_key_up_'.$navData['nid'].'" href="'.$navData['link'].'" title="'.$navData['alt'].'" '.$target.'>'.$title.'</a>';
}