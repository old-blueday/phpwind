<?php
!defined('P_W') && exit('Forbidden');


/**
 * saveAttach
 * 
 * @package upload
 */
class saveAttach {
	
	var $upload = array();
	var $bhv;
	
	function saveAttach($bhv) {
		$this->bhv = $bhv;
	}

	function getInstance($uid, $aid, $num) {
		$saveAttach = null;
		L::loadClass('photo', 'colony', false);
		$photoService = new PW_Photo($uid, 0, 1, 0);
		$albumInfo = $photoService->getAlbumInfo($aid);
		$photonums = $albumInfo['photonum'];
		$GLOBALS += L::config(null, 'o_config');
		if ($albumInfo && (!$GLOBALS['o_maxphotonum'] || ($albumInfo['photonum'] + $num <= $GLOBALS['o_maxphotonum']))) {
			L::loadClass('photoupload', 'upload', false);
			$saveAttach = new saveAttach(new PhotoUpload($aid));
		}
		return $saveAttach;
	}

	function add($attach) {
		$upload = array(
			'attname' => 'attachment',
			'id' => intval($attach['aid']),
			'name' => $attach['name'],
			'size' => $attach['size'],
			'type' => 'zip',
			'ifthumb' => 0,
			'fileuploadurl' => ''
		);
		$upload['ext'] = strtolower(substr(strrchr($attach['attachurl'], '.'), 1));

		if (empty($upload['ext']) || !isset($this->bhv->ftype[$upload['ext']])) {
			return false;
		}
		if ($upload['size'] < 1 || $upload['size'] > $this->bhv->ftype[$upload['ext']] * 1024) {
			return false;
		}
		$dir = dirname($attach['attachurl']);
		$dir && $dir .= '/';
		$srcfile = PwUpload::savePath($this->bhv->ifftp, basename($attach['attachurl']), $dir);

		if (!file_exists($srcfile)) {
			if ($this->bhv->ifftp) {
				$ftp =& PwUpload::getFtpObj();
				PwUpload::createFolder(dirname($srcfile));
				if (!$ftp->get($srcfile, $attach['attachurl'])) return false;
			} else {
				return false;
			}
		}
		list($filename, $savedir) = $this->bhv->getFilePath($upload);
		$source = PwUpload::savePath($this->bhv->ifftp, $filename, $savedir);
		
		PwUpload::createFolder(dirname($source));
		if (!copy($srcfile, $source)) {
			return false;
		}
		$upload['fileuploadurl'] = $savedir . $filename;
		PwUpload::operateAttach($source, $filename, $savedir, $upload, $this->bhv);

		$this->upload[] = $upload;
		return true;
	}

	function execute() {
		$this->bhv->update($this->upload);
	}
}
?>