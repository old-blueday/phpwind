<?php
!function_exists('readover') && exit('Forbidden');

$attachs = $aids = $elementpic = array();
$ifupload = 0;

foreach ($_FILES as $key => $val) {
	if (!$val['tmp_name'] || $val['tmp_name'] == 'none') {
		unset($_FILES[$key]);
	}
}
$filenum = count($_FILES);

if ($filenum > 0 && $filenum <= $db_attachnum) {
	if (!$db_allowupload) {
		Showmsg('upload_close');
	}
	if ($winddb['uploadtime'] < $tdtime) {
		$winddb['uploadnum'] = 0;
	}
	if ($_G['allownum'] > 0 && ($winddb['uploadnum'] + $filenum) >= $_G['allownum']) {
		Showmsg('upload_num_error');
	}
	$uploaddb = UploadDiary($winduid);

	foreach ($uploaddb as $value) {
		$value['name'] = addslashes($value['name']);

		if (!$value['ifreplace']) {
			S::gp(array('atc_desc'.$value['id']),'P',1);
			$value['descrip']	= ${'atc_desc'.$value['id']};
			$value['needrvrc'] = $value['special'] = 0;
			$value['ctype'] = '';

			$db->update("INSERT INTO pw_attachs SET ".S::sqlSingle(array(
				'fid'		=> $fid,				'uid'		=> $winduid,
				'hits'		=> 0,					'name'		=> $value['name'],
				'type'		=> $value['type'],		'size'		=> $value['size'],
				'attachurl'	=> $value['attachurl'],	'needrvrc'	=> $value['needrvrc'],
				'special'	=> $value['special'],	'ctype'		=> $value['ctype'],
				'uploadtime'=> $timestamp,			'descrip'	=> $value['descrip'],
				'ifthumb'	=> $value['ifthumb']
			)));
			$aid = $db->insert_id();
			$attachs[$aid] = array(
				'aid'       => $aid,
				'name'      => stripslashes($value['name']),
				'type'      => $value['type'],
				'attachurl' => $value['attachurl'],
				'needrvrc'  => $value['needrvrc'],
				'special'	=> $value['special'],
				'ctype'		=> $value['ctype'],
				'size'      => $value['size'],
				'hits'      => 0,
				'desc'		=> str_replace('\\','',$value['descrip']),
				'ifthumb'	=> $value['ifthumb']
			);
			$atc_content = str_replace("[upload=$value[id]]","[attachment=$aid]",$atc_content);
		} else {
			$value['needrvrc']	= 0;
			$value['special']	= 0;
			$value['ctype']		= 0;
			$value['descrip']	= $replacedb[$value['id']]['desc'];
			$aid = $replacedb[$value['id']]['aid'];
			$db->update("UPDATE pw_attachs SET ".S::sqlSingle(array(
				'name'		=> $value['name'],			'type'		=> $value['type'],
				'size'		=> $value['size'],			'attachurl'	=> $value['attachurl'],
				'needrvrc'	=> $value['needrvrc'],		'special'	=> $value['special'],
				'ctype'		=> $value['ctype'],			'uploadtime'=> $timestamp,
				'descrip'	=> $value['descrip'],		'ifthumb'	=> $value['ifthumb']
			)) . " WHERE aid=".S::sqlEscape($aid));
			$oldattach[$aid]['name'] = $value['name'];
			$oldattach[$aid]['type'] = $value['type'];
			$oldattach[$aid]['size'] = $value['size'];
			$oldattach[$aid]['ifthumb'] = $value['ifthumb'];
		}
	}
}
unset($_FILES);

foreach ($attachs as $key => $value) {
	$aids[] = $key;
}
$aids && $aids = S::sqlImplode($aids);
require_once(R_P.'require/functions.php');
pwFtpClose($ftp);


function UploadDiary($uid,$uptype = 'all',$thumbs = null){
	global $ifupload,$db_attachnum,$o_uploadsize,$a,$did,$replacedb,$winddb,$_G,$tdtime,$timestamp,$o_attachdir,$attachdir,$db_watermark,$db_waterwidth,$db_waterheight,$db_ifgif,$db_waterimg,$db_waterpos,$db_watertext,$db_waterfont,$db_watercolor,$db_waterpct,$db_jpgquality,$db_ifathumb,$db_iffthumb,$db_athumbsize,$db_fthumbsize,$atc_attachment_name,$attach_ext,$savedir;
	$uploaddb = array();
	foreach ($_FILES as $key => $value) {
		if (if_uploaded_file($value['tmp_name'])) {
			list($t,$i) = explode('_',$key);
			$i = (int)$i;
			$atc_attachment = $value['tmp_name'];
			$atc_attachment_name = S::escapeChar($value['name']);
			$atc_attachment_size = $value['size'];
			$attach_ext = strtolower(substr(strrchr($atc_attachment_name,'.'),1));

			if (empty($attach_ext) || !isset($o_uploadsize[$attach_ext])) {
				uploadmsg($uptype,'upload_type_error');
			}

			if ((int)$atc_attachment_size < 1) {
				uploadmsg($uptype,'upload_size_0');
			}
			if ($o_uploadsize[$attach_ext] && $atc_attachment_size > $o_uploadsize[$attach_ext]*1024) {
				$GLOBALS['oversize'] = $o_uploadsize[$attach_ext];
				uploadmsg($uptype,'upload_size_error');
			}
			if ($a == 'edit' && $t == 'replace' && isset($replacedb[$i])) {
				$ifreplace = 1;
				$fileuplodeurl = $replacedb[$i]['attachurl'];
				$tmpurl = strrchr($fileuplodeurl,'/');
				$tmpname = $uptype.'_'.($tmpurl ? substr($tmpurl,1) : $fileuplodeurl);
			} else {
				$ifreplace = 0;
				$attach_ext = preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $attach_ext);
				$winddb['uploadtime'] = $timestamp;
				$winddb['uploadnum']++;
				$prename = substr(md5($timestamp.$i.randstr(8)),10,15);
				$tmpname = $uptype."_$prename.$attach_ext";
				$fileuplodeurl = $uid."_{$did}_$prename.$attach_ext";
				if ($o_attachdir) {
					if ($o_attachdir == 1) {
						$savedir = "Type_$attach_ext";
					} elseif ($o_attachdir == 2) {
						$savedir = 'Mon_'.date('ym');
					} elseif ($o_attachdir == 3) {
						$savedir = 'Day_'.date('ymd');
					}
					$fileuplodeurl = $savedir.'/'.$fileuplodeurl;
				}
			}
			$thumbdir = "thumb/diary/$fileuplodeurl";

			$havefile = $ifthumb = 0;

			$source = "$attachdir/diary/$fileuplodeurl";

			if (!postupload($atc_attachment,$source)) {
				uploadmsg($uptype,'upload_error');
			}
			$ifupload = 3;
			$img_size[0] = $img_size[1] = 0;
			$size = ceil(filesize($source)/1024);

			if (in_array($attach_ext,array('gif','jpg','jpeg','png','bmp'))) {
				require_once(R_P.'require/imgfunc.php');
				if (!$img_size = GetImgSize($source,$attach_ext)) {
					P_unlink($source);
					uploadmsg($uptype,'upload_content_error');
				}
				$ifupload = 1;
				$img_size[0] = $img_size['width'];
				$img_size[1] = $img_size['height'];
				unset($img_size['width'],$img_size['height']);
				$type = 'img';
				if ($db_ifathumb) {
					$thumburl = $havefile ? D_P."data/tmp/thumb_$tmpname" : "$attachdir/$thumbdir";
					list($db_thumbw,$db_thumbh) = explode("\t",$db_athumbsize);
					list($cenTer,$sameFile) = explode("\t",$thumbs);
					createFolder(dirname($thumburl));
					if ($thumbsize = MakeThumb($source,$thumburl,$db_thumbw,$db_thumbh,$cenTer,$sameFile)) {
						$img_size[0] = $thumbsize[0];
						$img_size[1] = $thumbsize[1];
						$source != $thumburl && $ifthumb = 1;
					}
				}

				if ($uptype == 'all' && $db_watermark && $img_size[2]<'4' && $img_size[0]>$db_waterwidth && $img_size[1]>$db_waterheight && function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($attach_ext!='gif' || function_exists('imagegif') && ($db_ifgif==2 || $db_ifgif==1 && (PHP_VERSION > '4.4.2' && PHP_VERSION < '5' || PHP_VERSION > '5.1.4'))) && ($db_waterimg && function_exists('imagecopymerge') || !$db_waterimg && function_exists('imagettfbbox'))) {
					ImgWaterMark($source,$db_waterpos,$db_waterimg,$db_watertext,$db_waterfont,$db_watercolor, $db_waterpct,$db_jpgquality);
					if ($ifthumb == 1) {
						ImgWaterMark($thumburl,$db_waterpos,$db_waterimg,$db_watertext,$db_waterfont,$db_watercolor, $db_waterpct,$db_jpgquality);
					}
				}
			}

			if ($havefile) {
				P_unlink("$attachdir/diary/$fileuplodeurl");
				@rename($source,"$attachdir/diary/$fileuplodeurl");
				if ($ifthumb == 1) {
					P_unlink("$attachdir/$thumbdir");
					@rename($thumburl,"$attachdir/$thumbdir");
				}

				if ($m_ifthumb == 1) {//TODO $m_ifthumb?
					P_unlink("$attachdir/$m_thumbdir/diary");//TODO $m_thumbdir?
					@rename($m_thumburl,"$attachdir/$m_thumbdir/diary");//TODO $m_thumburl?
				}
				if ($s_ifthumb == 1) {//TODO $m_ifthumb?
					P_unlink("$attachdir/$s_thumbdir/diary");//TODO $s_thumbdir?
					@rename($s_thumburl,"$attachdir/$s_thumbdir/diary");//TODO $s_thumburl?
				}
			}
			$uploaddb[] = array('id' => $i,'ifreplace' => $ifreplace,'name' => $atc_attachment_name,'size' => $size,'type' => $type,'attachurl' => $fileuplodeurl,'ifthumb' => $ifthumb,'img_w' => $img_size[0],'img_h' => $img_size[1]);
		}
	}
	return $uploaddb;
}
?>