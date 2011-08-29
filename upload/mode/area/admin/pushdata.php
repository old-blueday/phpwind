<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('action'));
$invokeService = L::loadClass('invokeservice', 'area');
$pushdataService = L::loadClass('pushdataservice', 'area');
S::gp(array('keyword','page','alias','invoke','invokepiece'));

if (!$action || $action =='viewverify') {
	$noformat = 1;
	$page = (int) $page;
	$page<=0 && $page =1;
	
	$portalPageService = L::loadClass('portalpageservice', 'area');
	$portalPages = $portalPageService->getPortalPages();

	$ifverify = $action ? 1 : 0;
	$invokes = $portalPageService->getPageInvokesForSelect($alias,$ifverify);

	if ($invokes && $invoke && $invokes[$invoke]) {
		$invokepieces = $invokes[$invoke]['pieces'];
	}
	$invokesForJs = pwJsonEncode($invokes);
	
	$searchArray = array('alias'=>$alias,'invoke'=>$invoke,'invokepiece'=>$invokepiece,'keyword'=>$keyword,'ifverify'=>$ifverify);
	$ajax_basename = EncodeUrl($basename."&ifverify=$ifverify");
	$pushdatas = $pushdataService->searchPushDatas($searchArray,$page);

	$editUrl = "{$basename}&page={$page}&alias={$alias}&invoke=$invoke&invokepiece={$invokepiece}";

	$url = $basename.'&';

	$pages = $pushdataService->searchPushdatasCount($searchArray,$page,$url);
	include PrintMode('pushdata');exit;
} elseif ($action=='edit') {
	S::gp(array('pushdataid','step'));
	$editUrl = "{$basename}&page={$page}&alias={$alias}&invoke=$invoke&invokepiece={$invokepiece}";
	$manageService = L::loadclass("AreaManage", 'area');
	$dataSourceService = L::loadClass('datasourceservice', 'area');
	if (!$step) {
		if(!($push = $pushdataService->getPushDataById($pushdataid))){
			adminmsg('抱歉,编辑数据有误',$baseUrl);
		}
		$temp_invokepiece = $invokepiece;
		$invokepiece = $invokeService->getInvokePieceByInvokeId($push['invokepieceid']);
		
		$invokepieceid = $invokepiece['id'];
		$invokename = $invokepiece['invokename'];
		$invokeInfo = $invokeService->getInvokeByName($invokename);
		$channelAlias = $invokeInfo['sign'];
		
		$portalPageService = L::loadClass('portalpageservice', 'area');
		$portalPages = $portalPageService->getPortalPages();
	
		$invokes = $portalPageService->getPageInvokesForSelect($channelAlias);
		
		$offsets = array(0=>"",1=>"",2=>"",3=>"",4=>"",5=>"");
		$offsets[$push['vieworder']] = 'checked="checked"';
		$stylename	       = $pushdataService->getTitleCss($push);
		ifcheck($push['ifbusiness'],'ifbusiness');
		$push['starttime'] = $push['starttime'] ? get_date($push['starttime'],'Y-m-d H:i') : '';
		
		if ($invokes && $invokename) {
			$invokepieces = $invokes[$invokename]['pieces'];
		}
		$invokesForJs = pwJsonEncode($invokes);

		$ajax_basename = EncodeUrl($basename);
		
		include PrintMode('pushdata');exit;
	} else {
		S::gp(array('param','offset','starttime','css','ifbusiness','invokepieceid'),'GP');
		$pushdataService = L::loadClass('pushdataservice', 'area');
		$pushdataService->editPushdata($pushdataid,array('invokepieceid'=>$invokepieceid,'editor'=>$admin_name,'starttime'=>$starttime,'vieworder'=>$offset,'data'=>$param,'titlecss'=>$css,'ifbusiness'=>$ifbusiness,'ifverify'=>0));

		$baseUrl = "{$basename}&page={$page}&alias={$alias}&invoke=$invoke&invokepiece={$invokepiece}";
		adminmsg('operate_success',$baseUrl);
	}
} elseif($action == "fetch" ) {
	$dataSourceService = L::loadClass('datasourceservice', 'area');
	define('AJAX',1);
	S::gp(array('pushdataid','invokepieceid'));
	if (!$invokepieceid) {
		ajax_footer();
	}
	$invokepiece = $invokeService->getInvokePieceByInvokeId($invokepieceid);

	$default	= array();

	$pushdataService = L::loadClass('pushdataservice', 'area');
	$push	= $pushdataService->getPushData($pushdataid);
	$default = $push['data'];
	$stylename	= $pushdataService->getTitleCss($push);
	require_once PrintMode('ajax_pushdata');ajax_footer();
} elseif ($action=='dels') {
	S::gp(array('selid'), '', 2);
	if (!$selid) Showmsg('请选择要删除的推送内容');
	$pushdataService->deletePushdatas($selid);
	adminmsg('operate_success');
} elseif ($action=='del') {
	S::gp(array('id'), '', 2);
	define('AJAX',1);
	$pushdataService->deletePushdata($id);
	echo getLangInfo('msg','operate_success')."\treload";
	ajax_footer();
} elseif ($action=='verify') {
	S::gp(array('id'), '', 2);
	define('AJAX',1);
	$pushdataService->verifyPushdata($id);
	echo getLangInfo('msg','operate_success')."\treload";
	ajax_footer();
} elseif ($action == 'channelchange') {
	S::gp(array('alias','ifverify'));
	define('AJAX',1);
	$portalPageService = L::loadClass('portalpageservice', 'area');
	
	$invokes = $portalPageService->getPageInvokesForSelect($alias,$ifverify);
	echo pwJsonEncode($invokes);
	ajax_footer();
} elseif ($action == 'verifys') {
	S::gp(array('selid'), '', 2);
	if (!S::isArray($selid)) Showmsg('请选择要审核的推送内容');
	foreach ($selid as $value) {
		$value = (int) $value;
		if (!$value) continue;
		$pushdataService->verifyPushdata($value);
	}
	adminmsg('operate_success');
}
?>