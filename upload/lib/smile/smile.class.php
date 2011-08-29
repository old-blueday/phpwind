<?php
/**
 * 记录表情服务类文件
 * 
 * @package Smile
 */

!defined('P_W') && exit('Forbidden');

/**
 * 记录表情服务对象
 * 
 * @package Smile
 */
class PW_Smile {
	var $_allowedFiles = array(
		"gif",
		"bmp",
		"jpeg",
		"jpg",
		"png"
	);
	
	function findByType($typeId = 0) {
		$typeId = intval($typeId);
		$smileDb = $this->_getSmileDB();
		$smileBaseUrl = $this->_getSmileBaseUrl($typeId);
		
		$smiles = array();
		foreach ($smileDb->findByTypeId($typeId) as $smile) {
			$smile['url'] = $smileBaseUrl . $smile['path'];
			if ($smile['name']) $smile['tag'] = "[s:" . $smile['name'] . "]";
			$smiles[$smile['path']] = $smile;
		}
		return $smiles;
	}
	
	function findNewInType($typeId = 0, $existSmileFiles = array()) {
		$smileBasePath = $this->_getSmileBaseDir($typeId);
		$smileBaseUrl = $this->_getSmileBaseUrl($typeId);
		
		$newSmiles = array();
		$dirPointer = opendir($smileBasePath);
		while ($fileName = readdir($dirPointer)) {
			if (!$this->_isSmileFileAllowed($fileName)) continue;
			if (!in_array($fileName, $existSmileFiles)) {
				$newSmiles[$fileName] = array(
					'path' => $fileName,
					'url' => $smileBaseUrl . $fileName
				);
			}
		}
		closedir($dirPointer);
		return $newSmiles;
	}
	
	function adds($typeId = 0, $smiles) {
		$typeId = intval($typeId);
		
		if (!is_array($smiles) || !count($smiles) || !is_array(current($smiles))) return 0;
		
		$exists = $this->findByType();
		
		$addCount = 0;
		$smileDb = $this->_getSmileDB();
		foreach ($smiles as $smile) {
			if (isset($exists[$smile['path']])) continue;
			$addCount += (bool) $smileDb->add(array(
				'typeid' => $typeId,
				'name' => $smile['name'],
				'path' => $smile['path'],
				'vieworder' => $smile['order']
			));
		}
		return $addCount;
	}
	
	function updates($updateSmiles) {
		if (!is_array($updateSmiles) || !count($updateSmiles) || !is_array(current($updateSmiles))) return 0;
		
		$updateCount = 0;
		$smileDb = $this->_getSmileDB();
		foreach ($updateSmiles as $smileId => $smile) {
			$addCount += $smileDb->update($smileId, array(
				'name' => $smile['name'],
				'vieworder' => $smile['order']
			));
		}
		return $addCount;
	}
	
	function delete($smileId) {
		$smileId = intval($smileId);
		$smileDb = $this->_getSmileDB();
		return $smileDb->delete($smileId);
	}
	
	
	function _isSmileFileAllowed($fileName) {
		$fileValue = explode(".",$fileName);
		if(!$fileValue) return false;
		return in_array(strtolower(end($fileValue)), $this->_allowedFiles);
	}
	
	function _getSmileBaseUrl($typeId = 0) {
		return $GLOBALS['imgpath'] . "/post/smile/write/";
	}
	
	function _getSmileBaseDir($typeId = 0) {
		return $GLOBALS['imgdir'] . "/post/smile/write/";
	}
	
	/**
	 * Get PW_SmileDB
	 * 
	 * @access protected
	 * @return PW_SmileDB
	 */
	function _getSmileDB() {
		return L::loadDB('Smile', 'smile');
	}

}
