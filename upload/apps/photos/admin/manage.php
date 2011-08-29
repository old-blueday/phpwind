<?php
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
!$action && $action = 'albums';
if ($action == 'albums') {

	if ($job == 'delete') {

		S::gp(array('selid','aname','owner','crtime_s','crtime_e','lasttime_s','lasttime_e','private','lines','orderway','ordertype'));
		empty($selid) && adminmsg("no_album_selid", "$basename&action=albums");
		require_once(R_P . 'u/require/core.php');
		foreach ($selid as $key => $aid) {
			$query = $db->query("SELECT cn.pid,cn.path,cn.ifthumb,ca.ownerid FROM pw_cnphoto cn LEFT JOIN pw_cnalbum ca ON cn.aid=ca.aid WHERE cn.aid=" . S::sqlEscape($aid));
			if (($num = $db->num_rows($query)) > 0) {
				$affected_rows = 0;
				while ($rt = $db->fetch_array($query)) {
					$uids[] = $rt['ownerid'];
					pwDelatt($rt['path'], $db_ifftp);
					if ($rt['ifthumb']) {
						$lastpos = strrpos($rt['path'],'/') + 1;
						pwDelatt(substr($rt['path'], 0, $lastpos) . 's_' . substr($rt['path'], $lastpos), $db_ifftp);
					}
					$affected_rows += delAppAction('photo',$rt['pid'])+1;//TODO 效率？
				}
				pwFtpClose($ftp);
				countPosts("-$affected_rows");
			}
			$db->update("DELETE FROM pw_cnphoto WHERE aid=" . S::sqlEscape($aid));
			$db->update("DELETE FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid));
		}
		$uids = array_unique($uids);
		updateUserAppNum($uids,'photo','recount');
		adminmsg('operate_success',"$basename&action=albums&job=list&aname=".rawurlencode($aname)."&owner=".rawurlencode($owner)."&crtime_s=$crtime_s&crtime_e=$crtime_e&lasttime_s=$lasttime_s&lasttime_e=$lasttime_e&private=$private&lines=$lines&orderway=$orderway&ordertype=$ordertype&page=$page&");
	} elseif ($job == 'edit') {
		S::gp(array('aid'));
		$album = $db->get_one("SELECT * FROM pw_cnalbum WHERE aid=".S::sqlEscape($aid));
		empty($album) && Showmsg('album_not_exist',"$basename&action=albums");
		if (empty($_POST['step'])) {
			S::gp(array('aname','owner','crtime_s','crtime_e','lasttime_s','lasttime_e','private','lines','orderway','ordertype','page'));
			${'private_'.$album['private']} = 'selected';
			require_once PrintApp('admin');
		} else {
			S::gp(array('aname','aintro','private','pwd','repwd'));
			S::gp(array('url_aname','url_owner','url_crtime_s','url_crtime_e','url_lasttime_s','url_lasttime_e','url_private','url_lines','url_orderway','url_ordertype','url_page'));
			//密码情况
			if ($pwd) {
				if (strlen($pwd) < 3 || strlen($pwd) > 15) {
					Showmsg('photo_password_minlimit');
				}
				$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%','?');
				if (str_replace($S_key,'',$pwd) != $pwd) {
					Showmsg('illegal_password');
				}
				if ($pwd != $repwd) {
					Showmsg('password_confirm');
				}
				$pwd = md5($pwd);
				$sqlArr = array('aname' => $aname,'aintro' => $aintro, 'private' => $private, 'albumpwd'=>$pwd);
			} else {
				$sqlArr = array('aname' => $aname,'aintro' => $aintro, 'private' => $private);
			}
			$db->update("UPDATE pw_cnalbum SET ".S::sqlSingle($sqlArr)." WHERE aid=".S::sqlEscape($aid));
			adminmsg('operate_success',"$basename&action=albums&job=list&aname=".rawurlencode($url_aname)."&owner=".rawurlencode($url_owner)."&crtime_s=$url_crtime_s&crtime_e=$url_crtime_e&lasttime_s=$url_lasttime_s&lasttime_e=$url_lasttime_e&private=$url_private&lines=$url_lines&orderway=$url_orderway&ordertype=$url_ordertype&page=$url_page&");
		}
	} else {
		S::gp(array('aname','owner','crtime_s','crtime_e','lasttime_s','lasttime_e','private','lines','orderway','ordertype','page', 'searchDisplay'));
		$photoPrivateSelection = array(
			'-1'=>'不限制', 
			'0'=>'全站可见', 
			'1'=>'仅好友可见', 
			'2'=>'仅自己可见', 
			'3'=>'需要密码访问',
		);
		$photoPrivateSelection = formSelect('private', $private, $photoPrivateSelection, 'class="select_wa"');
		$orderBySelection = array(
			'crtime'=>'按发表时间排序',
			'lasttime'=>'最后更新时间',
		);
		$orderBySelection = formSelect('orderway', $orderway, $orderBySelection, 'class="select_wa fl mr20"');
		$crttimeStartString = $crtime_s && is_numeric($crtime_s) ? get_date($crtime_s, 'Y-m-d') : $crtime_s;
		$crttimeEndString = $crtime_e && is_numeric($crtime_e) ? get_date($crtime_e, 'Y-m-d') : $crtime_e;
		$lasttimeStartString = $lasttime_s && is_numeric($lasttime_s) ? get_date($lasttime_s, 'Y-m-d') : $lasttime_s;
		$lasttimeEndString = $lasttime_e && is_numeric($lasttime_e) ? get_date($lasttime_e, 'Y-m-d') : $lasttime_e;
		$lines = $lines <= 0 ? 30 : $lines;
		$private = null === $private ? -1 : $private;
		null === $searchDisplay && $searchDisplay = 'none';
		
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';
		
		$albumdb = array();
		if (empty($aname) && empty($owner) && empty($crtime_s) && empty($crtime_e) && empty($lasttime_s) && empty($lasttime_e) && ($private == '-1')) {
			$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
		} else {
		
		$encode_aname = rawurlencode($aname);
		$encode_owner = rawurlencode($owner);
		$crtime_s && !is_numeric($crtime_s) && $crtime_s = PwStrtoTime($crtime_s);
		$crtime_e && !is_numeric($crtime_e) && $crtime_e = PwStrtoTime($crtime_e);
		$crtime_e && $sqlcrtime_e = $crtime_e + 86400;
		$lasttime_s && !is_numeric($lasttime_s) && $lasttime_s = PwStrtoTime($lasttime_s);
		$lasttime_e && !is_numeric($lasttime_e) && $lasttime_e = PwStrtoTime($lasttime_e);
		$lasttime_e && $sqllasttime_e = $lasttime_e + 86400;
		$sql = "atype='0'";
		$urladd = '';
		if ($aname) {
			$aname = str_replace('*','%',$aname);
			$sql .= ' AND aname LIKE '.S::sqlEscape($aname);
			$urladd .= '&aname='.rawurlencode($aname);
		}
		if ($owner) {
			$owner = str_replace('*','%',$owner);
			$sql .= ' AND owner LIKE '.S::sqlEscape($owner);
			$urladd .= '&owner='.rawurlencode($owner);
		}
		if ($crtime_s) {
			$sql .= ' AND crtime>='.S::sqlEscape($crtime_s);
			$urladd .= "&crtime_s=$crtime_s";
		}
		if ($crtime_e) {
			$sql .= ' AND crtime<='.S::sqlEscape($sqlcrtime_e);
			$urladd .= "&crtime_e=$crtime_e";
		}
		if ($lasttime_s) {
			$sql .= ' AND lasttime>='.S::sqlEscape($lasttime_s);
			$urladd .= "&lasttime_s=$lasttime_s";
		}
		if ($lasttime_e) {
			$sql .= ' AND lasttime<='.S::sqlEscape($sqllasttime_e);
			$urladd .= "&lasttime_e=$lasttime_e";
		}
		if ($private != -1) {
			$sql .= ' AND private='.S::sqlEscape($private);
			$urladd .= "&private=$private";
		}
		$orderway = $orderway == 'crtime' ? 'crtime' : 'lasttime';
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&orderway=$orderway&ordertype=$ordertype&lines=$lines&searchDisplay=$searchDisplay";
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cnalbum WHERE $sql");
		//empty($count) && adminmsg('album_not_exist',"$basename&action=albums");
		!is_numeric($lines) && $lines=30;
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=$action&job=list$urladd&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);
		$query = $db->query("SELECT aid,aname,private,ownerid,owner,photonum,lasttime,lastpid,crtime FROM pw_cnalbum WHERE $sql "."ORDER BY $orderway $ordertype ".$limit);
		while ($rt = $db->fetch_array($query)) {
			$rt['s_aname'] = substrs($rt['aname'],30);
			$rt['lasttime'] = $rt['lasttime'] ? get_date($rt['lasttime']) : '-';
			$rt['crtime'] 	= $rt['crtime'] ? get_date($rt['crtime']) : '-';
			$albumdb[] = $rt;
		}
		
		}
		
		$aname = str_replace('%', '*', $aname);
		$owner = str_replace('%', '*', $owner);
		
		require_once PrintApp('admin');
	}
} elseif ($action == 'photos') {
	
	${'ordertypedesc'} = 'checked';
		
	if ($job == 'delete') {

		S::gp(array('aid','aname','uploader','pintro','uptime_s','uptime_e','orderway','ordertype','lines','page','selid'));
		require_once(R_P . 'u/require/core.php');
		foreach ($selid as $key => $pid) {
			$photo = $db->get_one("SELECT cp.path,ca.aid,ca.lastphoto,ca.lastpid,ca.ownerid FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid WHERE cp.pid=" . S::sqlEscape($pid) . " AND ca.atype='0'");
			if (empty($photo)) {
				adminmsg('data_error',"$basename&action=photos");
			}
			$uids[] = $photo['ownerid'];
			$db->update("DELETE FROM pw_cnphoto WHERE pid=" . S::sqlEscape($pid));

			$pwSQL = array();
			if ($photo['path'] == $photo['lastphoto']) {
				$pwSQL['lastphoto'] = $db->get_value("SELECT path FROM pw_cnphoto WHERE aid=" . S::sqlEscape($photo['aid']) . " ORDER BY pid DESC LIMIT 1");
			}
			if (strpos(",$photo[lastpid],",",$pid,") !== false) {
				$pwSQL['lastpid'] = implode(',',getLastPid($photo['aid']));
			}
			$upsql = $pwSQL ? ',' . S::sqlSingle($pwSQL) : '';
			$db->update("UPDATE pw_cnalbum SET photonum=photonum-1{$upsql} WHERE aid=" . S::sqlEscape($photo['aid']));

			pwDelatt($photo['path'], $db_ifftp);
			$lastpos = strrpos($photo['path'],'/') + 1;
			pwDelatt(substr($photo['path'], 0, $lastpos) . 's_' . substr($photo['path'], $lastpos), $db_ifftp);
			pwFtpClose($ftp);

			$affected_rows = delAppAction('photo',$pid) + 1;
			countPosts("-$affected_rows");
		}
		$uids = array_unique($uids);
		updateUserAppNum($uids,'photo','recount');
		adminmsg('operate_success',"$basename&action=photos&job=list&aid=$aid&aname=".rawurlencode($aname)."&uploader=".rawurlencode($uploader)."&pintro=".rawurlencode($pintro)."&uptime_s=$uptime_s&uptime_e=$uptime_e&orderway=$orderway&ordertype=$ordertype&lines=$lines&page=$page&");

	} else {

		require_once(R_P . 'u/require/core.php');
		S::gp(array('aid','aname','uploader','pintro','uptime_s','uptime_e','orderway','ordertype','lines','page'));
		$cnpho = array();
		$orderBySelection = array(
			'default'=>'默认排序',
			'uptime'=>'上传日期',
			'hits'=>'浏览数',
			'c_num'=>'评论数',
		);
		$orderBySelection = formSelect('orderway', $orderway, $orderBySelection, 'class="select_wa fl mr20"');
		$uptimeStartString = $uptime_s && is_numeric($uptime_s) ? get_date($uptime_s, 'Y-m-d') : $uptime_s;
		$uptimeEndString = $uptime_e && is_numeric($uptime_e) ? get_date($uptime_e, 'Y-m-d') : $uptime_e;
		!is_numeric($lines) && $lines = 30;
		
		if (empty($aid) && empty($aname) && empty($uploader) && empty($pintro) && empty($uptime_s) && empty($uptime_e)) {
			$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
		} else {
		$uptime_s=$uptime_s && !is_numeric($uptime_s) ?  PwStrtoTime($uptime_s):$uptime_s;
		$uptime_e=$uptime_e && !is_numeric($uptime_e) ?  PwStrtoTime($uptime_e):$uptime_e;	
		$uptime_e && $sqluptime_e = $uptime_e + 86400;
		$urladd = '';
		$sql = "ca.atype='0'";
		if ($aid) {
			$sql .= ' AND ca.aid ='.S::sqlEscape($aid);
			$urladd .= '&aid='.$aid;
		}
		if ($aname) {
			$aname = str_replace('*','%',$aname);
			$sql .= ' AND ca.aname LIKE '.S::sqlEscape($aname);
			$urladd .= '&aname='.rawurlencode($aname);
		}
		if ($uploader) {
			$uploader = str_replace('*','%',$uploader);
			$sql .= ' AND cp.uploader LIKE '.S::sqlEscape($uploader);
			$urladd .= '&uploader='.rawurlencode($uploader);
		}
		if ($pintro) {
			$pintro = str_replace('*','%',$pintro);
			$sql .= ' AND cp.pintro LIKE '.S::sqlEscape($pintro);
			$urladd .= '&pintro='.rawurlencode($pintro);
		}
		if ($uptime_s) {
			$sql .= ' AND cp.uptime>='.S::sqlEscape($uptime_s);
			$urladd .= "&uptime_s=$uptime_s";
		}
		if ($uptime_e) {
			$sql .= ' AND cp.uptime<='.S::sqlEscape($sqluptime_e);
			$urladd .= "&uptime_e=$uptime_e";
		}
		switch ($orderway) {
			case 'uptime' :
				$orderway = 'cp.uptime';
				$orderwayurl = 'uptime';
				break;
			case 'hits' :
				$orderway = 'cp.hits';
				$orderwayurl = 'hits';
				break;
			case 'c_num' :
				$orderway = 'cp.c_num';
				$orderwayurl = 'c_num';
				break;
			default:
				$orderway = '';break;
		}
		${'ordertypedesc'} = '';
		$ordertype == 'asc' ? 'asc' : 'desc';
		
		${'ordertype'.$ordertype} = 'checked';
		
		$sqladd = $orderway ? "ORDER BY $orderway $ordertype" : '';
		$urladd .= $orderwayurl ? "&orderway=$orderwayurl&ordertype=$ordertype" : '';
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid WHERE $sql");
		//empty($count) && adminmsg('no_photos',"$basename&action=photos&job=list");
		
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=$action&job=list&lines=$lines$urladd&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);
		$query = $db->query("SELECT cp.pid,cp.aid,cp.path,cp.uploader,cp.uptime,cp.ifthumb,cp.hits,cp.c_num,ca.aname FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid WHERE ".$sql." ".$sqladd." ".$limit);
		$cnpho = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['s_aname']	= substrs($rt['aname'],10);
			$rt['path']	= getphotourl($rt['path'], $rt['ifthumb']);
			$rt['uptime']	= get_date($rt['uptime']);
			$cnpho[] = $rt;
		}
		
		$aname = str_replace('%', '*', $aname);
		$uploader = str_replace('%', '*', $uploader);
		$pintro = str_replace('%', '*', $pintro);
		
		}
		
		require_once PrintApp('admin');
	}
}
?>