<?php
!defined('A_P') && exit('Forbidden');
$USCR = 'user_collection';
$baseUrl = 'apps.php?';
$basename = 'apps.php?q='.$q.'&';
InitGP(array('see', 'job'));
$collectionService = L::loadClass('Collection', 'collection'); /* @var $collectionService PW_Collection */

$a = $a ? $a : 'list';
InitGP(array('type'));
if ($a == 'list') {
	if ($type != 'postfavor') {
		$count = !$type ? $collectionService->countByUid($winduid) : $collectionService->countByUidAndType($winduid, $type);
		$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
		$collectionDb = ($count) ? (!$type ? $collectionService->findByUidInPage($winduid, $page, $db_perpage) : $collectionService->findByUidAndTypeInPage($winduid, $type, $page, $db_perpage)) : array();
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$basename}type=$type&");
	} else {
		
		InitGP(array('job','ftype'));
		if (empty($job)) {
			$page = (int)GetGP('page');
			!$ftype && $ftype = 'all';
			$ftype == '-1' && $ftype = 'def';
			
			$username = $windid;
			$where = '';
			
			$favor = $db->get_one("SELECT tids,type FROM pw_favors WHERE uid=".pwEscape($winduid));
			Add_S($favor);
			$ftypeSelection = array();
			if ($favor['type']) {
				$ftypeid = explode(',',$favor['type']);
				foreach ($ftypeid as $k => $v) {
					$ftypeSelection[$k+1] = $v;
				}
			}
			$ftypeSelection = formSelect('ftype', null, $ftypeSelection ? $ftypeSelection : array(0=>'无'), 'style="vertical-align:middle"');
			
			$tids = $ptids = array();
			list($tiddb,$favor_num) = getfavor($favor['tids']);
			if ($tiddb) {
				if ($ftype == 'all') {
					foreach ($tiddb as $key => $val) {
						if ($val) {
							$tids += $val;
						}
					}
				} elseif ($ftype == 'def') {
					$tids = $tiddb['0'];
				} elseif ($tiddb[$ftype]) {
					$tids = $tiddb[$ftype];
				}
				
				$db_perpage = 20;
				$page<1 && $page = 1;
				$start_limit = intval(($page-1) * $db_perpage);
				$count = count($tids);
				$numofpage = ceil($count/$db_perpage);
				$numofpage < 1 && $numofpage = 1;
				$page > $numofpage && $page = $numofpage;
				$pages = numofpage($count,$page,$numofpage,$basename."a=$a&type=$type&ftype=$ftype&");
			}
			if ($tids) {
				$article = array();
				$tids = pwImplode($tids);
				$where .= ' ORDER BY postdate DESC';
				$query = $db->query("SELECT fid,tid,subject,postdate,author,authorid,anonymous,replies,hits,titlefont FROM pw_threads WHERE tid IN($tids) $where");
				while ($rt = $db->fetch_array($query)) {
					$rt['subject']	= substrs($rt['subject'],45);
					list($rt['postdate'],$rt['posttime']) = getLastDate($rt['postdate']);
					$keyvalue		= get_key($rt['tid'],$tiddb);
					if ($rt['anonymous'] && !in_array($groupid,array('3','4')) && $rt['authorid'] != $winduid) {
						$rt['author']	= $db_anonymousname;
						$rt['authorid'] = 0;
					}
					$ftype == 'all' && $tidarray[$keyvalue][$rt['tid']] = $rt['tid'];
					$rt['forum']	= $forum[$rt['fid']]['name'];
					$rt['sel']		= $ftypeid[$keyvalue-1];
					$article[]		= $rt;
				}
				$article = array_slice($article,$start_limit,$db_perpage);
			}
			//$thisbase .= "a=$a&";
			$favor_other_num = $db->get_value("SELECT count(*) as sum FROM pw_collection s WHERE s.uid=".pwEscape($winduid)." AND s.ifhidden=1");
			$sum = (int)$favor_num + (int)$favor_other_num;
			
		} elseif ($job == 'addtype') {
			PostCheck();
			(!$ftype || strlen($ftype)>20) && Showmsg('favor_cate_error');
			strpos($ftype,',') !== false && Showmsg('favor_cate_limit');
			$favor   = $db->get_one("SELECT type FROM pw_favors WHERE uid=".pwEscape($winduid));
			$newtype = $favor['type'];
			$newtype.= $newtype ? ",".stripslashes($ftype) : stripslashes($ftype);
			$newtype = addslashes(Char_cv($newtype));
			if ($favor) {
				$db->update("UPDATE pw_favors SET type=".pwEscape($newtype)."WHERE uid=".pwEscape($winduid));
			} else{
				$db->update("INSERT INTO pw_favors SET".pwSqlSingle(array('uid'=>$winduid,'type'=>$newtype)));
			}
			refreshto("{$basename}a=list&type={$type}&",'operate_success');
		} elseif ($job == 'clear') {

			PostCheck();
			
			InitGP(array('selid'),'P');
			!$selid && Showmsg('sel_error');
			$rs = $db->get_one("SELECT tids FROM pw_favors WHERE uid=".pwEscape($winduid));
			if ($rs) {
				list($tiddb) = getfavor($rs['tids']);

				$db->update("UPDATE pw_threads SET favors=favors-1 WHERE tid IN (".pwImplode($selid).")");
				$db->update("UPDATE pw_elements SET value=value-1 WHERE type='hotfavor' AND id IN (".pwImplode($selid).")");
				$db->update("UPDATE pw_elements SET value=value-1 WHERE type='newfavor' AND id IN (".pwImplode($selid).")");

				foreach ($selid as $key => $tid) {
					foreach ($tiddb as $k => $v) {
						if (in_array($tid,$v)) {
							unset($tiddb[$k][$tid]);
						}
					}
				}
				foreach ($tiddb as $key => $val) {
					if (empty($val)) {
						unset($tiddb[$key]);
					}
				}
				$newtids = makefavor($tiddb);
				$db->update("UPDATE pw_favors SET tids=".pwEscape($newtids)."WHERE uid=".pwEscape($winduid));
				refreshto("{$basename}type={$type}&",'operate_success');
			} else {
				Showmsg('job_favor_del');
			}
		} elseif ($job == 'change') {

			PostCheck();
			
			InitGP(array('selid'),'P');
			!$selid && Showmsg('sel_error');
			$rs = $db->get_one("SELECT tids FROM pw_favors WHERE uid=".pwEscape($winduid));
			if ($rs) {
				list($tiddb) = getfavor($rs['tids']);
				foreach ($selid as $key => $tid) {
					if (!is_numeric($tid)) continue;
					foreach ($tiddb as $k => $v) {
						if (in_array($tid,$v)) {
							unset($tiddb[$k][$tid]);
						}
					}
					$tiddb[$ftype][$tid] = $tid;
				}
				foreach ($tiddb as $key => $val) {
					if (empty($val)) {
						unset($tiddb[$key]);
					}
				}
				$newtids = makefavor($tiddb);
				$db->update("UPDATE pw_favors SET tids=".pwEscape($newtids)."WHERE uid=".pwEscape($winduid));
			}
			refreshto("{$basename}type={$type}&",'operate_success');

		}  elseif ($job == 'deltype') {

			PostCheck();
			
			(int)$ftype<1 && Showmsg('type_error');
			$tnum  = $ftype-1;
			$rs    = $db->get_one("SELECT tids,type FROM pw_favors WHERE uid=".pwEscape($winduid));
			list($tiddb) = getfavor($rs['tids']);
			$ftypedb= explode(',',$rs['type']);
			Add_S($ftypedb);
			unset($ftypedb[$tnum]);
			if ($tiddb[$ftype]) {
				foreach ($tiddb[$ftype] as $key => $val) {
					$tiddb['0'][$val] = $val;
				}
			}
			unset($tiddb[$ftype]);
			$newtids = makefavor($tiddb);
			$newtype = Char_cv(implode(',',$ftypedb));
			$db->update("UPDATE pw_favors SET ".pwSqlSingle(array('tids'=>$newtids,'type'=>$newtype))."WHERE uid=".pwEscape($winduid));
			refreshto("{$basename}type={$type}&",'operate_success');
		}
	}
} elseif ($a == 'post') {
	PostCheck();
	InitGP(array('link'),'P',1);
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
	InitGP(array('idarray'),'P',1);
	$collectionService->delete($idarray);
	refreshto("{$basename}type={$type}&",'operate_success');
}elseif ($a == 'recommend') {
	define('AJAX',1);
	define('F_M',true);
	if (empty($_POST['step'])) {
		InitGP(array('id'), null, 2);

		$friend = getFriends($winduid);
		if ($friend) {
			foreach ($friend as $key => $value) {
				$frienddb[$value['ftid']][] = $value;
			}
		}
		$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".pwEscape($winduid)." ORDER BY ftid");
		$friendtype = array();
		while ($rt = $db->fetch_array($query)) {
			$friendtype[$rt['ftid']] = $rt;
		}
		$no_group_name = getLangInfo('other','no_group_name');
		$friendtype[0] = array('ftid' => 0,'uid' => $winduid,'name' => $no_group_name);

		$a = 'recommend';
		$rt = $db->get_one("SELECT id,type,content,username FROM pw_collection WHERE id=" . pwEscape($id));

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

