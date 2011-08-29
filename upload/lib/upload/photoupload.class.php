<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class PhotoUpload extends uploadBehavior {
	
	var $db;
	var $aid;
	var $atype;
	var $attachs;
	var $pid = null;

	function PhotoUpload($aid,$atype=0) {
		global $db,$o_maxfilesize;
		parent::uploadBehavior();
		$this->aid = (int)$aid;
		$this->atype = (int)$atype;
		$this->db =& $db;
		
		!isset($o_maxfilesize) && $o_maxfilesize = 1000;

		$this->ftype = array(
			'gif'  => $o_maxfilesize,				'jpg'  => $o_maxfilesize,
			'jpeg' => $o_maxfilesize,				'bmp'  => $o_maxfilesize,
			'png'  => $o_maxfilesize
		);
	}

	function allowType($key) {
		return true;
	}

	function allowThumb() {
		return true;
	}

	function getThumbInfo($filename, $dir) {
		return array(
			array('s_' . $filename, $dir, "100\t100"),
		);
	}

	function getFilePath($currUpload) {
		global $timestamp,$o_mkdir;
		$prename	= randstr(4) . $timestamp . substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename	= $this->aid . "_$prename." . $currUpload['ext'];
		$savedir	= 'photo/';
		if ($o_mkdir == '2') {
			$savedir .= 'Day_' . date('ymd') . '/';
		} elseif ($o_mkdir == '3') {
			$savedir .= 'Cyid_' . $this->aid . '/';
		} else {
			$savedir .= 'Mon_'.date('ym') . '/';
		}
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		global $windid,$winduid,$timestamp,$pintro;
		foreach ($uploaddb as $key => $value) {
			$this->attachs[] = array(
				'aid'		=> $this->aid,
				'pintro'	=> $pintro[$value['id']] ? $pintro[$value['id']] : substr($value['name'], 0, strrpos($value['name'], '.')),
				'path'		=> $value['fileuploadurl'],
				'uploader'	=> $windid,
				'uptime'	=> $timestamp,
				'ifthumb'	=> $value['ifthumb']
			);
		}
		if ($this->attachs) {
			$this->db->update("INSERT INTO pw_cnphoto (aid,pintro,path,uploader,uptime,ifthumb) VALUES " . S::sqlMulti($this->attachs));
			$this->pid = $this->db->insert_id();
			$cnalbum = $this->db->get_one("SELECT * FROM pw_cnalbum WHERE aid=". S::sqlEscape($this->aid));
			if ($this->atype) {
				if (!$cnalbum['private']) {
					updateDatanalyse($this->pid, 'groupPicNew', $timestamp);
				}
			} else {
				$statistics = L::loadClass('Statistics', 'datanalyse');
				$statistics->photouser($winduid, count($this->attachs));
			}
			if (isset($cnalbum['lastphoto']) && !$cnalbum['lastphoto']) {
				$lastphoto = $this->getLastPhotoThumb();
				$lastphotosqlAdd = ",lastphoto= " . S::sqlEscape($lastphoto);
			}
			$this->db->update("UPDATE pw_cnalbum SET photonum=photonum+" . S::sqlEscape(count($this->attachs)) . ",lasttime=" . S::sqlEscape($timestamp) . $lastphotosqlAdd . " WHERE aid=" . S::sqlEscape($this->aid));
		}
		return true;
	}

	function getAttachs() {
		return $this->attachs;
	}

	function getNewID() {
		return $this->pid;
	}

	function getLastPhoto() {
		$tmp = end($this->attachs);
		$lastpos = strrpos($tmp['path'],'/') + 1;
		$tmp['ifthumb'] && $tmp['path'] = substr($tmp['path'], 0, $lastpos) . 's_' . substr($tmp['path'], $lastpos);
		return $tmp['path'];
	}
	
	function getLastPhotoThumb(){
		$tmp = end($this->attachs);
		$thumbpath = '';
		$lastpos = strrpos($tmp['path'],'/') + 1;
		if($tmp['ifthumb']){
			$thumbpath = substr($tmp['path'], 0, $lastpos) . 's_' . substr($tmp['path'], $lastpos);
		}else{
			$thumbpath = $tmp['path'];
		}
		return $thumbpath;
	}
}
?>