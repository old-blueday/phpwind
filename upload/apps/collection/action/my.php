<?php
!defined('A_P') && exit('Forbidden');
$USCR = 'user_collection';
$baseUrl = 'apps.php?';
$basename = 'apps.php?q='.$q.'&';
S::gp(array('see', 'job'));
$collectionService = L::loadClass('Collection', 'collection'); /* @var $collectionService PW_Collection */
$db_perpage = 20;
$a = $a ? $a : 'list';
S::gp(array('type','ftype'));
if ($a == 'list') {
		$collectionTypeService = L::loadClass('CollectionTypeService', 'collection');
		$ftypeid = array();
		$ftypeNum = array();
		$ftypeSelection = array();
		$ftypeNumTotal = 0; 
	 
		$ftypeData = $collectionTypeService->getTypesByUid($winduid);
		$collectionService = L::loadClass('Collection', 'collection'); /* @var $collection PW_Collection */
		$typeNumArr = $collectionService->countTypesByUid($winduid);
		if (!is_array($typeNumArr)) $typeNumArr = array();
		$defaultNum = ($typeNumArr[-1] ) ?  $typeNumArr[-1] : 0;
		$ftypeNumTotal = $defaultNum;
		foreach ($ftypeData as $val) {
			$ftypeid[$val['ctid']] = $val['name'];
			$ftypeNum[$val['ctid']] = ($typeNumArr[$val['ctid']]) ?  $typeNumArr[$val['ctid']] : 0;
			$ftypeNumTotal = $ftypeNumTotal + $ftypeNum[$val['ctid']];
		}
		foreach ($ftypeid as $k => $v) {
			$ftypeSelection[$k] = $v;
		}
		!$ftype && $ftype = 'all';

		$count = !$type ? $collectionService->countByUid($winduid,$ftype) : $collectionService->countByUidAndType($winduid,$type,$ftype);
		$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
		$collectionDb = ($count) ? (!$type ? $collectionService->findByUidInPage($winduid, $page, $db_perpage, $ftype) : $collectionService->findByUidAndTypeInPage($winduid, $type, $page, $db_perpage, $ftype)) : array();
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$basename}type=$type&ftype=$ftype&");

} elseif ($a == 'post') {
	$totalCollection = $collectionService->countByUid($winduid);
	$totalCollection >= $_G['maxfavor'] && Showmsg('已达到用户组允许的收藏上限');
	PostCheck();
	S::gp(array('link'),'P',1);
	$link	= str_replace('&#61;','=',$link);
	!$link && Showmsg('链接地址不能为空');
	!preg_match("/^https?\:\/\/.{4,255}$/i", $link) && Showmsg('mode_share_link_error');
	$share['uid'] = $winduid;
	$share['username'] = $windid;
	$share['link'] = $link;
	$parselink = parse_url($link);
	if(preg_match("/(youku.com|youtube.com|sohu.com|sina.com.cn)$/i",$parselink['host'],$hosts)) {
		$hash = getVideo($link,$hosts[1]);
		if(!empty($hash)) {
			$type = "multimedia";
			$share['type'] = 'video';
			$share['video']['hash'] = $f_hash = $hash;
			$share['video']['host'] = $hosts[1];
		} else {
			//$type = "multimedia";
			$type = $share['type'] = 'web';
		}
		if (preg_match("/\.swf\??.*$/i",$link)) {
			$type = "multimedia";
			$share['type'] = 'flash';
			$f_hash = $share['link'];
		}
	} elseif (preg_match("/\.(mp3|wma)\??.*$/i",$link)) {
		$type = "multimedia";
		$share['type'] = 'music';
		$f_hash = $share['link'];
	} elseif (preg_match("/\.swf\??.*$/i",$link)) {
		$type = "multimedia";
		$share['type'] = 'flash';
		$f_hash = $share['link'];
	} else {
		$type = $share['type'] = 'web';
	}
	
	$collectionDate = array(
						'type'	=> 	$type,
						'uid'	=>	$winduid,
						'username'	=> $windid,
						'content'	=>	serialize($share),
						'postdate'	=>	$timestamp
					);
	if ($collectionService->insert($collectionDate)) {
		refreshto("{$basename}&",'operate_success');
	} else {
		Showmsg('data_error');
	}
} elseif($a == 'dels') {
	PostCheck();
	S::gp(array('idarray'),'P',1);
	$ids = $collectionService->checkCollectionIds($idarray,$winduid);
	$collectionService->delete($ids);
	refreshto("{$basename}type={$type}&",'operate_success');
} elseif($a == 'remove') {
	S::gp(array('ftype','idarray'));
	!$idarray && Showmsg('undefined_action');
	$return = $collectionService->remove($idarray,$ftype);
	if($return === true) {
		echo "success\t";ajax_footer();
	}
}  elseif ($a == 'recommend') {
	define('AJAX',1);
	define('F_M',true);
	if (empty($_POST['step'])) {
		S::gp(array('id'), null, 2);

		$friend = getFriends($winduid);
		if ($friend) {
			foreach ($friend as $key => $value) {
				$frienddb[$value['ftid']][] = $value;
			}
		}
		$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".S::sqlEscape($winduid)." ORDER BY ftid");
		$friendtype = array();
		while ($rt = $db->fetch_array($query)) {
			$friendtype[$rt['ftid']] = $rt;
		}
		$no_group_name = getLangInfo('other','no_group_name');
		$friendtype[0] = array('ftid' => 0,'uid' => $winduid,'name' => $no_group_name);

		$a = 'recommend';
		$rt = $db->get_one("SELECT id,type,content,username FROM pw_collection WHERE id=" . S::sqlEscape($id));

		if (empty($rt)) {
			Showmsg('data_error');
		}

		$temp = unserialize($rt['content']);

		$rt['link']	= $temp['link'];
		if ($rt['type']=='user') {
			$title = $temp['user']['username']."($rt[link])";
		} elseif ($rt['type']=='photo') {
			$belong	= getLangInfo('app','photo_belong');
			$title= $belong.$temp['photo']['username']."($rt[link])";
		} elseif ($rt['type']=='album') {
			$belong	= getLangInfo('app','photo_belong');
			$title = $belong.$temp['album']['username']."($rt[link])";
		} elseif ($rt['type']=='group') {
			$title = $temp['group']['name']."($rt[link])";
		} elseif ($rt['type']=='diary') {
			$title = $temp['diary']['subject']."($rt[link])";
		} elseif ($rt['type']=='topic') {
			$title = $temp['topic']['subject']."($rt[link])";
		} else {
			$title = $rt['link'];
		}
		$descrip = $temp['descrip'];
		$username = $rt['username'];
		$atc_name = getLangInfo('app',$rt['type']);
		require_once PrintEot('m_ajax');
		ajax_footer();
	}
}


require_once PrintEot('m_collection');
pwOutPut();

function getVideo($link, $host) {
	$matches = array();
	switch ($host) {
		case 'youku.com':
			preg_match("/v_show\/id_(\w+)\.html/",$link,$matches);
			break;
		case 'youtube.com':
			preg_match("/v\=([\w\-]+)/",$link,$matches);
			break;
		case 'sina.com.cn':
			preg_match("/\/(\d+)-(\d+)\.html/",$link,$matches);
			break;
		case 'sohu.com':
			preg_match("/\/(\d+)\/*$/",$link,$matches);
			break;
	}
	if(!empty($matches[1])) {
		$return = $matches[1];
	} else {
		$return = '';
	}
	return $return;
}

