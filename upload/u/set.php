<?php
!defined('R_P') && exit('Forbidden');
$USCR = 'space_set';
!$winduid && Showmsg('not_login');
$isGM = S::inArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($winduid);
$newSpace->initSet();
$space = $newSpace->getInfo();
$isSpace = true;

$spaceModel = $newSpace->models;
$lang_model = array(
	//'info' => '个人资料',
	'friend' => array('朋友', '人', 'friend'),
	'visitor' => array('最近访客', '人', 'visitor'),
	'visit' => array('我访问过' , '人', 'visit'),
	'messageboard' => array('留言板', '条', 'messageboard'),
	'diary' => array('日志', '篇', 'diary'),
	'photos' => array('相册照片', '张', 'photos'),
	'weibo' => array('新鲜事', '条', 'write'),
	'article' => array('帖子' , '篇', 'article'),
	'colony' => array('群组', '个', 'colony')
);

if (empty($_POST['step'])) {
	
	S::gp(array('tab'));

	$modeSel = $tab ? $tab : substr($_COOKIE['spacemodeset'], 4);
	!in_array($modeSel, array('basic', 'skin', 'model')) && $modeSel = 'basic';
	!$space['spacetype'] && $space['spacetype'] = 0;

	$sel_basic = $sel_skin = $sel_model = $ifcheck_0 = $ifcheck_1 = '';
	$style_basic = $style_skin = $style_model = 'none';
	${'sel_' . $modeSel} = ' class="current"';
	${'style_' . $modeSel} = '';
	${'ifcheck_' . $space['spacetype']} = ' checked';
	$maxuploadsize = ini_get('upload_max_filesize');
	//$privacy = $newSpace->getPrivacy();
	!$o_uskin && $o_uskin = array('default' => 'default');
	$space['namelength'] = strlen($space['name']);
	$space['desclength'] = strlen($space['descript']);
	require_once(uTemplate::printEot('space_set'));
	pwOutPut();

} else {

	S::gp(array('name', 'spaceskin', 'domain','descript'));
	S::gp(array('spacetype','ifopen','privacy','shownum'), 'GP', 2);
	if (strlen($name)>80) {
		Showmsg('space_name_toolong');
	}
	if (strlen($descript)>255) {
		Showmsg('space_descript_toolong');
	}
	
	$modelset = array();
	foreach ($spaceModel as $key => $value) {
		(!$shownum[$value] || $shownum[$value] < 1) && $shownum[$value] = 1;
		if ($shownum[$value] > 50) {
			Showmsg($lang_model[$value][0] . '模块展示条目请不要超过50!');
		}
		$modelset[$value] = array(
			'ifopen'	=> intval($ifopen[$value]),
			'num'		=> $shownum[$value]
		);
	}
	/*
	if ($privacy && is_array($privacy)) {
		$pwSQL = array();
		foreach ($privacy as $key => $value) {
			if (in_array($key, $spaceModel)) {
				$pwSQL[] = array(
					'uid'	=> $winduid,
					'type'	=> 'space',
					'key'	=> $key,
					'value'	=> $value
				);
			}
		}
		$pwSQL && $db->update("replace INTO pw_privacy (uid, ptype, pkey, value) values " . S::sqlMulti($pwSQL));
	}
	if ($domain != $space['domain'] && $db->get_value("SELECT COUNT(*) AS sum FROM pw_space WHERE domain=" . S::sqlEscape($domain))) {
		Showmsg('该域名已被使用!');
	}
	*/

	$pwSQL = array(
		'name'		=> $name,
		'descript'  => $descript,
		'domain'	=> $domain,
		'spacetype'	=> $spacetype,
		'skin'		=> $spaceskin,
		'modelset'	=> serialize($modelset)
	);
	set_time_limit(0);
	require_once(R_P . 'u/lib/spacebannerupload.class.php');
	$upload = new spaceBannerUpload($winduid);
	PwUpload::upload($upload);
	if ($img = $upload->getImgUrl()) {
		$pwSQL['banner'] = $img;
	}

	$newSpace->updateInfo($pwSQL);

	refreshto('u.php?a=set', 'operate_success');
}
?>