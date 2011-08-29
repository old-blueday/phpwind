<?php
/**
 * @author Xie Jin <xiejin@alibaba-inc.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class PostBehaviorHookitem extends PW_HookItem {
	function run () {
		$winduid = $this->getVar('winduid');
		$winddb = $this->getVar('winddb');
		$db_md_ifopen = $this->getVar('db_md_ifopen');
		if ($db_md_ifopen) {
			require_once(R_P.'require/functions.php');
			doMedalBehavior($winduid,'continue_thread_post');
			doMedalBehavior($winduid,'continue_post',$winddb['lastpost']);
			$medalservice = L::loadClass('medalservice','medal');
			$medalservice->runAutoMedal($winddb,'post',$winddb['postnum'],1);
		}
	}
}