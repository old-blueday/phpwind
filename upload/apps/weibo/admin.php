<?php
!function_exists('adminmsg') && exit('Forbidden');
$weiboService = L::loadClass('weibo', 'sns');/* @var $weiboService PW_Weibo */
require_once(R_P.'require/showimg.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
$nav = array("$action" => "class='current'");

if (empty($action)) {

	if (empty($_POST['step'])) {
		
		require_once(R_P.'require/credit.php');
		!is_array($creditset = unserialize($o_weibo_creditset)) && $creditset = array();
		
		$creditlog = array();
		!is_array($weibo_creditlog = unserialize($o_weibo_creditlog)) && $weibo_creditlog = array();
		foreach ($weibo_creditlog as $key => $value) {
			foreach ($value as $k => $v) {
				$creditlog[$key][$k] = 'CHECKED';
			}
		}
	
		ifcheck($o_weibopost, 'weibopost');
		ifcheck($o_weibophoto, 'weibophoto');
		ifcheck($o_weibourl, 'weibourl');
		ifcheck($o_weibo_hottopicdays, 'weibo_hottopicdays');
		ifcheck($o_weibo_hotcommentdays, 'weibo_hotcommentdays');
		ifcheck($o_weibo_hottransmitdays, 'weibo_hottransmitdays');

		$creategroup = ''; $num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2 && $key !='6' && $key !='7' && $key !='3') {
				$num++;
				$htm_tr = $num % 4 == 0 ? '' : '';
				$g_checked = strpos($o_weibo_groups,",$key,") !== false ? 'checked' : '';
				$creategroup .= "<li><input type=\"checkbox\" name=\"groups[]\" value=\"$key\" $g_checked>$value</li>$htm_tr";
			}
		}
		$creategroup && $creategroup = "<ul class=\"list_A list_120 cc\">$creategroup</ul>";
		
		require_once PrintApp('admin');
		
	} else {
		
		S::gp(array('creditset','creditlog', 'weibophoto','weibopost', 'weibourl', 'weibotip','weibo_hottopicdays','weibo_hotcommentdays','weibo_hotfansdays','weibo_hottransmitdays'),'GP');
		S::gp(array('groups'),'GP',2);
		
		$updatecache = false;
		$config['weibophoto'] = $weibophoto ? 1 : 0;
		$config['weibopost'] = $weibopost ? 1 : 0;
		$config['weibourl'] = $weibourl ? 1 : 0;
		$config['weibo_hottopicdays'] = $weibo_hottopicdays ? int($weibo_hottopicdays) : 7 ;
		$config['weibo_hotcommentdays'] = $weibo_hotcommentdays ? intval($weibo_hotcommentdays) : 1 ;
		$config['weibo_hottransmitdays'] = $weibo_hottransmitdays ? intval($weibo_hottransmitdays) : 1 ;
		$config['weibo_hotfansdays'] = $weibo_hotfansdays ? intval($weibo_hotfansdays) : 1 ;
		$config['weibo_creditset'] = '';
		$config['weibotip'] = S::escapeStr($weibotip);
		$config['weibo_groups'] = is_array($groups) ? ','.implode(',',$groups).',' : '';

		if (is_array($creditset) && !empty($creditset)) {
			foreach ($creditset as $key => $value) {
				foreach ($value as $k => $v) {
					$creditset[$key][$k] = round($v,($k=='rvrc' ? 1 : 0));
				}
			}
			$config['weibo_creditset'] = addslashes(serialize($creditset));
		}
		is_array($creditlog) && !empty($creditlog) && $config['weibo_creditlog'] = addslashes(serialize($creditlog));
		foreach ($config as $key => $value) {
			if (${'o_'.$key} != $value) {
				$db->pw_update(
					'SELECT hk_name FROM pw_hack WHERE hk_name=' . S::sqlEscape("o_$key"),
					'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $value, 'vtype' => 'string')) . ' WHERE hk_name=' . S::sqlEscape("o_$key"),
					'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_$key", 'vtype' => 'string', 'hk_value' => $value))
				);
				$updatecache = true;
			}
		}
		$updatecache && updatecache_conf('o',true);
		adminmsg('operate_success');
	}
} elseif ($action == 'weibo') {

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
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';
		$weibotypeSelect = $weibotype == '-1' ? array('please'=>'selected') : array($weibotype=>'selected');
			
		intval($lines) < 1 && $lines=30;
		intval($page)  < 1 && $page = 1;
		$urladd  = $content ? '&content='.rawurlencode($content) : '';
		$urladd .= $username ? '&username='.rawurlencode($username) : '';
		$urladd .= $weibotype != '-1' ? '&weibotype='.$weibotype : '';
		$urladd .= $postdate_s ? '&postdate_s='.rawurlencode($postdate_s) : '';
		$urladd .= $postdate_e ? '&postdate_e='.rawurlencode($postdate_e) : '';		
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&ordertype=$ordertype&lines=$lines";
		
		list($count,$weibos) = $weiboService->adminSearch($username,$content,$postdate_s,$postdate_e,$weibotype,$ordertype,$page,$lines);
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=weibo&job=list$urladd&");
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
		list($count,$comments) = $commentService->adminSearch($username,$content,$postdate_s,$postdate_e,$ordertype,$page,$lines);
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=comment&job=list$urladd&");
		require_once PrintApp('admin');
	}
}
?>