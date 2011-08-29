<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class CertificateUpload extends uploadBehavior {
	var $uid;
	var $db;
	var $key;
	var $ifthumb;
	
	function CertificateUpload($uid) {
		global $db,$db_imgsize;
		parent::uploadBehavior();
		
		$this->uid = $uid;
		$this->db =& $db;
		!$db_imgsize && $db_imgsize = 1000;
		$this->ftype = array(
			'gif'  => $db_imgsize,				'jpg'  => $db_imgsize,
			'jpeg' => $db_imgsize,				'bmp'  => $db_imgsize,
			'png'  => $db_imgsize
		);
	}
	
	function allowType($key) {
		if(in_array($key,array('certificateattach_1','certificateattach_2'))){
			$this->key = $key;
			return true; 
		}
		return false;
	}
	
	function update($uploaddb) {
		$attaches = array();
		foreach ($uploaddb as $v) {
			$attaches['attach'.$v['id']] = $v['fileuploadurl'];
		}
		$attaches && $this->db->pw_update(
			"SELECT * FROM pw_auth_certificate WHERE uid=".S::sqlEscape($this->uid),
			"UPDATE pw_auth_certificate SET ".S::sqlSingle($attaches).' WHERE uid='. $this->uid,
			"INSERT INTO pw_auth_certificate SET ".S::sqlSingle($attaches).',state=0,uid='.$this->uid
		);
	}
	
	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = $this->uid . "_{$this->key}." . $currUpload['ext'];
		$savedir = 'certificate/' . str_pad(substr($this->uid,-2),2,'0',STR_PAD_LEFT) . '/';
		
		return array($filename, $savedir);
	}
}