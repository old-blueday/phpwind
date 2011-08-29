<?php
!function_exists('readover') && exit('Forbidden');
if ( !$step ){
	$ifppt = false;
	if (!$db_pptifopen || $db_ppttype == 'server') {
		$ifppt = true;
	}
	$ifpublic = $userService->getUserStatus($winduid, PW_USERSTATUS_PUBLICMAIL) ? 'checked' : '';
	require_once uTemplate::PrintEot('info_safe');
	pwOutPut();
}else if($step == '2') {
	PostCheck();
	S::slashes($userdb);
	$upmembers = $upmemdata = $upmeminfo = array();
	if ($ifppt) {

		//* include_once pwCache::getPath(D_P.'data/bbscache/dbreg.php');
		extract(pwCache::getData(D_P.'data/bbscache/dbreg.php', false));
		
		S::gp(array('propwd','proemail','question'),'P');
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
				S::inArray($windid,$manager) && Showmsg('pro_manager');
				$propwd != $_POST['check_pwd'] && Showmsg('password_confirm');
/*
				if ($propwd != str_replace(array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%'),'',$propwd)) {
					Showmsg('illegal_password');
				}
*/
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
								if (!preg_match('/[\\|\/|\&\'\"\*\,<>#\?%]/',$propwd)) {
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
				//* include_once pwCache::getPath(D_P.'data/bbscache/dbreg.php');
				extract(pwCache::getData(D_P.'data/bbscache/dbreg.php', false));
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
	//密码修改问题
	if ($db_ifsafecv && $question != '-2') {
		$safecv = '';
		if ($db_ifsafecv) {
			require_once(R_P.'require/checkpass.php');
			$safecv = questcode($question,$_POST['customquest'],$_POST['answer']);
		}
		$upmembers['safecv'] = $safecv;
	}
	$pwSQL = array_merge($upmembers, array('email' => $proemail));
	//update memdata
	if ($upmemdata) {
		$userService->update($winduid, array(), $upmemdata);
	}
	//update meminfo
	if ($upmeminfo) {
		updateThreadTrade($upmeminfo,$winduid);
	}
	unset($upmemdata,$upmeminfo);

	$result = $userService->update($winduid, $pwSQL);
	// defend start	
	CloudWind::yunUserDefend('editprofile', $winduid, $windid, $upmemdata['pwdctime'], 0, 101,'','','',array('profile'=>array_keys($pwSQL)));
	// defend end

	/* platform weibo app */
	$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
	if ($siteBindService->isOpen() && $upmembers['password']) {
		$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
		$weiboLoginService->setLoginUserPasswordHasReset($winduid);
		
		Cookie("winduser",StrCode($winduid."\t".PwdCode($upmembers['password'])."\t".$upmembers['safecv']));
		Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
		Cookie('lastvisit','',0);
		//自动获取勋章_start
		require_once(R_P.'require/functions.php');
		doMedalBehavior($winduid,'continue_login');
		//自动获取勋章_end
	}

	//* $_cache = getDatastore();
	//* $_cache->delete('UID_'.$winduid);
	initJob($winduid,"doUpdatedata");
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}