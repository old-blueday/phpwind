<?php
!defined('A_P') && exit('Forbidden');
//TODO 删除不用的请求处理分支，分离出ajax请求

$basename = 'apps.php?q='.$q.'&uid='.$uid.'&';

empty($space) && Showmsg('您访问的空间不存在!');

$a = isset($a) ? $a : 'list';
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
	//$ouserdataService = L::loadClass('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
	//$ouserDb = $ouserPrivacy = array();
	//$ouserDb = $ouserdataService->get($uid);
	//list(,$ouserPrivacy) = pwUserPrivacy($uid,$ouserDb);
	//!$ouserPrivacy['index'] &&  Showmsg('该朋友的空间设置了查看权限');
	//!$ouserPrivacy['diary'] &&  Showmsg('该朋友的日志设置了查看权限');

if ($a == 'list' && $indexRight) {
	$dtid = (int)S::getGP('dtid');//TODO 查看日志分类ID
	$diaryTypeId = $dtid == '-1' ? 0 : ( (is_numeric($dtid) && $dtid > 0) ? $dtid : null );
	
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */ //TODO 是否好友
	$friendsUids = array();
	$friendsUids = $friendsService->findFriendsByUid($winduid); //找出空间主人的好友uid List
	$is_friend = in_array($uid, $friendsUids) ? 1 : 0;
	$diaryPrivacy =  ($uid != $winduid && !$is_friend) ? array(0) : ( ($uid != $winduid) ? array(0,1) : array() );//TODO 筛选权限
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	list($diaryNums, $diaryType, $defaultTypeNum, $privacyNum) = $diaryService->getDiaryTypeMode($uid, $diaryPrivacy);
	$dtid == '-2' && $diaryPrivacy = array(2);//-2日志分类ID为自己隐私
	$count = (int)$diaryService->countUserDiarys($uid, $diaryTypeId, $diaryPrivacy);
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$diaryDb = ($count) ? $diaryService->findUserDiarysInPage($uid, $page, $db_perpage, $diaryTypeId, $diaryPrivacy) : array();
	$pages = numofpage($diaryCount,$page,ceil($count/$db_perpage),"{$basename}dtid=$dtid&");

} elseif ($a == 'detail' && $indexRight) {

	$did = (int)S::getGP('did');
	!$did && Showmsg("日志不存在");
	if($indexRight && !$newSpace->viewRight('diary')){
		Showmsg('该空间日志设置隐私，您没有权限查看!');
	}
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */	//TODO 是否好友
	$is_friend = 1;
	if ($friendsService->isFriend($winduid,$uid) !== true) $is_friend = 0;
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$diaryPrivacy =  ($uid != $winduid && !$is_friend) ? array(0) : ( ($uid != $winduid) ? array(0,1) : array() );//TODO 筛选权限
	list($diaryNums, $diaryType, $defaultTypeNum, $privacyNum) = $diaryService->getDiaryTypeMode($uid, $diaryPrivacy);//TODO 日志分类 start
		
	$diaryTemp = $diaryService->get($did);
	!$diaryTemp && Showmsg('日志不存在');
	$diaryTemp['uid'] != $winduid && $diaryTemp['privacy'] == 2 && Showmsg('该日志对方设置了隐私，没有权限查看.');
	$winduid != $uid && $diaryTemp['privacy'] == 1 && !$is_friend && Showmsg('diary_friend_right');

	$diary = $diaryService->getDiaryDbView($diaryTemp);
	$url = 'apps.php?q=diary&a=detail&uid='.$uid.'&did='.$did.'&';
	list($commentdb,$subcommentdb,$pages) = getCommentDbByTypeid('diary',$did,$page,$url);
	$comment_type = 'diary';
	$comment_typeid = $did;
	
	$siteName = getSiteName('o');
	$uSeo = USeo::getInstance();
	$uSeo->set(
		$diary['subject'] . ' - ' . $space['name'] . ' - ' . $siteName,
		'日志',
		$diary['subject'] . ',' . $siteName
	);
	
	$weiboPriv = false;
	if ($uid == $winduid) {
		$ouserdataPrivacy = $newSpace->getPrivacy();
		(!$ouserdataPrivacy['index'] && !$ouserdataPrivacy['diary'] && !$diary['privacy']) && $weiboPriv = true;
	} else {
		!$diary['privacy'] && $weiboPriv = true;
	}
	$diaryNextName=getNextOrPreDiaryName($did, $uid,'next');
	$diaryPreName=getNextOrPreDiaryName($did, $uid,'pre');
	
} elseif ($a == 'copydiary') {

	define('AJAX', 1);
	define('F_M',true);
	banUser();
	S::gp(array('did'));

	empty($did) && Showmsg('data_error');

	$dtsel = '';
	$query = $db->query("SELECT * FROM pw_diarytype WHERE uid=".S::sqlEscape($winduid)." ORDER BY dtid");
	while ($rt = $db->fetch_array($query)) {
		$dtsel .= "<option value=\"$rt[dtid]\">$rt[name]</option>";
	}
	require_once PrintEot('m_ajax');ajax_footer();

} elseif ($a == 'next') {

	define('AJAX',1);
	$did = (int)S::getGP('did');
	
	//TODO 是否好友
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	$friendsUids = array();
	$friendsUids = $friendsService->findFriendsByUid($winduid); //找出空间主人的好友uid List
	$is_friend = 0;
	in_array($uid, $friendsUids) && $is_friend = 1;
	
	//TODO 筛选权限
	$diaryPrivacy = array();	
	if ($uid != $winduid && !$is_friend) $diaryPrivacy = array(0);
	elseif ($uid != $winduid) $diaryPrivacy = array(0,1); //日志权限，
	
	
	$sqladd = "WHERE uid=".S::sqlEscape($uid)." AND did>".S::sqlEscape($did);
	$diaryPrivacy && is_array($diaryPrivacy) && $sqladd .= " AND privacy IN(".S::sqlImplode($diaryPrivacy).")";
	

	$did = $db->get_value("SELECT MIN(did) FROM pw_diary $sqladd");

	echo "success\t$did\t{$basename}a=detail&";
	ajax_footer();

} elseif ($a == 'pre') {

	define('AJAX',1);
	$did = (int)S::getGP('did');
	

	//TODO 是否好友
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	$friendsUids = array();
	$friendsUids = $friendsService->findFriendsByUid($winduid); //找出空间主人的好友uid List
	$is_friend = 0;
	in_array($uid, $friendsUids) && $is_friend = 1;
	
	//TODO 筛选权限
	$diaryPrivacy = array();	
	if ($uid != $winduid && !$is_friend) $diaryPrivacy = array(0);
	elseif ($uid != $winduid) $diaryPrivacy = array(0,1); //日志权限，
	
	$sqladd = "WHERE uid=".S::sqlEscape($uid)." AND did<".S::sqlEscape($did);
	$diaryPrivacy && is_array($diaryPrivacy) && $sqladd .= " AND privacy IN(".S::sqlImplode($diaryPrivacy).")";
	

	$did = $db->get_value("SELECT MAX(did) FROM pw_diary $sqladd");
	echo "success\t$did\t{$basename}a=detail&";
	ajax_footer();

}

require_once PrintEot('m_space_diary');
pwOutPut();