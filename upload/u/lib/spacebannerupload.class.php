<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class spaceBannerUpload extends uploadBehavior {
	
	var $uid;
	var $attachs;

	function spaceBannerUpload($uid) {
		parent::uploadBehavior();
		$this->uid = $uid;
		$size = 1024 * 2048;

		$this->ftype = array(
			'gif'  => $size,		'jpg'  => $size,
			'jpeg' => $size,		'bmp'  => $size,
			'png'  => $size
		);
	}

	function allowThumb() {
		return false;
	}

	function getThumbSize() {
		return "110\t110";
	}

	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		$savedir = 'space/';
		$filename = "{$this->uid}." . $currUpload['ext'];
		return array($filename, $savedir, $filename, $savedir);
	}

	function update($uploaddb) {
		$this->attachs = $uploaddb;
	}

	function getImgUrl() {
		return $this->attachs ? $this->attachs[0]['fileuploadurl'] : null;
	}
}
?>