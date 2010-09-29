<?php
!function_exists('readover') && exit('Forbidden');
include_once (R_P . 'lib/datanalyse/datanalyseservice.class.php');

class PW_CMSDatanalyseService extends PW_DatanalyseService {
	var $types = array('article');

	function getAllActions($type) {
		$datanalyse = $this->_getDatanalyseServiceByType($type);
		/* @var $datanalyse PW_Articleanalyse */
		return $datanalyse->actions;
	}

	/**
	 * @param string $type
	 * @return Object
	 */
	function _getDatanalyseServiceByType($type) {
		$type = $this->_filterType($type);
		return C::loadClass(strtolower($type) . 'analyse', 'datanalyse/datanalyse');
	}
}
?>