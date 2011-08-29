<?php
/**
 * 新鲜事评论表DAO
 * 
 * @package PW_Weibo_CommentDB
 * @author suqian
 * @access private
 */

!defined('P_W') && exit('Forbidden');

class PW_Weibo_CommentDB extends BaseDB {

	var $_tableName = 'pw_weibo_comment';	
	var $_foreignTableName = 'pw_weibo_cmrelations';
	var $_primaryKey = 'cid';

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
	function getCommentsByMid($mid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($mid)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE  mid = '.$this->_addSlashes($mid).'  ORDER BY postdate DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getCommentsCountByMid($mid){
		if (!$this->_isLegalNumeric($mid)){
			return 0;
		} 
		$sql = 'SELECT count(*) FROM '.$this->_tableName.' WHERE  mid = '.$this->_addSlashes($mid);
		return $this->_db->get_value($sql);
	}

	function getCidsOfCommentsByMid($mid) {
		if (empty($mid)) {
			return array();
		}
		$mid = is_array($mid) ? $mid : array($mid);
		$sql = 'SELECT cid FROM ' . $this->_tableName . ' WHERE mid IN (' . S::sqlImplode($mid) . ')';
		$array = array();
		$query = $this->_db->query($sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$array[] = $rt['cid'];
		}
		return $array;
	}
	
	function getUserReceiveComments($uid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_foreignTableName.' a LEFT JOIN '.$this->_tableName.' b ON a.cid = b.cid WHERE  a.uid = '.$this->_addSlashes($uid).' AND b.uid <> '.$this->_addSlashes($uid).' ORDER BY a.cid DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getUserReceiveCommentsCount($uid){
		if (!$this->_isLegalNumeric($uid)){
			return array();
		} 
		$sql = 'SELECT count(*) FROM '.$this->_foreignTableName.' a LEFT JOIN '.$this->_tableName.' b ON a.cid = b.cid WHERE  a.uid = '.$this->_addSlashes($uid);
		return  $this->_db->get_value($sql);
	}

	function getUserSendComments($uid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE  uid = '.$this->_addSlashes($uid).'  ORDER BY postdate DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getUserCommentOfRelpays($uid,$mid,$cuid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid) || !$this->_isLegalNumeric($mid) || !$this->_isLegalNumeric($cuid)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT a.*,b.cid,b.uid as cuid FROM '.$this->_tableName.' a LEFT JOIN '.$this->_foreignTableName.' b ON a.cid = b.cid WHERE  a.uid = '.$this->_addSlashes($uid).' AND a.mid = '.$this->_addSlashes($mid).' AND b.uid = '.$this->_addSlashes($cuid).' ORDER BY a.postdate DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getUserCommentOfRelpaysCount($uid,$mid,$cuid){
		if (!$this->_isLegalNumeric($uid) || !$this->_isLegalNumeric($mid) || !$this->_isLegalNumeric($cuid)){
			return array();
		} 
		$sql = 'SELECT count(*) FROM '.$this->_tableName.' a LEFT JOIN '.$this->_foreignTableName.' b ON a.cid = b.cid WHERE a.uid = '.$this->_addSlashes($uid).' AND a.mid = '.$this->_addSlashes($mid).' AND b.uid = '.$this->_addSlashes($cuid);
		return  $this->_db->get_value($sql);
	}
	
	/**
	 * 取得n天内评论次数最多的新鲜事Id
	 * @param int $num 获取记录条数
	 * @return array
	 */
	function getHotComment($num,$time){
		if(!$time || !$num) return array();
		$query = $this->_db->query("SELECT mid,count(mid) as counts FROM " . $this->_tableName . ' WHERE postdate > ' . S::sqlEscape($time) . ' GROUP BY mid ORDER BY counts DESC,postdate DESC'. $this->_limit($num));
		return array_keys($this->_getAllResultFromQuery($query,'mid'));
	}
	
	function deleteCommentByCids($cids){
		if(empty($cids)){
			return false;
		}
		$cids = is_array($cids) ? $cids : array($cids);
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE cid  IN (' . S::sqlImplode($cids) . ') ';
		$query = $this->_db->query($sql);
		return true;
	}
	
	function deleteCommentsByUid($uid){
		if(!$this->_isLegalNumeric($uid)){
			return false;
		}
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE uid = '.$this->_addSlashes($uid);
		$query = $this->_db->query($sql);
		return true;
	}
	
	function deleteCommentsByMid($mid){
		if(!$this->_isLegalNumeric($mid)){
			return false;
		}
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE mid = ' . $this->_addSlashes($mid);
		$query = $this->_db->query($sql);
		return true;
	}
	
	//this function for mysql server > 4
	function unionDeleteCommentsByMid($mids) {
		if (empty($mids)) {
			return false;
		}
		$mids = is_array($mids) ? $mids : array($mids);
		$this->_db->update('DELETE ' . ($this->_db->server_info() > '4.1' ? 'a,b' : $this->_tableName . ',' . $this->_foreignTableName) .  " FROM $this->_tableName a LEFT JOIN $this->_foreignTableName b ON a.cid = b.cid WHERE a.mid IN (" . S::sqlImplode($mids) . ')');
		return true;
	}
	
	function adminSearch($uids,$contents,$startDate,$endDate,$orderby,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage)){
			return array();
		} 
		$sqlAdd = '';
		if($uids && is_array($uids)){
			$sqlAdd .= ' AND uid IN (' . S::sqlImplode($uids) . ') ';
		}
		if($contents){
			$sqlAdd .= ' AND content like '.S::sqlEscape('%'.$contents.'%');
		}
		if($startDate && is_numeric($startDate)){
			$sqlAdd .= ' AND postdate >= ' . S::sqlEscape($startDate);
		}
		
		if($endDate && is_numeric($endDate)){
			$sqlAdd .= ' AND postdate <= ' . S::sqlEscape($endDate);
		}
		$orderby = in_array($orderby,array('desc','asc')) ? $orderby : 'desc';
		$sql = 'SELECT count(*) FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		$total =  $this->_db->get_value($sql);
		
		$sqlAdd .= ' ORDER BY postdate '.$orderby;
		$offset = ($page - 1) * $perpage;
		$sqlAdd .= $this->_Limit($offset,$perpage);
		
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		$query = $this->_db->query($sql);
		$result =  $this->_getAllResultFromQuery($query);
		return array($total,$result);
	}
	
	function _isLegalNumeric($id){
		return intval($id) > 0;
	}
}
?>