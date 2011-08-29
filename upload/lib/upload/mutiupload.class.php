<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class MutiUpload extends uploadBehavior {
	
	var $db;
	var $uid;
	var $attachs;

	function MutiUpload($uid) {
		global $db,$db_uploadfiletype;
		parent::uploadBehavior();
		$this->uid = (int)$uid;
		
		$this->db =& $db;
		$this->ftype =& $db_uploadfiletype;
		$this->ifftp = 0;
	}

	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$savedir = 'mutiupload/';
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = "0_{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		global $db_charset,$timestamp;
		foreach ($uploaddb as $key => $value) {
			$value['name'] = pwConvert($value['name'], $db_charset, 'utf-8');
			$this->db->update("INSERT INTO pw_attachs SET " . S::sqlSingle(array(
				'fid'		=> 0,						'uid'		=> $this->uid,
				'tid'		=> 0,						'pid'		=> 0,
				'hits'		=> 0,						'name'		=> $value['name'],
				'type'		=> $value['type'],			'size'		=> $value['size'],
				'attachurl'	=> $value['fileuploadurl'],	'uploadtime'=> $timestamp,
				'ifthumb'	=> $value['ifthumb']
			)));
			$aid = $this->db->insert_id();
			$this->attachs[$aid] = array(
				'aid'       => $aid,
				'name'      => stripslashes($value['name']),
				'type'      => $value['type'],
				'attachurl' => $value['fileuploadurl'],
				'needrvrc'  => $value['needrvrc'],
				'special'	=> $value['special'],
				'ctype'		=> $value['ctype'],
				'size'      => $value['size'],
				'hits'      => 0,
				'desc'		=> str_replace('\\','',$value['descrip']),
				'ifthumb'	=> $value['ifthumb']
			);
		}
		return true;
	}

	function getAttachInfo() {
		$array = current($this->attachs);
		list($path) = geturl($array['attachurl'],'lf');
		return array('aid' => $array['aid'], 'path' => $path);
	}
}
?>