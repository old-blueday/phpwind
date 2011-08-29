<?php
!defined('W_P') && exit('Forbidden');
define ( 'SCR', 'register' );

include_once (D_P . "data/bbscache/dbreg.php");
include_once (D_P . 'data/bbscache/customfield.php');
@include_once (D_P . 'data/bbscache/inv_config.php');

if (! $db_wapregist) {
	wap_header ();
	require_once PrintWAP ( 'register_error' );
	wap_footer ();
}

list ( $rg_regminname, $rg_regmaxname ) = explode ( "\t", $rg_namelen );
list ( $rg_regminpwd, $rg_regmaxpwd ) = explode ( "\t", $rg_pwdlen );

if ($db_pptifopen && $db_ppttype == 'client') {
	wap_msg ( 'passport_register', $basename );
	exit ();
}
list ( $regq ) = explode ( "\t", $db_qcheck );

if ($rg_allowregister == 0 || ($rg_registertype == 1 && date ( 'j', $timestamp ) != $rg_regmon) || ($rg_registertype == 2 && date ( 'w', $timestamp ) != $rg_regweek)) {
	wap_msg ( $rg_whyregclose, $basename );
	exit ();
}

InitGP ( array ('forward' ) );
! $db_pptifopen && $forward = '';
InitGP ( array ('invcode', 'step' ) );
if ($rg_allowsameip && file_exists ( D_P . 'data/bbscache/ip_cache.php' ) && ! in_array ( $step, array ('finish', 'permit' ) )) {
	$ipdata = readover ( D_P . 'data/bbscache/ip_cache.php' );
	$pretime = ( int ) substr ( $ipdata, 13, 10 );
	if ($timestamp - $pretime > $rg_allowsameip * 3600) {
		P_unlink ( D_P . 'data/bbscache/ip_cache.php' );
	} elseif (strpos ( $ipdata, "<$onlineip>" ) !== false) {
		wap_msg ( 'reg_limit', $basename );
		exit ();
	}
}
$step != 'finish' && $groupid != 'guest' && wap_msg ( 'reg_repeat', $basename );
$tmpVerify = GetVerify ( $onlineip );

// echo $onlineip,$tmpVerify;
if ($step == 2) {
	InitGP ( array ('agreergpermit' ) );
	if (! $agreergpermit)
		wap_msg ( '请先确定已阅读并完全同意条款内容', $basename );
	
	if ($_GET ['method'] || $_POST ['_hexie'] != GetVerify ( $onlineip )) {
		wap_msg ( 'undefined_action', $basename );
		exit ();
	}
	wap_PostCheck ( 0, $db_gdcheck & 1, $regq, 0 );
	InitGP ( array ('regreason', 'regname', 'regpwd', 'regpwdrepeat', 'regemail', 'customdata', 'regemailtoall', 'rgpermit' ), 'P' );
	InitGP ( array ('question', 'customquest', 'answer' ), 'P' );
	$sRegpwd = $regpwd;
	$userstatus = 0;
	setstatus ( $userstatus, 11 );
	$regemailtoall && setstatus ( $userstatus, 7 );
	
	if ($rg_allowregister == '2') {
		if (empty ( $invcode )) {
			wap_msg ( 'invcode_empty', $basename );
		} else {
			$inv_days *= 86400;
			$inv = $db->get_one ( "SELECT id,uid FROM pw_invitecode WHERE invcode=" . pwEscape ( $invcode ) . " AND ifused<'2' AND createtime>" . pwEscape ( $timestamp - $inv_days ) );
			! $inv && wap_msg ( 'illegal_invcode', $basename );
		}
	}
	if ($rg_ifcheck && ! $regreason) {
		wap_msg ( 'reg_reason', $basename );
	}
	if (strlen ( $regname ) > $rg_regmaxname || strlen ( $regname ) < $rg_regminname) {
		wap_msg ( 'reg_username_limit', $basename );
	}
	if (strlen ( $regpwd ) < $rg_regminpwd) {
		wap_msg ( 'reg_password_minlimit', $basename );
	} elseif ($rg_regmaxpwd && strlen ( $regpwd ) > $rg_regmaxpwd) {
		wap_msg ( 'reg_password_maxlimit', $basename );
	} elseif ($rg_npdifferf && $regpwd == $regname) {
		wap_msg ( 'reg_nameuptopwd', $basename );
	}
	$S_key = array ("\\", '&', ' ', "'", '"', '/', '*', ',', '<', '>', "\r", "\t", "\n", '#', '%', '?' );
	if (str_replace ( $S_key, '', $regname ) != $regname) {
		wap_msg ( 'illegal_username', $basename );
	} elseif ($regpwd != $regname && str_replace ( $S_key, '', $regpwd ) != $regpwd) {
		wap_msg ( 'illegal_password', $basename );
	}
	if ($regpwd != $regpwdrepeat) {
		wap_msg ( 'password_confirm', $basename );
	}
	if ($rg_pwdcomplex) {
		$arr_rule = array ();
		$arr_rule = explode ( ',', $rg_pwdcomplex );
		foreach ( $arr_rule as $value ) {
			$value = ( int ) $value;
			if (! $value)
				continue;
			switch ($value) {
				case 1 :
					if (! preg_match ( '/[a-z]/', $regpwd )) {
						wap_msg ( 'reg_password_lowstring', $basename );
					}
					break;
				case 2 :
					if (! preg_match ( '/[A-Z]/', $regpwd )) {
						wap_msg ( 'reg_password_upstring', $basename );
					}
					break;
				case 3 :
					if (! preg_match ( '/[0-9]/', $regpwd )) {
						wap_msg ( 'reg_password_num', $basename );
					}
					break;
				case 4 :
					if (! preg_match ( '/[^a-zA-Z0-9]/', $regpwd )) {
						wap_msg ( 'reg_password_specialstring', $basename );
					}
					break;
			}
		}
	}
	$safecv = '';
	if ($db_ifsafecv) {
		require_once (R_P . 'require/checkpass.php');
		$safecv = questcode ( $question, $customquest, $answer );
	}
	if (! $rg_rglower && ! checkRglower ( $regname )) {
		wap_msg ( 'username_limit', $basename );
	}
	$regpwd = md5 ( $regpwd );
	require_once (D_P . 'data/bbscache/level.php');
	@asort ( $lneed );
	$rg_memberid = key ( $lneed );
	
	if (empty ( $regemail ) || ! ereg ( "^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$", $regemail )) {
		wap_msg ( 'illegal_email', $basename );
	} else {
		if ($rg_email) {
			$e_check = 0;
			$e_limit = explode ( ',', $rg_email );
			foreach ( $e_limit as $key => $val ) {
				if (strpos ( $regemail, "@" . $val ) !== false) {
					$e_check = 1;
					break;
				}
			}
			$e_check == 0 && wap_msg ( 'email_check' );
		}
		$email_check = $db->get_one ( 'SELECT COUNT(*) AS count FROM pw_members WHERE email=' . pwEscape ( $regemail ) );
		if ($email_check ['count']) {
			wap_msg ( 'reg_email_have_same', $basename );
		}
	}
	if ($regname !== Sql_cv ( $regname )) {
		wap_msg ( 'illegal_username', $basename );
	}
	$rs = $db->get_one ( 'SELECT COUNT(*) AS count FROM pw_members WHERE username=' . pwEscape ( $regname ) );
	if ($rs ['count'] > 0) {
		wap_msg ( 'username_same', $basename );
	}
	
	CkInArray ( strtolower ( $regname ), array ('guest', 'system' ) ) && wap_msg ( 'illegal_username' );
	$rg_banname = explode ( ',', $rg_banname );
	foreach ( $rg_banname as $value ) {
		if ($value && strpos ( $regname, $value ) !== false) {
			wap_msg ( 'illegal_username', $basename );
		}
	}
	
	if ($rg_ifcheck == '1') {
		$rg_groupid = '7'; //后台控制是否需要验证
	} else {
		$rg_groupid = '-1';
	}
	
	if ($rg_emailcheck == 1) {
		$rg_yz = num_rand ( 9 );
	} else {
		$rg_yz = 1;
	}
	
	$upmeminfo = array ();
	
	$pwSQL = pwSqlSingle ( array ('username' => $regname, 'password' => $regpwd, 'safecv' => $safecv, 'email' => $regemail, 'groupid' => $rg_groupid, 'memberid' => $rg_memberid, 'regdate' => $timestamp, 'icq' => '', 'yz' => $rg_yz, 'userstatus' => $userstatus ) );
	$db->update ( "INSERT INTO pw_members SET $pwSQL" );
	$winduid = $db->insert_id ();
	
	require_once (R_P . 'require/credit.php');
	$credit->addLog ( 'reg_register', $rg_regcredit, array ('uid' => $winduid, 'username' => stripslashes ( $regname ), 'ip' => $onlineip ) );
	$credit->sets ( $winduid, $rg_regcredit, false );
	$credit->runsql ();
	$pwSQL = pwSqlSingle ( array ('postnum' => 0, 'lastvisit' => $timestamp, 'thisvisit' => $timestamp, 'onlineip' => $onlineip ) );
	$db->update ( "INSERT INTO pw_memberdata SET uid=" . pwEscape ( $winduid ) . ",$pwSQL " );
	
	if ($rg_ifcheck) {
		$upmeminfo ['regreason'] = $regreason;
	}
	if (! is_array ( $db_union )) {
		$db_union = explode ( "\t", stripslashes ( $db_union ) );
	}
	$custominfo = unserialize ( $db_union [7] );
	if ($custominfo && $customdata) {
		foreach ( $customdata as $key => $val ) {
			$key = Char_cv ( $key );
			$customdata [stripslashes ( $key )] = stripslashes ( $val );
		}
		$upmeminfo ['customdata'] = addslashes ( serialize ( $customdata ) );
	}
	$db->update ( "UPDATE pw_bbsinfo SET newmember=" . pwEscape ( $regname ) . ",totalmember=totalmember+1 WHERE id='1'" );
	if ($upmeminfo) {
		$db->update ( "REPLACE INTO pw_memberinfo SET uid=" . pwEscape ( $winduid ) . ',' . pwSqlSingle ( $upmeminfo ) );
	}
	if ($inv_open == '1') {
		$db->update ( "UPDATE pw_invitecode SET " . pwSqlSingle ( array ('receiver' => $regname, 'usetime' => $timestamp, 'ifused' => 2 ) ) . ' WHERE id=' . pwEscape ( $inv ['id'] ) );
		if ($inv ['uid'] == 0) {
			$db->update ( "UPDATE pw_clientorder SET uid=" . pwEscape ( $winduid ) . " WHERE type='4' AND uid='0' AND paycredit=" . pwEscape ( $inv ['id'] ) );
		}
	}
	$windid = $regname;
	$windpwd = $regpwd;
	// $iptime=$timestamp+86400;
	// Cookie("ifregip",$onlineip,$iptime);
	if ($rg_allowsameip) {
		if (file_exists ( D_P . 'data/bbscache/ip_cache.php' )) {
			writeover ( D_P . 'data/bbscache/ip_cache.php', "<$onlineip>", "ab" );
		} else {
			writeover ( D_P . 'data/bbscache/ip_cache.php', "<?php die;?><$timestamp>\n<$onlineip>" );
		}
	}
	if (GetCookie ( 'userads' ) && $db_ads == '2') {
		list ( $u, $a ) = explode ( "\t", GetCookie ( 'userads' ) );
		if (is_numeric ( $u ) || ($a && strlen ( $a ) < 16)) {
			require_once (R_P . 'require/userads.php');
		}
	}
	
	if (GetCookie ( 'o_invite' ) && $db_modes ['o'] ['ifopen'] == 1) {
		list ( $o_u, $hash, $app ) = explode ( "\t", GetCookie ( 'o_invite' ) );
		if (is_numeric ( $o_u ) && strlen ( $hash ) == 18) {
			require_once (R_P . 'require/o_invite.php');
		}
		Cookie ( 'o_invite', '' );
	}
	
	if ($rg_yz == 1) {
		Cookie ( "winduser", StrCode ( $winduid . "\t" . PwdCode ( $windpwd ) . "\t" . $safecv ) );
		Cookie ( "ck_info", $db_ckpath . "\t" . $db_ckdomain );
		Cookie ( 'lastvisit', '', 0 ); //将$lastvist清空以将刚注册的会员加入今日到访会员中
	}
	
	// 发送短消息
	if ($rg_regsendmsg) {
		require_once (R_P . 'require/msg.php');
		$rg_welcomemsg = str_replace ( '$rg_name', $regname, $rg_welcomemsg );
		$messageinfo = array ('toUser' => $windid, 'subject' => "Welcome To[{$db_bbsname}]!", 'content' => $rg_welcomemsg );
		pwSendMsg ( $messageinfo );
	}
	
	// 发送邮件
	@include_once (D_P . 'data/bbscache/mail_config.php');
	if ($rg_emailcheck) {
		$verifyhash = GetVerify ();
		$rg_yz = md5 ( $rg_yz . substr ( md5 ( $db_sitehash ), 0, 5 ) . substr ( md5 ( $regname ), 0, 5 ) );
		require_once (R_P . 'require/sendemail.php');
		$sendinfo = sendemail ( $regemail, 'email_check_subject', 'email_check_content', 'email_additional' );
		if ($sendinfo === true) {
			ObHeader ( "$db_registerfile?step=finish&email=$regemail&verify=$verifyhash" );
		} else {
			wap_msg ( is_string ( $sendinfo ) ? $sendinfo : 'reg_email_fail', $basename );
		}
	} elseif ($rg_regsendemail && $ml_mailifopen) {
		require_once (R_P . 'require/sendemail.php');
		sendemail ( $regemail, 'email_welcome_subject', 'email_welcome_content', 'email_additional' );
	}
	// 发送结束
	if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
		$action = 'login';
		$jumpurl = $forward ? $forward : $db_ppturls;
		empty ( $forward ) && $forward = $db_bbsurl;
		require_once (R_P . 'require/passport_server.php');
	}
	$verifyhash = GetVerify ( $winduid );
	wap_msg ( 'reg_success', 'index.php' );
	exit ();
}
$returnUrl = getReturnUrl();
wap_header ();
require_once PrintWAP ( 'register' );
wap_footer ();
?>
