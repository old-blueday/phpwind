<?php
!defined('P_W') && exit('Forbidden');
C::loadClass('sourcetype', 'base', false);
class PW_NoneSourceType extends PW_SourceType {
	function getSourceData($sourceId) {
		return array();
	}
	function getSourceUrl($sourceId) /*Abstract function*/ {
		return '';
	}
	function getSourceType() {
		return '';
	}

}