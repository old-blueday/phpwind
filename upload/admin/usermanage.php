<?php
!function_exists('adminmsg') && exit('Forbidden');
empty($adminitem) && $adminitem = 'edit';
$jobUrl = "$admin_file?adminjob=usermanage";
$basename = "$admin_file?adminjob=usermanage&adminitem=$adminitem";

if ($adminitem == 'edit') {
	if ($action == 'search') {
		S::gp(array('groupid','schname','schuid','schname_s','schemail','userip','regdate','schlastvisit','orderway','asc','lines','page','yz'));
		S::gp(array('vaguename','vagueemail'));
		if (!$groups && !$schname && !$schuid && !$schemail && !$groupid && !$userip && $regdate=='all' && $schlastvisit=='all') {
			adminmsg('noenough_condition');
		}
		$sql     = is_numeric($groupid) ? "m.groupid=".S::sqlEscape($groupid) : 1;
		$order   = '';
		$schname = trim($schname);
		if ($schname!='') {
			if (strpos($schname,'*') !== false) {
				$t_schname = addslashes(str_replace('*','%',$schname));
				$sql .= " AND (m.username LIKE ".S::sqlEscape($t_schname).')';
			} else {
				$schname = addslashes($schname);
				$sql .= " AND m.username = ".S::sqlEscape($schname);
			}
		}
		if ($schuid!='') {
			if (strpos($schuid,'*') !== false) {
				$t_schuid = addslashes(str_replace('*','%',$schuid));
				$sql .= " ";
			} else {
				$schuid = addslashes($schuid);
				$sql .= " AND m.uid = ".S::sqlEscape($schuid);
			}
		}
		
		if ($schemail!='') {
			if (strpos($schemail,'*') !== false) {
				$t_schemail = str_replace('*','%',$schemail);
				$sql .= " AND (m.email LIKE ".S::sqlEscape($t_schemail).")";
			}else{
				$schemail = addslashes($schemail);
				$sql .= " AND m.email = ".S::sqlEscape($schemail);
			}
		}
		if ($userip!='') {
			$userip = str_replace('*','%',$userip);
			$sql .= " AND (md.onlineip LIKE ".S::sqlEscape("$userip%").")";
		}
		if ($regdate!='all' && is_numeric($regdate)) {
			$schtime = $timestamp-$regdate;
			$sql .= " AND m.regdate<".S::sqlEscape($schtime);
		}
		if ($schlastvisit!='all' && is_numeric($schlastvisit)) {
			$schtime = $timestamp-$schlastvisit;
			$sql .= " AND md.thisvisit<".S::sqlEscape($schtime);
		}
		if ($orderway) {
			switch ($orderway) {
				case 'username' : $orderway = 'm.username'; break;//用户名
				case 'regdate' : $orderway = 'm.regdate'; break;//注册时间
				case 'lastpost' : $orderway = 'md.lastpost'; break;//最后发表
				case 'lastvisit' : $orderway = 'md.lastvisit'; break;//最后登录
				case 'postnum' : $orderway = 'md.postnum'; break;// 发帖
				default: $orderway = S::sqlEscape($orderway); break;
			}
			$order = "ORDER BY " . $orderway;
			$asc=='DESC' && $order .= " $asc";
		}
		$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql");
		$count = $rs['count'];
	
		!is_numeric($lines) && $lines=100;
		(!is_numeric($page) || $page < 1) && $page=1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page>$numofpage) {
			$page = $numofpage;
		}
		$pages = numofpage($count,$page,$numofpage, "$admin_file?adminjob=usermanage&action=$action&schname=".rawurlencode($schname)."&groupid=$groupid&schuid=$schuid&schemail=$schemail&regdate=$regdate&schlastvisit=$schlastvisit&orderway=$orderway&lines=$lines&asc=$asc&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);
		$groupselect = "<option value='-1'>".getLangInfo('all','reg_member')."</option>";
		$query = $db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gid>2 AND gptype<>'member' ORDER BY gid");
		while ($group = $db->fetch_array($query)) {
			$gid = $group['gid'];
			$groupselect .= "<option value='$gid'>$group[grouptitle]</option>";
		}
		$schdb = array();
		$query = $db->query("SELECT m.uid,m.username,m.email,m.groupid,m.memberid,m.regdate,m.yz,md.lastvisit,md.postnum,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql $order $limit");
		while ($sch = $db->fetch_array($query)) {
			$sch['regdate'] = get_date($sch['regdate']);
			$sch['lastvisit'] = get_date($sch['lastvisit']);
			strpos($sch['onlineip'],'|') && $sch['onlineip']=substr($sch['onlineip'],0,strpos($sch['onlineip'],'|'));
			if ($sch['groupid']=='-1') {
				$sch['groupselect'] = str_replace("<option value='-1'>".getLangInfo('all','reg_member')."</option>","<option value='-1' selected>".getLangInfo('all','reg_member')."</option>",$groupselect);
			} else {
				$sch['groupselect'] = str_replace("<option value='$sch[groupid]'>".$ltitle[$sch['groupid']]."</option>","<option value='$sch[groupid]' selected>".$ltitle[$sch['groupid']]."</option>",$groupselect);
			}
			$schdb[] = $sch;
		}
		if (empty($schdb) && $schname){
			$errorname = $schname;
			Showmsg('user_not_exists');
		}
		include PrintEot('usermanage');exit;
	} elseif ($action == 'edit') {
		S::gp(array('uid'),'GP',2);
		$temUid = $uid;
		//* include_once pwCache::getPath(D_P.'data/bbscache/customfield.php');
		pwCache::getData(D_P.'data/bbscache/customfield.php');
		require_once(R_P.'require/showimg.php');
		if (empty($_POST['step'])) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			@extract($userService->get($uid, true, true, true));
			$messageServer = L::loadClass('message', 'message');
			$config = $messageServer->getMsConfigs($uid);
			$banpm = implode(',',unserialize($config['blacklist']));
			$messageServer = L::loadClass('message', 'message');
			$messageServer->grabMessage($uid,array(($groupid == '-1') ? $memberid : $groupid ),max($lastgrab,$regdate));
			$rvrc = floor($rvrc/10);
			if (strpos($onlineip,'|')) {
				$onlineip = substr($onlineip,0,strpos($onlineip,'|'));
			}
			$onlinetime = floor($onlinetime / 3600);
			$regdate = get_date($regdate);
			$lastvisit = get_date($lastvisit);
			$ifpublicmail = getstatus($userstatus, PW_USERSTATUS_PUBLICMAIL) ? 'checked' : '';
			getstatus($userstatus, PW_USERSTATUS_RECEIVEMAIL) ? $email_open = 'checked' : $email_close = 'checked';
			$sexselect[$gender] = 'selected';
			$selected[$groupid] = 'selected';
			$getbirthday = explode("-",$bday);
			$yearslect[(int)$getbirthday[0]]="selected";
	        $monthslect[(int)$getbirthday[1]]="selected";
			$dayslect[(int)$getbirthday[2]]="selected";
	
			$groups = explode(',',$groups);
			foreach ($groups as $key => $value) {
				${'check_'.$value} = 'checked';
			}
			$usergroup = "<ul class=\"list_A list_160\">";
			$groupselect = "<option value='-1' $selected[member]>".getLangInfo('all','reg_member')."</option>";
	
			$query = $db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gid>2 AND gptype<>'member' ORDER BY gid");
			while ($rt = $db->fetch_array($query)) {
				$gid = $rt['gid'];
				$groupselect.="<option value='$gid' $selected[$gid]>$rt[grouptitle]</option>";
	
				if ($rt['gid'] != $groupid) {
					$num++;
					$htm_tr=$num%3==0 ? '' : '';
					$ifchecked=${'check_'.$rt['gid']};
					$usergroup.="<li><input type='checkbox' name='groups[]' value='$rt[gid]' $ifchecked>$rt[grouptitle]</li>$htm_tr";
				}
			}
			$usergroup.="</ul>";
	
			list($iconurl,$icontype,$iconwidth,$iconheight,$iconfile,$iconpig) = showfacedesign($icon,true,'m');
			$iconsize = $httpurl = '';
			$disabled = 'disabled';
			$width2 = $width3 = $iconwidth;
			$height2 = $height3 = $iconheight;
			$iconsize = $iconwidth ? " width=\"$iconwidth\"" : " width=\"75\"";
			$iconsize .= $iconheight ? " height=\"$iconheight\"" : " height=\"100\"";
			if ($icontype == 2) {
				$httpurl = $iconurl;
				$width3 = $height3 = '';
			} elseif ($icontype == 3) {
				$width2 = $height2 = $disabled = '';
			}
			$ifselected = false;
			$fp = opendir($imgdir.'/face/');
			while ($facefile = readdir($fp)) {
				if (preg_match('/\.(gif|png|jpg|jepg)$/i',$facefile)) {
					if ($facefile==$iconfile) {
						$ifselected = true;
						$faces .= "<option value=\"$facefile\" selected>$facefile</option>";
					} else {
						$fselected = (!$ifselected && $facefile=='none.gif') ? 'selected' : '';
						$faces .= "<option value=\"$facefile\" $fselected>$facefile</option>";
					}
				}
			}
			closedir($fp);
			$mdcredit = $credit;
			//custom credits
			if ($_CREDITDB) {
				require_once(R_P.'require/credit.php');
				$custom_credits = $credit->get($uid,'CUSTOM');
			}
			$customerService = L::loadClass('CustomerFieldService','user');
			$customerTemplate = $customerService->getAdminTemplate($uid);
			include PrintEot('usermanage');exit;
	
		} elseif ($_POST['step'] == 2) {
	
			S::gp(array('groupid','groups','username','password','check_pwd','email','publicmail','receivemail','regdate','yz','userip','facetype','proicon','delupload','postnum','rvrc','money','deposit','ddeposit','credit','currency','onlinetime','site','location','oicq','icq','msn','aliww','yahoo','honor','gender','year','month','day','signature','introduce','banpm','question','customquest','answer','creditdb'),'P');
			$basename .= "&action=edit&uid=$uid";
			$upmembers = $uc_edit = array();
	
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$oldinfo = $userService->get($uid);
			if ($password != '') {
				$password != $check_pwd && adminmsg('password_confirm');
				$upmembers['password'] = md5($password);
				$uc_edit['password'] = md5($password);
			}
			if ($email && $email != $oldinfo['email']) {
				$uc_edit['email'] = $email;
			}
			if ($question != '-2') {
				$upmembers['safecv'] = questcode($question,$customquest,$answer);
			}
			$newgroups = $groups ? ','.implode(',',$groups).',' : '';
			$newgroups = str_replace(','.$groupid.',',',',$newgroups);
			if (($oldinfo['groupid'] == '3' || strpos($oldinfo['groups'],',3,') !== false) && !If_manager) {
				adminmsg('manager_right');
			} elseif ($oldinfo['groupid'] != '3' && ($groupid == '3' || strpos($newgroups,',3,') !== false) && !If_manager) {
				adminmsg('manager_right');
			}
			if (ifadmin($oldinfo['username']) && $groupid != '5' && strpos($newgroups,',5,') === false) {
				if (strpos($oldinfo['groups'],',5,') !== false) {
					adminmsg('setuser_forumadmin');
				} else {
					$newgroups .= $newgroups ? '5,' : ',5,';
				}
			} elseif (!ifadmin($oldinfo['username']) && ($groupid == '5' || strpos($newgroups,',5,') !== false)) {
				adminmsg('setuser_forumadmin');
			}
			$newgroups == ',' && $newgroups = '';
			if ($groupid <> '-1' || $newgroups) {
				admincheck($uid,$username,$groupid,$newgroups,'update');
			} elseif ($oldinfo['groupid'] <> '-1' || $oldinfo['groups']) {
				admincheck($uid,$username,$groupid,$newgroups,'delete');
			}
			$newgroups != $oldinfo['groups'] && $upmembers['groups'] = $newgroups;
	
			/*
			list($iconurl,$icontype,$iconwidth,$iconheight,$iconfile,$iconpig,$ifhavasmallicon) = showfacedesign(addslashes($oldinfo['icon']),true);
			if ($facetype == 2) {
				if (substr($_POST['i_http'],0,4) != 'http' || strrpos($_POST['i_http'],'|') !== false) {
					adminmsg('illegal_customimg');
				}
				$icontype == 3 && DelIcon($iconfile);
				$i_w = (int)$_POST['i_w'];
				$i_h = (int)$_POST['i_h'];
				$iconfile = $_POST['i_http'];
				list($iconwidth,$iconheight) = getfacelen($i_w,$i_h);
			} elseif ($facetype == 3 && $delupload) {
				DelIcon($delupload);
				$facetype = $icontype = 1;
				$proicon = 'none.gif';
			}
			$facetype != 1 && $facetype != 2 && $facetype != 3 && $facetype = $icontype;
			if ($facetype == 1) {
				if ($icontype != 1) {
					$icontype == 3 && DelIcon($iconfile);
					if (!file_exists("$imgdir/face/$proicon")) {
						$proicon = 'none.gif';
					}
				}
				if (!empty($proicon)) {
					if (strlen($proicon)>20 || !preg_match('/^[0-9A-Za-z]+\.[A-Za-z]{2,5}$/',$proicon)) {
						adminmsg('undefined_action');
					}
					$iconfile = $proicon;
				}
				$iconwidth = $iconheight = 0;
			}
			$iconwidth < 1 && $iconwidth = '';
			$iconheight < 1 && $iconheight = '';
			$icon = "$iconfile|$facetype|$iconwidth|$iconheight";
			if ($iconpig) {
				$icon .= "|$iconpig";
			} else {
				$icon .= "|";
			}
			if ($facetype == 3 && $ifhavasmallicon == 1) {
				$icon .= "|1";
			}
			strlen($icon)>100 && adminmsg('illegal_customimg');
			pwFtpClose($ftp);
			*/
			$user_a = explode('|',$oldinfo['icon']);
			$usericon = '';
			if ($facetype == 3 && $delupload) {
				$facetype = 1;
				$proicon = 'none.gif';
			}
			if ($facetype == 1) {
				$usericon = setIcon($proicon, $facetype, $user_a);
			} elseif ($facetype == 2) {
				$httpurl = $_POST['httpurl'];
				if (strncmp($httpurl[0],'http',4) != 0 || strrpos($httpurl[0],'|') !== false) {
					Showmsg('illegal_customimg');
				}
				$proicon = $httpurl[0];
				$httpurl[1] = '';
				$httpurl[2] = '';
				$httpurl[3] = (int)$httpurl[3];
				$httpurl[4] = (int)$httpurl[4];
				list($user_a[2], $user_a[3]) = flexlen($httpurl[1], $httpurl[2], $httpurl[3], $httpurl[4]);
				$usericon = setIcon($proicon, $facetype, $user_a);
				unset($httpurl);
			}
			pwFtpClose($ftp);
			$usericon && $upmembers['icon'] = $usericon;
	
			$bday = $year."-".$month."-".$day;
			//$rvrc*=10;
			$regdate = PwStrtoTime($regdate);
	
			if ($oldinfo['username'] != stripcslashes($username)) {
				if (!$username) {
					Showmsg('username_empty');
				}
				if (strlen($username) > 15) {
					adminmsg('用户名长度不能大于15个字符');
				}
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$isUsernameExist = $userService->getUserIdByUserName($username);
				if ($isUsernameExist > 0) {
					adminmsg('username_exists');
				}
				$uc_edit['username'] = $username;
			}
			if ($uc_edit) {
				$ucuser = L::loadClass('Ucuser', 'user');
				list($ucstatus, $errmsg) = $ucuser->edit($uid, $oldinfo['username'], $uc_edit);
				if ($ucstatus < 0) {
					Showmsg($errmsg);
				}
			}
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userinfo = $userService->get($uid);
			
			$userService->setUserStatus($uid, PW_USERSTATUS_PUBLICMAIL, $publicmail);
			$userService->setUserStatus($uid, PW_USERSTATUS_RECEIVEMAIL, $receivemail);
	
			require_once(R_P.'require/bbscode.php');
			$cksign = convert($signature,$db_windpic,2);
			$signstatus = $cksign != $signature ? 1 : 0;
	
			if ($signstatus != getstatus($userstatus, PW_USERSTATUS_SIGNCHANGE)) {
				$userService->setUserStatus($uid, PW_USERSTATUS_SIGNCHANGE, $signstatus);
			}
			
			if ($groupid == 6) {
				/**
				$db->update("REPLACE INTO pw_banuser"
					. " SET " .S::sqlSingle(array(
						'uid'		=> $uid,
						'fid'		=> 0,
						'type'		=> 2,
						'startdate'	=> $timestamp,
						'days'		=> 0,
						'admin'		=> $admin_name,
						'reason'	=> ''
				),false));
				**/
				pwQuery::replace('pw_banuser', array(
						'uid'		=> $uid,
						'fid'		=> 0,
						'type'		=> 2,
						'startdate'	=> $timestamp,
						'days'		=> 0,
						'admin'		=> $admin_name,
						'reason'	=> ''			
				));
			}
			
			/**
			if ($groupid == 6 || $groupid != $oldinfo['groupid']) {
				$_cache = getDatastore();
				$_cache->delete('UID_'.$uid);
			}
			**/
	
			$mainFields = array_merge($upmembers, array(
				'username'		=> $username,
				'gender'		=> $gender,
				'email'			=> $email,
				'regdate'		=> $regdate,
				'groupid'		=> $groupid,
				'site'			=> $site,
				'oicq'			=> $oicq,
				'aliww'			=> $aliww,
				'icq'			=> $icq,
				'msn'			=> $msn,
				'yahoo'			=> $yahoo,
				'location'		=> $location,
				'bday'			=> $bday,
				'honor'			=> $honor,
				'yz'			=> $yz,
				'signature'		=> $signature,
				'introduce'		=> $introduce
			));
			
			$memberDataFields = array(
				//'rvrc'		=> $rvrc,
				//'money'		=> $money,
				//'credit'	=> $credit,
				//'currency'	=> $currency,
				'postnum'	=> $postnum,
				'onlinetime'=> $onlinetime * 3600,
				'onlineip'	=> $userip
			);
	
			$userService->update($uid, $mainFields, $memberDataFields);
	
			$setCredit = array(
				'rvrc'		=> $rvrc,
				'money'		=> $money,
				'credit'	=> $credit,
				'currency'	=> $currency
			);
			if ($_CREDITDB && !empty($creditdb)) {
				foreach ($creditdb as $key => $value) {
					if (is_numeric($key) && is_numeric($value)) {
						$setCredit[$key] = $value;
					}
				}
			}
			require_once(R_P.'require/credit.php');
			$credit->runsql(array($uid => $setCredit), false);
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$mi = $userService->get($uid, false, false, true);
			if (!$mi) {
				if ($deposit || $ddeposit) {
					$userService->update($uid, array(), array(), array('deposit'=>$deposit,'ddeposit'=>$ddeposit));
				}
			} elseif ($deposit != $mi['deposit'] || $ddeposit != $mi['ddeposit']) {
				$userService->update($uid, array(), array(), array('deposit'=>$deposit,'ddeposit'=>$ddeposit));
			}
			if ($customfield) {
				$customfieldService = L::loadClass('CustomerFieldService','user');
				$customfieldService->saveAdminCustomerData($uid);
			}
			$messageServer = L::loadClass('message', 'message'); 
			$blacklist = array_unique(explode(',',$banpm));			
			$messageServer->setMsConfig(array('blacklist'=>serialize($blacklist)),$uid);

			/* phpwind数据统计 */
			if ($gender != $oldinfo['gender'] || $bday != $oldinfo['bday']) {
				$statistics = L::loadClass('Statistics', 'datanalyse');
				$statistics->alertSexDistribution($oldinfo['gender'], $gender);
				$statistics->alertAgeDistribution(intval($oldinfo['bday']), intval($bday));
			}

			adminmsg('operate_success');
		}
	} elseif ($action == 'editgroup'){
		S::gp(array('gid'),'P');
		if (!$gid) adminmsg('operate_error');
		$_cache = getDatastore();
		$messageServer = L::loadClass('message', 'message');
		foreach ($gid as $uid => $groupid) {
			if ($uid) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$rt = $userService->get($uid,true,true);
				if ($rt['groupid'] == 7 && $groupid != 7) $userService->update($uid, array('yz' => 1));
				$messageServer->grabMessage($uid,array(($rt['groupid'] == '-1') ? $rt['memberid'] : $rt['groupid'] ),max($rt['lastgrab'],$rt['regdate']));
				if ($rt['groupid']==3 && $groupid!=3 && !If_manager) {
					adminmsg('manager_right');
				} elseif ($rt['groupid']!=3 && $groupid==3 && !If_manager) {
					adminmsg('manager_right');
				} elseif ($rt['groupid']==5 && $groupid==-1 || $rt['groupid']!=5 && $groupid==5) {
					adminmsg('setuser_forumadmin');
				}
				
				$groups = $rt['groups'];
				if ($groups && strpos($groups,','.$groupid.',')!==false) {
					$groups = str_replace(','.$groupid.',',',',$groups);
					$groups == ',' && $groups = '';
				}
				$userService->update($uid, array('groupid'=>$groupid, 'groups'=>$groups));
				if ($groupid == 6) {
					/**
					$db->update("REPLACE INTO pw_banuser"
						. " SET " .S::sqlSingle(array(
							'uid'		=> $uid,
							'fid'		=> 0,
							'type'		=> 2,
							'startdate'	=> $timestamp,
							'days'		=> 0,
							'admin'		=> $admin_name,
							'reason'	=> ''
					),false));
					**/
					pwQuery::replace('pw_banuser', array(
							'uid'		=> $uid,
							'fid'		=> 0,
							'type'		=> 2,
							'startdate'	=> $timestamp,
							'days'		=> 0,
							'admin'		=> $admin_name,
							'reason'	=> ''				
					));
				}
				$_cache->delete('UID_'.$uid);
	
				if ($groupid <> '-1' || $groups) {
					admincheck($uid,$rt['username'],$groupid,$groups,'update');
				} elseif ($rt['groupid'] <> '-1' || $rt['groups']) {
					admincheck($uid,$rt['username'],$groupid,$groups,'delete');
				}
			}
		}
		adminmsg('operate_success');
	} else {
		initGroupOptions();
		include PrintEot('usermanage');exit;
	}
} elseif ($adminitem == 'delete') {
	if ($action == 'del') {
		S::gp(array('groupid','schname','schuid','schemail','postnum','onlinetime','userip','regdate','schlastvisit','orderway','asc','lines','item'));
		S::gp(array('page','skipcheck'),'GP',2);
		if (empty($_POST['step'])) {

			if (!$skipcheck && !$schname && !$schuid && !$schemail && !$groupid && $regdate=='all' && $schlastvisit='all') {
				adminmsg('noenough_condition');
			}
			is_array($item) && $item = array_sum($item);
			for($i=0; $i<4; $i++){
				${'check_'.$i} = ($item & pow(2,$i)) ? 'checked' : '';
			}
			if ($groupid != '-1' || !$groupid) {
				if ($groupid) {
					if ($groupid == '3' && !If_manager) {
						adminmsg('manager_right');
					} elseif (($groupid == '4' || $groupid == '5') && $admin_gid != 3) {
						adminmsg('admin_right');
					}
					$sql = "m.groupid=".S::sqlEscape($groupid);
				} else {
					$sql = '1';
				}
			} else {
				$sql = "m.groupid='-1'";
			}
			if ($schname != '') {
				$schname = str_replace('*','%',$schname);
				$sql .= " AND (m.username LIKE ".S::sqlEscape($schname).")";
			}
			if ($schuid!='') {
				$schuid = intval($schuid);
				$sql .= " AND m.uid = ".S::sqlEscape($schuid);
			}
		
			if ($schemail != '') {
				$schemail = str_replace('*','%',$schemail);
				$sql .= " AND (m.email LIKE ".S::sqlEscape($schemail).")";
			}
			if ($postnum) {
				$sql .= " AND md.postnum<".S::sqlEscape($postnum);
			}
			if ($onlinetime) {
				$sql .= " AND md.onlinetime<".S::sqlEscape($onlinetime);
			}
			if ($userip) {
				$userip = str_replace('*','%',$userip);
				$sql .= " AND (md.onlineip LIKE ".S::sqlEscape("$userip%").")";
			}
			if ($regdate != 'all') {
				$schtime = $timestamp-$regdate;
				$sql .= " AND m.regdate<".S::sqlEscape($schtime);
			}
			if ($schlastvisit != 'all') {
				$schtime = $timestamp - $schlastvisit;
				$sql .= " AND md.thisvisit<".S::sqlEscape($schtime);
			}
			$order = '';
			if ($orderway) {
				switch ($orderway) {
					//case 'username' : $orderway = 'm.username'; break;//用户名
					case 'regdate' : $orderway = 'm.regdate'; break;//注册时间
					//case 'lastpost' : $orderway = 'md.lastpost'; break;//最后发表
					case 'lastvisit' : $orderway = 'md.lastvisit'; break;//最后登录
					case 'postnum' : $orderway = 'md.postnum'; break;// 发帖
					default: $orderway = $orderway; break;
				}
				$order = "ORDER BY " .$orderway;
				$asc=='DESC' && $order .= " $asc";
			}
			
			$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql");
			$count = $rs['count'];
			if (!is_numeric($lines)) $lines = 100;
			$page < 1 && $page = 1;
			$numofpage = ceil($count/$lines);
			if ($numofpage && $page > $numofpage) {
				$page = $numofpage;
			}
			$pages = pagerforjs($count,$page,$numofpage, "onclick=\"manageclass.superdel(this,'superdel_member','')\"");
			$start = ($page-1)*$lines;
			$limit = "LIMIT $start,$lines";
			$delid = $schdb = array();
			$query = $db->query("SELECT m.uid,m.username,m.email,m.groupid,m.regdate,md.lastvisit,md.postnum,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql $order $limit");
			while ($sch = $db->fetch_array($query)) {
				if ($_POST['direct']) {
					$delid[] = $sch['uid'];
				} else {
					strpos($sch['onlineip'],'|') && $sch['onlineip'] = substr($sch['onlineip'], 0, strpos($sch['onlineip'],'|'));
					if ($sch['groupid'] == '-1') {
						$sch['group'] = getLangInfo('all','reg_member');
					} else {
						$sch['group'] = $ltitle[$sch['groupid']];
					}
					$sch['regdate']   = get_date($sch['regdate']);
					$sch['lastvisit'] = get_date($sch['lastvisit']);
					$schdb[] = $sch;
				}
			}
			if (!$_POST['direct']) {
				include PrintEot('usermanage');exit;
			}
		}
		if ($_POST['step'] == 2 || $_POST['direct']) {
			@set_time_limit(300);
			S::gp(array('item'),'P');
			$item = array_sum($item);
			!$item && adminmsg('operate_error');
			if (!$_POST['direct']) {
				S::gp(array('delid'),'P');
			}
			!$delid && adminmsg('operate_error');
			$userIds = $delid;
			$delids = S::sqlImplode($delid);

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userInfos = $userService->getByUserIds($userIds);
			foreach($userInfos as $v) {
				if ($v['groupid'] == '3' && !If_manager) {
					adminmsg('manager_right');
				} elseif (($v['groupid'] == '4' || $v['groupid'] == '5') && $admin_gid != 3) {
					adminmsg('admin_right');
				}
			}
			if ($item & 1) {
				$ucuser = L::loadClass('Ucuser', 'user'); /* @var $ucuser PW_Ucuser */
				$ucuser->delete($delid);
				
				#TO DO群组
				foreach($userIds as $userId){
					$colonyid =  $db->get_value("SELECT colonyid FROM pw_cmembers WHERE uid=".S::sqlEscape($userId));
					$colonyMembers =  $db->get_value("SELECT count(id) FROM pw_cmembers WHERE colonyid =".S::sqlEscape($colonyid));
					//* $db->update("UPDATE pw_colonys SET members=".S::sqlEscape($colonyMembers-1)." WHERE id= ".S::sqlEscape($colonyid));
					$db->update(pwQuery::buildClause("UPDATE :pw_table SET members=:members WHERE id=:id", array('pw_colonys', $colonyMembers-1, $colonyid)));
					//* $db->update("DELETE FROM pw_cmembers WHERE uid=".S::sqlEscape($userId));
					pwQuery::delete('pw_cmembers', 'uid=:uid', array($userId));
				}
				//朋友
				$friendService = L::loadClass('friend', 'friend'); /* @var $friendService PW_Friend */
				$friendService->delFriendByFriendids($userIds);
				
				//关注
				$db->update("DELETE FROM pw_attention WHERE friendid IN (".S::sqlImplode($userIds).")");
			}
			if ($item & 2) {
				$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
				$delarticle->delTopicByUids($delid);
			}
			if ($item & 4) {
				$delarticle = L::loadClass('DelArticle', 'forum'); /* @var $delarticle PW_DelArticle */
				$delarticle->delReplyByUids($delid);
			}
			if ($item & 8) {
				$messageServer = L::loadClass('message', 'message'); /* @var $messageServer PW_Message */
				foreach($userIds as $userId){
					$messageServer->clearMessages($userId,array('groupsms','sms','notice','request','history'));
				}
			}
			if ($item & 16) {
				require_once(R_P. 'require/app_core.php');
				$photoService = L::loadClass('photo', 'colony'); /* @var $photoService PW_Photo */
				$photoService->delAlbumByUids($userIds);
			}
			if ($item & 32) {
				$diaryService = L::loadClass('diary', 'diary'); /* @var $diaryService PW_Diary */
				$diaryService->delByUids($userIds);
			}
			
			if ($item & 64) {
				$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
				$weiboService->deleteWeibosByUids($userIds);
				
				$db->update("DELETE FROM pw_cwritedata WHERE uid IN (".S::sqlImplode($userIds).")");
			}
			if ($item & 128) {
				$commentQuery = $db->query('SELECT cid FROM pw_weibo_comment WHERE uid IN (' . S::sqlImplode($userIds) . ')');
				$commentIds = array();
				while ($commentResults = $db->fetch_array($commentQuery)) {
					$commentIds[] = $commentResults['cid'];
				}
				$db->update("DELETE FROM pw_comment WHERE uid IN (".S::sqlImplode($userIds).")");
				S::isArray($commentIds) && $db->update('DELETE FROM pw_weibo_cmrelations WHERE cid IN (' . S::sqlImplode($commentIds) . ')');
				$db->update("DELETE FROM pw_weibo_comment WHERE uid IN (".S::sqlImplode($userIds).")");
				$db->update("UPDATE pw_weibo_content SET replies=0 WHERE uid IN(".S::sqlImplode($userIds).")");
			}
			if ($item & 256) {
				//评分
				$userService = L::loadClass('userservice', 'user'); /* @var $userService PW_Userservice */
				$userNames = $userService->getUserNamesByUserIds($userIds);
				if ($userNames) {
					//$db->update("DELETE FROM pw_pinglog WHERE pinger IN (".S::sqlImplode($userNames).")");
					pwQuery::delete('pw_pinglog', 'pinger IN (:pinger)', array($userNames));
				}

				//收藏
				$collectionService = L::loadClass('collection', 'collection'); /* @var $collectionService PW_Collection */
				$collectionService->deleteByUids($userIds);
				
				//朋友
				$friendService = L::loadClass('friend', 'friend'); /* @var $friendService PW_Friend */
				$friendService->delFriendByUids($userIds);
				
				//关注
				$db->update("DELETE FROM pw_attention WHERE uid IN (".S::sqlImplode($userIds).")");
			}

			$userCache = L::loadClass('userCache', 'user');
			$userCache->delete($userIds);
			adminmsg('operate_success', "$basename&action=$action&groupid=$groupid&schname=" . rawurlencode($schname)."&schuid=$schuid&schemail=$schemail&postnum=$postnum&onlinetime=$onlinetime&regdate=$regdate&schlastvisit=$schlastvisit&orderway=$orderway&asc=$asc&lines=$lines&page=$page&skipcheck=1");
		}
	} else {
		initGroupOptions();
		include PrintEot('usermanage');exit;
	}
} elseif ($adminitem == 'add') {
	if ($action == 'addnew') {
		S::gp(array('username','password','email','groupid'),'P');
		if (!$username || !$password || !$email) {
			adminmsg('setuser_empty');
		}
		!$groupid && $groupid = '-1';
	
		$username = trim($username);
		$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
		foreach ($S_key as $value) {
			if (strpos($username,$value)!==false) {
				adminmsg('illegal_username');
			}
			if (strpos($password,$value)!==false) {
				adminmsg('illegal_password');
			}
		}
		if (strlen($username)>15 || strrpos($username,"|")!==false || strrpos($username,'.')!==false || strrpos($username,' ')!==false || strrpos($username,"'")!==false || strrpos($username,'/')!==false || strrpos($username,'*')!==false || strrpos($username,";")!==false || strrpos($username,",")!==false || strrpos($username,"<")!==false || strrpos($username,">")!==false) {
			adminmsg('illegal_username');
		}
		if (strrpos($password,"\r")!==false || strrpos($password,"\t")!==false || strrpos($password,"|")!==false || strrpos($password,"<")!==false || strrpos($password,">")!==false) {
			adminmsg('illegal_password');
		} else {
			$password = md5($password);
		}
		if ($email&&!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,3}$",$email)) {
			adminmsg('illegal_email');
		}
		if ($groupid == '3' && !If_manager) {
			adminmsg('manager_right');
		}
		$register = L::loadClass('Register', 'user');
		$register->setField('username', $username);
		$register->setField('password', $password);
		$register->setField('email', $email);
		$register->setField('groupid', $groupid);
		$register->setField('yz', 1);
		$register->execute();
		$customfieldService = L::loadClass('CustomerFieldService','user');/* @var $customfieldService PW_CustomerFieldService */
		$customfieldService->saveRegisterCustomerData();
		if ($groupid <> '-1') {
			admincheck($register->uid,$username,$groupid,'','update');
		}
		adminmsg('operate_success');
	} else {
		initGroupOptions();
		include PrintEot('usermanage');exit;
	}
} elseif ($adminitem == 'usertitle') {
	//头衔管理 
	if ($action == 'groups') {
		S::gp(array('groupid','schname'),'P');
		$sql = is_numeric($groupid) ? "a.groups LIKE '%,$groupid,%'" : "a.groups!=''";
		$schname = trim($schname);
		if ($schname != '') {
			if (strpos($schname,'*') !== false) {
				$schname = addslashes(str_replace('*','%',$schname));
				$sql .= " AND (a.username LIKE ".S::sqlEscape($schname).")";
			} else {
				$schname = addslashes($schname);
				$sql .= " AND a.username=".S::sqlEscape($schname);
			}
		}
		$query = $db->query("SELECT a.uid,a.username,a.groupid,a.groups,m.memberid FROM pw_administrators a LEFT JOIN pw_members m USING(uid) WHERE $sql LIMIT 200");
		while ($rt = $db->fetch_array($query)) {
			$rt['system'] = $rt['groupid']=='-1' ? $ltitle[$rt['memberid']] : $ltitle[$rt['groupid']];
			$groupds = explode(',',$rt['groups']);
			foreach ($groupds as $key => $value) {
				if ($value) {
					$rt['gtitle'] .= $ltitle[$value].' ';
				}
			}
			$schdb[] = $rt;
		}
	} else {
		initGroupOptions();
	}
	include PrintEot('usermanage');exit;
} elseif ($adminitem == 'unituser') {
	require_once(R_P.'require/credit.php');
	if ($_POST['action'] == "unit") {
		S::gp(array('uids','newuid'),'P');
		if (!$uids) {
			adminmsg('unituser_username_empty');
		}
		if (!$newuid) {
			adminmsg('unituser_newname_empty');
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$touser = $userService->get($newuid);
		S::slashes($touser);
		if (!$touser['username']) {
			adminmsg('unituser_newname_error');
		}
		$oldinfo = array();
		$uids = explode(',',$uids);
		foreach ($uids as $key => $value) {
			if (is_numeric($value)) {
				if ($value == $newuid) {
					adminmsg('unituser_samename');
				}
				$rt = $userService->get($value, true, true, true);
				if (!$rt['uid']) {
					adminmsg('unituser_username_error');
				} else {
					$oldinfo[] = $rt;
				}
			}
		}
		$ptable_a = array('pw_posts');
	
		if ($db_plist && count($db_plist)>1) {
			foreach ($db_plist as $key => $value) {
				if($key == 0) continue;
				$ptable_a[] = 'pw_posts'.$key;
			}
		}
		$postnum = $digests = $rvrc = $money = $credits = $currency = $deposit = $ddeposit = 0;
		foreach ($oldinfo as $key => $value) {
			$postnum  += $value['postnum'];
			$digests  += $value['digests'];
			$rvrc     += $value['rvrc'];
			$money    += $value['money'];
			$credits   += $value['credit'];
			$currency += $value['currency'];
			$deposit  += $value['deposit'];
			$ddeposit += $value['ddeposit'];
	
			$creditdb = $credit->get($value['uid'],'CUSTOM');
			foreach ($creditdb as $k => $val) {
				/**
				$db->pw_update(
					"SELECT uid FROM pw_membercredit WHERE uid=".S::sqlEscape($newuid)."AND cid=".S::sqlEscape($k),
					"UPDATE pw_membercredit SET value=value+".S::sqlEscape($val[1])."WHERE uid=".S::sqlEscape($newuid)."AND cid=".S::sqlEscape($k),
					"INSERT INTO pw_membercredit SET".S::sqlSingle(array('uid'=>$newuid,'cid'=>$k,'value'=>$val[1]))
				);
				**/
				$db->pw_update(
					"SELECT uid FROM pw_membercredit WHERE uid=".S::sqlEscape($newuid)."AND cid=".S::sqlEscape($k),
					pwQuery::buildClause("UPDATE :pw_table SET value=value+:value WHERE uid=:uid AND cid=:cid", array('pw_membercredit', $val[1], $newuid, $k)),
					pwQuery::insertClause('pw_membercredit', array('uid'=>$newuid,'cid'=>$k,'value'=>$val[1]))
				);			
			}
	
			//$db->update("UPDATE pw_threads SET ".S::sqlSingle(array('author'=>$touser['username'],'authorid'=>$newuid))."WHERE authorid=".S::sqlEscape($value['uid']));
			pwQuery::update('pw_threads', 'authorid=:authorid', array($value['uid']), array('author'=>$touser['username'],'authorid'=>$newuid));
			foreach ($ptable_a as $val) {
				//$db->update("UPDATE $val SET ".S::sqlSingle(array('author'=>$touser['username'],'authorid'=>$newuid))."WHERE authorid=".S::sqlEscape($value['uid']));
				pwQuery::update($val, 'authorid=:authorid', array($value['uid']), array('author'=>$touser['username'],'authorid'=>$newuid));
			}
			$db->update("UPDATE pw_attachs SET uid=".S::sqlEscape($newuid)."WHERE uid=".S::sqlEscape($value['uid']));
	
			$userService->delete($value['uid']);
	
			$messageServer = L::loadClass('message', 'message');
			$messageServer->clearMessages($value['uid'],array('groupsms','sms','notice','request','history'));
		}
	
		$mainFields = array();
		$memberDataFields = array(
			'postnum' => $postnum,
			'digests' => $digests,
			'rvrc' => $rvrc,
			'money' => $money,
			'credit' => $credits,
			'currency' => $currency
		);
		$memberInfoFields = array(
			'deposit' => $deposit,
			'ddeposit' => $ddeposit
		);
	
		$userService->updateByIncrement($newuid, $mainFields, $memberDataFields, $memberInfoFields);
		adminmsg('operate_success');
	}
	include PrintEot('usermanage');exit;
} elseif ($adminitem == 'customcredit'){
	require_once(R_P."require/credit.php");
	if ($action == 'edit') {
		if (empty($_POST['step'])) {
			S::gp(array('uid','username'));
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			if(is_numeric($uid)){
				$rt = $userService->get($uid);
			} else{
				$rt = $userService->getByUserName($username);
			}
	
			if (!$rt) {
				!$username && adminmsg('user_username_empty');
				$errorname = $username;
				adminmsg('user_not_exists');
			}
			$u_credit = $credit->get($rt['uid'],'CUSTOM');
			include PrintEot('usermanage');exit;
	
		} else {
	
			S::gp(array('uid','creditdb'),'P');
			!is_numeric($uid) && adminmsg('operate_error');
			foreach ($creditdb as $key => $value) {
				if (is_numeric($key) && is_numeric($value)) {
					/**
					$db->pw_update(
						"SELECT uid FROM pw_membercredit WHERE uid=".S::sqlEscape($uid)."AND cid=".S::sqlEscape($key),
						"UPDATE pw_membercredit SET value=".S::sqlEscape($value)."WHERE uid=".S::sqlEscape($uid)."AND cid=".S::sqlEscape($key),
						"INSERT INTO pw_membercredit SET ".S::sqlSingle(array('uid'=>$uid,'cid'=>$key,'value'=>$value))
					);
					**/
					$db->pw_update(
						"SELECT uid FROM pw_membercredit WHERE uid=".S::sqlEscape($uid)."AND cid=".S::sqlEscape($key),
						pwQuery::updateClause('pw_membercredit', 'uid=:uid AND cid=:cid', array($uid,$key), array('value'=>$value)),
						pwQuery::insertClause('pw_membercredit', array('uid'=>$uid,'cid'=>$key,'value'=>$value))
					);									
				}
			}
			adminmsg('operate_success');
		}
	} else {
		S::gp(array('page'),'GP',2);
		$page<1 && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_membercredit WHERE value!=0");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");
	
		$query = $db->query("SELECT m.uid,m.username,mc.cid,mc.value FROM pw_membercredit mc LEFT JOIN pw_members m USING(uid) WHERE value!=0 ORDER BY cid, value DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['name'] = $_CREDITDB[$rt['cid']][0];
			$creditdb[] = $rt;
		}
		include PrintEot('usermanage');exit;
	}
}

function initGroupOptions(){
	global $db,
		$groupselect,$groupselect_add,$g_sel;//for template file
		
	$groupselect = "<option value='-1'>".getLangInfo('all','reg_member')."</option>";
	$groupselect_add = "<option value='-1'>".getLangInfo('all','reg_member')."</option>";
	$g_sel = '';
	$query = $db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gid>2 AND gptype<>'member' ORDER BY gid");
	while ($group = $db->fetch_array($query)) {
		$groupselect .= "<option value=\"$group[gid]\">$group[grouptitle]</option>";
		if($group['gid'] != 5){
			$groupselect_add .= "<option value=\"$group[gid]\">$group[grouptitle]</option>";
		}
		if ($group['gptype'] != 'default') { 
			$g_sel .= "<option value=\"$group[gid]\">$group[grouptitle]</option>";
		}
	}
}