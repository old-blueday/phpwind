<?php
/**
 * @author Xie Jin <xiejin@alibaba-inc.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class ReplyBehaviorHookitem extends PW_HookItem {
	function run () {
		$db_md_ifopen = $this->getVar('db_md_ifopen');
		$winduid = $this->getVar('winduid');
		$winddb = $this->getVar('winddb');
		$tpcarray = $this->getVar('tpcarray');
		require_once(R_P.'require/functions.php');
		doMedalBehavior($winduid,'continue_post',$winddb['lastpost']);
		if ($db_md_ifopen) {
			$medalservice = L::loadClass('medalservice','medal');
			$medalservice->runAutoMedal($winddb,'post',$winddb['postnum'],1);
		}
		
		if (!$tpcarray['replies']) {
			$userServer = L::loadClass('UserService', 'user');
			$userServer->updateByIncrement($winduid, array(), array('shafa' => 1));
			if ($db_md_ifopen) {
				$medalservice->runAutoMedal($winddb,'shafa',$winddb['shafa']+1,1);
			}
		}
	}
}