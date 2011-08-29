<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('t'));

if (empty($t)) {

	if (empty($_POST['step'])) {
		
		$privacyCurrent = ' class="current"';
		$weiboCurrent = '';
		$userdb = $db->get_one("SELECT index_privacy,profile_privacy,info_privacy,credit_privacy,owrite_privacy,msgboard_privacy FROM pw_ouserdata WHERE uid=" . S::sqlEscape($winduid));
		${'index_'.$userdb['index_privacy']} = 'selected="selected"';
		${'msgboard_'.$userdb['msgboard_privacy']} = 'selected="selected"';
		${'friend_'.getstatus($winddb['userstatus'], PW_USERSTATUS_CFGFRIEND, 3)} = 'checked';
		
		$attentionService = L::loadClass('attention', 'friend');
		$blackList = $attentionService->getNamesOfBlackList($winduid);
		$names = implode(',', $blackList);

		require_once uTemplate::printEot('profile_privacy');
		pwOutPut();

	} else {

		PostCheck();
		S::gp(array('privacy', 'friendcheck'), 'P', 2);
		S::gp(array('attentionblacklist'));

		$pwSQL = array('uid' => $winduid);
		$pwSQL['index_privacy'] = $privacy['index'] < 0 || $privacy['index'] > 2 ? 0 : $privacy['index'];
		$pwSQL['msgboard_privacy'] = $privacy['msgboard'] < 0 || $privacy['msgboard'] > 2 ? 0 : $privacy['msgboard'];
		$db->pw_update(
			"SELECT uid FROM pw_ouserdata WHERE uid=" . S::sqlEscape($winduid),
			"UPDATE pw_ouserdata SET " . S::sqlSingle($pwSQL) . " WHERE uid=" . S::sqlEscape($winduid),
			"INSERT INTO pw_ouserdata SET " . S::sqlSingle($pwSQL)
		);
		if ($friendcheck != getstatus($winddb['userstatus'], PW_USERSTATUS_CFGFRIEND, 3)) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			switch ($friendcheck) {
				case 1:
					$userService->setUserStatus($winduid, PW_USERSTATUS_CFGFRIEND, 1);
					break;
				case 2:
					$userService->setUserStatus($winduid, PW_USERSTATUS_CFGFRIEND, 2);
					break;
				default:
					$userService->setUserStatus($winduid, PW_USERSTATUS_CFGFRIEND, 0);
			}
		}
		$uids = array();
		if ($attentionblacklist) {
			$userService = L::loadClass('UserService', 'user');
			$user = $userService->getByUserNames(explode(',', $attentionblacklist));
			$uids = array();
			foreach ($user as $key => $value) {
				$uids[] = $value['uid'];
			}
		}
		$attentionService = L::loadClass('attention', 'friend');
		$blacklist = $attentionService->setBlackList($winduid, $uids);

		refreshto('profile.php?action=privacy','operate_success');
	}
} elseif ($t == 'weibo') {

	if (empty($_POST['step'])) {
		$privacyCurrent = '';
		$weiboCurrent = ' class="current"';
		$userdb = $db->get_one("SELECT at_isfeed,article_isfeed,diary_isfeed,photos_isfeed,group_isfeed,self_isfollow,friend_isfollow,cnlesp_isfollow, article_isfollow,diary_isfollow, photos_isfollow, group_isfollow".$appendFetchField." FROM pw_ouserdata WHERE uid=" . S::sqlEscape($winduid));
		if (!$userdb) {
			$userdb = array(
				'article_isfeed' => 1,
				'diary_isfeed' => 1,
				'photos_isfeed' => 1,
				'group_isfeed' => 1,

				'self_isfollow' => 1,
				'friend_isfollow' => 1,
				'cnlesp_isfollow' => 1,

				'article_isfollow' => 1,
				'diary_isfollow' => 1,
				'photos_isfollow' => 1,
				'group_isfollow' => 1
			);
		}

		ifchecked('article_isfeed', $userdb['article_isfeed']);
		ifchecked('diary_isfeed', $userdb['diary_isfeed']);
		ifchecked('photos_isfeed', $userdb['photos_isfeed']);
		ifchecked('group_isfeed', $userdb['group_isfeed']);

		ifchecked('self_isfollow', $userdb['self_isfollow']);
		ifchecked('friend_isfollow', $userdb['friend_isfollow']);
		ifchecked('cnlesp_isfollow', $userdb['cnlesp_isfollow']);

		ifchecked('article_isfollow', $userdb['article_isfollow']);
		ifchecked('diary_isfollow', $userdb['diary_isfollow']);
		ifchecked('photos_isfollow', $userdb['photos_isfollow']);
		ifchecked('group_isfollow', $userdb['group_isfollow']);
		$at_isfeed = $userdb['at_isfeed'];
		
		/* platform weibo app */
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen()) {
			$bindTypes = array();
			foreach ($siteBindService->getBindTypes() as $key => $config) {
				$bindTypes[$key . '_isfollow'] = $config['title'];
			}
		}
		$isSiteBindWeibo = $siteBindService->isOpen();
		
		require_once uTemplate::printEot('profile_privacy');
		pwOutPut();

	} else {
		
		PostCheck();
		S::gp(array('at_isfeed','article_isfeed', 'diary_isfeed', 'photos_isfeed', 'group_isfeed', 'self_isfollow', 'friend_isfollow', 'cnlesp_isfollow', 'article_isfollow', 'diary_isfollow', 'photos_isfollow', 'group_isfollow'), 'P', 2);

		$pwSQL = array(
			'uid'				=> $winduid,

			'article_isfeed'	=> $article_isfeed ? 1 : 0,
			'diary_isfeed'		=> $diary_isfeed ? 1 : 0,
			'photos_isfeed'		=> $photos_isfeed ? 1 : 0,
			'group_isfeed'		=> $group_isfeed ? 1 : 0,
			'at_isfeed'			=> $at_isfeed ? $at_isfeed : 0,

			'self_isfollow'		=> $self_isfollow ? 1 : 0,
			'friend_isfollow'	=> $friend_isfollow ? 1 : 0,
			'cnlesp_isfollow'	=> $cnlesp_isfollow ? 1 : 0,

			'article_isfollow'	=> $article_isfollow ? 1 : 0,
			'diary_isfollow'	=> $diary_isfollow ? 1 : 0,
			'photos_isfollow'	=> $photos_isfollow ? 1 : 0,
			'group_isfollow'	=> $group_isfollow ? 1 : 0,
		);
		
		$db->pw_update(
			"SELECT uid FROM pw_ouserdata WHERE uid=" . S::sqlEscape($winduid),
			"UPDATE pw_ouserdata SET " . S::sqlSingle($pwSQL) . " WHERE uid=" . S::sqlEscape($winduid),
			"INSERT INTO pw_ouserdata SET " . S::sqlSingle($pwSQL)
		);

		refreshto('profile.php?action=privacy&t=weibo','operate_success');
	}
} elseif ($t == 'bindsuccess') { //TODO keep it till...
	extract(L::style('',$skinco));
	
	$msg_info = '绑定帐号成功（窗口将自动关闭）';
	require_once uTemplate::printEot('profile_privacy_bindsuccess');
	pwOutPut();
}

function ifchecked($out, $var) {
	$GLOBALS[$out] = $var ? ' checked' : '';
}
?>