<?php
!defined('P_W') && exit('Forbidden');
class PW_PushDataDB extends BaseDB {
	var $_tableName = "pw_pushdata";

	function get($id) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
		return $this->_initData($temp);
	}
	function delete($id) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
	}
	function insertData($array){
		global $timestamp;
		!$array['vieworder'] && $array['vieworder'] = 0;
		if (!isset($array['invokepieceid']) ) {
			return null;
		}
		return $this->_insertData($array);
	}
	function update($id,$array){
		global $timestamp;
		$array	= $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update("UPDATE ".$this->_tableName." SET ".S::sqlSingle($array,false)." WHERE id=".S::sqlEscape($id));
	}
	
	function deleteByPiece($invokePieceId) {
		$invokePieceId = (int) $invokePieceId;
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE invokepieceid=".S::sqlEscape($invokePieceId));
	}
	
	function searchPushDatas($array,$page,$perPage=20) {
		$page = (int) $page;
		$page<1 && $page = 1;
		$_sql = $this->_getSearchSQL($array,1);
		if (!$_sql) return array();
		
		$_sql_order = $this->_getOrderSQL($array,'p');
		$temp = array();
		$_sql_final = "SELECT p.*,ip.invokename,i.title as invoketitle,i.sign FROM ".$this->_tableName." p LEFT JOIN pw_invokepiece ip ON p.invokepieceid=ip.id LEFT JOIN pw_invoke i ON ip.invokename=i.name $_sql $_sql_order ".S::sqlLimit(($page-1)*$perPage,$perPage);

		$query	= $this->_db->query($_sql_final);
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[$rt['id']] = $this->_initData($rt);
		}
		return $temp;
	}
	function searchPushdatasCount($array) {
		$_sql = $this->_getSearchSQL($array);
		if (!$_sql) return 0;
		$_sql_final = "SELECT COUNT(*) AS count FROM ".$this->_tableName."$_sql";
		return $this->_db->get_value($_sql_final);
	}
	function getEffectInvokePushDatas($invokepieceid,$num=10) {
		global $timestamp;
		$temp = array();

		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE invokepieceid=".S::sqlEscape($invokepieceid)." AND ifverify=0 AND starttime<=".S::sqlEscape($timestamp)." ORDER BY vieworder DESC,starttime DESC ".S::sqlLimit(0,$num));
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[$rt['id']] = $this->_initData($rt);
		}
		return $temp;
	}
	

	function getStruct(){
		return array('id','invokepieceid','editor','starttime','vieworder','data','titlecss','pushtime','ifverify','ifbusiness');
	}
	function _insertData($array){
		$array	= $this->_checkData($array);
		if (!$array || !$array['invokepieceid'] || !$array['data']) {
			return null;
		}
		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	
	function _initData($temp) {
		$temp = $this->_unserializeData($temp);
		$temp = $this->_colorTitle($temp);
		return $temp;
	}
	function _colorTitle($rt) {
		global $timestamp;
		if ($rt['titlecss'] && $rt['data']['title'] && (!$rt['titlecss']['endtime'] || $rt['titlecss']['endtime']>$timestamp)) {
			if ($rt['titlecss']['color']) $rt['data']['title'] = "<font color=".$rt['titlecss']['color'].">".$rt['data']['title']."</font>";
			if ($rt['titlecss']['b']) $rt['data']['title'] = "<b>".$rt['data']['title']."</b>";
			if ($rt['titlecss']['i']) $rt['data']['title'] = "<i>".$rt['data']['title']."</i>";
			if ($rt['titlecss']['u']) $rt['data']['title'] = "<u>".$rt['data']['title']."</u>";
		}
		return $rt;
	}
	
	function _getOrderSQL($array,$pix='') {
		if ($array['invokepiece'] || $array['invoke']) {
			return ' ORDER BY p.vieworder DESC,p.starttime DESC ';
		}
		return ' ORDER BY p.id DESC ';
	}
	
	function _getSearchSQL($array,$ifJoin=0) {
		if (!$array || !is_array($array)) return false;
	
		$invokepieceIdField = $ifJoin ? 'p.invokepieceid' : 'invokepieceid';
		$ifverifyField = $ifJoin ? 'p.ifverify' : 'ifverify';
		
		$temp = $array['ifverify'] ? " WHERE $ifverifyField = 1 " : " WHERE $ifverifyField = 0 ";
		if ($array['invokepiece']) {
			$temp .= " AND $invokepieceIdField=".S::sqlEscape($array['invokepiece']);
		} elseif ($array['invoke']) {
			$invokeService = L::loadClass('invokeservice', 'area');
			$invokepieces = $invokeService->getInvokePieces($array['invoke']);
			$invokepieces = array_keys($invokepieces);
			if (!$invokepieces) return false;

			$temp .= " AND $invokepieceIdField IN (".S::sqlImplode($invokepieces).")";
		} elseif ($array['alias']) {
			$invokeService = L::loadClass('invokeservice', 'area');
			$portalPageService = L::loadClass('portalpageservice', 'area');
			$signType = $portalPageService->getSignType($array['alias']);
			$invokepieces = $invokeService->getEffectPageInvokePieces($signType,$array['alias']);
			if (!$invokepieces) return false;
			$temp .= " AND $invokepieceIdField IN (".S::sqlImplode($invokepieces).")";
		}
		return $temp;
		//TODO
	}

	function _checkData($data){
		if (!is_array($data) || !count($data)) return false;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		return $data;
	}
	function _serializeData($array) {
		if ($array['data'] && is_array($array['data'])) {
			foreach ($array['data'] as $key=>$value) {
				if ($key == 'tagrelate') continue;
				$array['data'][$key] = stripslashes($value);
			}
			$array['data']	= serialize($array['data']);
		}
		if ($array['titlecss'] && is_array($array['titlecss'])) {
			$array['titlecss'] = serialize($array['titlecss']);
		}
		return $array;
	}
	function _unserializeData($data) {
		if ($data['data']) $data['data'] = unserialize($data['data']);
		if ($data['titlecss']) $data['titlecss'] = unserialize($data['titlecss']);
		return $data;
	}
	
}
?>