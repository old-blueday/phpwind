<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class activePoster extends uploadBehavior {
	
	var $db;
	var $cid;
	var $attachs;

	function activePoster($cid) {
		global $db,$db_imgsize;
		parent::uploadBehavior();
		$this->cid = intval($cid);
		$this->db =& $db;
		
		!$db_imgsize && $db_imgsize = 1000;
		$this->ftype = array(
			'gif'  => $db_imgsize,				'jpg'  => $db_imgsize,
			'jpeg' => $db_imgsize,				'bmp'  => $db_imgsize,
			'png'  => $db_imgsize
		);
	}

	function allowType($key) {
		list($t) = explode('_', $key);
		return $t == 'poster';
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = $this->cid . '_' . $prename . '.' . $currUpload['ext'];
		$savedir = 'active/';
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		$this->attachs = $uploaddb;
		return true;
	}

	function getImgUrl() {
		return $this->attachs ? $this->attachs[0]['fileuploadurl'] : null;
	}
}

class activeAtt extends uploadBehavior {
	
	var $db;
	var $cid;
	var $attachs;
	var $replacedb = array();

	function activeAtt($cid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$winduid,$_G;
		parent::uploadBehavior();
		$this->cid = intval($cid);
		$this->uid = $winduid;
		$this->db =& $db;
		
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
		!is_array($db_uploadfiletype) && $db_uploadfiletype = unserialize($db_uploadfiletype);
		$this->ftype =& $db_uploadfiletype;
	}

	function setReplaceAtt($replacedb) {
		if ($replacedb && is_array($replacedb)) {
			$this->replacedb = $replacedb;
		}
	}

	function transfer() {
		if (empty($this->flashatt)) {
			return false;
		}
		require_once(R_P . 'require/functions.php');
		$saveAttach = $this->getSaveAttach($this->uid);
		$deltmp = array();
		$query = $this->db->query("SELECT * FROM pw_actattachs WHERE actid=0 AND uid=" . S::sqlEscape($this->uid));
		while ($rt = $this->db->fetch_array($query)) {
			if (!isset($this->flashatt[$rt['aid']])) {
				pwDelatt($rt['attachurl'], $this->ifftp);
				$deltmp[] = $rt['aid'];
				continue;
			}
			$saveAttach && $saveAttach->add($rt);
			$value = $this->flashatt[$rt['aid']];
			$rt['descrip'] = $value['desc'];
			
			if ($rt['descrip']) {
				$this->db->update("UPDATE pw_actattachs SET " . S::sqlSingle(array('descrip'	=> $rt['descrip'])) . ' WHERE aid=' . S::sqlEscape($rt['aid']));
			}
			$this->attachs[$rt['aid']] = array(
				'aid'       => $rt['aid'],
				'name'      => $rt['name'],
				'type'      => $rt['type'],
				'attachurl' => $rt['attachurl'],
				'size'      => $rt['size'],
				'hits'      => $rt['hits'],
				'desc'		=> str_replace('\\','', $rt['descrip']),
				'ifthumb'	=> $rt['ifthumb']
			);
		}
		$saveAttach && $saveAttach->execute();
		if ($deltmp) {
			$this->db->update("DELETE FROM pw_actattachs WHERE aid IN(" . S::sqlImplode($deltmp) . ')');
		}
		return true;
	}

	function allowType($key) {
		list($t) = explode('_', $key);
		return in_array($t, array('replace', 'attachment'));
	}

	function getFilePath($currUpload) {
		if ($currUpload['attname'] == 'replace' && isset($this->replacedb[$currUpload['id']])) {
			$arr = explode('/', $this->replacedb[$currUpload['id']]['attachurl']);
			$filename = array_pop($arr);
			$savedir  = $arr ? implode('/',$arr) . '/' : '';
		} else {
			global $timestamp;
			$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
			$filename = $this->cid . "_{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
			$savedir = $this->getSaveDir($currUpload['ext']);
		}
		return array($filename, $savedir);
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = 'active/';
		if ($db_attachdir) {
			if ($db_attachdir == 2) {
				$savedir .= "Type_$ext/";
			} elseif ($db_attachdir == 3) {
				$savedir .= 'Mon_'.date('ym').'/';
			} elseif ($db_attachdir == 4) {
				$savedir .= 'Day_'.date('ymd').'/';
			} else {
				$savedir .= "Cid_{$this->cid}/";
			}
		}
		return $savedir;
	}

	function allowThumb() {
		return $this->ifthumb;
	}

	function allowWaterMark() {
		return true;
	}
	
	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize)
		);
	}

	function update($uploaddb) {
		global $timestamp;
		$this->transfer();
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			if ($value['attname'] == 'replace' && isset($this->replacedb[$value['id']])) {
				$aid = $value['id'];
				$value['descrip'] = $this->replacedb[$aid]['desc'];
				$this->db->update('UPDATE pw_actattachs SET ' . S::sqlSingle(array(
					'name'		=> $value['name'],			'type'		=> $value['type'],
					'size'		=> $value['size'],			'attachurl'	=> $value['fileuploadurl'],
					'uploadtime'=> $timestamp,
					'descrip'	=> $value['descrip'],		'ifthumb'	=> $value['ifthumb']
				)) . ' WHERE aid=' . S::sqlEscape($aid));
			} else {
				$value['descrip'] = S::escapeChar(S::getGP('atc_desc'.$value['id'], 'P'));
				$this->db->update("INSERT INTO pw_actattachs SET " . S::sqlSingle(array(
					'uid'		=> $this->uid,
					'hits'		=> 0,							'name'		=> $value['name'],
					'type'		=> $value['type'],				'size'		=> $value['size'],
					'attachurl'	=> $value['fileuploadurl'],
					'uploadtime'=> $timestamp,					'descrip'	=> $value['descrip'],
					'ifthumb'	=> $value['ifthumb']
				)));
				$aid = $this->db->insert_id();
				$this->attachs[$aid] = $value;
			}
		}
	}

	function updateById($aids, $data) {
		if (empty($aids) || empty($data)) return false;
		if (is_array($aids)) {
			$this->db->update("UPDATE pw_actattachs SET " . S::sqlSingle($data) . ' WHERE aid IN(' . S::sqlImplode($aids) . ')');
		} else {
			$this->db->update("UPDATE pw_actattachs SET " . S::sqlSingle($data) . ' WHERE aid=' . intval($aids));
		}
		return true;
	}

	function getAids() {
		return array_keys($this->attachs);
	}

	function getAttNum() {
		return count($this->attachs);
	}
}

class activeMutiUpload extends uploadBehavior {

	var $db;
	var $cid;
	var $uid;
	var $attachs;

	function activeMutiUpload($uid, $cid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$_G;
		parent::uploadBehavior();
		$this->cid = intval($cid);
		$this->uid = $uid;
		$this->db =& $db;
		
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
		!is_array($db_uploadfiletype) && $db_uploadfiletype = unserialize($db_uploadfiletype);
		$this->ftype =& $db_uploadfiletype;
	}
	
	function check() {
		return true;
	}

	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = $this->cid . "_{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		$savedir = $this->getSaveDir($currUpload['ext']);
		return array($filename, $savedir);
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = 'active/';
		if ($db_attachdir) {
			if ($db_attachdir == 2) {
				$savedir .= "Type_$ext/";
			} elseif ($db_attachdir == 3) {
				$savedir .= 'Mon_'.date('ym').'/';
			} elseif ($db_attachdir == 4) {
				$savedir .= 'Day_'.date('ymd').'/';
			} else {
				$savedir .= "Cid_{$this->cid}/";
			}
		}
		return $savedir;
	}

	function allowThumb() {
		return $this->ifthumb;
	}

	function allowWaterMark() {
		return true;
	}
	
	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize)
		);
	}

	function update($uploaddb) {
		global $timestamp,$db_charset;
		foreach ($uploaddb as $value) {
			$value['name'] = pwConvert($value['name'], $db_charset, 'utf-8');
			$this->db->update("INSERT INTO pw_actattachs SET " . S::sqlSingle(array(
				'uid'		=> $this->uid,					'actid'		=> 0,
				'hits'		=> 0,							'name'		=> $value['name'],
				'type'		=> $value['type'],				'size'		=> $value['size'],
				'attachurl'	=> $value['fileuploadurl'],
				'uploadtime'=> $timestamp,					'descrip'	=> $value['descrip'],
				'ifthumb'	=> $value['ifthumb']
			)));
			$aid = $this->db->insert_id();
			$value['aid'] = $aid;
			$this->attachs[$aid] = $value;
		}
	}
	
	function getAttachInfo() {
		$array = current($this->attachs);
		list($path) = geturl($array['fileuploadurl'], 'lf', $array['ifthumb']);
		return array('aid' => $array['aid'], 'path' => $path);
	}
}

class ActiveModify extends uploadBehavior {

	var $db;
	var $attach;
	var $attachs;

	function ActiveModify($aid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$_G;
		parent::uploadBehavior();

		$this->db =& $db;
		$this->attach = $this->db->get_one("SELECT * FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid));

		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
		$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
		$this->ftype =& $db_uploadfiletype;
	}

	function check() {
		global $db_allowupload, $winddb, $groupid, $_G, $windid, $winduid, $manager;
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		if (!$db_allowupload) {
			return 'upload_close';
		} elseif ($_G['allowupload'] == 0) {
			return 'upload_group_right';
		}
		if (!($winduid == $this->attach['uid'] || S::inArray($windid, $manager))) {
			return 'modify_noper';
		}
		return true;
	}

	function allowType($key) {
		list(, $t) = explode('_', $key);
		return $t == $this->attach['aid'];
	}

	function getFilePath($currUpload) {
		$arr = explode('/', $this->attach['attachurl']);
		$filename = array_pop($arr);
		$savedir  = $arr ? implode('/',$arr) . '/' : '';
		
		return array($filename, $savedir);
	}

	function allowThumb() {
		return $this->ifthumb;
	}

	function allowWaterMark() {
		return true;
	}
	
	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize)
		);
	}

	function update($uploaddb) {
		global $timestamp;
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			$aid = $value['id'];
			pwQuery::update('pw_actattachs', 'aid=:aid', array($aid), array(
				'name'		=> $value['name'],			'type'		=> $value['type'],
				'size'		=> $value['size'],			'attachurl'	=> $value['fileuploadurl'],
				'uploadtime'=> $timestamp,				'ifthumb'	=> $value['ifthumb']
			));
		}
		$this->attachs = $uploaddb;
		return true;
	}

	function getAttachName() {
		$array = current($this->attachs);
		return $array['name'];
	}
}
?>