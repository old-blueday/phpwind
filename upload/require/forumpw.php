<?php
!function_exists('readover') && exit('Forbidden');

if(!$_POST['wind_action']){
	global $printmsgpwd,$tplpath;
	$tplpath='wind';
	Showmsg('forumpw_needpwd','forumpwd');
} else{
	if($forum['password']==md5($_POST['wind_password']) && $groupid!='guest'){
		/**
		* 不同版块不同密码
		*/
		Cookie("pwdcheck[$fid]",$forum['password']);
	} elseif($groupid=='guest'){
		Showmsg('forumpw_guest');
	} else{
		Showmsg('forumpw_pwd_error');
	}
}
?>