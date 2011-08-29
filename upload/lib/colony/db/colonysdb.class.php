<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_ColonysDB extends BaseDB {
	var $_tableName = "pw_colonys";
	var $_primaryKey = 'id';
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$id){
		return $this->_update($fieldData,$id);
	}
	function delete($id){
		return $this->_delete($id);
	}
	function get($id){
		return $this->_get($id);
	}
	function count(){
		return $this->_count();
	}
	function getsIds($ids){
		$query = $this->_db->query ( "SELECT id as colonyid,cname,cnimg FROM " . $this->_tableName. " WHERE id in( ".S::sqlImplode($ids).") " );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getById($id){
		if(!$this->_check() || $id < 1 ) return false;
		return $this->_db->get_one ( "SELECT id as colonyid,cname,cnimg FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . "=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
	}
	function getSortByTypeAndClassId($type,$classID,$num) {
		$_sqlAdd = $this->_getClassidAdd($classID);
		$white = array('tnum','pnum','members','todaypost','createtime','credit');
		if (!$type || !in_array($type,$white)) $type = 'tnum';
		$_sql_type = $type=='credit' ? 'credit' : 'c.'.$type;
		$_sql_credit = $this->_getCreditAdd($type);

		$_sql = "SELECT c.id,c.styleid,c.cname,c.tnum,c.pnum,c.members,c.todaypost,c.createtime,c.cnimg,c.descrip,s.cname as stylename $_sql_credit FROM ".$this->_tableName." c LEFT JOIN pw_cnstyles s ON c.styleid=s.id WHERE 1 $_sqlAdd ORDER BY $_sql_type DESC ".S::sqlLimit(0,$num);
		$temp = array();
		$query = $this->_db->query($_sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[$rt['id']]	= $rt;
		}
		return $temp;
	}
	function _getClassidAdd($classID) {
		$classID = (int) $classID;
		if (!$classID) {
			return '';
		}
		$o_styledb = L::config('o_styledb', 'o_config');
		if (!isset($o_styledb[$classID])) {
			return '';
		}
		if ($o_styledb[$classID]['upid'] != '0') {
			return ' AND c.styleid=' . S::sqlEscape($classID);
		}
		$array = array();
		foreach ($o_styledb as $k => $v) {
			if ($v['upid'] == $classID) {
				$array[] = $k;
			}
		}
		return $array ? ' AND c.styleid IN(' . S::sqlImplode($array) . ')' : ' AND c.styleid=' . S::sqlEscape($classID);
	}
	function _getCreditAdd($type) {
		if ($type!='credit') return '';
		//* include pwCache::getPath(D_P . 'data/bbscache/o_config.php');
		extract(pwCache::getData(D_P . 'data/bbscache/o_config.php', false));
		$tnum = $o_groups_upgrade['tnum'] ? $o_groups_upgrade['tnum'] : 0;
		$pnum = $o_groups_upgrade['pnum'] ? $o_groups_upgrade['pnum'] : 0;
		$members = $o_groups_upgrade['members'] ? $o_groups_upgrade['members'] : 0;
		$albumnum = $o_groups_upgrade['albumnum'] ? $o_groups_upgrade['albumnum'] : 0;
		$photonum = $o_groups_upgrade['photonum'] ? $o_groups_upgrade['photonum'] : 0;
		$writenum = $o_groups_upgrade['writenum'] ? $o_groups_upgrade['writenum'] : 0;
		$activitynum = $o_groups_upgrade['activitynum'] ? $o_groups_upgrade['activitynum'] : 0;
		return ",(tnum*$tnum+pnum*$pnum-tnum*$pnum+members*$members+albumnum*$albumnum+photonum*$photonum+writenum*$writenum+activitynum*$activitynum) AS credit";
	}
	/**
	 * 注意只提供搜索服务
	 */
	function countSearch($keywords){
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE cname like ".S::sqlEscape("%$keywords%")." LIMIT 1" );
		return ($result) ? $result['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 */
	function getSearch($keywords,$offset,$limit){
		$query = $this->_db->query ("SELECT c.*,s.cname as sname FROM " . $this->_tableName . " c LEFT JOIN pw_cnstyles s ON c.styleid = s.id WHERE c.cname like ".S::sqlEscape("%$keywords%")." LIMIT ".$offset.",".$limit);
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function countLatestColonys() {
		$total = $this->_db->get_value("SELECT COUNT(*) as total FROM " . $this->_tableName ." LIMIT 1" );
		return ($total<500) ? $total :500;
	}
	
	function getLatestColonys($offset, $limit) {
		$query = $this->_db->query ("SELECT c.*,s.cname as sname FROM " . $this->_tableName . " c LEFT JOIN pw_cnstyles s ON c.styleid = s.id ".$this->_Limit($offset, $limit));
		return $this->_getAllResultFromQuery($query);
	}
}
?>