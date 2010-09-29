<?php
if (isset($_GET['ajax'])) {
	define('AJAX','1');
}
require_once('global.php');

!$windid && Showmsg('not_login');
InitGP(array('action'));

$template = 'ajax_operate';
if (empty($_POST['step']) && !defined('AJAX')) {
	require_once(R_P.'require/header.php');
	$template = 'operate';
}

if ($action == 'showping') {
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/bbscode.php');
	require_once R_P . 'require/pingfunc.php';
	InitGP(array('selid','pid','page'));

	if (empty($selid) && empty($pid)) {
		Showmsg('selid_illegal');
	}
	$jump_pid = $pid ? $pid : $selid[0];
	empty($selid) && $selid = array($pid);
	!is_array($selid) && $selid = array($selid);
	$pids = $atcdb = array();
	$ptpc = '';
	foreach ($selid as $key => $val) {
		if (is_numeric($val)) {
			$pids[] = $val;
		} else {
			$ptpc = 1;
		}
	}
	$pw_tmsgs = GetTtable($tid);
	$atc = $db->get_one("SELECT t.fid,t.author,t.authorid,t.postdate,t.subject,t.anonymous,t.ptable,tm.content,tm.ifmark FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid=".pwEscape($tid));
	$subject = $atc['subject'];
	$fid = $atc['fid'];
	$ptpc && $atcdb['tpc'] = $atc;
	$pw_posts = GetPtable($atc['ptable']);

	if ($pids) {
		$pids = pwImplode($pids);
		$query = $db->query("SELECT pid,fid,author,authorid,postdate,subject,ifmark,anonymous,content FROM $pw_posts WHERE pid IN($pids) AND tid=".pwEscape($tid));
		while ($rt = $db->fetch_array($query)) {
			if (!$rt['subject']) {
				$rt['subject'] = 'RE:'.$atc['subject'];
			}
			$atcdb[$rt['pid']] = $rt;
		}
	}
	empty($atcdb) && Showmsg('data_error');

	if (!($foruminfo = L::forum($fid))) {
		Showmsg('data_error');
	}
	(!$foruminfo || $foruminfo['type'] == 'category') && Showmsg('data_error');
	wind_forumcheck($foruminfo);

	$admincheck = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);

	require_once(R_P.'require/credit.php');
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$mcredit = $userService->get($winduid, false, false, true);
	$_G['markset'] = unserialize($_G['markset']);

	$rcreditdb = array();
	if ($mcredit['credit']) {
		$creditdb = explode("|", $mcredit['credit']);
		foreach ($creditdb as $value) {
			$creditvalue = explode("\t",$value);
			if ($creditvalue['0'] >= $tdtime) {
				$rcreditdb[$creditvalue['2']]['pingdate'] = $creditvalue['0'];
				$rcreditdb[$creditvalue['2']]['pingnum'] = $creditvalue['1'];
				$rcreditdb[$creditvalue['2']]['pingtype'] = $creditvalue['2'];
			}
		}
	}
	$markset = $credittype = array();
	foreach ($_G['markset'] as $key => $value) {
		if ($value['markctype']) {
			$markset[$key]['minper']	= $value['marklimit'][0];
			$markset[$key]['maxper']	= $value['marklimit'][1];
			$markset[$key]['maxcredit']	= $value['maxcredit'];
			$markset[$key]['markdt']	= $value['markdt'];
			if (isset($rcreditdb[$key])) {
				$markset[$key]['leavepoint'] = abs($value['maxcredit'] - $rcreditdb[$key]['pingnum']);
			} else {
				$markset[$key]['leavepoint'] = $value['maxcredit'];
			}
			$credittype[] = $key;
		}
	}

	if ($winddb['groups']) {
		$gids = array();
		foreach (explode(',',$winddb['groups']) as $key => $gid) {
			is_numeric($gid) && $gids[] = $gid;
		}
		if ($gids) {
			$gids = pwImplode($gids);
			$mright = array();
			$query = $db->query("SELECT gid,rkey,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND gid IN($gids) AND rkey IN ('markset','markable') AND type='basic'");
			while ($rt = $db->fetch_array($query)) {
				$mright[$rt['gid']][$rt['rkey']] = $rt['rvalue'];
			}
			foreach ($mright as $key => $p) {
				if (is_array($p) && $p['markable']) {
					$p['markable'] > $_G['markable'] && $_G['markable'] = $p['markable'];
					$p['markset'] = (array)unserialize($p['markset']);
					foreach ($p['markset'] as $k => $v) {
						if ($v['markctype'] && in_array($k, $credittype)) {
							is_numeric($v['marklimit'][0]) && $v['marklimit'][0] < $markset[$k]['minper'] && $markset[$k]['minper'] = $v['marklimit'][0];
							is_numeric($v['marklimit'][1]) && $v['marklimit'][1] > $markset[$k]['maxper'] && $markset[$k]['maxper'] = $v['marklimit'][1];
							is_numeric($v['maxcredit']) && $v['maxcredit'] > $markset[$k]['maxcredit'] && $markset[$k]['maxcredit'] = $v['maxcredit'];

							if (isset($rcreditdb[$k])) {
								$markset[$k]['leavepoint'] = abs($markset[$k]['maxcredit'] - $rcreditdb[$k]['pingnum']);
							} else {
								$markset[$k]['leavepoint'] = $markset[$k]['maxcredit'];
							}
							!$v['markdt'] && $markset[$k]['markdt'] = 0;//正负->负 扣除积分权限
						}
					}
				}
			}
		}
	}

	$jscredit = pwJsonEncode($markset);
	if ((!$admincheck && !$_G['markable']) || !$credittype ) {
		Showmsg('no_markright');
	}
	$anonymous = 0;
	foreach ($atcdb as $pid => $atc) {
		if ($db_pingtime && $timestamp-$atc['postdate']>$db_pingtime*3600 && $gp_gptype!='system') {
			Showmsg('pingtime_over');
		}
		if ($winduid == $atc['authorid'] && !CkInArray($windid,$manager)) {
			Showmsg('masigle_manager');
		}
		$has_ping = $db->get_one("SELECT * FROM pw_pinglog WHERE fid=".pwEscape($fid)." AND tid=".pwEscape($tid)." AND pid=" . pwEscape(intval($pid)) . " AND pinger=".pwEscape($windid)." LIMIT 1");
		if ($_POST['step'] == 1 && $_G['markable'] < 2 && $has_ping) {
			Showmsg('no_markagain');
		}
		if ($_POST['step'] > 1 && !$has_ping) {
			Showmsg('have_not_showping');
		}
		$atc['anonymous'] && $anonymous++;
	}

	$count = count($atcdb);

	if (empty($_POST['step'])) {
		$ratelist = $raterange = array();
		$creditselect = '';
		foreach ($credittype as $key => $cid) {
			if ($markset[$cid]['minper'] || $markset[$cid]['maxper']) {
				if (isset($credit->cType[$cid])) {
					$creditselect .= '<option value="'.$cid.'">'.$credit->cType[$cid].'</option>';
					$raterange[$cid] = array('min'=>$markset[$cid]['minper'],'max'=>$markset[$cid]['maxper'],'mrpd'=>$markset[$cid]['leavepoint']);
				}
			}
		}
		$creditselect == '' && showmsg('评分设置有误，请联系管理员检查本用户组的评分设置');
		$ratelist = getratelist($raterange);
		$ratelist = pwJsonEncode($ratelist);
		$reason_sel = '';
		$reason_a = explode("\n",$db_adminreason);
		foreach ($reason_a as $k => $v) {
			if ($v = trim($v)) {
				$reason_sel .= "<option value=\"$v\">$v</option>";
			} else {
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		if ($anonymous == $count && $groupid!='3') {
			$check_Y = 'disabled';
			$check_N = 'checked';
		} else {
			$check_Y = 'checked';
			$check_N = '';
		}
		require_once PrintEot($template);footer();

	} elseif ($_POST['step'] == 1) {

		PostCheck();
		InitGP(array('cid','addpoint','ifmsg','atc_content'),'P');

		$add_c = $tmp = $pingLog = array();
		if (is_array($cid)) {
			foreach ($cid as $key => $value) {
				if ($value && isset($credit->cType[$value]) && is_numeric($addpoint[$key]) && $addpoint[$key] <> 0) {
					!in_array($value, $credittype) && Showmsg('masigle_credit_right');
					$tmp[$value] += intval($addpoint[$key]);
				}
			}

			foreach ($tmp as $key => $value) {
				if (!$value) continue;
				if ($value > $markset[$key]['maxper'] || $value < $markset[$key]['minper']) {
					$limitCreditType = $key;
					Showmsg('masigle_creditlimit');
				}
				$add_c[$key] = $value;
			}
		}

		empty($add_c) && Showmsg('member_credit_error');

		if (strlen($atc_content) > 100) Showmsg('showping_content_too_long');
		foreach ($add_c as $key => $value) {
			$allpoint = abs($value) * $count;
			if (isset($rcreditdb[$key])) {
				if ($rcreditdb[$key]['pingnum'] + $allpoint > $markset[$key]['maxcredit']) {
					$GLOBALS['leavepoint'] = $markset[$key]['maxcredit'];
					Showmsg('masigle_point');
				}
				$rcreditdb[$key]['pingdate'] = $timestamp;
				$rcreditdb[$key]['pingnum'] += $allpoint;
			} else {
				if ($allpoint > $markset[$key]['maxcredit']) {
					$GLOBALS['leavepoint'] = $markset[$key]['maxcredit'];
					Showmsg('masigle_point');
				}
				$rcreditdb[$key] = array(
					'pingdate'	=> $timestamp,
					'pingnum'	=> $allpoint,
					'pingtype'	=> $key
				);
			}
			if ($markset[$key]['markdt'] && $allpoint > 0) {
				$credit->get($winduid, $key) < $allpoint && Showmsg('credit_enough');
				$credit->set($winduid, $key, -$allpoint, false);
			}
		}
		$newcreditdb = '';
		foreach ($rcreditdb as $value) {
			$newcreditdb .= ($newcreditdb ? '|' : '') . implode("\t",$value);
		}
		$userService->update($winduid, array(), array(), array('credit' => $newcreditdb));
		
		$singlepoint = array_sum($add_c);
		foreach ($atcdb as $pid => $atc) {
			$tmpPid = $pid;
			!$atc['subject'] && $atc['subject'] = substrs(strip_tags(convert($atc['content'])),35);
			$credit->addLog('credit_showping', $add_c, array(
				'uid'		=> $atc['authorid'],
				'username'	=> $atc['author'],
				'ip'		=> $onlineip,
				'operator'	=> $windid,
				'tid'		=> $tid,
				'subject'	=> $atc['subject'],
				'reason'	=> $atc_content
			));
			$credit->sets($atc['authorid'], $add_c, false);

			if (!is_numeric($pid)) {
				$db->update("UPDATE pw_threads SET ifmark=ifmark+" . pwEscape($singlepoint)." WHERE tid=" . pwEscape($tid));
				$rpid = 0;
			} else {
				$rpid = $pid;
			}
			$pwSQL = $ping = array();
			$affect = '';
			
			list($ping['pingtime'],$ping['pingdate']) = getLastDate($timestamp);
			require_once(R_P.'require/showimg.php');
			list($face) = showfacedesign($winddb['icon'],1,'m');
			$pingLogRecord = $atc_content ? $atc_content : '-';
			
			foreach ($add_c as $key => $value) {
				$pwSQL = array(
					'fid'	=> $fid,
					'tid'	=> $tid,
					'pid'	=> $rpid,
					'name'	=> $credit->cType[$key],
					'point'	=> $value,
					'pinger'=> $windid,
					'record'=> $atc_content,
					'pingdate'=> $timestamp,
				);
				$affect .= ($affect ? ',' : '') . $credit->cType[$key] . ':' . $value;
				
				
				$db->update("INSERT INTO pw_pinglog SET " . pwSqlSingle($pwSQL));
				$pingLogId = $db->insert_id();
				
				$pointValue = $value>0 ? "+$value" : $value;
				$pingLog[$tmpPid][$key] = array(
					'fid'	=> $fid,
					'tid'	=> $tid,
					'pid'	=> $tmpPid,
					'name'	=> $credit->cType[$key],
					'point'	=> $pointValue,
					'pinger'=> $windid,
					'pingeruid'=> $winduid,
					'record'=> $pingLogRecord,
					'face'	=> $face,
					'pingtime'=> $ping['pingtime'],
					'pingdate'=> $ping['pingdate'],
					'pingLogId'=>$pingLogId
				);
			}

			update_markinfo($fid, $tid, $rpid);
			
//			$_cache = getDatastore();
//			$_cache->delete('UID_'.$atc['authorid']);

			$threadobj = L::loadClass("threads", 'forum');
			$threadobj->clearTmsgsByThreadId($tid);

			$atcdb[$pid]['ifmark'] = $ifmark;

			if ($ifmsg && !$atc['anonymous']) {
				$content = getLangInfo('writemsg','ping_content',array(
							'manager'	=> $windid,
							'fid'		=> $atc['fid'],
							'tid'		=> $tid,
							'pid'		=> $pid,
							'subject'	=> $atc['subject'],
							'postdate'	=> get_date($atc['postdate']),
							'forum'		=> strip_tags($foruminfo['name']),
							'affect'    => $affect,
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content),
							'sender'    => $windid,
							'receiver'  => $atc['author'],
						));
				$messageInfo = array(
					'create_uid'=>$winduid,
					'create_username'=>$windid,
					'title'=>getLangInfo('writemsg','ping_title',array('sender'=>$windid,'receiver'=>$atc['author'])),
					'content'=>$content
				);
				if ($atc['author'] != $windid) {
					M::sendMessage(
						$winduid,
						array($atc['author']),
						$messageInfo,
						'sms_ratescore',
						'sms_rate'
					);
				}
			}
			if ($gp_gptype == 'system'){
				require_once(R_P.'require/writelog.php');
				$log = array(
					'type'      => 'credit',
					'username1' => $atc['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'credit_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $tid,
					'forum'		=> strip_tags($foruminfo['name']),
					'subject'	=> $atc['subject'],
					'affect'	=> $affect,
					'reason'	=> $atc_content
				);
				writelog($log);
			}
		}
		$credit->runsql();

		if ($db_autoban && $singlepoint < 0) {
			require_once(R_P.'require/autoban.php');
			foreach ($atcdb as $pid => $atc) {
				autoban($atc['authorid']);
			}
		}
		if ($foruminfo['allowhtm'] && $page==1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($tid);
		}
		
		if (defined('AJAX')) {
			$pingLog = is_array($pingLog) ? pwJsonEncode($pingLog) : $pingLog;
			echo "success\t{$pingLog}";
			ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&page=$page#$jump_pid",'operate_success');
		}
	} else {

		PostCheck();
		$groupid == 'guest' && Showmsg('not_login');
		InitGP(array('ifmsg','atc_content'));
		foreach ($atcdb as $pid => $atc) {
			$rpid = $pid == 'tpc' ? '0' : $pid; // delete pinglog
			$pingdata = $db->get_one('SELECT * FROM pw_pinglog WHERE fid='.pwEscape($fid).' AND tid='.pwEscape($tid).' AND pid='.pwEscape($rpid).' AND pinger='.pwEscape($windid).' ORDER BY pingdate DESC LIMIT 1');
			$db->update('DELETE FROM pw_pinglog WHERE fid='.pwEscape($fid).' AND tid='.pwEscape($tid).' AND pid='.pwEscape($rpid).' AND pinger='.pwEscape($windid).' ORDER BY pingdate DESC LIMIT 1');
			update_markinfo($fid, $tid, $rpid);
//			$_cache = getDatastore();
//			$_cache->delete('UID_'.$atc['authorid']);

			$threadobj = L::loadClass("threads", 'forum');
			$threadobj->clearTmsgsByThreadId($tid);

			$cName = $pingdata['name'];
			$addpoint = $pingdata['point'];
			foreach ($credit->cType as $k => $v) {
				if ($v == $cName) {
					$cid = $k;break;
				}
			}

			!$atc['subject'] && $atc['subject'] = substrs(strip_tags(convert($atc['content'])),35);
			$addpoint = $addpoint>0 ? -$addpoint : abs($addpoint);

			$credit->addLog('credit_delping',array($cid => $addpoint),array(
				'uid'		=> $atc['authorid'],
				'username'	=> $atc['author'],
				'ip'		=> $onlineip,
				'operator'	=> $windid,
				'tid'		=> $tid,
				'subject'	=> $atc['subject'],
				'reason'	=> $atc_content
			));
			$credit->set($atc['authorid'],$cid,$addpoint);

			if (!is_numeric($pid)) {
				$db->update('UPDATE pw_threads SET ifmark=ifmark+'.pwEscape($addpoint).' WHERE tid='.pwEscape($tid));
			}
			if ($ifmsg) {
				M::sendMessage(
					$winduid,
					array($atc['author']),
					array(
						'create_uid'	=> $winduid,
						'create_username'	=> $windid,
						'title' => getLangInfo('writemsg','delping_title',array('sender'=> $windid,'receiver'=>$atc['author'])),
						'content' => getLangInfo('writemsg','delping_content',array(
							'manager'	=> $windid,
							'fid'		=> $atc['fid'],
							'tid'		=> $tid,
							'pid'		=> $pid,
							'subject'	=> $atc['subject'],
							'postdate'	=> get_date($atc['postdate']),
							'forum'		=> strip_tags($foruminfo['name']),
							'affect'    => "{$cName}:$addpoint",
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content),
							'sender'    => $windid,
							'receiver'  => $atc['author']
						)),
					),
					'sms_ratescore',
					'sms_rate'
				);
			}
			if ($gp_gptype == 'system'){
				require_once(R_P.'require/writelog.php');
				$log = array(
					'type'      => 'credit',
					'username1' => $atc['author'],
					'username2' => $windid,
					'field1'    => $atc['fid'],
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'creditdel_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $tid,
					'forum'		=> strip_tags($foruminfo['name']),
					'subject'	=> $atc['subject'],
					'affect'	=> "$name:$addpoint",
					'reason'	=> $atc_content
				);
				writelog($log);
			}
			$pingLog[$pid] = $pingdata['id'];
		}

		if ($foruminfo['allowhtm'] && $page==1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($tid);
		}
		if (defined('AJAX')) {
			$pingLog = is_array($pingLog) ? pwJsonEncode($pingLog) : $pingLog;
			echo "success\t{$pingLog}\tcancel";
			ajax_footer();
		} else {
			refreshto("read.php?tid=$tid&page=$page#$jump_pid",'operate_success');
		}
	}

} elseif ($action == 'toweibo') {
	$messageService = L::loadClass("message", 'message'); /* @var $messageService PW_Message */
	$numbers = $messageService->statisticUsersNumbers(array($winduid));
	$totalMessage = isset($numbers[$winduid]) ? $numbers[$winduid] : 0;
	$max = (int)$_G['maxmsg'];
	InitGP(array('type','id','tid','cyid'));
	require_once(R_P. 'apps/weibo/lib/sendweibo.class.php');
	$sendWeiboServer = getWeiboFactory($type);
	$sendWeiboServer->init($id);
	$content = $sendWeiboServer->getContent();
	$mailSubject = $sendWeiboServer->getMailSubject();
	$mailContent = $sendWeiboServer->getMailContent();
	$pids = $sendWeiboServer->getPids();
	require_once PrintEot('ajax_operate');ajax_footer();
} elseif ($action == 'report') {

	!$_G['allowreport'] && Showmsg('report_right');
	InitGP(array('pid','page'),'GP',2);
	$rt  = $db->get_one("SELECT tid FROM pw_report WHERE uid=".pwEscape($winduid).' AND tid='.pwEscape($tid).' AND pid='.pwEscape($pid));
	$rt && Showmsg('have_report');

	if (empty($_POST['step'])) {

		require_once PrintEot($template);footer();

	} else {

		PostCheck();
		InitGP(array('ifmsg','type','reason'),'P');

		$pwSQL = pwSqlSingle(array(
			'tid'	=> $tid,
			'pid'	=> $pid,
			'uid'	=> $winduid,
			'type'	=> $type,
			'reason'=> $reason
		));
		$db->update("INSERT INTO pw_report SET $pwSQL");

		if ($ifmsg) {
			if ($pid > 0) {
				$pw_posts = GetPtable('N',$tid);
				$sqlsel = "t.content as subject,t.postdate,";
				$sqltab = "$pw_posts t";
				$sqladd = 'WHERE t.pid='.pwEscape($pid);
			} else {
				$sqlsel = "t.subject,t.postdate,";
				$sqltab = "pw_threads t";
				$sqladd = 'WHERE t.tid='.pwEscape($tid);
			}
			$rs = $db->get_one("SELECT $sqlsel t.fid,f.forumadmin FROM $sqltab LEFT JOIN pw_forums f USING(fid) $sqladd");

			if ($rs['forumadmin']) {
				include_once(D_P.'data/bbscache/forum_cache.php');
				$admin_a = explode(',',$rs['forumadmin']);
				$iftpc = $pid ? '0' : '1';
				M::sendMessage(
					$winduid,
					array($admin_a),
					array(
						'create_uid'	=> $winduid,
						'create_username'	=> $windid,
						'title' => getLangInfo('writemsg','report_title'),
						'content' => getLangInfo('writemsg','report_content_'.$type.'_'.$iftpc,array(
							'fid'		=> $rs['fid'],
							'tid'		=> $tid,
							'pid'		=> $pid,
							'postdate'	=> get_date($rs['postdate']),
							'forum'		=> $forum[$rs['fid']]['name'],
							'subject'	=> $rs['subject'],
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($reason)
						)),
					)
				);
			}
		}
		if (defined('AJAX')) {
			Showmsg('report_success');
		} else {
			refreshto("read.php?tid=$tid&page=$page",'report_success');
		}
	}
} else {
	Showmsg('undefined_action');
}

function getphotourl($path,$thumb = false) {
	global $pwModeImg;
	if (!$path) {
		return "$imgpath/nophoto.gif";
	}
	$lastpos = strrpos($path,'/') + 1;
	$thumb && $path = substr($path, 0, $lastpos) . 's_' . substr($path, $lastpos);
	list($path) = geturl($path, 'show');
	if ($path == 'imgurl' || $path == 'nopic') {
		return "$imgpath/nophoto.gif";
	}
	return $path;
}
function getShareType ($type) {
	switch ($type) {
		case 'article' :
		case 'diary' :
			return 'diary';
		case 'album' :
		case 'photo' :
			return 'photo';
		case 'topic' :
		case 'reply' :
			return 'post';
		case 'groups' :
		case 'group'  :
			return 'group';
		case 'video' :
			return 'video';
		case 'music' :
			return 'music';
		default :
			return 'link';
	}
}

function getratelist($raterange) {
	global $markset;
	$ratelist = $result = array();
	foreach($raterange as $id => $rating) {
		if(isset($markset[$id])) {
			$rating['max'] = $rating['max'] < $rating['mrpd'] ? $rating['max'] : $rating['mrpd'];
			$rating['min'] = -$rating['min'] < $rating['mrpd'] ? $rating['min'] : -$rating['mrpd'];
			$offset = abs(ceil(($rating['max'] - $rating['min']) / 10));
			if($rating['max'] > $rating['min']) {
				$ratelist[$id][$rating['max']] = $rating['max'];
				for($vote = $rating['max']; $vote >= $rating['min']; $vote -= $offset) {
					if ($vote == 0) continue;
					$ratelist[$id][$vote] = $vote > 0 ? '+'.$vote : strval($vote);
				}
				$ratelist[$id][$rating['min']] = $rating['min'] >0 ? '+'.$rating['min']:strval($rating['min']);
			}
		}
	}
	foreach ($ratelist as $key =>$v) {
		$result[$key] = array_values($v);
	}
	return $result;
}

function getWeiboFactory($type) {
	switch ($type) {
		case 'diary':
			$obj = new diaryWeibo();break;
		case 'group':
			$obj = new groupWeibo();break;
		case 'groupactive':
			$obj = new groupActiveWeibo();break;
		case 'album':
			$obj = new albumWeibo();break;
		case 'photo':
			$obj = new photoWeibo();break;
		case 'topic':
			$obj = new topicWeibo();break;
		case 'reply':
			$obj = new replyWeibo();break;
		default:
			$obj = getWeiboOtherFactory($type);
	}
	return $obj;
}

function getWeiboOtherFactory($name) {
	$classes = array();
	$name = strtolower($name);
	$filename = R_P . "apps/weibo/lib/sendweibo/" . $name . ".weibo.php";
	if (!is_file($filename)) Showmsg('undefined_action');
	$class = 'weibo_' . ucfirst($name);
	if (isset($classes[$class])) return $classes[$class];
	if (!class_exists($class)) include Pcv($filename);
	$classes[$class] = new $class();
	return $classes[$class];
}
?>