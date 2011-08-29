<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class CsvUpload extends uploadBehavior {
	
	var $db;
	var $uid;
	var $ifftp;
	var $pathname;
	
	function CsvUpload($uid) {
		global $db,$db_ifftp;
		parent::uploadBehavior();
		$maxfilesize = 4000;
		$this->db =& $db;
		$this->uid = $uid;
		$this->ifftp = $db_ifftp;
		$this->ftype = array(
			'csv'  => $maxfilesize
		);

	}
	
	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		$filename	= $this->uid.'_'.'foxmail.'. $currUpload['ext'];
		$savedir	= 'csv/';
		$this->pathname = $this->getServerPath($filename,$savedir);
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		return true;
	}
	
	function getServerPath($filename, $dir) {
		global $attachdir;
		if ($this->ifftp) {
			$source = D_P . "data/tmp/{$filename}";
		} else {
			$source = $attachdir . '/' . $dir . $filename;
		}
		return $source;
	}
}
?>