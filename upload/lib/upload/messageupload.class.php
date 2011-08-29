<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class messageAtt extends uploadBehavior {
	
	var $db;
	var $mid;
	var $rid;
	var $attachs;
	var $replacedb = array();
	var $fieldDatas = array();

	function messageAtt($mid,$rid=0) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$winduid;
		parent::uploadBehavior();
		$this->mid = intval($mid);
		$this->rid = intval($rid);
		$this->uid = $winduid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype = !is_array($db_uploadfiletype) ? unserialize($db_uploadfiletype) : $db_uploadfiletype;
	}

	function check() {
		global $db_allowupload,$_G,$winddb;
		if (!$db_allowupload) {
			Showmsg('upload_close');
		} elseif ($_G['allowupload'] == 0) {
			Showmsg('upload_group_right');
		}
		if ($winddb['uploadtime'] < $GLOBALS['tdtime']) {
			$winddb['uploadnum'] = 0;
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->update($this->uid, array(), array('uploadnum' => 0));
		}
		if ($_G['allownum'] > 0 && ($winddb['uploadnum'] + count($_FILES) + count($this->flashatt)) >= $_G['allownum']) {
			Showmsg('upload_num_error');
		}
	}
	
	function transfer() {
		if (empty($this->flashatt)) {
			return false;
		}
		global $db_enhideset,$db_sellset,$db_ifpwcache,$timestamp;
		require_once(R_P . 'require/functions.php');
		$pw_attachs = L::loadDB('attachs', 'forum');
		$saveAttach = $this->getSaveAttach($this->uid);
		$deltmp = array();
		$attach = $pw_attachs->gets(array('tid' => 0, 'pid' => 0, 'uid' => $this->uid, 'did' => 0, 'mid' => 0));
		foreach ($attach as $rt) {
			if (!isset($this->flashatt[$rt['aid']])) {
				pwDelatt($rt['attachurl'], $this->ifftp);
				$deltmp[] = $rt['aid'];
				continue;
			}
			$saveAttach && $saveAttach->add($rt);
			$value = $this->flashatt[$rt['aid']];
			$rt['descrip'] = $value['desc'];

			$pw_attachs->updateById($rt['aid'], array(
				'mid'       => '1',
				'descrip'	=> $rt['descrip']
			));
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
			$this->fieldDatas[] = array('uid' => $this->uid, 'aid' => $rt['aid'], 'mid' => $this->mid, 'rid' => $this->rid, 'status' => 1);
		}
		$saveAttach && $saveAttach->execute();
		$deltmp && $pw_attachs->delete($deltmp);
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
		$savedir = 'message/';
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
		return false;
	}

	function allowWaterMark() {
		return true;
	}

	function update($uploaddb) {
		global $timestamp;
		$this->check();
		$this->transfer();
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			$value['descrip'] = S::escapeChar(S::getGP('atc_desc'.$value['id'], 'P'));
			$this->db->update("INSERT INTO pw_attachs SET " . S::sqlSingle(array(
				'uid'		=> $this->uid,
				'mid'       => '1',
				'hits'		=> 0,							'name'		=> $value['name'],
				'type'		=> $value['type'],				'size'		=> $value['size'],
				'attachurl'	=> $value['fileuploadurl'],
				'uploadtime'=> $timestamp,					'descrip'	=> $value['descrip'],
				'ifthumb'	=> $value['ifthumb']
			)));
			$aid = $this->db->insert_id();
			$this->attachs[$aid] = $value;
			$this->fieldDatas[] = array('uid'=>$this->uid,'aid'=>$aid,'mid'=>$this->mid,'rid'=>$this->rid,'status'=>1);
		}
		$messageService = L::loadClass("message", 'message');
		if ($this->fieldDatas) {
			$messageService->sendAttachs($this->fieldDatas);
			$this->updateUploadnum(count($this->fieldDatas),$this->uid);
		}
		return true;
	}
	
	function updateUploadnum($num,$uid){
		global $timestamp;
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($uid, array(), array('uploadtime' => $timestamp));
		$userService->updateByIncrement($uid, array(), array('uploadnum' => $num));
	}

	function getAids() {
		return array_keys($this->attachs);
	}

	function getAttNum() {
		return count($this->attachs);
	}
}

class messageMutiUpload extends uploadBehavior {
	
	var $db;
	var $attachs;

	function messageMutiUpload($uid) {
		global $db,$db_uploadfiletype;
		parent::uploadBehavior();
		$this->uid = $uid;
		$this->db =& $db;
		$this->ftype = !is_array($db_uploadfiletype) ? unserialize($db_uploadfiletype) : $db_uploadfiletype;
	}

	function check() {
		global $db_allowupload,$_G,$winddb;
		if (!$db_allowupload) {
			return 'upload_close';
		}
		if ($_G['allowupload'] == 0) {
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
		$filename = $this->cid . "_{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		$savedir = $this->getSaveDir($currUpload['ext']);
		return array($filename, $savedir);
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = 'message/';
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
		return false;
	}

	function allowWaterMark() {
		return true;
	}

	function update($uploaddb) {
		global $timestamp;
		foreach ($uploaddb as $value) {
			$value['name'] = pwConvert($value['name'], $db_charset, 'utf-8');
			$this->db->update("INSERT INTO pw_attachs SET " . S::sqlSingle(array(
				'uid'		=> $this->uid,
				'hits'		=> 0,							'name'		=> $value['name'],
				'type'		=> $value['type'],				'size'		=> $value['size'],
				'attachurl'	=> $value['fileuploadurl'],
				'uploadtime'=> $timestamp,					'ifthumb'	=> $value['ifthumb']
			)));
			$aid = $this->db->insert_id();
			$value['aid'] = $aid;
			$this->attachs[$aid] = $value;
		}
		return true;
	}
	
	function getAttachInfo() {
		$array = current($this->attachs);
		list($path) = geturl($array['fileuploadurl'], 'lf', $array['ifthumb']);
		return array('aid' => $array['aid'], 'path' => $path);
	}
}
?>