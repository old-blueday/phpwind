<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class ArticleUpload extends uploadBehavior {
	
	var $db;
	var $attachs;
	var $replacedb = array();

	function ArticleUpload() {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$winduid;
		parent::uploadBehavior();
		$this->uid = $winduid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype = !is_array($db_uploadfiletype) ? unserialize($db_uploadfiletype) : $db_uploadfiletype;
	}

	function allowType($key) {
		list($t) = explode('_', $key);
		return in_array($t, array('replace', 'attachment'));
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = "{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		$savedir = $this->getSaveDir($currUpload['ext']);
		$thumbdir = 'thumb/';
		$savedir && $thumbdir .= $savedir;

		return array($filename, $savedir, $filename, $thumbdir);
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = 'cms_article/';
		if ($db_attachdir) {
			if ($db_attachdir == 2) {
				$savedir .= "Type_$ext/";
			} elseif ($db_attachdir == 3) {
				$savedir .= 'Mon_'.date('ym').'/';
			} elseif ($db_attachdir == 4) {
				$savedir .= 'Day_'.date('ymd').'/';
			}
		}
		return $savedir;
	}

	function allowThumb() {
		return (int) $this->ifthumb;
	}

	function allowWaterMark() {
		global $db_watermark;
		return (int)$db_watermark;
	}

	function getThumbSize() {
		return $this->thumbsize;
	}

	function getAids() {
		return array_keys($this->attachs);
	}

	function getAttNum() {
		return count($this->attachs);
	}
}
?>