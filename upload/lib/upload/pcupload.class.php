<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class PcUpload extends uploadBehavior {

	var $db;
	var $tid;
	var $pcid;
	var $attachs;

	function PcUpload($tid,$pcid) {
		global $db;
		parent::uploadBehavior();
		$this->tid = (int)$tid;
		$this->pcid = (int)$pcid;
		$this->db =& $db;

		$maxfilesize = 1000;

		$this->ftype = array(
			'gif'  => $maxfilesize,				'jpg'  => $maxfilesize,
			'jpeg' => $maxfilesize,				'bmp'  => $maxfilesize,
			'png'  => $maxfilesize
		);
	}

	function allowType($key) {
		list($t) = explode('_', $key);
		return in_array($t, array('topic', 'postcate'));
	}

	function allowThumb() {
		return true;
	}

	function getThumbInfo($filename, $dir) {
		return array(
			array('s_' . $filename, $dir, "200\t150")
		);
	}

	function getFilePath($currUpload) {
		global $timestamp,$o_mkdir;
		$prename	= randstr(4) . $timestamp . substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename	= $this->tid.'_'.$currUpload['id'] . "_$prename." . $currUpload['ext'];
		$savedir	= 'postcate/';

		if ($currUpload['attname'] == 'topic') {
			$savedir .= 'topic/'.$this->pcid .'/';
		} elseif ($currUpload['attname'] == 'postcate') {
			$savedir .= 'pc/'.$this->pcid .'/';
		}
		if (!in_array($currUpload['attname'],array('topic','postcate'))) {
			$savedir = '';
		}
		return array($filename, $savedir);
	}

	function update($uploaddb) {

		foreach ($uploaddb as $key => $value) {
			if ($value['id']) {
				$attach = $this->db->get_one("SELECT fieldname FROM pw_pcfield WHERE fieldid=". S::sqlEscape($value['id']));
			}
			if ($value['attname'] == 'postcate' && $attach['fieldname'] == 'pcattach') {
				$fieldname = 'pcattach';
			} else {
				$fieldname = 'field'.$value['id'];
			}
			$this->attachs[$fieldname] = $value['fileuploadurl'];

			if ($value['attname'] == 'topic') {
				$tablename = GetTopcitable($this->pcid);
			} elseif ($value['attname'] == 'postcate') {
				$tablename = GetPcatetable($this->pcid);
			}
		}
		if ($this->attachs) {
			$this->db->update("UPDATE $tablename SET " . S::sqlSingle($this->attachs)." WHERE tid=". S::sqlEscape($this->tid));
		}
		return true;
	}

	function getAttachs() {
		return $this->attachs;
	}

}
?>