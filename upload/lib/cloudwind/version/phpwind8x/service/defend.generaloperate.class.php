<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Defend_General_Operate extends CloudWind_Core_Service {
	
	function insertPostDefend($data) {
		return $GLOBALS ['db']->query ( "INSERT INTO pw_log_postdefend (id,data) VALUES(NULL," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( CloudWind_Core_ToolKit::arrayToString ( $data ) ) . ")" );
	}
	
	function insertUserDefend($data) {
		return $GLOBALS ['db']->query ( "INSERT INTO pw_log_userdefend (id,data) VALUES(NULL," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $data ) . ")" );
	}
	
	function getPostDefend() {
		return $GLOBALS ['db']->get_one ( "SELECT * FROM pw_log_postdefend LIMIT 1" );
	}
	
	function deletePostDefend($id) {
		return $GLOBALS ['db']->update ( "DELETE FROM pw_log_postdefend WHERE id = " . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $id ) );
	}
	
	function userAudit($id, $userAudit, $expand) {
		if ($userAudit != 200 || CloudWind_getConfig ( 'yundefend_shielduser' ) == 0) {
			return DEFEND_SUCCESS;
		}
		if (2 == CloudWind_getConfig ( 'yundefend_shielduser' )) {
			$userService = L::loadClass ( 'UserService', 'user' ); /* @var $userService PW_UserService */
			$userService->delete ( $id );
		}
		if (1 == CloudWind_getConfig ( 'yundefend_shielduser' )) {
			$userService = L::loadClass ( 'UserService', 'user' ); /* @var $userService PW_UserService */
			$userService->update ( $id, array ('groupid' => 6 ) );
			$userService->setUserStatus ( $id, PW_USERSTATUS_BANUSER, true );
			$banArray = array('uid' => $id, 'fid' => 0, 'type' => 2, 'startdate' => CloudWind_getConfig('g_timestamp'), 'days' => 0, 'admin' => '', 'reason' => '');
			$GLOBALS['db']->update("REPLACE INTO `pw_banuser` SET " . CLOUDWIND_SECURITY_SERVICE::sqlSingle($banArray), false);
		}
		return DEFEND_SUCCESS;
	}
	
	function postAudit($id, $postAudit, $type, $expand) {
		if ($postAudit != 100 || CloudWind_getConfig ( 'yundefend_shieldpost' ) == 0) {
			return DEFEND_SUCCESS;
		}
		switch ($type) {
			case 'thread' :
				$this->_postThread ( $id, $postAudit, $expand );
				break;
			case 'diary' :
				$this->_postDiary ( $id, $postAudit, $expand );
				break;
			case 'reply' :
				$this->_postReply ( $id, $postAudit, $expand );
				break;
			default :
				break;
		}
		return DEFEND_SUCCESS;
	}
	
	function _postThread($id, $postAudit, $expand) {
		$postVerifyService = $this->getPostVerifyService ();
		$postVerifyService->insertPostVerify ( 1, $id, 0 );
		$GLOBALS ['db']->query ( "UPDATE pw_threads SET ifcheck=0 WHERE tid=" . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $id ) );
		if (class_exists ( "Perf" )) {
			Perf::gatherInfo ( 'changeThreadWithForumIds', array ('fid' => $expand ['fid'] ) ); // 8.3+
		} else {
			$threadList = L::loadClass ( "threadlist", 'forum' );
			($threadList) && $threadList->refreshThreadIdsByForumId ( $expand ['fid'] );
		}
		return true;
	}
	
	function _postDiary($id, $postAudit, $expand) {
		if (intval ( DEFEND_POST_DIARY ) === 1) {
			$GLOBALS ['db']->query ( "DELETE FROM pw_diary WHERE did=" . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $id ) );
		}
		return true;
	}
	
	function _postReply($id, $postAudit, $expand) {
		if (! isset ( $expand ['tid'] ) || $expand ['tid'] < 1)
			return false;
		$postVerifyService = $this->getPostVerifyService ();
		$postVerifyService->insertPostVerify ( 2, $expand ['tid'], $id );
		$postTable = GetPtable ( 'N', $expand ['tid'] );
		return $GLOBALS ['db']->query ( "UPDATE " . CLOUDWIND_SECURITY_SERVICE::sqlMetadata ( $postTable ) . " SET ifshield=1 WHERE pid=" . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $id ) );
	}
	
	function getPostVerifyService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$factory = new CloudWind_Service_Factory ();
			$service = $factory->getDefendPostVerifyService ();
		}
		return $service;
	}

}