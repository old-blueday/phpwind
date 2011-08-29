<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class ActivityUpload extends uploadBehavior {

	var $db;
	var $tid;
	var $actmid;
	var $attachs;

	function ActivityUpload($tid,$actmid) {
		global $db;
		parent::uploadBehavior();
		$this->tid = (int)$tid;
		$this->actmid = (int)$actmid;
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
		return in_array($t, array('act'));
	}

	function allowThumb() {
		return true;
	}

	function getThumbInfo($filename, $dir) {
		return array(
			array('s_' . $filename, $dir, "195\t150")
		);
	}

	function getFilePath($currUpload) {
		global $timestamp,$o_mkdir;
		$prename	= randstr(4) . $timestamp . substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		if ($this->tid) {
			$filename = $this->tid.'_'.$currUpload['id'] . "_$prename." . $currUpload['ext'];
		} else {
			$filename = $this->actmid."_ajax" . "_$prename." . $currUpload['ext'];
		}
		$savedir = 'activity/' . $this->actmid . '/';
		if (!in_array($currUpload['attname'], array('act'))) {
			$savedir = '';
		}
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		$fieldService = L::loadClass('ActivityField', 'activity');
		if ($this->tid) {
			$defaultAttach = $userAttach = array();
			foreach ($uploaddb as $key => $value) {
				if ($value['id']) {
					$attach = array();
					$attach = $fieldService->getField($value['id']);

					$this->attachs[$attach['fieldname']] = $value['fileuploadurl'];

					if ($attach['fieldname'] && $attach['ifdel'] == 1) {
						$userAttach[$attach['fieldname']] = $value['fileuploadurl'];
					} elseif ($attach['fieldname'] && !$attach['ifdel']) {
						$defaultAttach[$attach['fieldname']] = $value['fileuploadurl'];
					}
				}
			}
			$defaultValueTableName = getActivityValueTableNameByActmid();
			$userDefinedValueTableName = getActivityValueTableNameByActmid($this->actmid, 1, 1);

			if ($defaultAttach) {
				$this->db->update("UPDATE $defaultValueTableName SET " . S::sqlSingle($defaultAttach)." WHERE tid=". S::sqlEscape($this->tid));
			}
			if ($userAttach) {
				$this->db->update("UPDATE $userDefinedValueTableName SET " . S::sqlSingle($userAttach)." WHERE tid=". S::sqlEscape($this->tid));
			}
		} else {
			foreach ($uploaddb as $key => $value) { 
				$this->attachs['fileuploadurl'] = $value['fileuploadurl'];
			}
		}
		return true;
	}

	function getAttachs() {
		return $this->attachs;
	}

}
?>