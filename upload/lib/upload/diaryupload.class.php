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

	function DiaryUpload($uid) {
		global $db,$o_uploadsize,$db_ifathumb,$db_athumbsize;
		parent::uploadBehavior();

		is_array($o_uploadsize) || $o_uploadsize = (array)unserialize($o_uploadsize);
		//$this->pw_attachs = L::loadDB('attachs', 'forum');
		$this->uid = $uid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype =& $o_uploadsize;
	}

	function check() {
		global $db_allowupload,$winddb,$_G,$tdtime;

		if (!$db_allowupload) {
			Showmsg('upload_close');
		}
		if ($winddb['uploadtime'] < $tdtime) {
			$winddb['uploadnum'] = 0;
		}
		if (($winddb['uploadnum'] + count($_FILES)) >= $_G['allownum']) {
			Showmsg('upload_num_error');
		}
	}

	function setReplaceAtt($replacedb) {
		if ($replacedb && is_array($replacedb)) {
			$this->replacedb = $replacedb;
		}
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

		foreach ($uploaddb as $value) {

			$value['name'] = addslashes($value['name']);
			//$value['fileuploadurl'] = substr($value['fileuploadurl'], 6);

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

				$this->idrelate[$aid] = $value['id'];
				$winddb['uploadnum']++;
				$winddb['uploadtime'] = $timestamp;
			}
			$this->ifupload = ($value['type'] == 'img' ? 1 : ($value['type'] == 'txt' ? 2 : 3));
		}
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
			$attachIds[$value[id]] = $key;
		}
		
		return $attachIds;
	}
}
?>