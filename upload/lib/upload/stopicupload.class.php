<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('upload', '', false);

/**
 * 专题图片上传类
 * 
 * @package upload
 * @author	zhuli
 * @abstract
 */
class StopicUpload extends uploadBehavior{
 	var $db;
	var $atype;
	var $attachs;
	var $inputId;
	
	/**
	 * 初始化
	 * 
	 * @return 
	 */
	 function StopicUpload($inputId) {
		global $db;
		parent::uploadBehavior();
		$this->db =& $db;
		$this->inputId = $inputId;
		
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
	 * @return bool
	 */
	function allowType($key) {
		return $key == "image_upload_".$this->inputId;
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
		$filename	= date("YmdHis", time()).'_'. $this->inputId .'.'. $currUpload['ext'];
		$savedir	= 'stopic/img/';
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