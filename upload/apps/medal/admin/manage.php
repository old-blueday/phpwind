<?php
!function_exists('adminmsg') && exit('Forbidden');
!$action && $action = 'medal';

$medalService = L::loadClass('MedalService', 'medal'); /* @var $medalService PW_MedalService */
$medalCondition = $medalService->getAutoMedalType(); //勋章自动颁发条件
$actionCurrent[$action] = 'class="current"';
$typeArr  = array('系统发放', '自动发放', '手动发放');
$issueWay = array('系统自动发放', '用户申请通过', '管理员发放'); //勋章发放途径
S::gp(array('type'));

/* 勋章管理：列表、添加、编辑、删除、批量操作 */
if ($action == 'medal') {
	(!in_array($type, array('list', 'add', 'adddo', 'edit', 'editdo', 'del', 'batch','img'))) && $type = 'list';
	
	/* 勋章管理-列表页面 */
	if ($type == 'list') {
		$medal = $medalService->getAllMedals();
		require_once PrintApp('admin_medal');
	
	/* 勋章管理-勋章添加 */
	} elseif ($type == 'add') {
		$creategroup = getGroup(); //获取用户组
		$openMedal   = $medalService->getAllOpenAutoMedals(); //获取所有开启的勋章
		$openMedal   = getMedalJson($openMedal);
		require_once PrintApp('admin_medal_add');
		
	/* 勋章管理-勋章添加操作 */
	} elseif ($type == 'adddo') {
		S::gp(array('name', 'image', 'descrip', 'tp', 'day', 'associate', 'confine', 'allow_group'));
		if ($name == '') adminmsg('勋章名称不得为空',"$basename&type=add");
		if ($image == '') adminmsg('medal_image_is_not_select',"$basename&type=add");
		if ($descrip == '') adminmsg('勋章描述不得为空',"$basename&type=add");
		if (!$allow_group) $allow_group = array(); //如果会员组为空，则空数组
		if ($tp == 2) $confine = $day; //手动添加
		if ($confine < 0) $confine = 0; //不能小于0
		$info = array(
			'name'        => $name,
			'descrip'     => $descrip,
			'type'        => (int) $tp,
			'image'       => $image,
			'associate'   => $associate,
			'confine'     => (int) $confine,
			'allow_group' => $allow_group
		);
		$result = $medalService->addMedal($info);
		if (is_array($result)) {
			adminmsg($result[1],"$basename&type=add");
		} else {
			adminmsg('operate_success',"$basename");
		}
		
	/* 勋章管理-勋章编辑 */	
	} elseif ($type == 'edit') {
		S::gp(array('id'));
		$id = (int) $id;
		if ($id < 1) adminmsg('operate_error',"$basename");
		$medal       = $medalService->getMedal($id); //获取medal信息
		if ($medal['type'] == 0) adminmsg('medal_system_is_not_edit',"$basename");
		$creategroup = getGroup($medal['allow_group']); //获取用户组
		$openMedal   = $medalService->getAllOpenAutoMedals(); //获取所有开启的勋章
		$openMedal   = getMedalJson($openMedal);
		require_once PrintApp('admin_medal_add');
	
	/* 勋章管理-勋章编辑操作 */
	} elseif ($type == 'editdo') {
		S::gp(array('name', 'image', 'descrip', 'day', 'confine', 'allow_group', 'id'));
		$id = (int) $id;
		if ($id < 1) adminmsg('operate_error',"$basename&type=add");
		if ($name == '') adminmsg('勋章名称不得为空',"$basename&type=add");
		if ($image == '') adminmsg('medal_image_is_not_select',"$basename&type=edit&id=" . $id);
		if ($descrip == '') adminmsg('勋章描述不得为空',"$basename&type=add");
		$medal = $medalService->getMedal($id); //获取medal信息
		if ($medal['type'] == 0) adminmsg('medal_system_is_not_edit',"$basename");
		if ($medal['type'] == 2) $confine = $day; //手动添加
		if (!$allow_group) $allow_group = array();
		if ($confine < 0) $confine = 0; //不能小于0
		$info = array(
			'name'        => $name,
			'descrip'     => $descrip,
			'image'       => $image,
			'confine'     => (int) $confine,
			'allow_group' => $allow_group
		);
		$result = $medalService->updateMedal($id, $info);
		if (is_array($result)) { //用系统的函数判断
			adminmsg($result[1],"$basename&type=edit&id=" . $id);
		} else {
			adminmsg('operate_success',"$basename");
		}
		
	/* 勋章管理-勋章删除操作 */
	} elseif ($type == 'del') {
		S::gp(array('id'));
		$id = (int) $id;
		if ($id < 1) adminmsg('operate_error',"$basename");
		$medal = $medalService->getMedal($id); //获取medal信息
		if ($medal['type'] == 0) adminmsg('medal_system_is_not_del',"$basename");
		$result = $medalService->deleteMedal($id);
		if (is_array($result)) {
			adminmsg($result[1],"$basename");
		} else {
			adminmsg('operate_success',"$basename");
		}
	
	/* 勋章管理-勋章批量操作 */
	} elseif ($type == 'batch') {
		S::gp(array('name', 'sortorder', 'descrip','selid'));
		foreach ($name as $k => $v) {
			if ($k < 1) continue;
			$info = array(
				'name'      => $name[$k],
				'sortorder' => $sortorder[$k],
				'descrip'   => $descrip[$k],
				'is_open'   => $selid[$k]
			);
			$medalService->updateMedal((int)$k, $info);
		}
		adminmsg('operate_success',"$basename");
		
	/* 勋章管理-勋章图片AJAX读取 */
	} elseif ($type == 'img') { //图片
		define('AJAX', 1);
		//获取图片
		$medalImg = getMedalImgList();
		require_once PrintApp('admin_medal_add');
		ajax_footer();
	}
	
/* 勋章会员 */
} elseif ($action == 'user') {
	(!in_array($type, array('list', 'del','deldo', 'batchdel', 'batch', 'add', 'adddo'))) && $type = 'list';
		
	/* 勋章会员列表 */
	if ($type == 'list') {
		S::gp(array('page','searchName', 'searchUsername', 'searchType'));
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService*/
		//勋章分页列表
		$condition = array(); //装载搜索条件
		if ($searchName) $condition['medal_id'] = (int) $searchName;
		if ($searchUsername) {
			$user = $userService->getByUserName($searchUsername);
			$condition['uid'] = ($user) ? $user['uid'] : -1;
		}
		if (is_numeric($searchType)) $condition['type'] = (int) $searchType;

		(!is_numeric($page) || $page<1) && $page = 1;
		list($medalAward, $medalAwardCount) = $medalService->getAwardMedalUsers($condition,$page,20);
		$pages = numofpage($medalAwardCount, $page,ceil($medalAwardCount/20),"$basename&action=user&searchName=" . $searchName . "&searchUsername=" . $searchUsername .'&searchType=' . $searchType . '&');

		//勋章信息
		$openMedal = $medalService->getAllMedals(); //获取所有的勋章
		require_once PrintApp('admin_user');
	
	/* 手动添加会员-ajax弹出框模式 */
	} elseif ($type == 'add') {
		define('AJAX', 1);
		$openManualMedals =  $medalService->getAllOpenManualMedals();//获取手动勋章
		require_once PrintApp('admin_user');
		ajax_footer();
	
	/* 手动添加会员操作 */
	} elseif ($type == 'adddo') {
		define('AJAX', 1);
		S::gp(array('username','medal_id', 'descrip'));
		$medal_id = (int) $medal_id;
		if ($medal_id < 1) adminmsg('operate_error', "$basename&action=user");
		if (!$username) adminmsg('medal_username_error', "$basename&action=user");
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService*/
		$user = $userService->getByUserName($username);
		if (!$user) adminmsg('medal_username_error', "$basename&action=user");
		if ($appliInfo = $medalService->getApplyByUidAndMedalId($user['uid'], $medal_id)) {
			$result = $medalService->adoptApplyMedal($appliInfo['apply_id']); //审核通过，已经在审核表中的话
		} else {
			$result = $medalService->awardMedal($user['uid'], $medal_id, 0, array(),$descrip);//颁发勋章
		}
		if (is_array($result)) {
			adminmsg($result[1], "$basename&action=user");
		} else {
			//发送消息
			adminmsg('medal_ajax_operate_success');
		}
		ajax_footer();
	
	/* 删除操作 */	
	} elseif ($type == 'del') {
		define('AJAX', 1);
		S::gp(array('id'),'',1);
		$id = (int) $id;
		if ($id < 1) adminmsg('operate_error', "$basename&action=user");
		require_once PrintApp('admin_user');
		ajax_footer();
		
	/* 删除操作 */		
	} elseif ($type == 'deldo') {
		define('AJAX', 1);
		S::gp(array('id','descrip'));
		$id = (int) $id;
		$descrip = substrs($descrip, 200);
		if ($id < 1) adminmsg('operate_error', "$basename&action=user");
		$awardMedalInfo = $medalService->getAwardMedalById($id);
		$medal       = $medalService->getMedal($awardMedalInfo['medal_id']); //获取medal信息
		if ($medal['type'] == 1) adminmsg('medal_error');
		$result = $medalService->recoverMedal($id,$descrip);//摘除操作
		if (is_array($result)) {
			adminmsg($result[1], "$basename&action=user");
		} else {
			adminmsg('medal_ajax_operate_success');
		}
		ajax_footer();
	
	/* 批量操作显示页面 */
	} elseif ($type == 'batchdel') {
		define('AJAX', 1);
		S::gp(array('id'));
		if ($id == '') adminmsg('medal_is_not_select', "$basename&action=user");
		require_once PrintApp('admin_user');
		ajax_footer();
		
	/* 批量操作 */
	} elseif ($type == 'batch') {
		define('AJAX', 1);
		S::gp(array( 'id','descrip'));
		if ($id == '') adminmsg('medal_is_not_select',"$basename&action=user");
		$id = explode('|', $id);
		$uidArr = array();
		foreach ($id as $v) {
			$v = (int) $v;
			if ($v < 1) continue;
			$awardMedalInfo = $medalService->getAwardMedalById($v);
			$uidArr[]       = $awardMedalInfo['uid']; //消息发送的对象
			$medalService->recoverMedal($v,'批量删除勋章');
		}
		adminmsg('medal_ajax_operate_success');
		ajax_footer();
	}
	
/* 勋章审核 */
} elseif ($action == 'verify') {
	(!in_array($type, array('list', 'pass', 'batch'))) && $type = 'list';
	
	/* 审核列表 */
	if ($type == 'list') {
		S::gp(array('searchName', 'searchUsername', 'page'));
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService*/
		//搜索参数
		$condtion = array(); //装载搜索条件
		if ($searchName) $condtion['medal_id'] = (int) $searchName;
		if ($searchUsername) {
			$user = $userService->getByUserName($searchUsername);
			$condtion['uid'] = ($user) ? $user['uid'] : 0;
		}
		(!is_numeric($page) || $page<1) && $page = 1;
		list($medalApply, $medalApplyCount) = $medalService->getApplyMedalUsers($condtion,$page,20);
		$pages = numofpage($medalApplyCount, $page,ceil($medalApplyCount/20),"$basename&action=verify&searchName=" . $searchName . "&searchUsername=" . $searchUsername .'&');
		//勋章信息
		$openMedal = $medalService->getAllOpenManualMedals(); //获取所有开启的勋章
		require_once PrintApp('admin_verify');
	
	/* 审核通过-或者-不通过 */
	} elseif ($type == 'pass') {
		S::gp(array('val', 'applyid'),'',2);
		if ($applyid < 1) adminmsg('operate_error',"$basename&action=verify");
		$result = ($val == 1) ? $medalService->adoptApplyMedal($applyid) : $medalService->refuseApplyMedal($applyid); 
		if (!$result) adminmsg('operate_error',"$basename&action=verify");
		adminmsg('operate_success',"$basename&action=verify");
		
	/* 批量操作 批量通过或者不通过 */
	} elseif ($type == 'batch') {
		S::gp(array('passid', 'selid'));
		if (!$selid) adminmsg('medal_is_not_select',"$basename&action=verify");
		$passid = (int) $passid;
		$functionName = ($passid == 1) ? 'adoptApplyMedal' : 'refuseApplyMedal';
		foreach ($selid as $v) {
			$v = (int) $v;
			if ($v < 1) continue;
			$medalService->$functionName($v);
		}
		adminmsg('operate_success',"$basename&action=verify");
	}
	
	
/* 勋章设置 */
} elseif ($action == 'set') {
	S::gp(array('step'), 'P');
	if(!$step){
		ifcheck($db_md_ifopen,'ifopen');
		ifcheck($db_md_ifapply,'ifapply');
		require_once PrintApp('admin_set');
	} else {
		S::gp(array('config'),'P');
		foreach($config as $key=>$value){
			setConfig($key, $value);
		}
		updatecache_c();
		adminmsg('operate_success',"$basename&action=set");
	}
}

/**
 * JSON处理
 * 
 * @return Ambigous <multitype:, string>
 */
function getMedalJson($medal) {
	$openMedalTemp = array();
	foreach ($medal as $v) { 
		$openMedalTemp[] = $v;
	}
	return pwJsonEncode($openMedalTemp);
}

/**
 * 读取勋章文件夹下的勋章图片
 * 
 * @return Ambigous <multitype:, string>
 */
function getMedalImgList() {
	$medalImg = array();
	global $imgdir;
	if ($fp = opendir("$imgdir/medal/big")) { //
		while (($file = readdir($fp))) {
			if (!is_dir($file) && in_array(substr($file, -4), array('.gif', '.png'))) {
				$imgId = substr($file, 0, -4);
				$medalImg[$imgId] = $file;
			}
		}
		closedir($fp);
	}
	ksort($medalImg);
	return $medalImg;
}

/**
 * 获取会员组信息
 * 
 * @param $allow_group 编辑的时候选中的数组项
 */
function getGroup($allow_group = array()) {
	$creategroup = ''; $num = 0;
	global $ltitle;
	foreach ($ltitle as $key => $value) {
		if ($key != 1 && $key != 2 && $key !='6' && $key !='7' && $key !='3') {
			$num++;
			$htm_tr = $num % 4 == 0 ? '' : '';
			$g_checked = in_array($key,$allow_group) ? 'checked' : '';
			$creategroup .= "<li><input type=\"checkbox\" name=\"allow_group[]\" value=\"$key\" $g_checked>$value</li>$htm_tr";
		}
	}
	$creategroup && $creategroup = "<ul class=\"list_A list_120 cc\">$creategroup</ul>";	
	return $creategroup;
}



?>