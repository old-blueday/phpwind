<?php
/**
 * 云盾服务入口
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
function yunPostDefend($authorid, $author, $groupid, $id, $title, $content, $type = 'thread', $expand = array()) {
	$defendService = _getYunDefendService ();
	return $defendService->postDefend ( $authorid, $author, $groupid, $id, $title, $content, $type, $expand );
}
function yunUserDefend($operate, $uid, $username, $accesstime, $viewtime, $status = 0, $reason = "", $content = "", $behavior = array(), $expand = array()) {
	$defendService = _getYunDefendService ();
	return $defendService->userDefend ( $operate, $uid, $username, $accesstime, $viewtime, $status, $reason, $content, $behavior, $expand );
}
function _getYunDefendService() {
	static $defendService = null;
	if (! $defendService) {
		require_once R_P . 'lib/cloudwind/defend/yundefend.class.php';
		$defendService = new PW_YunDefend ();
	}
	return $defendService;
}