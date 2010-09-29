<?php
!defined('P_W') && exit('Forbidden');

/**
 * Picture
 * 
 * @package Upload
 */
class PW_UpdatePicture {
	var $allowSuffix;
	var $tmpName;
	var $name;
	var $size;
	var $directory;
	var $ext;
	var $filename;
	var $fileSize;
	var $thumbWidth;
	var $thumbHeight;
	var $thumbPrefix;
	var $isThumb;

	function PW_UpdatePicture() {
	}

	function init($dirctory) {
		$this->allowSuffix = array(
			"jpg",
			"gif",
			"jpeg",
			"png"
		); // 文件允许后缀
		//$this->directory = R_P . "attachment/stopic/"; // 文件上传目录
		$this->directory = & $dirctory; // 文件上传目录
		$this->fileName = date("YmdHis", time()); // 文件命名规则
		$this->fileSize = 1024 * 1024 * 2;
		$this->thumbWidth = 60;
		$this->thumbHeight = 60;
		$this->thumbPrefix = "thumb_";
		$this->isThumb = TRUE;
	}

	function upload($fileArray) {
		if (count($fileArray) != 1) {
			return FALSE;
		}
		foreach($fileArray as $value) {
			if (!isset($value['tmp_name']) || $value['tmp_name'] == '' || !isset($value['name']) || $value['name'] == '') {
				return false;
			}
			$this->tmpName = & $value['tmp_name'];
			$this->name = & $value['name'];
			$this->size = & $value['size'];
			if (!$this->_check()) {
				return FALSE;
			}
			$this->_createDirectory();
			$fileName = $this->_create();
			($this->isThumb && $fileName) ? $this->_createThumb($fileName) : "";
			return $fileName;
		}
		return FALSE;
	}

	function delete($fileName) {
		$filepath = $this->directory . $fileName;
		$thumbPath = $this->directory . $this->thumbPrefix . $fileName;
		if (file_exists($filepath)) {
			@P_unlink($filepath);
		}
		if (file_exists($thumbPath)) {
			@P_unlink($thumbPath);
		}
		return true;
	}

	function _check() {
		//文件是否有效
		if ($this->tmpName == "" || $this->name == "") {
			return FALSE;
		}
		//文件大小是否需要过滤
		if (filesize($this->tmpName) > $this->fileSize) {
			return FALSE;
		}
		//文件后缀检查
		$this->ext = substr($this->name, strrpos($this->name, ".") + 1);
		if (!in_array(strtolower($this->ext), $this->allowSuffix)) {
			return FALSE;
		}
		//文件是否可以上传
		$this->tmpName = str_replace("\\\\", "\\", $this->tmpName);
		if (!is_uploaded_file($this->tmpName)) {
			return FALSE;
		}
		return TRUE;
	}

	function _createDirectory() {
		if (!is_dir($this->directory)) {
			@mkdir($this->directory, 0777);
		}
	}

	function _create() {
		$filename = $this->fileName . "." . $this->ext;
		$destination = $this->directory . $filename;
		if (move_uploaded_file($this->tmpName, $destination)) {
			return $filename;
		} elseif (copy($this->tmpName, $destination)) {
			return $filename;
		}
		return FALSE;
	}

	function _createThumb($filename) {
		$filepath = $this->directory . $filename;
		list($width, $height, $type) = getimagesize($filepath);
		switch (strtolower($type)) {
			case 1:
				$source = imagecreatefromgif($filepath);
				break;

			case 2:
				$source = imagecreatefromjpeg($filepath);
				break;

			case 3:
				$source = imagecreatefrompng($filepath);
				break;

			default:
				return;
		}
		$thumb = imagecreatetruecolor($this->thumbWidth, $this->thumbHeight);
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $this->thumbWidth, $this->thumbHeight, $width, $height);
		$thumbfilename = $this->directory . $this->thumbPrefix . $filename;
		switch (strtolower($type)) {
			case 1:
				imageGIF($thumb, $thumbfilename);
			case 2:
				imageJPEG($thumb, $thumbfilename);
			case 3:
				imagePNG($thumb, $thumbfilename);
		}
		unset($source);
		unset($thumb);
	}
}
?>