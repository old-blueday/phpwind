<?php
/**
 * @author Xie Jin <xiejin@alibaba-inc.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class JobHookitem extends PW_HookItem {
	function run () {
		$winduid = $this->getVar('winduid');
		$fid = $this->getVar('fid');
		require_once(R_P.'require/functions.php');
		initJob($winduid,"doPost",array('fid'=>$fid));
	}
}