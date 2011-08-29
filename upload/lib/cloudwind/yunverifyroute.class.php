<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索校验路由服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yunwalker.class.php';
class PW_YunVerifyRoute {
	function verifyDispatch() {
		$this->_checkIP ();
		$yunWalker = new PW_YunWalker ();
		$result = $yunWalker->router ();
		print_r ( $result );
		exit ();
	}
	function _checkIP() {
		require_once R_P . "lib/cloudwind/yunhook.php";
		yun_hook_iphook ();
	}
}