<?php
!defined('R_P') && exit('Forbidden');
!$winduid && Showmsg('not_login');
$USCR = 'user_appset';

S::gp(array('action'));
require_once(R_P.'require/showimg.php');
require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($winduid);
$space = $newSpace->getInfo();
$basename = 'u.php?a='.$a.'&';
list($faceimg) = showfacedesign($winddb['icon'],1);
if (empty($action)) {
	if (!$db_appifopen || !$db_siteappkey) Showmsg('app_close');
	/*** userapp **/
	$appclient = L::loadClass('appclient');
	$app_array = $appclient->userApplist($winduid);
	$url = $appclient->ShowAppsList();
	/*** userapp **/
	require_once uTemplate::printEot('myapp');
	pwOutPut();

} elseif ($action == 'my') {

	$app_array = $basic_app_array = array();

	//userApp
	if ($db_appifopen && ($app_array = getUserApplist())) {
		foreach ($app_array as $key => $value) {
			if (strpos($winddb['appshortcut'], ','.$value['appid'].',') !== false) {
				$app_array[$key]['showchecked'] = 'checked';
			}
		}
	}

	//基础应用列表
	$isshowdb = explode(',',$winddb['appshortcut']);
	$rt = $db->get_one("SELECT photos_privacy,diary_privacy FROM pw_ouserdata WHERE uid=".S::sqlEscape($winduid));
	if (!$rt) {
		$db->query("INSERT INTO pw_ouserdata SET uid=".S::sqlEscape($winduid));
		$rt = $db->get_one("SELECT photos_privacy,diary_privacy FROM pw_ouserdata WHERE uid=".S::sqlEscape($winduid));
	}
	@extract($rt);
	$all_basic_app = array('article','weibo','diary','groups','photos');
	$basic_app_with_privacy = array('diary','photos');
	foreach ($all_basic_app as $key => $value) {
		if(!getIfopenOfApp($value)) continue;
		${$value.'_isshow'} = in_array($value,$isshowdb) ? 1 : 0;
		${$value.'_privacy'} = in_array($value,$basic_app_with_privacy) ? ${$value.'_privacy'} : 0;
		$name = getLangInfo('other',$value);
		$showchecked = ${$value.'_isshow'} ? 'checked' : '';
		if (in_array($value,$basic_app_with_privacy)) {
			$privacy = ${$value.'_privacy'};
			${'privace_'.$value.'_'.$privacy} = 'selected';
		}
		$basic_app_array[$value] = array('name' => $name, 'isshow' => ${$value.'_isshow'},'privacy' => ${$value.'_privacy'},'showchecked' => $showchecked);
	}
	require_once uTemplate::printEot('myapp');
	pwOutPut();
} elseif ($action == 'del') {
	define('AJAX',1);
	S::gp(array('id'));
	//$db->update("DELETE FROM pw_userapp WHERE uid=" . S::sqlEscape($winduid) . ' AND appid=' . S::sqlEscape($id));
	$appclient = L::loadClass('appclient');
	$appclient->deleteUserAppByUidAndAppid($winduid,$id);

	if ($db->affected_rows()) {

		if (!$db_appifopen || !$db_siteappkey) Showmsg('app_close');

		/*** userapp **/
		$appclient = L::loadClass('appclient');
		$url = $appclient->MoveAppsList($id);
		/*** userapp **/
	}
	echo 'ok';
	ajax_footer();
} elseif ($action == 'edit') {
	S::gp(array('show','privacy'));
	//显示在快捷菜单栏处理
	$showshortcut = array();
	list($fidshortcut) = explode("\t",$winddb['shortcut']);
	foreach ($show as $key => $value) {
		if ($value == 1) {
			$showshortcut[] = $key;
		}
	}
	$shortcut = $fidshortcut."\t".','.implode(',',$showshortcut).',';

	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->update($winduid, array('shortcut'=>$shortcut));

	$basic_app_with_privacy = array('diary','photos');
	$SQL = array('uid'=>$winduid);
	foreach ($privacy as $key => $value) {
		if (!in_array($key,$basic_app_with_privacy)) continue;
		if ($key == 'write') {
			$SQL['o'.$key.'_privacy'] = (int)$value;
		} else {
			$SQL[$key.'_privacy'] = (int)$value;
		}
	}
	$db->pw_update(
		"SELECT uid FROM pw_ouserdata WHERE uid=".S::sqlEscape($winduid),
		"UPDATE pw_ouserdata SET ".S::sqlSingle($SQL)." WHERE uid=".S::sqlEscape($winduid),
		"INSERT INTO pw_ouserdata SET ".S::sqlSingle($SQL)
	);
	refreshto("{$basename}action=my",'myapp_success');
}

function getIfopenOfApp($app) {
	global $db_dopen,$db_groups_open,$db_phopen;
	switch ($app) {
		case 'diary' :
			$return = $db_dopen ? '1' : '0';
			break;
		case 'groups' :
			$return = $db_groups_open ? '1' : '0';
			break;
		case 'photos' :
			$return = $db_phopen ? '1' : '0';
			break;
		default:
			$return = '1';
	}
	return $return;
}
?>