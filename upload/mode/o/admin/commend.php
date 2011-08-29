<?php 
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
//* @include_once pwCache::getPath(D_P.'data/bbscache/config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/config.php');

S::gp( array('step'), 'P' );

if(!$step ){
	ifcheck($o_ifcommend,'ifcommend');
	ifcheck($o_commendtype,'commendtype');
	require_once PrintMode('commend');
}else if($step == '2'){
	S::gp(array('config'),'P');
	
	require_once D_P . 'require/showimg.php';
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$UserInfo = $userService->get(intval($config['senderid']));
	$face = showfacedesign($UserInfo['icon'], true, 'm');
	$config['sender_face'] = $face[0];
	$config['sender_uid'] = $UserInfo['uid'];
	$config['sender_username'] = $UserInfo['username'];
	setoParams( $config );
	$updatecache && updatecache_conf('o',true);
	adminmsg('operate_success');
}

function setoParams( $config ){
	global $db;
	$updatecache = false;
	foreach ($config as $key => $value) {
		if (${'o_'.$key} != $value) {
			$db->pw_update(
				'SELECT hk_name FROM pw_hack WHERE hk_name=' . S::sqlEscape("o_$key"),
				'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $value, 'vtype' => 'string')) . ' WHERE hk_name=' . S::sqlEscape("o_$key"),
				'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_$key", 'vtype' => 'string', 'hk_value' => $value))
			);
			$updatecache = true;
		}
	}
	$updatecache && updatecache_conf('o',true);
}
?>