<?php
!defined('A_P') && exit('Forbidden');
define('AJAX', '1');
require_once (R_P . 'require/functions.php');

!$winduid && Showmsg('not_login');
$collectionService = L::loadClass('Collection', 'collection'); /* @var $collectionService PW_Collection */

if ($a == 'del') {
	InitGP(array('id'),'',2);
	if (!$id) Showmsg('undefined_action');
	$collection = $collectionService->get($id);
	!$collection && Showmsg('收藏不存在');
	if ($winduid != $collection['uid'] && !$isGM) {
		Showmsg('无权限删除');
	}
	$collectionService->delete($id);
//	if ($affected_rows = delAppAction('share',$id)) {
//		countPosts("-$affected_rows");
//	}
//	//积分变动
//	require_once(R_P.'require/credit.php');
//	$o_share_creditset = unserialize($o_share_creditset);
//	$creditset = getCreditset($o_share_creditset['Delete'],false);
//	$creditset = array_diff($creditset,array(0));
//	if (!empty($creditset)) {
//		require_once(R_P.'require/postfunc.php');
//		$credit->sets($share['uid'],$creditset,true);
//		updateMemberid($share['uid'],false);
//	}
//
//	if ($creditlog = unserialize($o_share_creditlog)) {
//		addLog($creditlog['Delete'],$share['username'],$share['uid'],'share_Delete');
//	}
//	updateUserAppNum($share['uid'],'share','minus');
	echo "success\t";ajax_footer();
} elseif ($a == 'favor') {
	
	InitGP(array('id', 'type'));
	!in_array($type,array('weibo','user','photo','album','group','groupactive','diary','topic','reply','cms')) && Showmsg('undefined_action');
	!$id && Showmsg('data_error');

	$collection = array();
	$collectionType = $type == 'groupactive' ? 'active' : $type;
	if ($collectionService->getByTypeAndTypeid($winduid, $collectionType, $id)) {
		Showmsg('job_favor_error');
	}
	if ($type == 'weibo') {
		$weiboService = L::loadClass('Weibo', 'sns'); /* @var $weiboService PW_Weibo */
		$weiboDb = $weiboService->getWeibosByMid($id);
		empty($weiboDb) && Showmsg('data_error');
		$userService = L::loadClass('Userservice', 'user'); /* @var $userService PW_Userservice */
			
		$collection['type'] = $weiboService->getType($weiboDb['type']);
		
		if ($collection['type'] == 'transmit') {
			$transmit = $weiboService->getWeibosByMid($weiboDb['objectid']);
			$transmit['username'] = $userService->getUserNameByUserId($transmit['uid']);
			$transmit['type'] = $weiboService->getType($transmit['type']);
			$collection['transmit'] = $transmit ? serialize($transmit) : array();
		}
		
		$collection['uid']	= $weiboDb['uid'];
		$collection['content']	= $weiboDb['content'];
		$collection['objectid'] =$weiboDb['objectid'];
		$collection['extra'] = $weiboDb['extra'];
		$collection['authorid'] = $weiboDb['uid'];

	} elseif ($type == 'diary') {
		$diaryService = L::loadClass('Diary' , 'diary'); /* @var $diaryService PW_Diary */
		$diary 	= $diaryService->get($id);
		empty($diary) && Showmsg('data_error');
		$collection['uid'] = $diary['uid'];
		$collection['link'] = $db_bbsurl.'/{#APPS_BASEURL#}q=diary&a=detail&uid='.$diary['uid']."&did=".$id;
		$collection['diary']['subject'] = $diary['subject'];
		updateDatanalyse($diary['did'],'diaryFav',$timestamp);
	} elseif ($type == 'photo') {
		InitGP(array('ptype'));
		if ($ptype != 'photo') {
			$album 	= $db->get_one("SELECT aname,ownerid,owner,lastphoto FROM pw_cnalbum WHERE atype='0' AND aid=" . pwEscape($id));
			empty($album) && Showmsg('data_error');
				$collection['type'] =  'album';
				$collection['link'] =  $db_bbsurl.'/{#APPS_BASEURL#}q=photos&a=album&uid='.$album['ownerid']."&aid=".$id;
				$collection['album']['aname']	= $album['aname'];
				$collection['uid']	= $album['ownerid'];
				//$collection['username']	= $album['owner'];
				$collection['album']['image']	= getphotourl($album['lastphoto']);
				$query = $db->query("SELECT pid FROM pw_cnphoto WHERE aid=" . pwEscape($id). " LIMIT 10");
				while ($rt = $db->fetch_array($query)) {
					$pids[] = $rt['pid'];
				}				
				if ($pids) {
					foreach ($pids as $pid) {
						updateDatanalyse($pid,'picFav',$timestamp);
					}
				}
				$db->free_result($query);
		} else {
			$photo = $db->get_one("SELECT p.pid,p.pintro,p.aid,p.path,p.uploader,p.ifthumb,a.aname,a.private,a.ownerid FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid WHERE p.pid=" . pwEscape($id) . " AND a.atype='0'");
			empty($photo) && Showmsg('data_error');
			$collection['type'] =  'photo';
			$collection['uid']	= $photo['ownerid'];
			$collection['link'] = $db_bbsurl.'/{#APPS_BASEURL#}q=photos&a=view&uid='.$photo['ownerid']."&aid=".$photo['aid'].'&pid='.$id;
			$collection['photo']['aname']	= $photo['aname'];
			$collection['photo']['pintro']	= $photo['pintro'];
			//$collection['photo']['username']	= $photo['uploader'];
			$collection['photo']['image']	= getphotourl($photo['path'],$photo['ifthumb']);
			updateDatanalyse($photo['pid'],'picFav',$timestamp);
		}
	} elseif ($type == 'group') {
		$group 	= $db->get_one("SELECT id,cname,cnimg,admin FROM pw_colonys WHERE id=" . pwEscape($id));
		empty($group) && Showmsg('data_error');
		if ($group['cnimg']) {
			list($cnimg) = geturl("cn_img/$group[cnimg]",'lf');
		} else {
			$cnimg = $imgpath.'/g/groupnopic.gif';
		}
		
		$collection['username']	= $group['admin'];
		$collection['link']	= $db_bbsurl.'/{#APPS_BASEURL#}q=group&cyid='.$id;
		$collection['group']['name']	= $group['cname'];
		$collection['group']['image']	= $cnimg;
	} elseif ($type == 'groupactive') {
		require_once(A_P . 'groups/lib/active.class.php');
		$newActive = new PW_Active();
		$active = $newActive->getActiveById($id);
		empty($active) && Showmsg('data_error');

		require_once(A_P. 'groups/lib/colonys.class.php');
		$newColony = new PW_Colony();
		$colony = $newColony->getColonyById($active['cid']);
		
		if ($active['poster']) {
			list($poster) = geturl("$active[poster]",'lf');
		} else {
			$poster = $imgpath.'/defaultactive.jpg';
		}
		
		$collection['uid']	= $active['uid']; 
		$collection['link']	= $db_bbsurl.'/apps.php?q=group&a=active&job=view&cyid=' .$colony['id'] . '&id=' . $active['id'];
		$collection['active']['type'] =  $type;
		$collection['active']['name']	= $active['title'];
		$collection['active']['image']	= $poster;
		$type	= 'active';
	} elseif ($type == 'cms') {
		define('M_P',1);
		require_once(R_P. 'mode/cms/require/core.php');
		$articleDB = C::loadDB('article');
		$article 	= $articleDB->get($id);
		empty($article) && Showmsg('data_error');
		$collection['uid'] = $article['userid'];
		$collection['link'] = $db_bbsurl.'/mode.php?m=cms&q=view&id='.$id;
		$collection['cms']['subject'] = $article['subject'];
	}
	
	if ($collection['uid']) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$collection['username'] = $userService->getUserNameByUserId($collection['uid']);
	} elseif ($collection['username']) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$collection['uid'] = $userService->getUserIdByUserName($collection['username']);
	}
	
	
	
	$collectionDate = array(
						'typeid'	=> 	$id,
						'type'		=> 	$type,
						'uid'		=>	$winduid,
						'username'	=> $windid,
						'content'	=>	serialize($collection),
						'postdate'	=>	$timestamp
					);
					
	if ($collectionService->insert($collectionDate)) {
		Showmsg('job_favor_success');ajax_footer();
	} else {
		Showmsg('data_error');
	}
}
?>