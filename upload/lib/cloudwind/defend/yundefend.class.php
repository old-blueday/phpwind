<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云盾服务中心
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
define ( "DEFEND_SUCCESS", 1 );
define ( "DEFEND_POST_DIARY", 0 );
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class PW_YunDefend extends PW_YunBase {
	
	function postDefend($authorid, $author, $groupid, $id, $title, $content, $type = 'thread', $expand = array()) {
		if (0 == $GLOBALS ['db_yundefend_shield'] || ! ($setting = $this->getYunSetting ()) || $setting ['dunstatus'] == 100) {
			return DEFEND_SUCCESS;
		}
		$data = array ('id' => $id, 'localtime' => $GLOBALS ['timestamp'], 'bbsurl' => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'], 'charset' => $GLOBALS ['db_charset'], 'uid' => $authorid, 'username' => $author, 'groupid' => $groupid, 'title' => $title, 'content' => $content, 'type' => $type, 'ip' => pwGetIp (), 'refere' => $_SERVER ['REQUEST_URI'], 'useragent' => $_SERVER ['HTTP_USER_AGENT'], 'method' => $_SERVER ['REQUEST_METHOD'], 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'], 'operate' => $type, 'operateid' => $id, 'expand' => $expand );
		if (isset ( $GLOBALS ['db_yun_model'] ['postdefend_model'] ) && $GLOBALS ['db_yun_model'] ['postdefend_model'] == 100) {
			return $GLOBALS ['db']->query ( "INSERT INTO pw_log_postdefend (id,data) VALUES(NULL," . pwEscape ( PW_YunToolKit::arrayToString ( $data ) ) . ")" );
		}
		$result = $this->_sendPost ( $data, 'post' );
		if (! $result || strlen ( $result ) > 10) {
			return DEFEND_SUCCESS;
		}
		list ( $userAudit, $postAudit ) = explode ( "\t", $result );
		$this->_userAudit ( $authorid, $userAudit, $expand );
		$this->_postAudit ( $id, $postAudit, $type, $expand );
		return DEFEND_SUCCESS;
	}
	
	function userDefend($operate, $uid, $username, $accesstime, $viewtime, $status = 0, $reason = "", $content = "", $behavior = array(), $expand = array()) {
		if (0 == $GLOBALS ['db_yundefend_shield'] || ! ($setting = $this->getYunSetting ()) || $setting ['dunstatus'] == 100) {
			return DEFEND_SUCCESS;
		}
		$data = $this->_dataEncode ( array ('operate' => $operate, 'status' => $status, 'uid' => $uid, 'username' => $username, 'localtime' => $GLOBALS ['timestamp'], 'bbsurl' => $GLOBALS ['db_bbsurl'], 'bbsname' => $GLOBALS ['db_bbsname'], 'charset' => $GLOBALS ['db_charset'], 'ip' => pwGetIp (), 'refere' => $_SERVER ['REQUEST_URI'], 'useragent' => $_SERVER ['HTTP_USER_AGENT'], 'uniqueid' => $setting ['uniqueid'], 'identifier' => $setting ['identifier'], 'method' => $_SERVER ['REQUEST_METHOD'], 'accesstime' => $accesstime, 'viewtime' => $viewtime, 'reason' => $reason, 'content' => $content, 'expand' => $expand, 'behavior' => $behavior, 'version' => WIND_VERSION, 'os' => '' ) );
		if (isset ( $GLOBALS ['db_yun_model'] ['userdefend_model'] ) && $GLOBALS ['db_yun_model'] ['userdefend_model'] == 100) {
			$GLOBALS ['db']->query ( "INSERT INTO pw_log_userdefend (id,data) VALUES(NULL," . pwEscape ( $data ) . ")" );
		} else {
			$this->_sendRequest ( $this->_buildRequestUrl ( 'user' ) . '&data=' . $data, '', 5 );
		}
		return DEFEND_SUCCESS;
	}
	
	function syncDefend() {
		$post = $GLOBALS ['db']->get_one ( "SELECT * FROM pw_log_postdefend LIMIT 1" );
		if (! $post) {
			return false;
		}
		$data = PW_YunToolKit::stringToArray ( $post ['data'] );
		$this->_syncDefend ( $data );
		$GLOBALS ['db']->update ( "DELETE FROM pw_log_postdefend WHERE id = " . pwEscape ( $post ['id'] ) );
		return DEFEND_SUCCESS;
	}
	
	function _syncDefend($data) {
		$result = $this->_sendPost ( $data, 'post' );
		if (! $result || strlen ( $result ) > 10) {
			return DEFEND_SUCCESS;
		}
		list ( $userAudit, $postAudit ) = explode ( "\t", $result );
		$this->_userAudit ( $data ['uid'], $userAudit, $data ['expand'] );
		$this->_postAudit ( $data ['id'], $postAudit, $data ['type'], $data ['expand'] );
		return DEFEND_SUCCESS;
	}
	
	function setPostDefend($data) {
		$data = PW_YunToolKit::stringToArray ( $data );
		if (! $data) {
			return false;
		}
		$this->_userAudit ( $data ['uid'], $data ['ua'], $data ['ed'] );
		$this->_postAudit ( $data ['nid'], $data ['pa'], $this->_getTypes ( $data ['tp'] ), $data ['ed'] );
		return DEFEND_SUCCESS;
	}
	
	function _userAudit($id, $userAudit, $expand) {
		if ($userAudit != 200 || $GLOBALS ['db_yundefend_shielduser'] == 0) {
			return DEFEND_SUCCESS;
		}
		if (2 == $GLOBALS ['db_yundefend_shielduser']) {
			$userService = L::loadClass ( 'UserService', 'user' ); /* @var $userService PW_UserService */
			$userService->delete ( $id );
		}
		if (1 == $GLOBALS ['db_yundefend_shielduser']) {
			$userService = L::loadClass ( 'UserService', 'user' ); /* @var $userService PW_UserService */
			$userService->update ( $id, array ('groupid' => 6 ) );
			$userService->setUserStatus ( $id, PW_USERSTATUS_BANUSER, true );
		}
		return DEFEND_SUCCESS;
	}
	
	function _postAudit($id, $postAudit, $type, $expand) {
		if ($postAudit != 100 || $GLOBALS ['db_yundefend_shieldpost'] == 0) {
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
		$postVerifyService = $this->_getPostVerifyService ();
		$postVerifyService->insertPostVerify ( 1, $id, 0 );
		return $GLOBALS ['db']->query ( "UPDATE pw_threads SET ifcheck=0 WHERE tid=" . pwEscape ( $id ) );
	}
	function _postDiary($id, $postAudit, $expand) {
		if (intval ( DEFEND_POST_DIARY ) === 1) {
			$GLOBALS ['db']->query ( "DELETE FROM pw_diary WHERE did=" . pwEscape ( $id ) );
		}
		return true;
	}
	function _postReply($id, $postAudit, $expand) {
		if (! isset ( $expand ['tid'] ) || $expand ['tid'] < 1)
			return false;
		$postVerifyService = $this->_getPostVerifyService ();
		$postVerifyService->insertPostVerify ( 2, $expand ['tid'], $id );
		$postTable = GetPtable ( 'N', $expand ['tid'] );
		return $GLOBALS ['db']->query ( "UPDATE " . S::sqlMetadata ( $postTable ) . " SET ifshield=1 WHERE pid=" . pwEscape ( $id ) );
	}
	function _getPostVerifyService() {
		require_once R_P . 'lib/cloudwind/defend/yunpostverify.class.php';
		return new PW_YunPostVerify ();
	}
	function _getTypes($type) {
		$types = array (1 => 'thread', 2 => 'diary', 6 => 'reply' );
		return isset ( $types [$type] ) ? $types [$type] : '';
	}
	function _sendPost($data, $action, $timeout = 5) {
		return $this->sendPost ( $this->_buildRequestUrl ( $action ), $data, $timeout );
	}
	function _buildRequestUrl($action) {
		return "http://" . trim ( $this->getYunDunHost (), "/" ) . "/defend.php?a=" . $action;
	}
	function _sendRequest($host, $data, $timeout = 1) {
		$parse = parse_url ( $host );
		if (empty ( $parse ))
			return null;
		$parse ['path'] = str_replace ( array ('\\', '//' ), '/', $parse ['path'] ) . "?" . $parse ['query'];
		$content = "GET " . $parse ['path'] . " HTTP/1.1\r\n";
		$content .= "Host: " . $parse ['host'] . "\r\n";
		$content .= "Connection: close\r\n\r\n";
		if (! $fp = @fsockopen ( $parse ['host'], 80, $errnum, $errstr, $timeout ))
			return null;
		@fwrite ( $fp, $content );
		@fclose ( $fp );
		return true;
	}
	function _dataEncode($array) {
		return urlencode ( PW_YunToolKit::arrayToString ( $array ) );
	}
}