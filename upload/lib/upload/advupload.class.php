<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('upload', '', false);

/**
 * 广告图片上传类
 * 
 * @package upload
 * @author	zhuli
 * @abstract
 */
class AdvUpload extends uploadBehavior{
	var $db;
	var $fileInputId;
	var $atype;
	var $attachs;
	
	/**
	 * 初始化
	 * 
	 * @param int $fileInputId 表单数字标识
	 * @return 
	 */
	function AdvUpload($fileInputId) {
		global $db;
		parent::uploadBehavior();
		$this->db =& $db;
		$this->fileInputId = $fileInputId;
		
		$o_maxfilesize = 2000;

		$this->ftype = array(
			'gif'  => $o_maxfilesize,				'jpg'  => $o_maxfilesize,
			'jpeg' => $o_maxfilesize,				'bmp'  => $o_maxfilesize,
			'png'  => $o_maxfilesize
		);
	}

	/**
	 * 表单file名称
	 * 
	 * @param string $key
	 * @return string
	 */
	function allowType($key) {
		return $key = "uploadurl_".$fileInputId;
	}

	/**
	 * 是否需要缩略图
	 * 
	 * @return bool
	 */
	function allowThumb() {
		return false;
	}

	/**
	 * 获取文件路劲
	 * 
	 * @return array
	 */
	function getFilePath($currUpload) {
		global $timestamp,$o_mkdir;
		$filename	= date("YmdHis", time()).'_'. $this->fileInputId .'.'. $currUpload['ext'];
		$savedir	= 'advpic/';
		return array($filename, $savedir);
	}
	
	/**
	 * 这边可以进行数据库更新操作等
	 * 
	 * @return bool
	 */
	function update($uploaddb) {
		return $uploaddb;
	}
	
	/**
	 * 获取图片路劲
	 * 
	 * @return string
	 */
	function getImagePath() {
		$imagePath = geturl($this->fileName);
		if ($imagePath[0]) return $imagePath[0];
		return '';
	}

	function getAttachs() {
		return $this->attachs;
	}
}
?>