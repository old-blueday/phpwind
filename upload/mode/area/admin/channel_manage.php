<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('action'));
$channel_db=L::loadDB('Channel', 'area');
$channelService=L::loadClass('channelService', 'area');
$actionUrl=$admin_file."?adminjob=mode&admintype=area_channel_manage";
//风格样式数组
$themes_array=getThemes();
//根据传过来的action参数选择不同的执行函数

if ($action == 'add') {
	S::gp(array('add_step'));

	$actionUrl=EncodeUrl($actionUrl);
	if(empty($add_step)) {
		include PrintMode('channel_manage');
		ajax_footer();
	} else {
		S::gp(array('channel_name','channel_alias','channel_theme','channel_domain','ifreplace'));
		checkChannelPost($channel_name,$channel_alias);
		if ($channelService->getChannelInfoByAlias($channel_alias)) Showmsg("英文别名不能重复");
		$channelPath = $channelService->getChannelPath($channel_alias);
		if ($ifreplace && is_dir($channelPath)) {
			L::loadClass('fileoperate', 'utility', false);
			PW_FileOperate::deleteDir(S::escapePath($channelPath));
			clearstatcache();
		}
		if (is_dir($channelPath)) Showmsg('该别名的频道目录已存在，无法创建');

		$channelId = $channelService->createChannel($channel_name,$channel_alias,$channel_theme,$channel_domain);

		$channelUrl = $channelService->getChannelUrl($channelId);
		$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
		$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'area_'.$channel_alias, 'pos' => '-1', 'title' => $channel_name, 'link' => $channelUrl, 'isshow' => 0));

		define('AREA_STATIC','1');
		//频道相关服务
		require_once(R_P.'require/nav.php');
		require M_P.'index.php';
		aliasStatic($channel_alias);
		
		Showmsg("添加成功!");
	}
} elseif ($action == 'addcheck') {
	S::gp(array('ckalias'));
	$channelPath = $channelService->getChannelPath($ckalias);
	$channel_info=$channelService->getChannelInfoByAlias($ckalias);
	if ($channel_info) {
		echo 'havechannel';
		ajax_footer();exit;
	}
	if (is_dir($channelPath)) {
		echo 'error';
	} else {
		echo 'success';
	}
	ajax_footer();exit;
} elseif ($action == 'del') {
	//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
	pwCache::getData(D_P.'data/bbscache/area_config.php');
	S::gp(array('id'));
	$channel_info=$channelService->getChannelByChannelid($id);
	if (!$channel_info) Showmsg("频道不存在");

	$del_flag=$channelService->delChannel($id);

	$dir=AREA_PATH.$channel_info['alias'];
	if(is_dir($dir) && $channel_info['alias']!="") {
		L::loadClass('fileoperate', 'utility', false);
		PW_FileOperate::deleteDir(S::escapePath($dir));
	}
	if ($area_default_alias == $channel_info['alias']) {
		$channelService->updateDefaultAlias('');
	}
	$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
	$navConfigService->deleteByKey('area_'.$channel_info['alias']);

	Showmsg("删除成功!");


} elseif ($action == 'edit') {
	S::gp(array('edit_step','id','channel_name','channel_theme','channel_domain'));
	$actionUrl=EncodeUrl($actionUrl);
	if(empty($edit_step)) {
		$channel_info = $channelService->getChannelByChannelid($id);
		include PrintMode('channel_manage');
		ajax_footer();
	} else {
		checkChannelPost($channel_name,'alias',$id);

		$update_flag=$channelService->updateChannel($id,array(
			'name' 	=> $channel_name,
			//'relate_theme'	=> $channel_theme,
			'domain_band'	=> $channel_domain
		));
		if($update_flag){
			Showmsg("修改成功!");
		} else {
			Showmsg("修改失败!");
		}
	}
} elseif ($action == 'static') {
	S::gp(array('alias'));
	define('AREA_STATIC','1');
	//频道相关服务
	require_once(R_P.'require/nav.php');
	require M_P.'index.php';
	aliasStatic($channelInfo['alias']);
	echo getLangInfo('msg','operate_success');
	ajax_footer();exit;
} elseif ($action == 'static_all') {
	define('AREA_STATIC','1');
	$ChannelService = L::loadClass('channelService', 'area');
	$channelsArray=$ChannelService->getChannels();

	require_once(R_P.'require/nav.php');
	foreach ($channelsArray as $channelInfoValue) {
		$alias = $channelInfoValue['alias'];

		require M_P.'index.php';
		aliasStatic($channelInfoValue['alias']);
	}
	echo getLangInfo('msg','operate_success');
	ajax_footer();exit;
} else {
	S::gp(array('default_step','channels','defaultalias'));
	if($default_step == 1) {
		$channelService->updateChannels($channels);
		$channelService->updateDefaultAlias($defaultalias);
		Showmsg("operate_success");
	} else {
		//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
		pwCache::getData(D_P.'data/bbscache/area_config.php');

		$addUrl=$admin_file."?adminjob=mode&admintype=area_channel_manage&action=add&ajax=1";
		$editUrl=$admin_file."?adminjob=mode&admintype=area_channel_manage&action=edit&ajax=1";
		$delUrl=$admin_file."?adminjob=mode&admintype=area_channel_manage&action=del";
		$channel_list = $channelService->getChannels();
		$actionUrl=EncodeUrl($actionUrl."&ajax=1");
		include PrintMode('channel_manage');
	}
}

//验证提交表单
function checkChannelPost($channel_name,$channel_alias='alias',$id) {
	global $channel_db;
	if ($channel_name == '') {
		Showmsg('频道名称不能为空');
	} elseif ($channel_alias == '') {
		Showmsg('英文别名不能为空');
	} elseif (strlen($channel_name)>20) {
		Showmsg('频道名称不能超过20个字符');
	} elseif (strlen($channel_alias)>20) {
		Showmsg('英文别名不能超过20个字符');
	} elseif (!preg_match("/^[a-zA-Z0-9]+$/",$channel_alias)) {
		Showmsg('英文别名不能带中文');
	} else {
		$channelInfo=$channel_db->getChannelByChannelName($channel_name);
		if($channelInfo['id'] && $channelInfo['id'] != $id) {
			Showmsg('频道名称已经存在');
		}
	}
}

//获取风格列表
function getThemes() {
	$tplLib = array();
	$tplPath = R_P.'mode/area/themes/';
	if ($fp = opendir($tplPath)) {
		while ($tpldir = readdir($fp)) {
			if (in_array($tpldir,array('.','..','admin','.svn','default','bbsindex'))) continue;
			if (file_exists($tplPath.$tpldir.'/'.PW_PORTAL_MAIN) && file_exists($tplPath.$tpldir.'/'.PW_PORTAL_CONFIG)) {
				$tplLib[]=$tpldir;
			}
		}
		closedir($fp);
	}
	return $tplLib;
}
?>