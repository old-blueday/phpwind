<?php
!defined('A_P') && exit('Forbidden');
S::gp(array('do'));
!$winduid && Showmsg('not_login');
$USCR = 'user_weibo';
S::gp(array('s'));
if($s) {//底部快捷
	require_once(R_P.'require/showimg.php');
	list($faceurl) = showfacedesign($winddb['icon'],1,'m');
	require_once PrintEot('m_weibo_bottom');
	pwOutPut();
}
extract(L::config(null, 'o_config'));
$whilelist = array(
	'post','my','attention','refer','comment','lookround','filterweibo','ajax',
	'transmit','postcomment','deletecomment','receive','replay','deleteweibo',
	'conloy','topics','posttopic','page'
);

if (!in_array($do,$whilelist)) {
	$do = 'my';
}
$nav = !in_array($do,array('receive'))  ? array($do => 'class="current"') : array('refer' => 'class="current"');
$perpage = 20;
if ($do == 'post') {
	
	PostCheck();
	S::gp(array('atc_content'),'GP');
	S::gp(array('uploadPic', 'ismessage','type'), 'GP');
	$type != 'sendweibo' && $type = 'weibo';
	if ($o_weibourl != 1) {
		preg_match('/http:\/\//i', $atc_content) && Showmsg('weibo_link_close');
	}
	if ($db_tcheck ){
		require_once(R_P.'require/postfunc.php');
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userInfo = $userService->get($winduid,false,true);
		$userInfo['postcheck'] = unserialize($userInfo['postcheck']);
		$userInfo['postcheck']['weibo'] == tcheck($atc_content) && Showmsg('weibo_content_same'); //内容验证
	}
	if (($return = $weiboService->sendCheck($atc_content, $groupid)) !== true) {
		Showmsg($return);
	}
	if ($ismessage) {
		require_once(A_P . 'weibo/require/sendmessage.php');
	}
	$extra = array();
	if ($uploadPic && is_array($uploadPic)) {
		$array = array();
		$query = $db->query("SELECT p.* FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid WHERE p.pid IN (" . S::sqlImplode($uploadPic) . ")");
		while ($rt = $db->fetch_array($query)) {
			$array[$rt['pid']] = $rt;
		}
		$extra['photos'] = $array;
	}
	if ($weiboService->send($winduid, $atc_content, $type, 0, $extra)) {
		if ($db_tcheck) {
			$userInfo['postcheck']['weibo'] = tcheck($atc_content);
			$userService->update($winduid, array(), array('postcheck' => serialize($userInfo['postcheck'])));
		}
		weibocredit('weibo_Post');
		Showmsg('mode_o_write_success');
	}  else {
		Showmsg('undefined_action');
	}
} elseif ($do == 'my') {

	$count = $weiboService->getUserWeibosCount($winduid);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getUserWeibos($winduid,$page,$perpage);
	$pages = numofpage($count, $page, $pageCount, 'apps.php?q=weibo&do=my&', null, 'weiboList.my');

} elseif ($do == 'attention') {

	$count = $weiboService->getUserAttentionWeibosNotMeCount($winduid);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getUserAttentionWeibosNotMe($winduid,$page,$perpage);
	$pages = numofpage($count, $page, $pageCount, 'apps.php?q=weibo&do=attention&', null, 'weiboList.attention');

} elseif ($do == 'conloy') {
	
	$colonyids = array();
	$query = $db->query("SELECT cm.ifadmin,c.id FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id LEFT JOIN pw_members cm2 ON c.admin=cm2.username WHERE cm.uid=" . S::sqlEscape($winduid) . " ORDER BY cm.addtime DESC");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ifadmin'] != '-1') {
			$colonyids[] = $rt['id'];
		} 
	}
	
	$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
	$count = $weiboService->getConloysWeibosCount($colonyids);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getConloysWeibos($colonyids,$page,$perpage);
	
} elseif ($do == 'refer') {
	
	if ($winddb['newreferto'] > 0) {
		$userService = L::loadClass('UserService', 'user');
		$userService->update($winduid, array(), array('newreferto' => 0));
		$winddb['newreferto'] = 0;
	}
	$count = $weiboService->getRefersToMeCount($winduid);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getRefersToMe($winduid, $page, $perpage);
	$pages = numofpage($count, $page, $pageCount, 'apps.php?q=weibo&do=refer&', null, 'weiboList.refer');

} elseif ($do == 'receive') {

	if ($winddb['newcomment'] > 0) {
		$userService = L::loadClass('UserService', 'user');
		$userService->update($winduid, array(), array('newcomment' => 0));
		$winddb['newcomment'] = 0;
	}
	$commentService = L::loadClass("comment","sns");
	$count = $commentService->getUserReceiveCommentsCount($winduid);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$commentList = $commentService->getUserReceiveComments($winduid,$page,$perpage);
	$pages = numofpage($count, $page, $pageCount, 'apps.php?q=weibo&do=receive&', null, 'weiboList.receive');

} elseif ($do == 'replay') {
	
	S::gp(array('mid','uids','identify','commentpage'), 'GP');
	$perpage = 10;
	$commentService = L::loadClass("comment","sns");
	$userService = L::loadClass('UserService', 'user');
	$users = $userService->getByUserIds(array($uids)); 
	$users = current($users);
	$replayusername = '回复@'.$users['username'].' : ';
	$count = $commentService->getUserCommentOfRelpaysCount($winduid,$mid,$uids);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($commentpage,$pageCount);
	$commentList = $commentService->getUserCommentOfRelpays($winduid,$mid,$uids,$page,$perpage);
	$id = $identify ? $mid.'_'.$identify : $mid;
	
} elseif ($do == 'comment') {

	S::gp(array('mid','identify','commentpage'), 'GP');
	$perpage = 10;
	$commentService = L::loadClass("comment","sns");
	$count = $commentService->getCommentsCountByMid($mid);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($commentpage,$pageCount);
	$commentList = $commentService->getCommentsByMid($mid,$page,$perpage);
	$id = $identify ? $mid.'_'.$identify : $mid;

} elseif ($do == 'postcomment') {

	S::gp(array('mid','ifsendweibo','writeContent','identify'), 'GP');
	$writeContent = nl2br($writeContent);
	$commentService = L::loadClass("comment","sns");
	if (($status = $commentService->commentCheck($writeContent)) !== true) {
		Showmsg($status);
	}
	$result = $commentService->comment($winduid,$mid,$writeContent);
	if ($result) {
		$weiboService->updateCountNum(array('replies' => 1), $mid);
		if ($ifsendweibo) {
			if (($weiboStatus = $weiboService->sendCheck($writeContent, $groupid)) !== true) {
				Showmsg($weiboStatus);
			}
			$weiboService->send($winduid, $writeContent, 'transmit',$mid, array());
			$weiboService->updateCountNum(array('transmit' => 1), $mid);
		}
		echo 'ok';
	} else {
		Showmsg("对于对方隐私设置，您的评论或者回复失败!");
	}
	$id = $identify ? $mid.'_'.$identify : $mid;

} elseif ($do == 'deletecomment') {

	S::gp(array('cid','mid'));
	$commentService = L::loadClass("comment","sns");
	if($commentService->deleteComment($cid)){
		$weiboService->updateCountNum(array('replies' => -1), $mid,'plus');
	}
	echo 'ok';
	
} elseif($do == 'deleteweibo') {

	S::gp(array('mid'));
	$weibo = $weiboService->getWeibosByMid($mid);
	if ($weibo && ($weibo['uid'] == $winduid || $SYSTEM['delweibo'] || S::inArray($windid, $manager))) {
		$weiboService->deleteWeibos($mid);
		$type = $weiboService->getType($weibo['type']);
		if ($type == 'weibo') {
			weibocredit('weibo_Delete');
		}
		$userCache = L::loadClass('Usercache', 'user');
		$userCache->delete($weibo['uid'], 'weibo');
		echo 'ok';
	} else {
		Showmsg("您要删除的微博不存在");
	}

} elseif ($do == 'lookround') {

	$count = $weiboService->getWeibosCount();
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getWeibos($page,$perpage);
	$pages = numofpage($count, $page, $pageCount, 'apps.php?q=weibo&do=lookround&', null, 'weiboList.lookround');

} elseif ($do == 'filterweibo') {
	S::gp(array('filter'));
	$count = $weiboService->getUserAttentionWeibosCount($winduid,$filter);
	$count > 200 && $count = 200;
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getUserAttentionWeibos($winduid,$filter,$page,$perpage);
	$pages = numofpage($weiboCount, $page, ceil($count/$perpage), 'apps.php?q=weibo&do=attention&', 10, 'weiboList.filterWeibo');

} elseif ($do == 'transmit') {

	S::gp(array('mid'), 'GP', 2);
	if (!$weibo = $weiboService->getWeibosByMid($mid)) {
		Showmsg('您转发的新鲜事不存在，或已被删除!');
	}
	$transmits = array();
	$type = $weiboService->getType($weibo['type']);
	if ($type == 'transmit' && $weibo['objectid']) {
		$transmits = $weiboService->getWeibosByMid($weibo['objectid']);		
	}
	
	$attentionService = L::loadClass('Attention', 'friend');/* @var $attentionService PW_Attention */
	$blackList = $attentionService->getBlackListToMe($winduid, array($weibo['uid']));
	
	if (empty($_POST['step'])) {
		S::gp(array('istopic','topicname'), 'GP');
		$uids = array($weibo['uid']);
		if ($transmits) {
			$uids[] = $transmits['uid'];
		}
		$userService = L::loadClass('UserService', 'user');
		$uInfo = $userService->getByUserIds($uids);
		$weibo = array_merge($weibo, $uInfo[$weibo['uid']]);
		if ($transmits) {
			$showInfo = array_merge($transmits, $uInfo[$transmits['uid']]);
			$dString = ' ||@' . $weibo['username'] . ':' . $weibo['content'];
		} else {
			$showInfo = $weibo;
			$dString = '';
		}
		if (strpos($showInfo['content'],'[s:') !== false && strpos($showInfo['content'],']') !== false) {
			$sParse = L::loadClass('smileparser', 'smile');
			$showInfo['content'] = $sParse->parse($showInfo['content']);
		}
		$showInfo['extra'] = $showInfo['extra'] ? unserialize($showInfo['extra']) : array();
		$id = 'transmit_' . $id;
	} else {
		S::gp(array('atc_content','ifcomment'), 'P');
		$tmid = $transmits ? $weibo['objectid'] : $mid;
					
		if (($return = $weiboService->sendCheck($atc_content, $groupid,true)) !== true) {
			Showmsg($return);
		}
		if ($weiboService->send($winduid, $atc_content, 'transmit', $tmid)) {
			$weiboService->updateCountNum(array('transmit' => 1), $tmid);
			if($transmits){
				$weiboService->updateCountNum(array('transmit' => 1), $mid);
			}
			if ($ifcomment) {
				$commentService = L::loadClass("comment","sns");
				if($commentService->comment($winduid,$mid,$atc_content)){
					$weiboService->updateCountNum(array('replies' => 1), $mid);
				}
			}
			echo "success";
			ajax_footer();
		}  else {
			Showmsg('undefined_action');
		}
	}
} elseif ($do == 'topics') {
	/*话题 聚合页*/
	S::gp(array('topic'));
	$weiboList = array();
	$topicService = L::loadClass('topic','sns'); /* @var $topicService PW_Topic */
	$weiboHotTopics = $topicService->getWeiboHotTopics();
	if ($topic) {
		$searcherService = L::loadclass ( 'searcher', 'search' ); /* @var $searcherService PW_Searcher */
		list ($count, $weiboList) = $searcherService->searchWeibo($topic,'','','',$page);
		//分页
		$pageCount = ceil($count / $perpage);
		$page = validatePage($page,$pageCount);
		$pages = numofpage($count, $page, $pageCount, 'apps.php?q=weibo&do=topics&topic='.urlencode($topic).'&', null, 'weiboList.topics');
		//end
		is_array($weiboList) && $weiboList = $weiboService->buildData($weiboList);
		$topicInfo = (array)$topicService->getTopicByName($topic);
		$attentionedTopic = $topicService->getOneAttentionedTopic($topicInfo['topicid'],$winduid);
	} elseif ($weiboHotTopics) {
		$topicIds = array_keys($weiboHotTopics);
		$relations = (array)$topicService->getWeiboByTopicIds($topicIds, 20);
		$tmpWeibo = $weiboService->getWeibosByMid(array_keys($relations));
		$tmpWeibo = $weiboService->buildData($tmpWeibo);
		if (is_array($tmpWeibo)) {
			//用户是否关注话题
			$attentionedTopics = $topicService->getAttentionedTopicByTopicIds($topicIds,$winduid);
			foreach ($relations as $k=>$v) {
				!$weiboList[$v['topicid']]['topic'] && $weiboList[$v['topicid']]['topic'] = $weiboHotTopics[$v['topicid']];
				$attentionedTopics[$v['topicid']] && $weiboList[$v['topicid']]['topic']['attentioned'] = 1;
				$weiboList[$v['topicid']]['weibo'][] = $tmpWeibo[$k];
			}
		}
	}
} elseif ($do == 'posttopic') {
	S::gp(array('step','topic'), 'P');
} elseif ($do == 'page') {
	S::gp(array('type','page'));
	if($type == 'attentionedtopics') {
		$page = intval($page);
		$attentionedTopics = array();
		$perpage = 10;
		!$topicService && $topicService = L::loadClass('topic','sns');
		$total = $topicService->getUserAttentionTopicNum($winduid);
		$pageCount = ceil($total / $perpage);
		$page = validatePage($page,$pageCount);
		$total && $attentionedTopics = (array)$topicService->getUserAttentionTopics($winduid,$page,$perpage);
		$page > 1 && $prepage = $page - 1;
		$page < $pageCount && $nextpage = $page + 1;
	}
}

if (defined('AJAX')) {

	require_once PrintEot('m_ajax');
	ajax_footer();

} else {
	$userCache = L::loadClass('UserCache', 'user');
	$cacheData = $userCache->get($winduid, array('recommendUsers' => 3));
	$recommendUsers = $cacheData['recommendUsers'];
	
	$rt = $db->get_one("SELECT * FROM pw_cache WHERE name='weiboAuthorSort_5' AND time>" . S::sqlEscape($timestamp - 86400));
	if ($rt) {
		$weiboAuthorSort = unserialize($rt['cache']);
		is_array($weiboAuthorSort) || $weiboAuthorSort = array();
	} else {
		$weiboAuthorSort = $weiboService->getAuthorSort(5);
		$db->update("REPLACE INTO pw_cache SET " . S::sqlSingle(array(
			'name'	=> 'weiboAuthorSort_5',
			'cache'	=> serialize($weiboAuthorSort),
			'time'	=> $timestamp
		)));
	}
	/* 右侧话题排行 */
	!$topicService && $topicService = L::loadClass('topic','sns'); /* @var $topicService PW_Topic */
	!$weiboHotTopics && $weiboHotTopics =$topicService->getWeiboHotTopics();
	
	/*获取用户关注的话题*/
	$perpage = 10;
	$attentionCount = $topicService->getUserAttentionTopicNum($winduid);
	$attentionCount && $attentionedTopics = (array)$topicService->getUserAttentionTopics($winduid,1,$perpage);
	$attentionCount > $perpage && $nextpage = 2;
	
	is_array($weiboHotTopics) || $weiboHotTopics = array();
	/* end 右侧话题排行 */
	require_once PrintEot('m_weibo');
	pwOutPut();
}

function weibocredit($action = 'weibo_Post'){
	global $o_weibo_creditset,$o_weibo_creditlog,$onlineip,$winduid,$windid,$credit;
	require_once(R_P.'require/credit.php');
	$o_weibo_creditset = unserialize($o_weibo_creditset);
	$type = ($action == 'weibo_Post') ? true : false;
	$creditset = ($type == true) ? getCreditset($o_weibo_creditset['Post'],$type) : getCreditset($o_weibo_creditset['Delete'],$type);
	$creditset = array_diff($creditset,array(0));
	//积分变动
	if ($creditlog = unserialize($o_weibo_creditlog)) {
		$credit->appendLogSet($creditlog,'weibo');
		$credit->addLog($action, $creditset, array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip
		));
	}
	if (!empty($creditset)) {
		$credit->sets($winduid,$creditset,true);
		updateMemberid($winduid);
	}
}