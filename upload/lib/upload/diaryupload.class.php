<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class DiaryUpload extends uploadBehavior {

	var $uid;
	var $db;
	var $ifthumb;
	var $replacedb = array();
	var $attachs = array();
	var $ifupload = 0;
	var $pw_attachs;

	function DiaryUpload($uid, $flashatt = null, $savetoalbum = 0, $albumid = 0) {
		global $db,$o_uploadsize,$db_ifathumb,$db_athumbsize;
		parent::uploadBehavior();

		is_array($o_uploadsize) || $o_uploadsize = (array)unserialize($o_uploadsize);
		$this->pw_attachs = L::loadDB('attachs', 'forum');
		$this->uid = $uid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype =& $o_uploadsize;
		$this->setFlashAtt($flashatt, $savetoalbum, $albumid);
	}

	function check() {
		global $db_allowupload,$winddb,$_G,$tdtime;

		if (!$db_allowupload) {
			Showmsg('upload_close');
		}
		if ($winddb['uploadtime'] < $tdtime) {
			$winddb['uploadnum'] = 0;
		}
		if (($winddb['uploadnum'] + count($_FILES) + count($this->flashatt)) >= $_G['allownum']) {
			Showmsg('upload_num_error');
		}
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
		global $timestamp,$winddb;
		require_once(R_P . 'require/functions.php');
		$saveAttach = $this->getSaveAttach($this->uid);
		$deltmp = array();
		$attach = $this->pw_attachs->gets(array('tid' => 0, 'pid' => 0, 'uid' => $this->uid, 'did' => 0, 'mid' => 0));
		foreach ($attach as $rt) {
			if (!isset($this->flashatt[$rt['aid']])) {
				pwDelatt($rt['attachurl'], $this->ifftp);
				$deltmp[] = $rt['aid'];
				continue;
			}
			$saveAttach && $saveAttach->add($rt);
			$value = $this->flashatt[$rt['aid']];
			$rt['descrip'] = $value['desc'];

			$this->attachs[$rt['aid']] = array(
				'aid'       => $rt['aid'],
				'name'      => $rt['name'],
				'type'      => $rt['type'],
				'attachurl' => $rt['attachurl'],
				'needrvrc'  => 0,
				'special'	=> 0,
				'ctype'		=> '',
				'size'      => $rt['size'],
				'hits'      => $rt['hits'],
				'desc'		=> str_replace('\\','', $rt['descrip']),
				'ifthumb'	=> $rt['ifthumb']
			);
			if ($rt['descrip']) {
				$this->pw_attachs->updateById($rt['aid'], array('descrip' => $rt['descrip']));
			}
			$winddb['uploadnum']++;
			$winddb['uploadtime'] = $timestamp;
			$this->ifupload = ($rt['type'] == 'img' ? 1 : ($rt['type'] == 'txt' ? 2 : 3));
		}
		$saveAttach && $saveAttach->execute();
		$deltmp && $this->pw_attachs->delete($deltmp);
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
			$savedir  = 'diary/' . ($arr ? implode('/',$arr) . '/' : '');
		} else {
			global $timestamp;
			$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
			$filename = $this->uid . "_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
			$savedir = $this->getSaveDir($currUpload['ext']);
		}
		//$thumbdir = 'thumb/';
		//$savedir && $thumbdir .= $savedir;
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

	function getSaveDir($ext) {
		global $o_attachdir;
		$savedir = 'diary/';
		if ($o_attachdir) {
			if ($o_attachdir == 1) {
				$savedir .= "Type_$ext/";
			} elseif ($o_attachdir == 2) {
				$savedir .= 'Mon_'.date('ym').'/';
			} elseif ($o_attachdir == 3) {
				$savedir .= 'Day_'.date('ymd').'/';
			}
		}
		return $savedir;
	}

	function update($uploaddb) {
		global $timestamp,$winddb;
		$this->transfer();
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			if ($value['attname'] == 'replace' && isset($this->replacedb[$value['id']])) {
				$value['needrvrc']	= 0;
				$value['special']	= 0;
				$value['ctype']		= 0;
				$value['descrip']	= $this->replacedb[$value['id']]['desc'];
				$aid = $this->replacedb[$value['id']]['aid'];
				$this->db->update("UPDATE pw_attachs SET " . S::sqlSingle(array(
					'name'		=> $value['name'],			'type'		=> $value['type'],
					'size'		=> $value['size'],			'attachurl'	=> $value['fileuploadurl'],
					'needrvrc'	=> $value['needrvrc'],		'special'	=> $value['special'],
					'ctype'		=> $value['ctype'],			'uploadtime'=> $timestamp,
					'descrip'	=> $value['descrip'],		'ifthumb'	=> $value['ifthumb']
				)) . " WHERE aid=".S::sqlEscape($aid));
				
				$this->replacedb[$aid]['name'] = $value['name'];
				$this->replacedb[$aid]['type'] = $value['type'];
				$this->replacedb[$aid]['size'] = $value['size'];
				$this->replacedb[$aid]['ifthumb'] = $value['ifthumb'];

			} else {
				$value['descrip']	= S::escapeChar(S::getGP('atc_desc'.$value['id'], 'P'));
				$value['needrvrc']	= $value['special'] = 0;
				$value['ctype'] = '';

				$this->db->update("INSERT INTO pw_attachs SET " . S::sqlSingle(array(
					'fid'		=> 0,						'uid'		=> $this->uid,
					'hits'		=> 0,						'name'		=> $value['name'],
					'type'		=> $value['type'],			'size'		=> $value['size'],
					'attachurl'	=> $value['fileuploadurl'],	'needrvrc'	=> $value['needrvrc'],
					'special'	=> $value['special'],		'ctype'		=> $value['ctype'],
					'uploadtime'=> $timestamp,				'descrip'	=> $value['descrip'],
					'ifthumb'	=> $value['ifthumb']
				)));
				$aid = $this->db->insert_id();
				$this->attachs[$aid] = array(
					'id'        => $value['id'],
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
				//$atc_content = str_replace("[upload=$value[id]]", "[attachment=$aid]", $atc_content);
				$winddb['uploadnum']++;
				$winddb['uploadtime'] = $timestamp;
			}
			$this->ifupload = ($value['type'] == 'img' ? 1 : ($value['type'] == 'txt' ? 2 : 3));
		}
		return true;
	}

	function getAids() {
		return array_keys($this->attachs);
	}

	function getAttachs() {
		return $this->attachs;
	}
	
	function getAttachIds() {
		if (!S::isArray($this->attachs)) return array();
		$attachIds = array();
		foreach ($this->attachs as $key => $value) {
			$attachIds[$value['id']] = $key;
		}
		return $attachIds;
	}
}

class diaryMutiUpload extends uploadBehavior {

	var $uid;
	var $db;
	var $ifthumb;
	var $attachs = array();
	var $pw_attachs;

	function diaryMutiUpload($uid) {
		global $db,$db_ifathumb,$db_athumbsize;
		parent::uploadBehavior();
		
		$o_uploadsize = L::config('o_uploadsize', 'o_config');
		is_array($o_uploadsize) || $o_uploadsize = (array)unserialize($o_uploadsize);
		$this->pw_attachs = L::loadDB('attachs', 'forum');
		$this->uid = $uid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype =& $o_uploadsize;
	}

	function check() {
		global $db_allowupload,$winddb,$_G,$tdtime;
		if (!$db_allowupload) {
			return 'upload_close';
		}
		if ($winddb['uploadtime'] < $tdtime) {
			$winddb['uploadnum'] = 0;
		}
		if ($winddb['uploadnum'] + count($_FILES) >= $_G['allownum']) {
			return 'upload_num_error';
		}
		return true;
	}

	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = $this->uid . "_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		$savedir = $this->getSaveDir($currUpload['ext']);
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

	function getSaveDir($ext) {
		$o_attachdir = L::config('o_attachdir', 'o_config');
		$savedir = 'diary/';
		if ($o_attachdir) {
			if ($o_attachdir == 1) {
				$savedir .= "Type_$ext/";
			} elseif ($o_attachdir == 2) {
				$savedir .= 'Mon_'.date('ym').'/';
			} elseif ($o_attachdir == 3) {
				$savedir .= 'Day_'.date('ymd').'/';
			}
		}
		return $savedir;
	}

	function update($uploaddb) {
		global $timestamp,$db_charset;
		foreach ($uploaddb as $value) {
			$value['name'] = pwConvert($value['name'], $db_charset, 'utf-8');
			$aid = $this->pw_attachs->add(array(
				'uid'		=> $this->uid,
				'hits'		=> 0,						'name'		=> $value['name'],
				'type'		=> $value['type'],			'size'		=> $value['size'],
				'attachurl'	=> $value['fileuploadurl'],	'uploadtime'=> $timestamp,
				'ifthumb'	=> $value['ifthumb']
			));
			$this->attachs[$aid] = array(
				'aid'       => $aid,
				'name'      => stripslashes($value['name']),
				'type'      => $value['type'],
				'attachurl' => $value['fileuploadurl'],
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
		list($path) = geturl($array['attachurl'], 'lf', $array['ifthumb']);
		return array('aid' => $array['aid'], 'path' => $path);
	}
}
?>