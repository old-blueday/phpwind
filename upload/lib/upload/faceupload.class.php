<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class FaceUpload extends uploadBehavior {
	
	var $db;
	var $uid;
	var $attachs;

	function FaceUpload($uid) {
		global $db,$db_imgsize;
		parent::uploadBehavior();
		$this->uid = (int)$uid;
		$this->db =& $db;
		$this->ifftp = 0;
		
		!$db_imgsize && $db_imgsize = 1000;
		$this->ftype = array(
			'gif'  => $db_imgsize,				'jpg'  => $db_imgsize,
			'jpeg' => $db_imgsize,				'bmp'  => $db_imgsize,
			'png'  => $db_imgsize
		);
	}

	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		$filename = $this->uid . '_tmp.' . $currUpload['ext'];
		$savedir = 'upload/' . str_pad(substr($this->uid,-2),2,'0',STR_PAD_LEFT) . '/';
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		$this->attachs = $uploaddb;
		return true;
	}

	function getAttachs() {
		return current($this->attachs);
	}
}
?>