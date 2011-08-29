<?php
/**
 * @author Xie Jin <xiejin@alibaba-inc.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
class ReplyJobHookitem extends PW_HookItem {
	function run () {
		$tid = $this->getVar('tid');
		$db_htmifopen = $this->getVar('db_htmifopen');
		$winduid = $this->getVar('winduid');
		
		require_once(R_P.'require/functions.php');
		$_cacheService = Perf::gatherCache('pw_threads');
		$thread = ($page>1) ? $_cacheService->getThreadByThreadId($tid) : $_cacheService->getThreadAndTmsgByThreadId($tid);	
		initJob($winduid,"doReply",array('tid'=>$tid,'user'=>$thread['author']));
	}
}