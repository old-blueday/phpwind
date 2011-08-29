<?php
/**
 * 平台安装与概况说明
 * @author L.IuHu.I@2011 developer.liuhui@gmail.com
 */
require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
$factory = new PW_YunExtendFactory ();
$_checkService = $factory->getYunCheckServerService ();
$cloudstatus = $_checkService->checkCloudWind ();
list ( $bbsname, $bbsurl, $bbsversion, $cloudversion ) = $_checkService->getSiteInfo ();
S::gp ( array ('step' ) );
$step = (! $step && $cloudstatus < 9) ? (($cloudstatus == 1) ? 5 : (($cloudstatus == 3) ? 3 : (empty ( $step ) ? 1 : $step))) : $step;
if ($step == 1) {
	//show agreement
} elseif ($step == 2) {
	S::gp ( 'agree' );
	if ($agree != 1)
		Showmsg ( '你没有同意《phpwind云服务使用协议》', $basename . '&step=1' );
	
	if (! $_checkService->checkHost ())
		Showmsg ( '无法连接云服务，请检查网络是否为本地环境', $basename . '&step=1' );
	
	if (! $_checkService->getServerStatus ()) {
		list ( $fsockopen, $parse_url, $isgethostbyname, $gethostbyname ) = $_checkService->getFunctionsInfo ();
		list ( $searchHost, $searchIP, $searchPort, $searchPing ) = $_checkService->getSearchHostInfo ();
		list ( $defendHost, $defendIp, $defendPort, $defendPing ) = $_checkService->getDefendHostInfo ();
	} else {
		$step = 3;
	}
} elseif ($step == 3) {
	if (! $_checkService->getServerStatus ())
		Showmsg ( '环境检测末通过，请联系论坛空间提供商解决' );
} elseif ($step == 4) {
	S::gp ( array ('siteurl', 'sitename', 'bossname', 'bossphone' ) );
	
	if (! $siteurl || ! $sitename || ! $bossname || ! $bossphone)
		Showmsg ( '站点信息请填写完整', $basename . '&step=3' );
	
	if (! ($marksite = $_checkService->markSite ()))
		Showmsg ( '云服务验证失败，请重试', $basename . '&step=3' );
	
	$_applyService = $factory->getYunApplyService ();
	if (! $_applyService->apply ( $siteurl, $sitename, $bossname, $bossphone, $marksite )) {
		$marksite = $_checkService->markSite ( false );
		Showmsg ( '申请云服务失败，请检查网络或重试', $basename . '&step=3' );
	}
	(is_null ( $db_yun_model )) && $_checkService->setYunMode ( array () );
	$step = 5;
} else {
	$yundescribe = $_checkService->getYunDescribe ();
}
include PrintEot ( 'yunbasic' );

