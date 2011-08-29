<?php
!function_exists('readover') && exit('Forbidden');

$kmdService = L::loadClass('KmdService', 'forum');
$payLogInfo = $kmdService->getPayLogById($rt['extra_1']);

if ($payLogInfo && $payLogInfo['type'] == KMD_PAY_TYPE_ALIPAY && $payLogInfo['status'] == KMD_PAY_STATUS_NOTPAY) {
	$spreadInfo = $kmdService->getSpreadById($payLogInfo['sid']);
	$endTime = $timestamp + $spreadInfo['day'] * 86400;
	if (!$payLogInfo['kid']) { //新购买
		$kmdInfo = array('fid' => $payLogInfo['fid'], 'uid' => $payLogInfo['uid'], 'tid' => 0, 'status' => KMD_THREAD_STATUS_EMPTY, 'starttime' => $timestamp, 'endtime' => $endtime);
		$kmdService->addKmdInfo($kmdInfo);
	} else { //续费
		$kmdInfo = $kmdService->getKmdInfoByKid($payLogInfo['kid']);
		if ($kmdInfo && $kmdInfo['uid'] == $payLogInfo['uid'] && $kmdInfo['fid'] == $payLogInfo['fid']) {
			$updateKmdInfo = array('endtime' => $endTime);
			$kmdService->updateKmdInfo($updateKmdInfo, $kmdInfo['kid']);
		} else {
			$newKmdInfo = array('fid' => $payLogInfo['fid'], 'uid' => $payLogInfo['uid'], 'tid' => 0, 'status' => KMD_THREAD_STATUS_EMPTY, 'starttime' => $timestamp, 'endtime' => $endtime);
			$kmdService->addKmdInfo($newKmdInfo);
		}
	}
	$updatePayLog = array('status' => KMD_PAY_STATUS_PAYED);
	$kmdService->updatePayLog($updatePayLog, $payLogInfo['id']);
}
?>