<?php
!function_exists('readover') && exit('Forbidden');
define('AVATARS_PER_PAGE',12);
if (!$step){
	S::gp(array('facetype'),'G');
	//获取图像信息
	list($iconurl,$icontype,$iconwidth,$iconheight,$iconfile,,,$iconsize) = showfacedesign($userdb['icon'], true,'m');
	//系统头像
	$next_page = $pre_page = $page = 1;
	$img = @opendir("$imgdir/face");
	while ($imgname = @readdir($img)) {
		if ($imgname!='.' && $imgname!='..' && $imgname!='' && preg_match('/\.(gif|jpg|png|bmp)$/i',$imgname)) {
			$num++;
			if ($num > AVATARS_PER_PAGE){
				$next_page = 2;
				break;
			} 
			if ($imgname == 'none.gif') continue;
			$imgname_array[] = $imgname;
		}
	}
	
	@closedir($img);
	
	//flash头像上传参数
	if ($db_ifupload && $_G['upload']) {
		list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize) = explode("\t",$db_upload);
		$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
		$swfhash = GetVerify($winduid);
		//$upload_param = rawurlencode($db_bbsurl.'/job.php?action=uploadicon&verify='.$swfhash.'&uid='.$winduid.'&');
		$save_param = rawurlencode($db_bbsurl.'/job.php?action=uploadicon&step=2&');
		$default_pic = rawurlencode("$db_picpath/facebg.jpg");
		//$icon_encode_url = 'up='.$upload_param.'&saveFace='.$save_param.'&url='.$default_pic.'&PHPSESSID='.$sid.'&'.'imgsize='.$db_imgsize.'&';
		$icon_encode_url = 'saveFace='.$save_param.'&url='.$default_pic.'&imgsize='.$db_imgsize.'&';
	} else {
		$icon_encode_url = '';
	}
	
	if ($icontype == 2) {
		$httpurl = $iconurl;
	}
	if ($icontype != 1) {
		$iconfile = '';
	}
	require_once uTemplate::PrintEot('info_face');
	pwOutPut();
}else if( $step  == '2' ) {
	PostCheck();
	S::slashes($userdb);
	S::gp(array('facetype', 'proicon'),'P');

	require_once(R_P.'require/showimg.php');
	$user_a = explode('|',$winddb['icon']);
	$usericon = '';
	if ($facetype == 1) {
		$usericon = setIcon($proicon, $facetype, $user_a);
	} elseif ($_G['allowportait'] && $facetype == 2) {
		$httpurl = S::getGP('httpurl','P');
		if (strncmp($httpurl[0],'http://',7) != 0 || strrpos($httpurl[0],'|') !== false) {
			refreshto("profile.php?action=modify&info_type=$info_type&facetype=$facetype",getLangInfo('msg','illegal_customimg'),2,true);
		}
		$proicon = S::escapeChar($httpurl[0]);
		$httpurl[1] = (int)$httpurl[1];
		$httpurl[2] = (int)$httpurl[2];
		$httpurl[3] = (int)$httpurl[3];
		$httpurl[4] = (int)$httpurl[4];
		list($user_a[2], $user_a[3]) = flexlen($httpurl[1], $httpurl[2], $httpurl[3], $httpurl[4]);
		$usericon = setIcon($proicon, $facetype, $user_a);
		unset($httpurl);
	}
	pwFtpClose($ftp);

	//update member
	$usericon && $result = $userService->update($winduid, array('icon'=>$usericon));
	// defend start
	CloudWind::yunUserDefend('editprofile', $winduid, $windid, $timestamp, 0, (($result === true) ? 101 : 102),'','','',array('profile'=>'icon'));
	// defend end
	//* $_cache = getDatastore();
	//* $_cache->delete('UID_'.$winduid);
	//job sign
	initJob($winduid,"doUpdatedata");
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}