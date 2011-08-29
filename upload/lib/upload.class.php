<?php
!defined('P_W') && exit('Forbidden');

/**
 * Uploader
 *
 * @package Upload
 */
class PwUpload {

	function initCurrUpload($key, $value) {
		list($t, $i) = explode('_', $key);
		$arr = array(
			'id' => intval($i),
			'attname' => $t,
			'name' => S::escapeChar($value['name']),
			'size' => intval($value['size']),
			'type' => 'zip',
			'ifthumb' => 0,
			'fileuploadurl' => ''
		);
		$arr['ext'] = strtolower(substr(strrchr($arr['name'], '.'), 1));
		return $arr;
	}

	function upload(&$bhv,$ifUpdate = 1) {

		$uploaddb = array();
		foreach ($_FILES as $key => $value) {
			if ($value['error'] == 1) {
				$maxuploadsize = ini_get('upload_max_filesize');
				showUploadMsg('上传的附件超过服务器上传的最大限制'.$maxuploadsize.'！');
			}
			if (!PwUpload::if_uploaded_file($value['tmp_name']) || !$bhv->allowType($key)) {
				continue;
			}
			$atc_attachment = $value['tmp_name'];
			$upload = PwUpload::initCurrUpload($key, $value);

			if (empty($upload['ext']) || !isset($bhv->ftype[$upload['ext']])) {
				showUploadMsg('upload_type_error');
			}
			if ($upload['size'] < 1 || $upload['size'] > $bhv->ftype[$upload['ext']] * 1024) {
				$GLOBALS['atc_attachment_name'] = $upload['name'];
				$GLOBALS['oversize'] = $bhv->ftype[$upload['ext']];
				showUploadMsg($upload['size'] < 1 ? 'upload_size_0' : 'upload_size_error');
			}
			list($filename, $savedir) = $bhv->getFilePath($upload);
			$upload['fileuploadurl'] = $savedir . $filename;

			$source = PwUpload::savePath($bhv->ifftp, $filename, $savedir);

			if (!PwUpload::postupload($atc_attachment, $source)) {
				showUploadMsg('upload_error');
			}
			$upload['size'] = ceil(filesize($source) / 1024);

			if (in_array($upload['ext'], array('gif','jpg','jpeg','png','bmp','swf'))) {
				require_once (R_P . 'require/imgfunc.php');
				if (!$img_size = GetImgSize($source, $upload['ext'])) {
					P_unlink($source);
					showUploadMsg('upload_content_error');
				}
				if ($upload['ext'] != 'swf') {
					if ($bhv->allowThumb() && ($upload['ext'] != 'gif' || $GLOBALS['db_ifathumbgif'])) {
						$thumbInfo = PwUpload::makeThumb($source, $bhv->getThumbInfo($filename, $savedir), $bhv->ifftp, $upload['ifthumb']);
					}
					$bhv->allowWaterMark() && PwUpload::waterMark($source, $upload['ext'], $img_size);
					$upload['type'] = 'img';
				}
			} elseif ($upload['ext'] == 'txt') {
				if (preg_match('/(onload|submit|post|form)/i', readover($source))) {
					P_unlink($source);
					showUploadMsg('upload_content_error');
				}
				$upload['type'] = 'txt';
			}
			if ($bhv->ifftp) {
				PwUpload::movetoftp($source, $upload['fileuploadurl']);
			}
			if ($upload['ifthumb']) {
				PwUpload::operateThumb($thumbInfo, $bhv->allowWaterMark(), $bhv->ifftp, $upload['ext']);
			}
			$uploaddb[] = $upload;
		}
		if (!$ifUpdate) return $uploaddb;
		$bhv->update($uploaddb);
	}

	function operateThumb($list, $waterMark, $ifftp, $ext) {
		if (empty($list)) return false;
		foreach ($list as $k => $v) {
			$waterMark && PwUpload::waterMark($v[0], $ext);
			$ifftp && PwUpload::movetoftp($v[0], $v[1]);
		}
		return true;
	}
	/**
	 * @static
	 */
	function makeThumb($source, $thumbInfo, $ifftp, &$ifthumb) {
		$array = array();
		foreach ($thumbInfo as $key => $value) {
			list($thumbw, $thumbh, $cenTer) = explode("\t", $value[2]);
			$thumburl = PwUpload::savePath($ifftp, $value[0], $value[1], 'thumb_' . $key . '_');
			PwUpload::createFolder(dirname($thumburl));
			if (($thumb = MakeThumb($source, $thumburl, $thumbw, $thumbh, $cenTer)) && $source != $thumburl) {
				$ifthumb |= (1 << $key);
				$array[] = array($thumburl, $value[1] . $value[0]);
			}
		}
		return $array;
	}
	/**
	 * @static
	 */
	function waterMark($source, $ext, $imgsize = null) {
		global $db_watermark, $db_waterwidth, $db_waterheight, $db_ifgif, $db_waterimg, $db_waterpos, $db_watertext, $db_waterfont, $db_watercolor, $db_waterpct, $db_jpgquality;

		empty($imgsize) && $imgsize = GetImgSize($source, $ext);
		if (empty($imgsize)) {
			return;
		}
		if ($db_watermark && $imgsize['type'] < 4 && $imgsize['width'] > $db_waterwidth && $imgsize['height'] > $db_waterheight && function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($ext != 'gif' || function_exists('imagegif') && ($db_ifgif == 2 || $db_ifgif == 1 && (PHP_VERSION > '4.4.2' && PHP_VERSION < '5' || PHP_VERSION > '5.1.4'))) && ($db_waterimg && function_exists('imagecopymerge') || !$db_waterimg && function_exists('imagettfbbox'))) {
			ImgWaterMark($source, $db_waterpos, $db_waterimg, $db_watertext, $db_waterfont, $db_watercolor, $db_waterpct, $db_jpgquality);
		}
	}
	/**
	 * @static
	 */
	function getUploadNum() {
		foreach ($_FILES as $key => $val) {
			if (!$val['tmp_name'] || $val['tmp_name'] == 'none') {
				unset($_FILES[$key]);
			}
		}
		return count($_FILES);
	}
	
	/*检查上传是否有错误*/
	function checkUpload(){
		foreach ($_FILES as $k => $v) {
			switch ($v['error']){
				case UPLOAD_ERR_INI_SIZE:
					$maxuploadsize = @ini_get('upload_max_filesize');
					return '上传的附件超过服务器上传的最大限制' . $maxuploadsize;
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					return '附件上传失败,服务器TMP目录设置错误';
					break;
				default:
			}
		}
		return true;
	}
	/**
	 * @static
	 */
	function if_uploaded_file($tmp_name) {
		if (!$tmp_name || $tmp_name == 'none') {
			return false;
		} elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * @static
	 */
	function movetoftp($srcfile, $dstfile) {
		global $ftp;
		require_once (R_P . 'require/functions.php');
		if (pwFtpNew($ftp, true) && $ftp->upload($srcfile, $dstfile)) {
			P_unlink($srcfile);
			return true;
		}
		return false;
	}
	/**
	 * @static
	 */
	function movefile($srcfile, $dstfile) {
		PwUpload::createFolder(dirname($dstfile));
		if (rename($srcfile, $dstfile)) {
			@chmod($dstfile, 0777);
			return true;
		} elseif (@copy($srcfile, $dstfile)) {
			@chmod($dstfile, 0777);
			P_unlink($srcfile);
			return true;
		} elseif (is_readable($srcfile)) {
			pwCache::setData($dstfile, readover($srcfile));
			if (file_exists($dstfile)) {
				@chmod($dstfile, 0777);
				P_unlink($srcfile);
				return true;
			}
		}
		return false;
	}
	/**
	 * @static
	 */
	function postupload($tmp_name, $filename) {
		if (strpos($filename, '..') !== false || strpos($filename, '.php.') !== false || eregi("\.php$", $filename)) {
			exit('illegal file type!');
		}
		PwUpload::createFolder(dirname($filename));
		if (function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
			@chmod($filename, 0777);
			return true;
		} elseif (@copy($tmp_name, $filename)) {
			@chmod($filename, 0777);
			return true;
		} elseif (is_readable($tmp_name)) {
			pwCache::setData($filename, readover($tmp_name));
			if (file_exists($filename)) {
				@chmod($filename, 0777);
				return true;
			}
		}
		return false;
	}
	/**
	 * @static
	 */
	function createFolder($path) {
		if (!is_dir($path)) {
			PwUpload::createFolder(dirname($path));
			@mkdir($path);
			@chmod($path, 0777);
			@fclose(@fopen($path . '/index.html', 'w'));
			@chmod($path . '/index.html', 0777);
		}
	}
	/**
	 * @static
	 */
	function savePath($ifftp, $filename, $dir, $thumb = '') {
		global $attachdir;
		if ($ifftp) {
			$thumb && $filename = $thumb . $filename;
			$source = D_P . "data/tmp/{$filename}";
		} else {
			$source = $attachdir . '/' . $dir . $filename;
		}
		return $source;
	}
}

/**
 * UploadBehavior
 *
 * @package Upload
 */
class uploadBehavior {

	var $ftype;
	var $ifftp;

	function uploadBehavior() {
		global $db_ifftp;
		$this->ifftp = & $db_ifftp;
		$this->ftype = array();
	}

	function allowThumb() {
		return false;
	}

	function allowWaterMark() {
		return false;
	}
	
	/**
	 * @abstract 配置生成缩略图策略
	 * @retrun array(
	 *		array($filename_1, $dir_1, $thumbsize_1),
	 *		array($filename_2, $dir_2, $thumbsize_2),
	 *		...
	 *	)
	 */
	function getThumbInfo() {
		return array();
	}
	/**
	 * @abstract
	 */
	function allowType($key) {
	}
	/**
	 * @abstract
	 */
	function getFilePath($currUpload) {
	}
	/**
	 * @abstract
	 */
	function update($uploaddb) {
	}
}

function showUploadMsg($msg) {
	Showmsg($msg);
}
?>