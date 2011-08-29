<?php
!function_exists('adminmsg') && exit('Forbidden');
!$action && $action = 'weibo';
$weiboService = L::loadClass('weibo', 'sns');/* @var $weiboService PW_Weibo */
require_once(R_P.'require/showimg.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
$nav = array("$action" => "class='current'");

if ($action == 'weibo') {

	if ($job == 'delete') {

		S::gp(array('mids','content','username','postdate_s','postdate_e','ordertype','page','lines','weibotype'));
		empty($mids) && adminmsg("no_weibo_mids","$basename&action=weibo");

		$urladd  = $content ? '&content='.rawurlencode($content) : '';
		$urladd .= $username ? '&username='.rawurlencode($username) : '';
		$urladd .= $weibotype != '-1' ? '&weibotype='.$weibotype : '';
		$urladd .= $postdate_s ? '&postdate_s='.rawurlencode($postdate_s) : '';
		$urladd .= $postdate_e ? '&postdate_e='.rawurlencode($postdate_e) : '';
		$urladd .= $ordertype ? '&ordertype='.rawurlencode($ordertype) : '';
		$urladd .= $lines ? '&lines='.rawurlencode($lines) : '';
		$urladd .= $page ? '&page='.rawurlencode($page) : '';
		$weiboService->deleteWeibos($mids);
		adminmsg('operate_success',"$basename&action=weibo&job=list$urladd");
		
	} else {

		S::gp(array('content','username','postdate_s','postdate_e','ordertype','page','lines','weibotype'));
		$weiboTypeDesc = $weiboService->getValueMapDescript();
		$lines = $lines ? $lines : $db_perpage;
		$postdateStartString = $postdate_s && is_numeric($postdate_s) ? get_date($postdate_s, 'Y-m-d') : $postdate_s;
		$postdateEndString = $postdate_e && is_numeric($postdate_e) ? get_date($postdate_e, 'Y-m-d') : $postdate_e;
		$postdate_e && $sqlpostdate = PwStrtoTime($postdate_e) + 86400;
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';
		$weibotypeSelect = $weibotype == '-1' ? array('please'=>'selected') : array($weibotype=>'selected');
				
		intval($lines) < 1 && $lines=30;
		intval($page)  < 1 && $page = 1;
		$urladd  = $content ? '&content='.rawurlencode($content) : '';
		$urladd .= $username ? '&username='.rawurlencode($username) : '';
		$urladd .=  '&weibotype='.$weibotype;
		$urladd .= $postdate_s ? '&postdate_s='.rawurlencode($postdate_s) : '';
		$urladd .= $postdate_e ? '&postdate_e='.rawurlencode($postdate_e) : '';		
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&ordertype=$ordertype&lines=$lines";
		list($count,$weibos) = $weiboService->adminSearch($username,$content,$postdate_s,$sqlpostdate,$weibotype,$ordertype,$page,$lines);
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=weibo&job=list$urladd&");
		require_once PrintApp('admin');
	}
} elseif ($action == 'topic') {
	$topicService = L::loadClass("topic","sns");
	if ($job == 'sethot') {	
		S::gp(array('topicid','ifhot'));
		if (!$topicid) adminmsg('operate_error',"$basename&action=topic&job=list");
		$urladd .=  '&ifhot='.$ifhot;
		$urladd .=  '&topicid='.$topicid;
	//	$ifhot = $ifhot ? 1 : 2;
		$topicService->setHotTopics($topicid,$ifhot);
		//$weiboHotTopics = $topicService->getHotTopics(10,$days);
		//$rt = $db->update("delete FROM pw_cache WHERE name='weiboHotTopics_10'");
		pwQuery::delete('pw_cache', 'name=:name', array('weiboHotTopics_10'));
		adminmsg('operate_success',"$basename&action=topic&job=list");
	}else{
		S::gp(array('topicname','ifhot','startnum','endnum','page','ordertype'));
		$page < 1 && $page = 1;
		${'sel_'.$ifhot} = 'selected';
		$perpage = 20;
		$urladd  = $topicname ? '&topicname='.rawurlencode($topicname) : '';
		$urladd .=  '&ifhot='.$ifhot;
		$urladd .= ($startnum >= 0) ? '&startnum='.rawurlencode($startnum) : '';
		$urladd .= ($endnum >= 0) ? '&endnum='.rawurlencode($endnum) : '';
		$ordertype = $ordertype == 'desc' ? 'desc' : 'asc';
		list($count,$topics) = $topicService->getAdminSearchResult($ifhot,$topicname,$startnum,$endnum,$ordertype,$page,$perpage);
		$numofpage = ceil($count/$perpage);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=topic&job=list$urladd&");
		require_once PrintApp('admin');
	}
} elseif ($action == 'comment') {

	if ($job == 'delete') {

		S::gp(array('cids','content','username','postdate_s','postdate_e','ordertype','page','lines'));
		empty($cids) && adminmsg("请选择要删除的新鲜事评论","$basename&action=comment");

		$urladd  = $content ? '&content='.rawurlencode($content) : '';
		$urladd .= $username ? '&username='.rawurlencode($username) : '';
		$urladd .= $postdate_s ? '&postdate_s='.rawurlencode($postdate_s) : '';
		$urladd .= $postdate_e ? '&postdate_e='.rawurlencode($postdate_e) : '';
		$urladd .= $ordertype ? '&ordertype='.rawurlencode($ordertype) : '';
		$urladd .= $lines ? '&lines='.rawurlencode($lines) : '';
		$urladd .= $page ? '&page='.rawurlencode($page) : '';
		
		$commentService = L::loadClass("comment","sns");
		$commentService->deleteComment($cids);
		adminmsg('operate_success',"$basename&action=comment&job=list$urladd");
		
	} else {

		S::gp(array('content','username','postdate_s','postdate_e','ordertype','page','lines'));
		$lines = $lines ? $lines : $db_perpage;
		$postdateStartString = $postdate_s && is_numeric($postdate_s) ? get_date($postdate_s, 'Y-m-d') : $postdate_s;
		$postdateEndString = $postdate_e && is_numeric($postdate_e) ? get_date($postdate_e, 'Y-m-d') : $postdate_e;
		$postdate_e && $sqlpostdate = PwStrtoTime($postdate_e) + 86400;
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';
			
		intval($lines) < 1 && $lines=30;
		intval($page)  < 1 && $page = 1;
		$urladd  = $content ? '&content='.rawurlencode($content) : '';
		$urladd .= $username ? '&username='.rawurlencode($username) : '';
		$urladd .= $postdate_s ? '&postdate_s='.rawurlencode($postdate_s) : '';
		$urladd .= $postdate_e ? '&postdate_e='.rawurlencode($postdate_e) : '';		
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&ordertype=$ordertype&lines=$lines";
		
		$commentService = L::loadClass("comment","sns");
		list($count,$comments) = $commentService->adminSearch($username,$content,$postdate_s,$sqlpostdate,$ordertype,$page,$lines);
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=comment&job=list$urladd&");
		require_once PrintApp('admin');
	}
}
?>