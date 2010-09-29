<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_SharelinksDB extends BaseDB {
	var $_tableName = "pw_sharelinks";

	/**
	 * 获得最新友情链接数据
	 * @param unknown_type $num
	 * @return multitype:
	 */
	function getNewData($num,$haveLogo=false) {
		$num = (int) $num;
		$_sqlAdd = $haveLogo ? " AND logo<>'' " : " AND logo='' ";
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE ifcheck = '1' $_sqlAdd ORDER BY threadorder ASC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}
}

?>