<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class PushUpload extends uploadBehavior {
	var $db;
	var $invokePieceId;
	var $atype;
	var $attachs;

	function PushUpload($invokePieceId) {
		global $db;
		parent::uploadBehavior();
		$this->db =& $db;
		$this->invokePieceId = (int) $invokePieceId;
		
		$o_maxfilesize = 2000;

		$this->ftype = array(
			'gif'  => $o_maxfilesize,				'jpg'  => $o_maxfilesize,
			'jpeg' => $o_maxfilesize,				'bmp'  => $o_maxfilesize,
			'png'  => $o_maxfilesize
		);
	}

	function allowType($key) {
		return true;
	}

	function allowThumb() {
		return false;
	}

	function getFilePath($currUpload) {
		global $timestamp,$o_mkdir;
		$filename	= date("YmdHis", time()).'.'. $currUpload['ext'];
		$savedir	= 'pushpic/';
		
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		global $windid,$timestamp;
		
		$pushPicDB = $this->_getPushPicDB();
		foreach ($uploaddb as $key => $value) {
			$fieldData = array (
				'invokepieceid'	=> $this->invokePieceId,
				'path' => $value['fileuploadurl'],
				'creator' => $windid,
				'createtime'=> $timestamp,
			);
			$pushPicDB->add($fieldData);
			$this->fileName = $value['fileuploadurl'];
			$this->attachs[] = $fieldData;
		}
		return true;
	}
	
	function getImagePath() {
		$imagePath = geturl($this->fileName);
		if ($imagePath[0]) return $imagePath[0];
		return '';
	}

	function getAttachs() {
		return $this->attachs;
	}

	function getNewID() {
		return $this->pid;
	}

	function _getPushPicDB() {
		return L::loadDB('PushPic', 'area');
	}
}