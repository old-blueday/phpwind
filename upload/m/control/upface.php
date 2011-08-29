<?php
!defined('W_P') && exit('Forbidden');
! $winduid && wap_msg ( 'not_login' );

InitGP(array('step'),'GP');
list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize) = explode("\t",$db_upload);

if(!$db_upload){
	wap_msg('pro_loadimg_close','index.php?a=myhome');
}

if(!$_G['upload']) wap_msg('您没有上传头像的权限','index.php?a=myhome');

if ($step == '2') {
	InitGP(array('uid'));
	require_once(R_P . 'lib/upload/faceupload.class.php');
	$ext = strtolower(substr(strrchr($_FILES['attachment_']['name'],'.'),1));
	if (!in_array($ext, array('gif','jpg','jpeg','png'))) {
		wap_msg('illegal_loadimg',$basename);
	}
	
	$face = new FaceUpload($winduid);
	if ($_FILES['attachment_']['size'] < 1 || $_FILES['attachment_']['size'] > (int)$db_imgsize*1024) {
		wap_msg('pro_loadimg_limit',$basename);
	}
	
	PwUpload::upload($face);
	$uploaddb = $face->getAttachs();
	
	require_once(R_P . 'lib/upload.class.php');
	$udir = str_pad(substr($winduid,-2),2,'0',STR_PAD_LEFT);
	$source = PwUpload::savePath(0, "{$winduid}_tmp.$ext", "upload/$udir/");
	if (!file_exists($source)) {
		wap_msg('undefined_action',$basename);
	}
	$data = file_get_contents($source);
	if ($data) {
		InitGP(array('from'));
		require_once(R_P . 'require/showimg.php');
		$filename  = "{$winduid}.jpg";
		//$normalDir = "upload/$udir/";
		$middleDir = "upload/middle/$udir/";
		$smallDir  = "upload/small/$udir/";
		$img_w = $img_h = 0;
		
		$middleFile = PwUpload::savePath($db_ifftp, $filename, $middleDir);
		PwUpload::createFolder(dirname($middleFile));
		writeover($middleFile, $data);
		MakeThumb($middleFile, $middleFile, 128, 128);

		require_once(R_P.'require/imgfunc.php');
		if (!$img_size = GetImgSize($middleFile,'jpg')) {
			P_unlink($middleFile);
			wap_msg('upload_content_error',$basename);
		}

		list($img_w, $img_h) = getimagesize($normalFile);

		$smallFile = PwUpload::savePath($db_ifftp, $filename, $smallDir);
		$s_ifthumb = 0;
		PwUpload::createFolder(dirname($smallFile));
		if (MakeThumb($middleFile, $smallFile, 48, 48)) {
			$s_ifthumb = 1;
		}
		if ($db_ifftp) {
			PwUpload::movetoftp($middleFile, $middleDir . $filename);
			$s_ifthumb && PwUpload::movetoftp($smallFile, $smallDir . $filename);
		}
		pwFtpClose($GLOBALS['ftp']);

		$user_a = explode('|',$winddb['icon']);
		$user_a[2] = $img_w;
		$user_a[3] = $img_h;
		$usericon = setIcon("$udir/{$winduid}.$ext", 3, $user_a);
		$db->update("UPDATE pw_members SET icon=" . pwEscape($usericon,false) . " WHERE uid=" . pwEscape($winduid));
		wap_msg("operate_success","index.php?a=myhome&t=".$timestamp);
	}else{
		wap_msg('undefined_action',$basename);
	}
}
wap_header ();
require_once PrintWAP ( 'upface' );
wap_footer ();
?>