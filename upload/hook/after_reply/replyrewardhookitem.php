<?php
defined('P_W') || exit('Forbidden');
class ReplyRewardHookitem extends PW_HookItem {
	function run () {
		$tid = $this->getVar('tid');
		$uid = $this->getVar('winduid');
		$pid = $this->getVar('pid');
		
		$replyRewardRecordService = L::loadClass('ReplyRewardRecord', 'forum');
		$GLOBALS['isReplyRewardSuccess'] = $replyRewardRecordService->rewardReplyUser($uid, $tid, $pid);
	}
}