<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
define ( 'CLOUDWIND', dirname ( __FILE__ ) );
define ( 'CLOUDWIND_CLIENT_VERSION', 'phpwind8x' );
define ( 'CLOUDWIND_VERSION_DIR', CLOUDWIND . '/version/' . CLOUDWIND_CLIENT_VERSION );
require_once CLOUDWIND . '/client/core/public/core.security.class.php';
require_once CLOUDWIND . '/client/core/public/core.common.class.php';
class CloudWind {
	
	function yunCollectSQL($sql) {
		if (! CloudWind_getConfig ( 'yunsearch_search' ) || ! CloudWind_getConfig ( 'yunsearch_hook' )) {
			return true;
		}
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
			$service = $factory->getSearchAggregateService ();
		}
		$service->collectSQL ( $sql );
		return true;
	}
	
	function yunSearchEntry() {
		require_once CLOUDWIND . '/client/search/search.entry.php';
		$service = new CloudWind_Search_Entry ();
		$service->searcher ();
	}
	
	function yunRouter() {
		require_once CLOUDWIND . '/client/core/public/core.router.class.php';
		$service = new Core_Router_Service ();
		return $service->router ();
	}
	
	function yunApplyPlatform($siteurl, $sitename, $bossname, $bossphone, $marksite) {
		require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
		$factory = new CloudWind_Platform_Factory ();
		$service = $factory->getApplyService ();
		return $service->apply ( $siteurl, $sitename, $bossname, $bossphone, $marksite );
	}
	
	function getSearchGuessTemplateForRead($tid, $subject, $fid, $uid) {
		if (! CloudWind_getConfig ( 'yunsearch_search' )) {
			return '';
		}
		$yunExpand = CloudWind_getConfig ( 'yun_expand' );
		return ($yunExpand && $yunExpand ['guess_read_middle']) ? sprintf ( html_entity_decode ( $yunExpand ['guess_read_middle'] ), $tid, $subject, $fid, $uid ) : '';
	}
	
	function getSearchGuessTemplateForFooter($source, $fid, $uid, $tid, $subject) {
		$yunExpand = CloudWind_getConfig ( 'yun_expand' );
		if (CloudWind_getConfig ( 'yunsearch_search' ) && $yunExpand ['guess_pop_setting'] && in_array ( $source, explode ( "|", $yunExpand ['guess_pop_setting'] ) )) {
			return ($yunExpand && $yunExpand ['guess_pop_bottom']) ? sprintf ( html_entity_decode ( $yunExpand ['guess_pop_bottom'] ), $fid, $uid, $tid, $subject ) : '';
		}
		return '';
	}
	
	function getSearchKeywordsForList($fid, $uid) {
		if (! CloudWind_getConfig ( 'yunsearch_search' )) {
			return '';
		}
		$yunExpand = CloudWind_getConfig ( 'yun_expand' );
		return ($yunExpand && $yunExpand ['search_keyword_list']) ? sprintf ( html_entity_decode ( $yunExpand ['search_keyword_list'] ), $fid, $uid ) : '';
	}
	
	function getSearchGuessForRefer($fid, $uid) {
		if (! CloudWind_getConfig ( 'yunsearch_search' )) {
			return '';
		}
		$yunExpand = CloudWind_getConfig ( 'yun_expand' );
		if (! $yunExpand ['guess_refer_setting'] || ! $_SERVER ['HTTP_REFERER'] || strpos ( $_SERVER ['HTTP_REFERER'], CloudWind_getConfig ( 'g_bbsurl' ) ) === 0) {
			return '';
		}
		return ($yunExpand && $yunExpand ['guess_refer_bottom']) ? sprintf ( html_entity_decode ( $yunExpand ['guess_refer_bottom'] ), $fid, $uid, urlencode ( $_SERVER ['HTTP_REFERER'] ) ) : '';
	}
	
	function getSearchFooterTemplate($source, $fid, $uid, $tid, $subject) {
		$cloudwind_footer = CloudWind::getSearchGuessTemplateForFooter ( $source, $fid, $uid, $tid, $subject );
		$cloudwind_footer .= CloudWind::getSearchGuessForRefer ( $fid, $uid );
		return $cloudwind_footer;
	}
	
	// ---- customize for phpwind8x  ----
	

	function createCloudWindTables() {
		static $platformTableService = null;
		if (! $platformTableService) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
			$platformTableService = $factory->getPlatformTableService ();
		}
		return $platformTableService->createCloudWindTables ();
	}
	
	function getDefendGeneralService() {
		static $defendService = null;
		if (! $defendService) {
			require_once CLOUDWIND . '/client/defend/service/defend.factory.class.php';
			$factory = new CloudWind_Defend_Factory ();
			$defendService = $factory->getDefendGeneralService ();
		}
		return $defendService;
	}
	
	function getPlatformCheckServerService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND . '/client/platform/service/platform.factory.class.php';
			$factory = new CloudWind_Platform_Factory ();
			$service = $factory->getCheckServerService ();
		}
		return $service;
	}
	
	function getDefendPostVerifyService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
			$service = $factory->getDefendPostVerifyService ();
		}
		return $service;
	}
	
	function yunPostDefend($authorid, $author, $groupid, $id, $title, $content, $type = 'thread', $expand = array()) {
		$service = CloudWind::getDefendGeneralService ();
		return $service->postDefend ( $authorid, $author, $groupid, $id, $title, $content, $type, $expand );
	}
	
	function yunUserDefend($operate, $uid, $username, $accesstime, $viewtime, $status = 0, $reason = "", $content = "", $behavior = array(), $expand = array()) {
		$service = CloudWind::getDefendGeneralService ();
		$service->userDefend ( $operate, $uid, $username, $accesstime, $viewtime, $status, $reason, $content, $behavior, $expand );
		Cookie ( "ci", '' );
		return true;
	}
	
	function yunSetCookie($name, $tid = '', $fid = '') {
		global $timestamp;
		if (! $name)
			return false;
		Cookie ( "ci", $name . "\t" . $timestamp . "\t" . $tid . "\t" . $fid );
	}
	
	function checkSync() {
		if (! CloudWind::checkSyncLevel () || (! CloudWind_getConfig ( 'yunsearch_search' ) && ! CloudWind_getConfig ( 'yundefend_shield' ))) {
			return false;
		}
		$yunModel = CloudWind_getConfig ( 'yun_model' );
		if ($yunModel ['search_model'] != 100 || $yunModel ['userdefend_model'] == 100) {
			return true;
		}
		if ($yunModel ['postdefend_model'] == 100 && SCR == 'read') {
			return true;
		}
		return false;
	}
	
	function checkSyncLevel() {
		if (! $GLOBALS ['_G'] ['allowvisit'] || ! in_array ( SCR, array ('index', 'read', 'thread', 'post', 'login' ) ))
			return false;
		return true;
	}
	
	function getUserInfo() {
		$getCookie = GetCookie ( 'ci' );
		if (! $getCookie)
			return array ();
		return explode ( "\t", $getCookie );
	}
	
	function sendUserInfo($cloud_information) {
		if (! CLOUDWIND_SECURITY_SERVICE::isArray ( $cloud_information ) || SCR == 'yi')
			return false;
		list ( $operate, $leaveTime, $tid, $fid ) = $cloud_information ? $cloud_information : array ('', '' );
		
		if (! in_array ( $operate, array ('index', 'read', 'thread' ) ) || $operate == SCR)
			return false;
		$user = CloudWind::getOnlineUserInfo ();
		$viewTime = CloudWind_getConfig ( 'g_timestamp' ) - $leaveTime ? CloudWind_getConfig ( 'g_timestamp' ) - $leaveTime : '';
		CloudWind::yunUserDefend ( 'view' . $operate, $user ['uid'], $user ['username'], $leaveTime, $viewTime, 101, '', '', '', array ('uniqueid' => $tid . '-' . $fid ) );
		return true;
	}
	
	function getOnlineUserInfo() {
		if (! $GLOBALS ['winduid'] && ! GetCookie ( 'cloudClientUid' )) {
			Cookie ( "cloudClientUid", CloudWind::getNotLoginUid () );
		}
		$cloudClientUid = GetCookie ( 'cloudClientUid' ) ? GetCookie ( 'cloudClientUid' ) : CloudWind::getNotLoginUid ();
		return array ('uid' => $GLOBALS ['winduid'] ? $GLOBALS ['winduid'] : $cloudClientUid, 'username' => $GLOBALS ['windid'] ? $GLOBALS ['windid'] : '游客' );
	}
	
	function getNotLoginUid() {
		global $loginhash;
		$length = strlen ( $loginhash );
		for($i = 0; $i < $length; $i ++) {
			if ($i % 2 == 0)
				$odd .= ord ( $loginhash [$i] );
			if ($i % 2 != 0)
				$even .= ord ( $loginhash [$i] );
		}
		return substrs ( "$odd+$even", 8, 'N' );
	}

}
