<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户前台管理中心
 * 权限/推送管理/新增推送/删除/审核/模块属性/等
 * 支持ajax访问和直接访问
 * @author liuhui @2010-3-10
 */
$db_ifjump = 1;
$portalPageService = L::loadClass('portalpageservice', 'area');
$levelService = L::loadclass("AreaLevel", 'area');
$invokeService = L::loadClass('invokeservice', 'area');
$pageInvokeService = L::loadClass('pageinvokeservice', 'area');

InitGP(array("action","invokename","channelid","invokepieceid","page","selid","subinvoke","step","ifpush"));
$IS_PROTAL = 0;
$invokename = trim(strip_tags($invokename));

if (is_numeric($channelid)) {
	$channelid = intval($channelid);
	$pageInvokeInfo = $pageInvokeService->getPageInvokeByChannelIdAndName($channelid, $invokename);
} elseif ($portalPageService->checkPortal($channelid)) {
	$IS_PROTAL = 1;
	$pageInvokeInfo = $pageInvokeService->getPageInvokeBySignAndName($channelid, $invokename);
}

if (!in_array($action, array("pushto","fetch","recommend","success")) && !($userLevel = $levelService->getAreaLevel($winduid, $channelid, $invokename))) {
	showmsg($levelService->language("area_no_level"));
}
$manageService = L::loadclass("AreaManage", 'area');
$actions = array(
	'pushto' => 1,
	'add' => 2,
	'edit' => 3
);
!$ifpush && $ifpush = isset($actions[$action]) ? $actions[$action] : 1;
$baseUrl = "mode.php?m=area&q=manage&invokename=" . urlencode($invokename) . "&channelid=" . $channelid . "&";
list($hasedit, $hasattr) = array(
	$userLevel['hasedit'],
	$userLevel['hasattr']
);
if (empty($action) || "verify" == $action) {
	$page = ($page > 1) ? $page : 1;
	$invokeInfo = $invokeService->getInvokeByName($invokename);
	if (!$invokeInfo) Showmsg('模块不存在');
	list($bool, $channels, $invokes, $subInvokes) = $manageService->getFirstGrade($winduid, $channelid, $invokename);
	$subInvokesSelect = $manageService->buildSelect($subInvokes, 'invokepieceid', 'invokepieceid', $invokepieceid, true, "选择位置");
	$ifverify = $action ? 1 : 0;
	$changeUrl = $ifverify ? $baseUrl . "action=verify" : $baseUrl;
	$pageUrl = $invokepieceid ? $baseUrl.'invokepieceid='.$invokepieceid.'&' : $baseUrl;
	if ($invokepieceid > 0) {
		list($lists, $pager) = $manageService->getPushData(array(
			'invokepiece' => $invokepieceid,
			'ifverify' => $ifverify
		), $page, $pageUrl, 8);
	} else {
		list($lists, $pager) = $manageService->getPushData(array(
			'invoke' => $invokename,
			'ifverify' => $ifverify
		), $page, $pageUrl, 8);
	}
} elseif ("verifydata" == $action) {
	InitGP(array('pushdataid'));
	$pushdataid = intval($pushdataid);
	if ($pushdataid > 0) {
		$pushDataService = L::loadclass("PushDataService", 'area');
		$pushDataService->verifyPushdata($pushdataid);
		if ($IS_PROTAL)	$portalPageService->setPortalStaticState($channelid,1);
		refreshto($baseUrl, 'operate_success');
	}
} elseif ("edit" == $action) {
	InitGP(array('pushdataid'));
	if (!$step) {
		$pushdataService = L::loadClass('pushdataservice', 'area');
		if (!($push = $pushdataService->getPushDataById($pushdataid))) {
			refreshto($baseUrl, '抱歉,编辑数据有误');
		}
		$invokepiece = $invokeService->getInvokePieceByInvokeId($push['invokepieceid']);

		$push['starttime'] = $push['starttime'] ? get_date($push['starttime'], 'Y-m-d H:i') : '';
		$stylename = $pushdataService->getTitleCss($push);
		$offsets = array(
			0 => "",
			1 => "",
			2 => "",
			3 => "",
			4 => "",
			5 => ""
		);
		$offsets[$push['vieworder']] = 'checked="checked"';
		$manageService->ifcheck($push['ifbusiness'], 'ifbusiness');
		$title = "编辑";
	} else {
		InitGP(array('param','offset','starttime','css','ifbusiness'), 'GP');
		$pushdataService = L::loadClass('pushdataservice', 'area');
		$pushdataService->editPushdata($pushdataid, array(
			'invokepieceid' => $subinvoke,
			'editor' => $windid,
			'starttime' => $starttime,
			'vieworder' => $offset,
			'data' => $param,
			'titlecss' => $css,
			'ifbusiness' => $ifbusiness,
			'ifverify' => 0
		));
		if ($IS_PROTAL)	$portalPageService->setPortalStaticState($channelid,1);
		refreshto($baseUrl, 'operate_success');
	}
} elseif ("delete" == $action) {
	InitGP(array('pushdataid'));
	$pushdataid = intval($pushdataid);
	if ($pushdataid > 0) {
		$pushDataService = L::loadclass("PushDataService", 'area');
		$pushDataService->deletePushdata($pushdataid);
		if ($IS_PROTAL)	$portalPageService->setPortalStaticState($channelid,1);
		refreshto($baseUrl, 'operate_success');
	}
} elseif ("editconfig" == $action) {
	(!$hasattr) && refreshto($baseUrl, '抱歉,你没有设置模块属性的权限');
	$invokedata	= $pageInvokeInfo;

	if (!$step) {
		if (!$IS_PROTAL) {
			$channelService = L::loadClass('channelService', 'area');
			$alias = $channelService->getAliasByChannelId($channelid);
		} else {
			$alias = $channelid;
		}
		
		ifcheck($invokedata['ifverify'],'ifverify');
		
		$invokepieces = $invokeService->getInvokePieceForSetConfig($invokename);
	} else {
		InitGP(array('alias'));
		InitGP(array('p_action','config','num','param','cachetime','ifpushonly','invokename','title'), 'GP');
		InitGP(array('ifverify','pageinvokeid'),'P');
		$pageInvokeService->updatePageInvoke($pageinvokeid,array('ifverify'=>(int)$ifverify));
		
		$pieces = array();
		foreach ($num as $key => $value) {
			$temp = array();
			$temp['num'] = (int) $value;
			$temp['action'] = $p_action[$key];
			$temp['config'] = $config[$key];
			$temp['param'] = $param[$key];
			$temp['cachetime'] = $cachetime[$key];
			$temp['ifpushonly'] = (int) $ifpushonly[$key];
			$piece = $invokeService->getInvokePieceByInvokeId($key);
			$temp['title'] = $piece['title'];
			$temp['invokename'] = $invokename;
			$pieces[] = $temp;
		}
		$portalPageService->updateModuleByConfig($alias, $invokename, $pieces,$title);
		//if ($IS_PROTAL)	portalStatic($channelid);
		refreshto($baseUrl . "action=editconfig", 'operate_success');
	}
} elseif ("edittpl" == $action) {
	(!$hasedit) && refreshto($baseUrl, '抱歉,你没有编辑模块代码的权限');
	if (!$step) {
		if (!$IS_PROTAL) {
			$channelService = L::loadClass('channelService', 'area');
			$alias = $channelService->getAliasByChannelId($channelid);
		} else {
			$alias = $channelid;
		}
		
		$pieceCode = $portalPageService->getPiecesCode($alias, $invokename);
	} else {
		InitGP(array('alias'));
		$moduleConfigService = L::loadClass('moduleconfigservice', 'area');
		
		$tagcode = $moduleConfigService->getTagCodeFromPost($_POST['tagcode']);
		if ($moduleConfigService->checkScript($tagcode)) {
			refreshto($baseUrl . "action=edittpl",'前台不支持javascript代码提交，如有需要，请到后台模块管理提交',5);
		}
		if ($tagcode === false) {
			refreshto($baseUrl . "action=edittpl",'模板编辑功能不支持php代码，以及一些特殊字符,如有需求，请直接修改模板文件',5);
		}
		
		$portalPageService->updateModuleCode($alias, $invokename, $tagcode);
		
		refreshto($baseUrl . "action=edittpl", 'operate_success');
	}
} elseif ('source' == $action) {
	InitGP(array('sourcetype','id'), 'P');
	$id = (int) $id;

	$pieceOperate = L::loadClass('pieceoperate', 'area');
	$sourceTypeConfig = $pieceOperate->getConfigHtmlBySourceType($sourcetype,$id);
	
	$result = '<table width="100%"><tbody>';
	foreach ($sourceTypeConfig as $key=>$value) {
		$result .= <<<EOT
	            <tr class="tr3">
		        	<td>$value[title] : $value[html]</td>
		        </tr>
EOT;
	}
	$result .= '</tbody></table>';

	echo $result;
	ajax_footer();
} elseif ("fetch" == $action) {
	$dataSourceService = L::loadClass('datasourceservice', 'area');
	$pushkey = $selid;
	if (!$subinvoke) ajax_footer();
	$invokepiece = $invokeService->getInvokePieceByInvokeId($subinvoke);
	$inputs = array();
	$channelService = L::loadClass('channelService', 'area');
	
	$default = array();
	if (1 == $ifpush) {
		
	} elseif (2 == $ifpush) {
		$inputs = $dataSourceService->getRelateHtmlForView($invokepiece['action']);
	} elseif (3 == $ifpush) {
		InitGP(array(
			'pushdataid'
		));
	} else {
		$inputs = $dataSourceService->getRelateHtmlForView($invokepiece['action'],$pushkey);
	}
	
	if ($pushkey) {
		$tempAction = 1 == $ifpush ? 'subject' : $invokepiece['action'];
		$default = $dataSourceService->getRelateInfoByKey($tempAction,$pushkey, $invokepiece['param']);
		if ($default['image'] && $default['image'][0] != 'nopic') {
			$selectImages = $default['image'];
			$default['image'] = '';
		} else {
			$selectImages = '';
			$default['image'] = '';
		}
	} else {
		if (3 == $ifpush && $pushdataid) {
			$pushdataService = L::loadClass('pushdataservice', 'area');
			$push = $pushdataService->getPushData($pushdataid);
			$default = $push['data'];
			$stylename = $pushdataService->getTitleCss($push);
		} else {
			foreach ($invokepiece['param'] as $key => $value) {
				$default[$key] = '';
			}
		}
	}

	require_once M_P . 'require/pingfen.php';
	require_once areaLoadFrontView('area_fetch');
	ajax_footer();
} elseif ("success" == $action) {
	InitGP(array('tid','ifrecommend'), '', 2);
	if ($tid) {
		$thread = $db->get_one("SELECT fid FROM pw_threads WHERE tid=" . pwEscape($tid));
		$postModifyUrl = 'post.php?action=modify&fid=' . $thread['fid'] . '&tid=' . $tid . '&pid=tpc';
	}
	$successLang = $ifrecommend ? '数据已推荐至相应模块！' : '您已成功的实现推送操作！';
	require_once areaLoadFrontView('area_manage');
	//footer();
}
/**
 * 公共业务组装 pushto、add和edit 三大业务公共服务 下拉联动
 */
if (in_array($action, array("pushto","add","edit","recommend"))) {
	initGP(array("ajax","channelid","doing"));
	$ifverify = (in_array("recommend", array($doing,$action))) ? 1 : 0;
	if (!$step) {
		$dataSourceService = L::loadClass('datasourceservice', 'area');
		list($channelid, $invokename, $invokepieceid) = array(
			($channelid ? $channelid : ($channelid ? $channelid : 0)),
			($invokename ? strip_tags(trim($invokename)) : ""),
			($invokepieceid ? intval($invokepieceid) : "")
		);
		list($bool, $channels, $invokes, $subInvokes) = $manageService->getFirstGrade($winduid, $channelid, $invokename, $ifverify);
		if (!$bool && 1 == $ajax) {
			echo "4\t抱歉,你没有管理权限";
			ajax_footer();
		}
		if ($action == 'add') {
			foreach ($subInvokes as $key=>$value) {
				$invokepieceid = $key;
				break;
			}
			$invokepiece = $invokeService->getInvokePieceByInvokeId($invokepieceid);
			$inputs = $dataSourceService->getRelateHtmlForView($invokepiece['action']);
			$push = array();
		}
		$channelsSelect = $manageService->buildSelect($channels, 'channel', 'channel', $channelid);

		$invokesSelect = $manageService->buildSelect($invokes, 'invokename', 'invokename', $invokename, false, "选择模块");
		$subInvokesSelect = $manageService->buildSelect($subInvokes, 'subinvoke', 'subinvoke', $invokepieceid, true, "选择位置");
		$status = ("" != $invokename) ? 2 : 1;
		if (1 == $ajax && $channelid) {
			echo $status . "\t" . $invokesSelect . "\t" . $subInvokesSelect . "\t";
			ajax_footer();
		}
		if ("add" == $action) {
			$offsets = array(
				0 => 'checked="checked"',
				1 => "",
				2 => "",
				3 => "",
				4 => "",
				5 => ""
			);
			$title = "增加";
		}
		$default = array();
	} else {
		InitGP(array('param','offset','starttime','css','ifbusiness'), 'GP');
		InitGP(array('push_ifcms','pushkey','channel'), 'GP');
		$pushdataService = L::loadClass('pushdataservice', 'area');
		if (!$subinvoke) refreshto($baseUrl.'action='.$action.'&selid='.$pushkey.'&','请选择模块');
		$ifverify = $action == 'recommend' ? 1 : 0;

		if ('pushto' == $action || 'recommend' == $action) {
			require_once M_P . 'require/pingfen.php';
		}

		$pushdataService->insertPushdata(array(
			'invokepieceid' => $subinvoke,
			'editor' => $windid,
			'starttime' => $starttime,
			'vieworder' => $offset,
			'data' => $param,
			'titlecss' => $css,
			'ifbusiness' => $ifbusiness,
			'ifverify' => $ifverify
		));

		if ($portalPageService->checkPortal($channel))	$portalPageService->setPortalStaticState($channel,1);
		if ('pushto' == $action || 'recommend' == $action) {
			$ifrecommend = 'recommend' == $action ? 1 : 0;
			ObHeader($baseUrl . "action=success&tid=$pushkey&ifrecommend=$ifrecommend");
		} elseif ('add' == $action) {
			refreshto($baseUrl . "action=", 'operate_success');
		}
	}
}
require_once areaLoadFrontView('area_manage');
areaFooter();


function getSubjectByTid($tid) {
	global $db;
	$tid = (int) $tid;
	return $db->get_one("SELECT t.subject,t.author,p.content FROM pw_threads t LEFT JOIN pw_tmsgs p USING(tid) WHERE t.tid=" . pwEscape($tid));
}

function manageActionView($action) {
	switch ($action) {
		case 'edit':
		case 'add' :
			return areaLoadFrontView('area_manage_edit');
		case 'editconfig':
			return areaLoadFrontView('area_manage_editconfig');
		case 'edittpl':
			return areaLoadFrontView('area_manage_edittpl');
		case 'pushto':
		case 'recommend':
			return areaLoadFrontView('area_manage_pushto');
		case 'success':
			return areaLoadFrontView('area_manage_success');
		default :
			return areaLoadFrontView('area_manage_default');
	}
	return areaLoadFrontView();
}
function ifcheck($var,$out) {
	$GLOBALS[$out.'_Y'] = $GLOBALS[$out.'_N'] = '';
	$GLOBALS[$out.'_'.($var ? 'Y' : 'N')] = 'checked';
}
?>