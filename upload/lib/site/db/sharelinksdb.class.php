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
		$_sqlAdd = $haveLogo ? " AND logo <> '' " : " AND logo='' ";
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE ifcheck = '1' $_sqlAdd ORDER BY threadorder ASC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 按照分类、是否有logo查找链接信息
	 * 
	 * @param int $num 条数
	 * @param bool false 是否有logo
	 * @param array $sids 链接ID数组
	 * @return array 友情链接分类信息
	 */
	function getData($num,$sids,$haveLogo=false) {
		$num = (int) $num;
		$num && $limit = $this->_Limit(0,$num);
		$haveLogo && $_sqlAdd = " AND logo <> '' ";
		is_array($sids) && $_sqlsids = " AND sid IN(" . S::sqlImplode($sids) . ")";
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE ifcheck = '1' ".$_sqlsids. $_sqlAdd ." ORDER BY threadorder ASC $limit");
		return $this->_getAllResultFromQuery($query);
	}
}
?>