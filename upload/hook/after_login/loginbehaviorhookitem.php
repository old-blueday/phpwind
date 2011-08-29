<?php
/**
 * @author Xie Jin <xiejin@alibaba-inc.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class LoginBehaviorHookitem extends PW_HookItem {
	function run () {
		$db_md_ifopen = $this->getVar('db_md_ifopen');
		if ($db_md_ifopen) {
			$winduid = $this->getVar('winduid');
			require_once(R_P.'require/functions.php');
			doMedalBehavior($winduid,'continue_login');
		}
	}
}