<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);
L::loadClass('forum', 'forum', false);

class AttMutiUpload extends uploadBehavior {

	var $db;
	var $pw_attachs;
	var $forum;

	var $uid;
	var $uptype;
	var $ifthumb;
	var $thumbsize;

	var $attachs = array();

	function AttMutiUpload($uid, $fid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$_G;
		parent::uploadBehavior();

		$this->pw_attachs = L::loadDB('attachs', 'forum');
		$this->uid = $uid;
		$this->db =& $db;
		$this->forum = new PwForum($fid);

		if ($this->forum->forumset['ifthumb'] == 1) {
			$this->ifthumb	 = 1;
			$this->thumbsize = $pwforum->forumset['thumbsize'];
		} elseif ($this->forum->forumset['ifthumb'] == 2) {
			$this->ifthumb	 = 0;
			$this->thumbsize = 0;
		} else {
			$this->ifthumb	 = $db_ifathumb;
			$this->thumbsize = $db_athumbsize;
		}
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
		$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
		$this->ftype =& $db_uploadfiletype;
		$this->uptype = 'all';
	}

	function check() {
		global $db_allowupload, $winddb, $groupid, $_G;
		if (!$db_allowupload) {
			return 'upload_close';
		}
		if (!$this->forum->allowupload($winddb, $groupid)) {
			return 'upload_forum_right';
		}
		if (!$this->forum->foruminfo['allowupload'] && $_G['allowupload'] == 0) {
			return 'upload_group_right';
		}
		return true;
	}

	function allowType($key) {
		return true;
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = $this->forum->fid . "_{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		$savedir = $this->getSaveDir($currUpload['ext']);
		return array($filename, $savedir);
	}

	function allowThumb() {
		return $this->ifthumb;
	}

	function getThumbInfo($filename, $dir) {
		global $db_athumbtype;
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize),
			array($filename, 'thumb/mini/' . $dir, "200\t150\t$db_athumbtype")
		);
	}

	function allowWaterMark() {
		return $this->forum->forumset['watermark'];
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = '';
		if ($db_attachdir) {
			if ($db_attachdir == 2) {
				$savedir = "Type_$ext/";
			} elseif ($db_attachdir == 3) {
				$savedir = 'Mon_'.date('ym').'/';
			} elseif ($db_attachdir == 4) {
				$savedir = 'Day_'.date('ymd').'/';
			} else {
				$savedir = "Fid_{$this->forum->fid}/";
			}
		}
		return $savedir;
	}

	function update($uploaddb) {
		global $db_charset,$timestamp;
		foreach ($uploaddb as $key => $value) {
			$value['name'] = pwConvert($value['name'], $db_charset, 'utf-8');
			$aid = $this->pw_attachs->add(array(
				'fid'		=> $this->forum->fid,		'uid'		=> $this->uid,
				'tid'		=> 0,						'pid'		=> 0,
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

	function getAttachs() {
		return $this->attachs;
	}

	function getAttachInfo() {
		$array = current($this->attachs);
		list($path) = geturl($array['attachurl'], 'lf', $array['ifthumb']&1);
		return array('aid' => $array['aid'], 'path' => $path);
	}
}
?>