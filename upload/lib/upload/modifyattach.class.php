<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);
L::loadClass('forum', 'forum', false);

class ModifyAttach extends uploadBehavior {

	var $db;
	var $forum;
	var $attach;
	var $attachs;
	var $pw_attachs;

	function ModifyAttach($aid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype;
		parent::uploadBehavior();

		$this->db =& $db;
		$this->pw_attachs = L::loadDB('attachs', 'forum');
		$this->attach = $this->pw_attachs->get($aid);
		$this->forum = new PwForum($this->attach['fid']);

		$this->ifthumb =& $db_ifathumb;
		if ($this->forum->forumset['ifthumb'] == 0) {
			$this->thumbsize =& $db_athumbsize;
		} elseif ($this->forum->forumset['ifthumb'] == 1) {
			$this->thumbsize =& $pwforum->forumset['thumbsize'];
		} elseif ($this->forum->forumset['ifthumb'] == 2) {
			$this->thumbsize = 0;
			$this->ifthumb = 0;
		} else {
			$this->thumbsize =& $db_athumbsize;
		}
		$this->ftype =& $db_uploadfiletype;
		$this->uptype = 'all';
	}

	function check() {
		global $db_allowupload, $winddb, $groupid, $_G, $windid, $winduid, $manager;
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		if (!$db_allowupload) {
			return 'upload_close';
		} elseif (!$this->forum->allowupload($winddb, $groupid)) {
			return 'upload_forum_right';
		} elseif (!$this->forum->foruminfo['allowupload'] && $_G['allowupload'] == 0) {
			return 'upload_group_right';
		}
		if (!($winduid == $this->attach['uid'] || S::inArray($windid, $manager) || pwRights($this->forum->isBM($windid), 'deltpcs'))) {
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

	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize),
			array($filename, 'thumb/mini/' . $dir, "120\t120\t1")
		);
	}

	function allowWaterMark() {
		return $this->forum->forumset['watermark'];
	}

	function update($uploaddb) {
		global $timestamp;
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			$aid = $value['id'];
			$this->pw_attachs->updateById($aid, array(
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