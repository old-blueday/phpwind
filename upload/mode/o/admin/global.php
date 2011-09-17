<?php
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
//* @include_once pwCache::getPath(D_P.'data/bbscache/config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/config.php');

S::gp( array('action') );
S::gp( array('step'), 'P' );

!$action && $action = 'setucenter';
if ( $action == 'setucenter' ) {
	if( ! $step ){
		ifcheck($o_browseopen,'browseopen');
		ifcheck($db_userurlopen,'userurlopen');
		
		//每日打卡功能  开始
		ifcheck($o_punchopen,'punchopen');
		$jobService = L::loadclass("job", 'job');
		$reward = ($o_punch_reward) ? unserialize($o_punch_reward) : array();
		$creditSelect = $jobService->getCreditSelect($reward['type'],'reward[credit][type]','reward_credit');
		$usergroups = ($o_punch_usergroup) ? explode(",",$o_punch_usergroup) : array();
		$levelCheckBox = $jobService->getLevelCheckbox($usergroups);
		//每日打卡功能  结束
		
		require_once PrintMode('global');
	}else if($step == '2'){
		S::gp(array('reward','usergroup'));
		S::gp(array('config','configa'),'P',2);
		$reward['credit']['min'] = intval($reward['credit']['min']);
		$reward['credit']['max'] = intval($reward['credit']['max']);
		$reward['credit']['step'] = intval($reward['credit']['step']);
		$config['cachetimemin']	=intval($config['cachetimemin']);
		$config['cachetimemax']	=intval($config['cachetimemax']);
		if ($config['punchopen'] && ($reward['credit']['min'] < 1 || $reward['credit']['max'] < 1)) {
			adminmsg('打卡奖励设置有误!');
		}
		
		//每日递增步长
		if ($reward['credit']['step'] < 1 || $reward['credit']['step'] > $reward['credit']['max'] - $reward['credit']['min']) {
			$reward['credit']['step'] = 1;
		}
		
		//每日打卡功能  结束
		if($config['punchopen'] == 0) $config['punchopen'] = "0";
		$jobService = L::loadclass("job", 'job');
		$t_reward = array();
		foreach($reward['credit'] as $k =>$v){
			$t_reward[$k] = $v;
		}
		$t_reward['category'] = 'credit';
		$t_reward['information'] = $jobService->buildCategoryInfo($t_reward);
		$config['punch_reward'] = serialize($t_reward);
		$config['punch_usergroup'] = ($usergroup) ? implode( ",", $usergroup) : '';
		//每日打卡功能 结束
		
		if($config['browseopen'] == 0) $config['browseopen'] = "0";
		if($configa['userurlopen'] == 0) $configa['userurlopen'] = "0";
		//url静态 设置到全局
		setConfig('db_userurlopen', $configa['userurlopen'], null, false);
		updatecache_c();
		updatecache_openforum();
		if ($config['browseopen']!='0'){
			updateLastPostUser();
			updateFansSort();
		}
		setoParams( $config );
		adminmsg('operate_success');
	}

} else if( $action == 'topnav' ){

	require_once PrintMode('global');
/*}else if( $action == 'commend' ){

	if( ! $step ){
		ifcheck($o_ifcommend,'ifcommend');
		ifcheck($o_commendtype,'commendtype');
		require_once PrintMode('global');
	}else if($step == '2'){
		S::gp(array('config'),'P');
		
		require_once D_P . 'require/showimg.php';
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService 
		$UserInfo = $userService->get(intval($config['senderid']));
		$face = showfacedesign($UserInfo['icon'], true, 'm');
		$config['sender_face'] = $face[0];
		$config['sender_uid'] = $UserInfo['uid'];
		$config['sender_username'] = $UserInfo['username'];
		setoParams( $config );
		$updatecache && updatecache_conf('o',true);
		adminmsg('operate_success', $basename . '&action=' . $action);
	}*/

}

function setoParams( $config ){
	global $db;
	$updatecache = false;
	foreach ($config as $key => $value) {
		$db->pw_update(
			'SELECT hk_name FROM pw_hack WHERE hk_name=' . S::sqlEscape("o_$key"),
			'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $value, 'vtype' => 'string')) . ' WHERE hk_name=' . S::sqlEscape("o_$key"),
			'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_$key", 'vtype' => 'string', 'hk_value' => $value))
		);
		$updatecache = true;
	}
	$updatecache && updatecache_conf('o',true);
}
function updateLastPostUser(){
	global $db,$timestamp;
	L::loadClass('getinfo', '', false);
	$getinfo =& GetInfo::getInstance();
	$userId=$userIds=array();
	$db->update("DELETE FROM pw_elements WHERE type='lastpostuser' ");
	$userIds = $getinfo->getLastPostUser(100);
	if (!S::isArray($userIds))return false;
	foreach ($userIds as $key => $value) {
		$userId[$key]['id'] = $value;
		$userId[$key]['value'] =$timestamp;
		$userId[$key]['type'] ="lastpostuser";
		$userId[$key]['mark'] ="tid";
	}
	if ($userId) {
		$sql = "REPLACE INTO pw_elements(id,value,type,mark) VALUES ".S::sqlMulti($userId,true);
		$db->update($sql);
	}
	return true;
}
function updateFansSort(){
	global $db;
	L::loadClass('getinfo', '', false);
	$getinfo =& GetInfo::getInstance();
	$userId=$userIds=array();
	$db->update("DELETE FROM pw_elements WHERE type='totalfans' ");
	$userIds = $getinfo->getTotalFansSort(100);
	if (!S::isArray($userIds))return false;
	foreach ($userIds as $key => $value) {
		$userId[$key]['id'] = $value['uid'];
		$userId[$key]['value'] =$value['fans'];
		$userId[$key]['type'] ="totalfans";
		$userId[$key]['mark'] ="fans";
	}
	if ($userId) {
		$sql = "REPLACE INTO pw_elements(id,value,type,mark) VALUES ".S::sqlMulti($userId,true);
		$db->update($sql);
	}
	return true;
}
?>