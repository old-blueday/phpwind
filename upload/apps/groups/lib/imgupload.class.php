<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class CnimgUpload extends uploadBehavior {
	
	var $cyid;
	var $attachs;

	function CnimgUpload($cyid) {
		global $o_imgsize,$db_uploadfiletype;
		parent::uploadBehavior();
		$this->cyid = $cyid;
		$o_imgsize  = 2048;

		if ($o_imgsize) {
			$this->ftype = array(
				'gif'  => $o_imgsize,				'jpg'  => $o_imgsize,
				'jpeg' => $o_imgsize,				'bmp'  => $o_imgsize,
				'png'  => $o_imgsize
			);
		} else {
			$filetype = (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype));
			$this->ftype = array(
				'gif'  => $filetype['gif'],			'jpg'  => $filetype['jpg'],
				'jpeg' => $filetype['jpeg'],		'bmp'  => $filetype['bmp'],
				'png'  => $filetype['png']
			);
		}
	}

	function allowThumb() {
		return true;
	}

	function getThumbSize() {
		return "110\t110";
	}

	function allowType($key) {
		return $key == 'cnimg_1';
	}

	function getFilePath($currUpload) {
		$savedir = 'cn_img/';
		$filename = "colony_$this->cyid." . $currUpload['ext'];
		return array($filename, $savedir, $filename, $savedir);
	}

	function update($uploaddb) {
		foreach ($uploaddb as $key => $value) {
			if ($value['id'] == '1') {
				$this->attachs = $value;
			}
		}
	}

	function getImgUrl() {
		return $this->attachs ? $this->attachs['fileuploadurl'] : null;
	}
}

class BannerUpload extends uploadBehavior {
	
	var $cyid;
	var $attachs;

	function BannerUpload($cyid) {
		parent::uploadBehavior();
		$this->cyid = $cyid;
		$o_imgsize  = 2048;

		$this->ftype = array(
			'gif'  => $o_imgsize,				'jpg'  => $o_imgsize,
			'jpeg' => $o_imgsize,				'bmp'  => $o_imgsize,
			'png'  => $o_imgsize
		);
	}

	function allowType($key) {
		return $key == 'cnimg_2';
	}

	function getFilePath($currUpload) {
		$savedir = 'cn_img/';
		$filename = "banner_$this->cyid." . $currUpload['ext'];
		return array($filename, $savedir, '', '');
	}

	function update($uploaddb) {
		foreach ($uploaddb as $key => $value) {
			if ($value['id'] == '2') {
				$this->attachs = $value;
			}
		}
	}

	function getImgUrl() {
		return $this->attachs ? $this->attachs['fileuploadurl'] : null;
	}
}
?>