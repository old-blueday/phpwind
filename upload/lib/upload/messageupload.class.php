<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class messageAtt extends uploadBehavior {
	
	var $db;
	var $mid;
	var $attachs;
	var $replacedb = array();

	function messageAtt($mid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$winduid;
		parent::uploadBehavior();
		$this->mid = intval($mid);
		$this->uid = $winduid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype = !is_array($db_uploadfiletype) ? unserialize($db_uploadfiletype) : $db_uploadfiletype;
	}

	function check($uploadnum) {
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
		if (($winddb['uploadnum'] + $uploadnum ) >= $_G['allownum']) {
			Showmsg('upload_num_error');
		}
	}
	
	function transfer($flashatt) {
		global $attachdir,$db_enhideset,$db_sellset,$db_ifpwcache;
		if (!$flashatt) {
			return false;
		}
		$this->check(count($flashatt));
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
			//$thumbdir = 'thumb/';
			if ($savedir = $this->getSaveDir($rt['ext'])) {
				$rt['fileuploadurl'] = $savedir . $filename;
				//$thumbdir .= $savedir;
			}
			$source   = PwUpload::savePath(0, $filename, $savedir);
			//$thumburl = PwUpload::savePath($this->ifftp, $thumbname, $thumbdir, 'thumb_');
			/*
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
			*/
			if ($this->ifftp) {
				if (!PwUpload::movetoftp($srcfile, $rt['fileuploadurl'])) continue;
				//$rt['ifthumb'] && PwUpload::movetoftp($thumburl, "thumb/$rt[fileuploadurl]");
			} else {
				if (!PwUpload::movefile($srcfile, $source)) continue;
			}
			$this->db->update("INSERT INTO pw_attachs SET " . S::sqlSingle(array(
				'uid'		=> $this->uid,
				'mid'       => '1',
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
			$fieldDatas[] = array('uid'=>$this->uid,'aid'=>$aid,'mid'=>$this->mid,'status'=>1);
		}
		$messageService = L::loadClass("message", 'message');
		if($fieldDatas){
			$messageService->sendAttachs($fieldDatas);
			$this->updateUploadnum(count($fieldDatas),$this->uid);
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
		//return $this->forum->forumset['watermark'];
	}

	function update($uploaddb) {
		global $timestamp;
		$this->check(count($_FILES));
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
			$fieldDatas[] = array('uid'=>$this->uid,'aid'=>$aid,'mid'=>$this->mid,'status'=>1);
		}
		$messageService = L::loadClass("message", 'message');
		if($fieldDatas){
			$messageService->sendAttachs($fieldDatas);
			$this->updateUploadnum(count($fieldDatas),$this->uid);
		}
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
?>