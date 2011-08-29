<?php
!function_exists('readover') && exit('Forbidden');
class JOB_Config {
	/*配置文件*/
		
	var $_members    = "members";
	var $_forums     = "forums";
	var $_modes      = "modes";
	var $_moderators = "moderators";
	var $_gifts      = "gifts";
	
	/*
	 * 任务类型与具体任务对应
	 */
	function getJobType($k=null){
		$data  = array();
		$data[$this->_members] = $this->members();
		$data[$this->_forums] = $this->forums();
		$data[$this->_modes] = $this->modes();
		$data[$this->_moderators] = $this->moderators();
		$data[$this->_gifts] = $this->gifts();
		$types = array();
		foreach($data as $type =>$v){
			foreach($v as $job=>$v){
				$types[$job] = $type;
			}
		}
		return $k ? $types[$k] :$types;
	}
	
	function jobs($k = null){
		$data = array(
			$this->_members    => "会员信息类",
			$this->_forums     => "论坛操作类",
			//$this->_modes      => "圈子操作类",
			//$this->_moderators => "版主管理类",
			$this->_gifts     => "红包奖励类",
		);
		return $k ? $data[$k] : $data;
	}
	/*
	 * 会员信息类
	 */
	function members($k = null){
		$data = array(
			'doUpdatedata'    =>'完善资料',
			'doUpdateAvatar'  =>'上传头像',
			'doSendMessage'   =>'发送消息',
			'doAddFriend'     =>'加好友',
			'doAuthAlipay'     =>'支付宝认证',
			'doAuthMobile'     =>'手机认证',
		);
		return $k ? $data[$k] : $data;
	}
	/*
	 * 论坛操作类
	 */
	function forums($k = null){
		$data = array(
			'doPost'         =>'发帖',
			'doReply'        =>'回复',
			//'doFavor'        =>'收藏',
			//'doForumShare'   =>'分享',
			//'doVote'         =>'评价',
			//'doUseTools'     =>'使用道具',
			//'doLookCard'     =>'查看用户名片',
		);
		return $k ? $data[$k] : $data;
	}
	/*
	 * 圈子操作类
	 */
	function modes($k = null){
		$data = array(
			//'doEntrySelf'    =>'进入个人空间',
			//'doEntryFriend'  =>'进入朋友个人空间',
			//'doWrite'        =>'发记录',
			//'doDiary'        =>'发日志',
			//'doPhoto'        =>'传照片',
			//'doModeShare'    =>'分享',
			//'doComment'      =>'评论',
		);
		return $k ? $data[$k] : $data;
	}
	/*
	 * 版主管理类
	 */
	function moderators($k = null){
		$data = array(
			//'doPing'     =>'评分',
			//'doHead'     =>'置顶',
			//'doDigest'   =>'精华',
			//'doLock'     =>'锁定',
			//'doUp'       =>'提前',
			//'doDown'     =>'压帖',
			//'doHighline' =>'加亮',
			//'doPush'     =>'推送',
		);
		return $k ? $data[$k] : $data;
	}
	/*
	 * 红包奖励类
	 */
	function gifts($k = null){
		$data = array(
			'doSendGift'    =>'红包发放',
		);
		return $k ? $data[$k] : $data;
	}
	
	/*
	 * 任务完成条件模板
	 */
	function condition($job){
		$jobName = $job['job'];
		$factor = unserialize($job['factor']);
		switch ($jobName){
			case 'doUpdatedata':
				return $this->finish_doUpdatedata($factor);
				break;
			case 'doUpdateAvatar':
				return $this->finish_doUpdateAvatar($factor);
				break;				
			case 'doSendMessage':
				return $this->finish_doSendMessage($factor);
				break;				
			case 'doAddFriend':
				return $this->finish_doAddFriend($factor);
				break;		
			case 'doPost':
				return $this->finish_doPost($factor);
				break;	
			case 'doReply':
				return $this->finish_doReply($factor);
				break;	
			case 'doSendGift':
				return $this->finish_doSendGift($factor);
				break;
			case 'doAuthAlipay':
				return $this->finish_doAuthAlipay($factor);
				break;
			case 'doAuthMobile':
				return $this->finish_doAuthMobile($factor);
				break;
			default :
				return '';
				break;
			
		}
	}
	
	function finish_doUpdatedata($factor){
		return '完善自己的个人资料后即可完成任务'.$this->getLimitTime($factor);
	}
	
	function finish_doUpdateAvatar($factor){
		return '成功上传个人头像后即可完成任务'.$this->getLimitTime($factor);
	}
	function finish_doAuthMobile($factor){
		return '成功绑定手机号码即可完成任务'.$this->getLimitTime($factor);
	}
	function finish_doAuthAlipay($factor){
		return '成功绑定支付宝帐号即可完成任务'.$this->getLimitTime($factor);
	}
	
	function finish_doSendMessage($factor){
		return '给 '.$factor['user'].' 发送消息'.$this->getLimitTime($factor);
	}
	
	function finish_doAddFriend($factor){
		if($factor['type'] == 1){
			return '将 '.$factor['user'].' 加为好友  '.$this->getLimitTime($factor);
		}else{
			return '成功加 '.$factor['num'].' 个好友后即可完成任务'.$this->getLimitTime($factor);
		}
	}
	
	function finish_doPost($factor){
		$forum = L::forum($factor['fid']);
		$title = '<a target="_blank" href="thread.php?fid='.$forum['fid'].'">'.$forum['name'].'</a>';
		return '在 【'.$title.'】 版块发 '.$factor['num'].' 个帖子即可完成任务 '.$this->getLimitTime($factor);
	}
	
	function finish_doReply($factor){
		if($factor['type'] == 1){
			$thread = $GLOBALS['db']->get_one("SELECT tid,subject FROM pw_threads WHERE tid=".S::sqlEscape($factor['tid']));
			if(!$thread){
				return '抱歉，指定的回复帖子不存在，请联系管理员';/*错误过滤*/
			}
			$title = '<a href="read.php?tid='.$thread['tid'].'" target="_blank">'.$thread['subject'].'</a>';
			return '在 【'.$title.'】这个帖子回复 '.$factor['replynum'].' 次即可完成任务  '.$this->getLimitTime($factor);
		}else{
			return '给【'.$factor['user'].'】 发布的任意帖子回复 '.$factor['replynum'].' 次即可完成任务'.$this->getLimitTime($factor);
		}
	}
	
	function finish_doSendGift($factor){
		return "申请任务后即可完成任务，得到奖励";
	}
	function getLimitTime($factor){
		return (isset($factor['limit']) && $factor['limit']>0 ) ? ",限制".$factor['limit']."小时内完成 " : "";
	}
	
}