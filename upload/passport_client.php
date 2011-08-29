<?php
require_once('global.php');
require_once(R_P.'require/checkpass.php');

S::gp(array('action','forward','verify'));
S::gp(array('userdb'),'GP',0);
if(!$db_pptifopen || $db_ppttype!='client'){
	Showmsg('passport_close');
}
if(empty($db_pptkey) || md5($action.$userdb.$forward.$db_pptkey) != $verify){
	Showmsg('passport_safe');
}
$_db_hash=$db_hash;

$db_hash=$db_pptkey;
parse_str(StrCode($userdb,'DECODE'),$userdb);

if($action=='login'){
	$userdb = S::escapeChar($userdb);
	if(!$userdb['time'] || !$userdb['username'] || !$userdb['password']){
		Showmsg('passport_data');
	}
	if($timestamp-$userdb['time']>3600){
		Showmsg('passport_error');
	}

	$member_field = array('username','password','email');
	$memberdata_field = array('rvrc','money','credit','currency');
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->getByUserName($userdb['username']);
	if($rt){
		$sql1 = $sql2 = array();
		foreach($userdb as $key=>$val){
			if(in_array($key,$member_field) && $rt[$key] != $val){
				$sql1[$key] = $val;
			}elseif(in_array($key,$memberdata_field) && strpos(",$db_pptcredit,",",$key,")!==false){
				$sql2[$key] = $val;
			}
		}
		$userService->update($rt['uid'], $sql1, $sql2);
		$winduid = $rt['uid'];
	} else{
		$sql1 = $sql2 = array();
		foreach($userdb as $key=>$val){
			if(in_array($key,$member_field)){
				$sql1[$key] = $val;
			}elseif(in_array($key,$memberdata_field) && strpos(",$db_pptcredit,",",$key,")!==false){
				$sql2[$key] = (int)$val;
			}
		}
		$sql1 += array(
			'groupid'	=> -1,
			'memberid'	=> 8,
			'gender'	=> 0,
			'regdate'	=> $timestamp
		);
		/**
		$db->update("REPLACE INTO pw_members SET".S::sqlSingle($sql1));
		**/
		pwQuery::replace('pw_members', $sql1);
		$winduid = $db->insert_id();
		$sql2 += array(
			'uid'		=> $winduid,
			'postnum'	=> 0,
			'lastvisit'	=> $timestamp,
			'thisvisit'	=> $timestamp,
			'onlineip'	=> $onlineip
		);
		/**
		$db->update("REPLACE INTO pw_memberdata SET".S::sqlSingle($sql2));
		**/
		pwQuery::replace('pw_memberdata', $sql2);
		//* $db->update("UPDATE pw_bbsinfo SET newmember=".S::sqlEscape($userdb['username']).",totalmember=totalmember+1 WHERE id='1'");
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET newmember=:newmember,totalmember=totalmember+1 WHERE id=:id", array('pw_bbsinfo', $userdb['username'], 1)));
	}
	$db_hash=$_db_hash;
	$windpwd = PwdCode($userdb['password']);
	Cookie("winduser",StrCode($winduid."\t".$windpwd),$userdb['cktime']);
	Cookie('lastvisit','',0);
	Loginipwrite();
	ObHeader($forward ? $forward : $db_bbsurl);
} elseif($action=='quit'){
	$db_hash=$_db_hash;
	Loginout();
	ObHeader($forward ? $forward : $db_bbsurl);
}
?>