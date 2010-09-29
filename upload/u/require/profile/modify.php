<?php
!function_exists('readover') && exit('Forbidden');

require_once(R_P. 'require/functions.php');
@include_once(D_P. 'data/bbscache/customfield.php');


$ifppt = false;

if (!$db_pptifopen || $db_ppttype == 'server') {
	$ifppt = true;
}
!is_array($customfield) && $customfield = array();

foreach ($customfield as $key => $value) {
	$customfield[$key]['id'] = $value['id'] = (int)$value['id'];
	$customfield[$key]['field'] = "field_$value[id]";
	if ($value['type'] == 3 && $_POST['step'] != 2) {
		$customfield[$key]['options'] = explode("\n",$value['options']);
	} elseif ($value['type'] == 2) {
		$SCR = 'post';
	}
}

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$userdb = $userService->get($winduid, true, true, true);

InitGP(array('info_type'));

//浏览页ajax创建商品分类名称
InitGP(array('ajax_create','tradeTypeName'),"P");
($ajax_create) &&  createThreadTradeType($tradeTypeName,$winduid);

if (!is_array($trade = unserialize($userdb['tradeinfo']))) {
	$trade = array();
}

if (empty($_POST['step'])) {

	list($iconurl,$icontype,$iconwidth,$iconheight,$iconfile,,,$iconsize) = showfacedesign($userdb['icon'], true,'m');
	include_once(D_P.'data/bbscache/dbreg.php');
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/credit.php');

	$customdata = $custominfo = $sexselect = $yearslect = $monthslect = $dayslect = array();
	$ifpublic = $groupselect = $httpurl = $email_Y = $email_N = $prosign_Y = $prosign_N = '';
	$ifsign = false;
	!in_array($info_type, array('base','trade','link','face','safe','binding','other')) && $info_type = 'base';
	getstatus($userdb['userstatus'], PW_USERSTATUS_PUBLICMAIL) && $ifpublic = 'checked';
	${'email_' . (getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL) ? 'Y' : 'N')} = 'checked';
	${'prosign_' . (getstatus($userdb['userstatus'], PW_USERSTATUS_SHOWSIGN) ? 'Y' : 'N')} = 'checked';
	if ($db_selectgroup && $userdb['groups']) {
		$ltitle = L::config('ltitle', 'level');
		$groupselect = $userdb['groupid']=='-1' ? '<option></option>' : "<option value=\"$userdb[groupid]\">".$ltitle[$userdb['groupid']]."</option>";
		$groups = explode(',',$userdb['groups']);
		foreach ($groups as $value) {
			$ltitle = (is_array($ltitle)) ? $ltitle : array($ltitle);
			if ($value && array_key_exists($value,$ltitle)) {
				$groupselect .= "<option value=\"$value\">$ltitle[$value]</option>";
			}
		}
	}
	$db_union[7] && list($customdata,$custominfo) = Getcustom($userdb['customdata']);

	$sexselect[(int)$userdb['gender']] = 'checked';

	!$rg_timestart && $rg_timestart = 1960;
	!$rg_timeend && $rg_timeend = 2010;
	$getbirthday = explode('-',$userdb['bday']);
	$yearslect[(int)$getbirthday[0]] = $monthslect[(int)$getbirthday[1]] = $dayslect[(int)$getbirthday[2]] = 'selected';

	//$width2 = $iconwidth;
	//$height2 = $iconheight;
	//$iconwidth && $iconsize = " width=\"$iconwidth\"";
	//$iconheight && $iconsize .= " height=\"$iconheight\"";
	if ($icontype == 2) {
		$httpurl = $iconurl;
	}
	if ($icontype != 1) {
		$iconfile = '';
	}
	if ($userdb['signature'] || $userdb['introduce']) {
		$SCR = 'post';
	}
	//!$userdb['site'] && $userdb['site'] ="http://";
	//系统头像
	$img = @opendir("$imgdir/face");
	while ($imgname = @readdir($img)) {
		if ($imgname!='.' && $imgname!='..' && $imgname!='' && preg_match('/\.(gif|jpg|png|bmp)$/i',$imgname)) {
			$num++;
			$imgname_array[] = $imgname;
			if ($num >= 10) break;
		}
	}
	@closedir($img);

	//flash头像上传参数
	if ($db_ifupload && $_G['upload']) {
		list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize) = explode("\t",$db_upload);
		$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
		$swfhash = GetVerify($winduid);
		$upload_param = rawurlencode($db_bbsurl.'/job.php?action=uploadicon&verify='.$swfhash.'&uid='.$winduid.'&');
		$save_param = rawurlencode($db_bbsurl.'/job.php?action=uploadicon&step=2&');
		$default_pic = rawurlencode("$db_picpath/facebg.jpg");
		$icon_encode_url = 'up='.$upload_param.'&saveFace='.$save_param.'&url='.$default_pic.'&PHPSESSID='.$sid.'&'.'imgsize='.$db_imgsize.'&';
	} else {
		$icon_encode_url = '';
	}
	if ($userdb['timedf']) {
		$temptimedf = str_replace('.','_',abs($userdb['timedf']));
		$userdb['timedf'] < 0 ? ${'zone_0'.$temptimedf} = 'selected' : ${'zone_'.$temptimedf} = 'selected';
	}
	$ubinding = array();
	$ubindingneedupdatepwd = false;
	$query = $db->query("SELECT u2.uid as uuid, u1.password as oldpassword, m.password, m.uid,m.username,m.groupid,m.memberid,m.regdate,mb.postnum FROM pw_userbinding u1 LEFT JOIN pw_userbinding u2 ON u1.id=u2.id LEFT JOIN pw_members m ON m.uid=u2.uid LEFT JOIN pw_memberdata mb ON m.uid=mb.uid WHERE u1.uid=" . pwEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		if (empty($rt['uid'])) {
			$db->update("DELETE FROM pw_userbinding WHERE uid=".pwEscape($rt['uuid'],false));
		} elseif ($rt['uid'] != $winduid) {
			$rt['groupid'] == '-1' && $rt['groupid'] = $rt['memberid'];
			$rt['regdate'] = get_date($rt['regdate']);
			$ubinding[] = $rt;
		} else {
			$ubindingneedupdatepwd = ($rt['password'] == $rt['oldpassword']) ? false : true;
		}
		unset($rt['password'], $rt['oldpassword']);
	}

	require_once uTemplate::PrintEot('profile_modify');
	pwOutPut();

} elseif ($_POST['step'] == '2') {

	PostCheck();
	Add_S($userdb);
	$upmembers = $upmemdata = $upmeminfo = array();
	Cookie('pro_tab', $info_type , 'F', false);
	if ($ifppt) {

		include_once(D_P.'data/bbscache/dbreg.php');
		InitGP(array('propwd','proemail'),'P');
		if ($propwd || $userdb['email'] != $proemail) {
			if ($_POST['oldpwd']) {
				if (strlen($userdb['password']) == 16) {
					$_POST['oldpwd'] = substr(md5($_POST['oldpwd']),8,16);//支持 16 位 md5截取密码
				} else {
					$_POST['oldpwd'] = md5($_POST['oldpwd']);
				}
			}
			$userdb['password'] != $_POST['oldpwd'] && Showmsg('pwd_confirm_fail');
			if ($propwd) {
				CkInArray($windid,$manager) && Showmsg('pro_manager');
				$propwd != $_POST['check_pwd'] && Showmsg('password_confirm');
				if ($propwd != str_replace(array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%'),'',$propwd)) {
					Showmsg('illegal_password');
				}
				list($rg_regminpwd,$rg_regmaxpwd) = explode("\t",$rg_pwdlen);
                if (strlen($propwd)<$rg_regminpwd) {
                    Showmsg('reg_password_minlimit');
                } elseif ($rg_regmaxpwd && strlen($propwd)>$rg_regmaxpwd) {
                    Showmsg('reg_password_maxlimit');
                } elseif ($rg_npdifferf && $propwd==$windid) {
                    Showmsg('reg_nameuptopwd');
                }
				if ($rg_pwdcomplex) {
					$arr_rule = array();
					$arr_rule = explode(',',$rg_pwdcomplex);
					foreach ($arr_rule as $value) {
						$value = (int)$value;
						if (!$value) continue;
						switch ($value) {
							case 1:
								if (!preg_match('/[a-z]/',$propwd)) {
									Showmsg('reg_password_lowstring');
								}
								break;
							case 2:
								if (!preg_match('/[A-Z]/',$propwd)) {
									Showmsg('reg_password_upstring');
								}
								break;
							case 3:
								if (!preg_match('/[0-9]/',$propwd)) {
									Showmsg('reg_password_num');
								}
								break;
							case 4:
								if (!preg_match('/[^a-zA-Z0-9\\\/\&\'\"\*\,<>#\?% ]/',$propwd)) {
									Showmsg('reg_password_specialstring');
								}
								break;
						}
					}
				}
				$upmembers['password'] = md5($propwd);
				$upmemdata['pwdctime'] = $timestamp;
			}
			if ($userdb['email'] != $proemail) {
				include_once(D_P.'data/bbscache/dbreg.php');
				$rg_emailcheck && Showmsg('pro_emailcheck');

				$email_check = $userService->getByEmail($proemail); //TODO Need to add a method for check user exist with email in PW_UserService?
				if ($email_check) {
					Showmsg('reg_email_have_same');
				} else {
					unset($email_check);
				}
			}
		}
		if ($_POST['propublicemail'] != getstatus($userdb['userstatus'], PW_USERSTATUS_PUBLICMAIL)) {
			$userService->setUserStatus($winduid, PW_USERSTATUS_PUBLICMAIL, (int)$_POST['propublicemail']);
		}
	} else {
		$proemail = $userdb['email'];
	}
	if ($proemail && !preg_match('/^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$proemail)) {
		Showmsg('illegal_email');
	}
	InitGP(array('proicon','prosign','showsign','profrom','proyahoo','promsn','prohomepage','prohonor', 'prointroduce','customdata', 'prooicq', 'proaliww','proicq','alipay','tradetype','question','timedf'),'P');
	InitGP(array('newgroupid','progender','proyear','promonth','proday','facetype','proreceivemail'), 'P', 2);

	if ($db_ifsafecv && $question != '-2') {
		$safecv = '';
		if ($db_ifsafecv) {
			require_once(R_P.'require/checkpass.php');
			$safecv = questcode($question,$_POST['customquest'],$_POST['answer']);
		}
		$upmembers['safecv'] = $safecv;
	}

	if ($db_selectgroup && $newgroupid && $newgroupid != $userdb['groupid'] && $userdb['groups']) {
		if (strpos($userdb['groups'],','.$newgroupid.',') === false) {
			Showmsg('undefined_action');
		} else {
			if ($userdb['groupid'] == '-1') {
				$groups = str_replace(",$newgroupid,",',',$userdb['groups']);
				$groups == ',' && $groups = '';
			} else {
				$groups = str_replace(",$newgroupid,",",$userdb[groupid],",$userdb['groups']);
			}
			$upmembers['groupid']	= $newgroupid;
			$upmembers['groups']	= $groups;
		}
	}
	$prooicq && !is_numeric($prooicq) && Showmsg('illegal_OICQ');
	$proicq && !is_numeric($proicq) && Showmsg('illegal_OICQ');

	strlen($prointroduce)>500 && Showmsg('introduce_limit');
	$_G['signnum'] && strlen($prosign) > $_G['signnum'] && Showmsg('sign_limit');
	if ($_G['allowhonor']) {
		$prohonor = trim(substrs($prohonor,90));
		$upmembers['honor'] = $prohonor;
	}
	require_once(R_P . 'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	foreach (array($prosign, $prointroduce, $prohonor) as $key => $value) {
		if (($banword = $wordsfb->comprise($value)) !== false) {
			Showmsg('sign_wordsfb');
		}
	}
	//upmeminfo
	if ($db_union[7]) {
		list($customdata) = Getcustom($customdata,false,true);
		!empty($customdata) && $upmeminfo['customdata'] = addslashes(serialize($customdata));
	}
	foreach ($customfield as $value) {
		$fieldvalue = Char_cv($_POST[$value['field']]);
		if ($value['required'] && ($value['editable'] == 1 || strlen($userdb[$value['field']]) == 0) && !$fieldvalue) {
			Cookie('pro_modify', 'other', 'F', false);
			Showmsg('field_empty');
		}
		if (strlen($userdb[$value['field']]) == 0 || ($userdb[$value['field']] != $fieldvalue && $value['editable'] == 1)) {
			if ($value['maxlen'] && strlen($fieldvalue) > $value['maxlen']) {
				Showmsg('field_lenlimit');
			}
			$upmeminfo[$value['field']] = $fieldvalue;
		}
	}

	$trade['alipay'] = '';
	if ($alipay) {
		if (preg_match('/^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$alipay) || preg_match('/^[\d]{11}/',$alipay)) {
			$trade['alipay'] = stripslashes($alipay);
		} else {
			Showmsg('alipay_error');
		}
	}
	/*
	if ($tenpay) {
		$trade['tenpay'] = stripslashes($tenpay);
	}
	*/
	$trade['tradetype'] = array();
	if ($tradetype && is_array($tradetype)) {
		$i = 0;
		foreach ($tradetype as $key => $value) {
			if ($key > 0 && $value && is_string($value)) {
				$trade['tradetype'][$key] = stripslashes($value);
			}
			if (++$i > 100) break;
		}
	}
	$tradeinfo = $trade ? addslashes(serialize($trade)) : '';
	if ($tradeinfo <> $userdb['tradeinfo']) {
		$upmeminfo['tradeinfo'] = $tradeinfo;
	}
	require_once(R_P.'require/showimg.php');
	$user_a = explode('|',$winddb['icon']);
	$usericon = '';
	if ($facetype == 1) {
		$usericon = setIcon($proicon, $facetype, $user_a);
	} elseif ($_G['allowportait'] && $facetype == 2) {
		$httpurl = GetGP('httpurl','P');
		if (strncmp($httpurl[0],'http://',7) != 0 || strrpos($httpurl[0],'|') !== false) {
			Showmsg('illegal_customimg');
		}
		$proicon = Char_cv($httpurl[0]);
		$httpurl[1] = (int)$httpurl[1];
		$httpurl[2] = (int)$httpurl[2];
		$httpurl[3] = (int)$httpurl[3];
		$httpurl[4] = (int)$httpurl[4];
		list($user_a[2], $user_a[3]) = flexlen($httpurl[1], $httpurl[2], $httpurl[3], $httpurl[4]);
		$usericon = setIcon($proicon, $facetype, $user_a);
		unset($httpurl);
	}
	pwFtpClose($ftp);

	//set signature state
	$showsign = $showsign ? 1 : 0;
	if ($db_signmoney && ($singstatus = getstatus($userdb['userstatus'], PW_USERSTATUS_SHOWSIGN)) != $showsign) {
		$userService->setUserStatus($winduid, PW_USERSTATUS_SHOWSIGN, $showsign);
		if ($singstatus && $showsign == 0) {
			$upmemdata['starttime'] = 0;
		} else {
			require_once(R_P.'require/credit.php');
			if (($cur = $credit->get($winduid,$db_signcurtype)) === false) {
				Showmsg('numerics_checkfailed');
			}
			if ($cur < $db_signmoney) {
				Showmsg('noenough_currency');
			}
			$credit->addLog('main_showsign',array($db_signcurtype => -$db_signmoney),array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ip'		=> $onlineip
			));
			if (!$credit->set($winduid,$db_signcurtype,-$db_signmoney,true)) {
				Showmsg('numerics_checkfailed');
			}
			$upmemdata['starttime'] = $tdtime;
		}
	}

	//update memdata
	if ($upmemdata) {
		$userService->update($winduid, array(), $upmemdata);
	}
	//update meminfo
	if ($upmeminfo) {
		updateThreadTrade($upmeminfo,$winduid);
	}
	unset($upmemdata,$upmeminfo, $facetype, $customfield, $singstatus);
	//other
	//$prohomepage && substr($prohomepage,0,4)!='http' && $prohomepage = "http://$prohomepage";

	$probday = '';
	if ($proyear || $promonth || $proday) {
		$probday = $proyear.'-'.$promonth.'-'.$proday;
	}
	$cksign = convert($prosign,$db_windpic,2);
	$signstatus = $cksign != $prosign ? 1 : 0;

	if ($signstatus != getstatus($userdb['userstatus'], PW_USERSTATUS_SIGNCHANGE)) {
		$userService->setUserStatus($winduid, PW_USERSTATUS_SIGNCHANGE, $signstatus);
	}

	if ($proreceivemail != getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL)) {
		$userService->setUserStatus($winduid, PW_USERSTATUS_RECEIVEMAIL, $proreceivemail);
	}
	//update member
	$pwSQL = array_merge($upmembers, array(
		'email' => $proemail, 'gender' => $progender, 'signature' => $prosign, 'introduce' => $prointroduce, 'oicq' => $prooicq, 'aliww' => $proaliww, 'icq' => $proicq, 'yahoo' => $proyahoo, 'msn' => $promsn, 'site' => $prohomepage, 'location' => $profrom, 'bday' => $probday,'timedf' => $timedf
	));
	$usericon && $pwSQL['icon'] = $usericon;

	$userService->update($winduid, $pwSQL);

	$_cache = getDatastore();
	$_cache->delete('UID_'.$winduid);

	//job sign
	initJob($winduid,"doUpdatedata");

	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);

} elseif ($_POST['step'] == '3') {

	PostCheck();
	!$_G['userbinding'] && Showmsg('undefined_action');

	InitGP(array('username','password','question','customquest','answer'));
	require_once(R_P . 'require/checkpass.php');

	if (empty($username) || empty($password)) {
		Showmsg('login_empty');
	}
	if ($username == $windid) {
		Showmsg('userbinding_same');
	}
	$password = md5($password);
	$safecv = $db_ifsafecv ? questcode($question, $customquest, $answer) : '';

	$db_logintype = 1;
	$logininfo = checkpass($username, $password, $safecv, 0);
	if (!is_array($logininfo)) {
		Showmsg($logininfo);
	}
	list($uid) = $logininfo;

	$arr = array();
	$query = $db->query("SELECT id,uid FROM pw_userbinding WHERE uid IN(" . pwImplode(array($winduid, $uid)) . ")");
	while ($rt = $db->fetch_array($query)) {
		$arr[$rt['uid']] = $rt;
	}
	if (empty($arr)) {

		$db->update("INSERT INTO pw_userbinding SET " . pwSqlSingle(array('uid' => $winduid, 'password' => $userdb['password'])));
		$id = $db->insert_id();
		$db->update("INSERT INTO pw_userbinding SET " . pwSqlSingle(array('id' => $id, 'uid' => $uid, 'password' => $password)));

	} elseif (isset($arr[$winduid]) && !isset($arr[$uid])) {

		$db->update("INSERT INTO pw_userbinding SET " . pwSqlSingle(array('id' => $arr[$winduid]['id'], 'uid' => $uid, 'password' => $password)));
		$id = $arr[$winduid]['id'];

	} elseif (!isset($arr[$winduid]) && isset($arr[$uid])) {

		$db->update("INSERT INTO pw_userbinding SET " . pwSqlSingle(array('id' => $arr[$uid]['id'], 'uid' => $winduid, 'password' => $userdb['password'])));
		$id = $arr[$uid]['id'];

	} elseif (isset($arr[$winduid]) && isset($arr[$uid])) {

		if ($arr[$uid]['id'] == $arr[$winduid]['id']) {
			Showmsg('userbinding_has');
		} else {
			$db->update("UPDATE pw_userbinding SET id=" . pwEscape($arr[$winduid]['id']) . ' WHERE id=' . pwEscape($arr[$uid]['id']));
			$id = $arr[$winduid]['id'];
		}
	} else {
		Showmsg('undefined_action');
	}
	$db->update("UPDATE pw_userbinding u LEFT JOIN pw_members m ON u.uid=m.uid SET m.userstatus=m.userstatus|(1<<11) WHERE u.id=" . pwEscape($id));

	refreshto("profile.php?action=modify&info_type=binding",'operate_success', 2, true);

} elseif ($_POST['step'] == '4') {

	PostCheck();
	InitGP(array('selid'));

	if ($selid && is_array($selid)) {
		$arr = array();
		$query = $db->query("SELECT u2.uid FROM pw_userbinding u1 LEFT JOIN pw_userbinding u2 ON u1.id=u2.id WHERE u1.uid=" . pwEscape($winduid));
		while ($rt = $db->fetch_array($query)) {
			$arr[] = $rt['uid'];
		}
		if ($delarr = array_intersect($arr, $selid)) {
			$db->update("DELETE FROM pw_userbinding WHERE uid IN(" . pwImplode($delarr) . ')');
			$tmp = $delarr + array($winduid);
			if (count(array_unique($tmp)) == count($arr)) {
				$delarr = $tmp;
			}

			$delarr = $userService->getByUserIds($delarr);
			foreach($delarr as $del) {
				$userService->setUserStatus($del['uid'], PW_USERSTATUS_USERBINDING, false);
			}
		}
	}

	refreshto("profile.php?action=modify&info_type=binding",'operate_success', 2, true);

} elseif ($_POST['step'] == '5') {

	PostCheck();
	InitGP(array('bindpassword'));
	$bindpassword = md5($bindpassword);

	$userinfo = $userService->get($winduid);
	$userpwd = $userinfo['password'];
	if ($userpwd != $bindpassword) Showmsg('password_confirm_fail');

	$db->update("UPDATE pw_userbinding SET password=" . pwEscape($userpwd) . ' WHERE uid=' . pwEscape($winduid));
	unset($userinfo, $userpwd, $bindpassword);

	refreshto("profile.php?action=modify&info_type=binding",'operate_success', 2, true);
}

function Getcustom($data,$unserialize=true,$strips=null){
	global $db_union;
	$customdata = array();
	if (!$data || ($unserialize ? !is_array($data=unserialize($data)) : !is_array($data))) {
		$data = array();
	} elseif (!is_array($custominfo = unserialize($db_union[7]))) {
		$custominfo = array();
	}
	if (!empty($data) && !empty($custominfo)) {
		foreach ($data as $key => $value) {
			if (!empty($strips)) {
				$customdata[stripslashes(Char_cv($key))] = stripslashes(Char_cv($value));
			} elseif ($custominfo[$key] && $value) {
				$customdata[$key] = $value;
			}
		}
	}
	return array($customdata,$custominfo);
}

/***********************   快速创建商品分类功能   *************************************/
function createThreadTradeType($tradeTypeName,$userId){
	global $db,$userdb;
	if($tradeTypeName == ""){
		showThreadTrade('empty');
	}
	$tradeinfo = isset($userdb['tradeinfo']) ? unserialize($userdb['tradeinfo']) : array();
	$tradeType = isset($tradeinfo['tradetype']) ? $tradeinfo['tradetype'] : array();
	$tradeType[] = $tradeTypeName;
	$tradeinfo['tradetype'] = $tradeType;

	$upmeminfo = array();
	$upmeminfo['tradeinfo'] = ($tradeinfo) ? addslashes(serialize($tradeinfo)) : '';

	updateThreadTrade($upmeminfo,$userId);

	$selectHtml = getThreadTrade($tradeType);
	showThreadTrade($selectHtml,false);
}

function showThreadTrade($k,$showlang=true){
	$flag = $showlang ? 1 : 0;
	echo '[{"message":\''.(($showlang) ? getThreadTradeLang($k) : $k).'\',"flag":\''.$flag.'\'}]';
	ajax_footer();
}

function getThreadTradeLang($k){
	$message = array();
	$message['ok'] = getLangInfo('other','goods_create_success');
	$message['empty'] = getLangInfo('other','goods_cate_empty');
	return $message[$k];
}

function updateThreadTrade($upmeminfo, $userId){
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->update($userId, array(), array(), $upmeminfo);
}

function getThreadTrade($tradeTypes){
	$html = '';
	$html .= '<select name="ptype" id="ptype">';
	$html .= '<option></option>';
	$count = count($tradeTypes);
	$k = 1;
	foreach ($tradeTypes as $key => $value) {
		$selected = ($count == $k) ? 'selected' : '';
		$html .= '<option value=\"'.$key.'\" '.$selected.'>'.$value.'</option>';
		$k++;
	}
	$html .= '</select>';
	return $html;
}


/************************************************************/














?>