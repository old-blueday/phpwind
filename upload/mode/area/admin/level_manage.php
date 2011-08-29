<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户权限管理
 * @author liuhui
 */
$levelService = L::loadclass("AreaLevel", 'area');
$baseUrl=$admin_file."?adminjob=mode&admintype=area_level_manage&";
S::gp(array('step','username','hasedit','hasattr','level','page','uid','super'));
$portalPageService = L::loadClass('portalpageservice', 'area');
$channels = $portalPageService->getPortalInvokes(0,1);
/*$channelService = L::loadclass("channelService", 'area');
$channels = $channelService->getChannelsInvokes(0,1);*/

if(empty($action)){
	list($perpage) = array(20);
	$page = (intval($page) < 1 ) ? 1 : intval($page);
	$result = $levelService->getAreaUsers($page,$perpage);
	$areaUsers = array();
	foreach($result as $user){
		$user['hasedit'] = ($user['hasedit']) == 1 ? '开启' : '关闭';
		$user['hasattr'] = ($user['hasattr']) == 1 ? '开启' : '关闭';
		$areaUsers[] = $user;
	}
	
	$count = $levelService->countAreaUser();
	$numofpage = ceil($count/$perpage);
	$pager = numofpage($count,$page,$numofpage,$baseUrl);
}elseif( "edit" == $action ){
	(!$uid) && adminmsg($levelService->language('uid_empty'));
	if($step == 2){
		if($username == ""){
			adminmsg($levelService->language('username_empty'));
		}
		$fields = array();
		$fields['hasedit'] = intval($hasedit);
		$fields['hasattr'] = intval($hasattr);
		$fields['super'] = intval($super);
		$fields['level'] = ($super == 1) ? '' : $level;
		list($bool,$message) = $levelService->updateAreaUserByUserName($fields,$username);
		adminmsg($message);
	}
	$userLevel = $levelService->getAreaUser($uid);
	(!$userLevel) && adminmsg($levelService->language('userlevel_not_exist'));
	list($hasEditCheck,$hasAttrCheck,$superCheck) = array(buildCheck($userLevel['hasedit']),buildCheck($userLevel['hasattr']),(($userLevel['super'] == 1) ? "checked=checked" : ""));
	$level = isset($userLevel['level']) ? unserialize($userLevel['level']) : '';
	$disable = "readonly";
	$haystack = array('name'=>"编辑","action"=>"edit");
}elseif("add" == $action ){
	if($step == 2){
		if($username == ""){
			adminmsg($levelService->language('username_empty'));
		}
		$fields = array();
		$fields['username'] = $username;
		$fields['hasedit'] = intval($hasedit);
		$fields['hasattr'] = intval($hasattr);
		$fields['super'] = intval($super);
		$fields['level'] = ($super == 1) ? '' : $level;
		list($bool,$message) = $levelService->addAreaUsers($fields);
		adminmsg($message);
	}
	list($hasEditCheck,$hasAttrCheck,$superCheck,$level,$disable) = array(array(1=>"",0=>"checked=checked"),array(1=>"",0=>"checked=checked"),"","","");
	$haystack = array('name'=>"增加","action"=>"add");
}elseif("delete" == $action ){
	(!$uid) && adminmsg($levelService->language('uid_empty'));
	list($bool,$message) = $levelService->deleteAreaUser($uid);
	adminmsg($message);
}elseif("find" == $action ){
	(!$username) && adminmsg($levelService->language('username_empty'));
	list($bool,$message,$areaUser) = $levelService->getAreaUserByUserName($username);
	$areaUsers = array();
	if ($areaUser) {
		$areaUser['hasedit'] = ($areaUser['hasedit']) == 1 ? '开启' : '关闭';
		$areaUser['hasattr'] = ($areaUser['hasattr']) == 1 ? '开启' : '关闭';
		$areaUsers[] = $areaUser;
	}
}
include PrintMode('level_manage');exit;

function buildCheck($v){
	$check = array();
	$check[1] = ($v == 1) ? "checked=checked" : "";
	$check[0] = ($v == 0) ? "checked=checked" : "";
	return $check;
}
function buildChannel($super,$channels){
	if($super == 1){
		return '所有';
	}
	if(!$channels){
		return '无';
	}
	$channels = (is_array($channels)) ? $channels : unserialize($channels);
	$result = array();
	foreach($channels as $channel){
		$result[] = $channel['name'];
	}
	return implode(" ",$result);
}




















