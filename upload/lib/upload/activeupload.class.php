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
		$this->ifftp = 0;
		
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
		return array($filename, $savedir, '', '');
	}

	function update($uploaddb) {
		$this->attachs = $uploaddb;
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

	function transfer($flashatt) {
		global $attachdir,$db_enhideset,$db_sellset,$db_ifpwcache;
		if (!$flashatt) {
			return false;
		}
		$flattids = array_keys($flashatt);
		$pw_attachs = L::loadDB('attachs', 'forum');
		$attach = $pw_attachs->gets(array('tid'=>0, 'pid'=>0, 'uid'=>$this->uid,'aid'=>$flattids));
		foreach ($attach as $rt) {
			$value = $flashatt[$rt['aid']];
			$rt['ifthumb'] = 0;
			$rt['descrip'] = $value['desc'];
			$rt['ext'] = strtolower(substr(strrchr($rt['name'],'.'),1));
			$srcfile = "$attachdir/mutiupload/$rt[attachurl]";
			$rt['fileuploadurl'] = $filename = $thumbname = preg_replace('/^0_/', "{$this->cid}_", $rt['attachurl']);
			$thumbdir = 'thumb/';
			if ($savedir = $this->getSaveDir($rt['ext'])) {
				$rt['fileuploadurl'] = $savedir . $filename;
				$thumbdir .= $savedir;
			}
			$source   = PwUpload::savePath(0, $filename, $savedir);
			$thumburl = PwUpload::savePath($this->ifftp, $thumbname, $thumbdir, 'thumb_');

			if (in_array($rt['ext'], array('gif','jpg','jpeg','png','bmp'))) {
				require_once(R_P.'require/imgfunc.php');
				if (!$img_size = GetImgSize($srcfile, $rt['ext'])) {
					Showmsg('upload_content_error');
				}
				if ($this->allowThumb()) {
					$thumbsize = PwUpload::makeThumb($srcfile, $thumburl, $this->getThumbSize(), $rt['ifthumb']);
				}
				if ($this->allowWaterMark()) {
					PwUpload::waterMark($srcfile, $rt['ext'], $img_size);
					$rt['ifthumb'] && PwUpload::waterMark($thumburl, $rt['ext']);
				}
			}
			if ($this->ifftp) {
				if (!PwUpload::movetoftp($srcfile, $rt['fileuploadurl']))
					continue;
				$rt['ifthumb'] && PwUpload::movetoftp($thumburl, "thumb/$rt[fileuploadurl]");
			} else {
				if (!PwUpload::movefile($srcfile, $source))
					continue;
			}
			$this->db->update("INSERT INTO pw_actattachs SET " . pwSqlSingle(array(
				'uid'		=> $this->uid,
				'hits'		=> 0,							'name'		=> $rt['name'],
				'type'		=> $rt['type'],					'size'		=> $rt['size'],
				'attachurl'	=> $rt['fileuploadurl'],
				'uploadtime'=> $timestamp,					'descrip'	=> $rt['descrip'],
				'ifthumb'	=> $rt['ifthumb']
			)));
			$aid = $this->db->insert_id();

			$this->attachs[$aid] = array(
				'aid'       => $aid,
				'name'      => $rt['name'],
				'type'      => $rt['type'],
				'attachurl' => $rt['fileuploadurl'],
				'size'      => $rt['size'],
				'hits'      => $rt['hits'],
				'desc'		=> str_replace('\\','', $rt['descrip']),
				'ifthumb'	=> $rt['ifthumb']
			);
		}
		$pw_attachs->delete($flattids);
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
		$thumbdir = 'thumb/';
		$savedir && $thumbdir .= $savedir;

		return array($filename, $savedir, $filename, $thumbdir);
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
		//return $this->forum->forumset['watermark'];
	}

	function getThumbSize() {
		return $this->thumbsize;
	}

	function update($uploaddb) {
		global $timestamp;
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			if ($value['attname'] == 'replace' && isset($this->replacedb[$value['id']])) {
				$aid = $value['id'];
				$value['descrip'] = $this->replacedb[$aid]['desc'];
				$this->db->update('UPDATE pw_actattachs SET ' . pwSqlSingle(array(
					'name'		=> $value['name'],			'type'		=> $value['type'],
					'size'		=> $value['size'],			'attachurl'	=> $value['fileuploadurl'],
					'uploadtime'=> $timestamp,
					'descrip'	=> $value['descrip'],		'ifthumb'	=> $value['ifthumb']
				)) . ' WHERE aid=' . pwEscape($aid));
			} else {
				$value['descrip'] = Char_cv(GetGP('atc_desc'.$value['id'], 'P'));
				$this->db->update("INSERT INTO pw_actattachs SET " . pwSqlSingle(array(
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
			$this->db->update("UPDATE pw_actattachs SET " . pwSqlSingle($data) . ' WHERE aid IN(' . pwImplode($aids) . ')');
		} else {
			$this->db->update("UPDATE pw_actattachs SET " . pwSqlSingle($data) . ' WHERE aid=' . intval($aids));
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
?>