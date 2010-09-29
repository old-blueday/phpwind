<?php
!defined('P_W') && exit('Forbidden');

class PW_TplService {
	function getTpl($tplid) {
		$tplDb = $this->_getTplDB();
		return $tplDb->getData($tplid);
	}

	function insertTpl($data) {
		$tplDb = $this->_getTplDB();
		$tplDb->insertData($data);
	}

	function updateTpl($id, $data) {
		$tplDb = $this->_getTplDB();
		$tplDb->updataById($id, $data);
	}
	function _getTplDB() {
		return L::loadDB('Tpl', 'area');
	}
}