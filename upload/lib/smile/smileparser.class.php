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
class PW_SmileParser {
	var $_smileParseConfig = null;
	
	function parse($content) {
		if ('' == $content) return '';
		$parseConfig = $this->_getSmileParseConfig();
		return str_replace(array_keys($parseConfig), $parseConfig, $content);
	}
	
	function _getSmileParseConfig() {
		if (null === $this->_smileParseConfig) {
			$parseConfig = array();
			$smileService = L::loadClass('smile', 'smile');
			foreach ($smileService->findByType() as $smile) {
				$parseConfig[$smile['tag']] = '<img src="'.$smile['url'].'" style="vertical-align:top;margin:0 3px 0 0;" />'; //@todo hard coded
			}
			$this->_smileParseConfig = $parseConfig;
		}
		return $this->_smileParseConfig;
	}
}
