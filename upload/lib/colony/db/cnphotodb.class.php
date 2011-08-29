<?php
/**
 * 群组照片数据库操作对象
 * 
 * @package CnPhotoDB
 */

!defined('P_W') && exit('Forbidden');
/**
 * 群组照片数据库操作DAO
 * @package PW_CnPhotoDB
 * @author suqian
 * @access
 */
class PW_CnPhotoDB extends BaseDB {
	var $_tableName = 'pw_cnphoto';
	var $_foreignTableName = 'pw_cnalbum';
	var $_primaryKey = 'pid';
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$pid){
		return $this->_update($fieldData,$pid);
	}
	function delete($pid){
		return $this->_delete($pid);
	}
	function get($pid){
		return $this->_get($pid);
	}
	function count(){
		return $this->_count();
	}
	
	function getPrevPhoto($pid,$aid=0){
		if(0 >= intval($pid) || ($aid && 0 >= intval($aid))){
			return array();
		}
		$prev_photo = array();
		if($aid){
			$prev_photo = $this->_db->get_one("SELECT pid,path,ifthumb FROM ".$this->_tableName." WHERE pid < ".$this->_addSlashes($pid)." AND  aid=".$this->_addSlashes($aid)." ORDER BY pid DESC");
		}else{
			$prev_photo = $this->_db->get_one("SELECT MAX(b.pid) AS pid FROM ".$this->_tableName." a LEFT JOIN ".$this->_tableName." b ON a.aid=b.aid AND a.pid>b.pid WHERE a.pid=" . $this->_addSlashes($pid));
		}
		return $prev_photo;	
	}
	
	function getNextPhoto($pid,$aid=0){
		if(0 >= intval($pid) || ($aid && 0 >= intval($aid))){
			return array();
		}
		$next_photo = array();
		if($aid){
			$next_photo = $this->_db->get_one("SELECT pid,path,ifthumb FROM ".$this->_tableName." WHERE pid > ".$this->_addSlashes($pid)." AND  aid=".$this->_addSlashes($aid)." ORDER BY pid");
		}else{
			$next_photo = $this->_db->get_one("SELECT MIN(b.pid) AS pid FROM ".$this->_tableName." a LEFT JOIN ".$this->_tableName." b ON a.aid=b.aid AND a.pid<b.pid WHERE a.pid=" . $this->_addSlashes($pid));
		}
		return $next_photo;	
	}
	
	function getPhotoUnionInfoByPid($pid){
		if(0 >= intval($pid)){
			return array();
		}
		$photoinfo = $this->_db->get_one("SELECT a.*,p.* FROM ".$this->_tableName." p LEFT JOIN ".$this->_foreignTableName." a ON p.aid=a.aid WHERE pid=" . $this->_addSlashes($pid));
		return $photoinfo;
	}
	
	function getPhotoInfoByPid($pid){
		if(0 >= intval($pid)){
			return array();
		}
		$photoinfo = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE pid=" . $this->_addSlashes($pid));
		return $photoinfo;
	}
	
	function getPhotosInfoByPids($pid){
		if(empty($pid)){
			return array();
		}
		$pid = is_array($pid) ? $pid : array($pid);
		$sql = "SELECT * FROM ".$this->_tableName." WHERE pid in (" . $this->_getImplodeString($pid).') ORDER BY pid DESC';
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getPhotoNumsGroupByAid($aid = array()){	
		$sqlAdd = '';
		if(!empty($aid)){
			$aid = is_array($aid) ? $aid : array($aid);
			$sqlAdd = ' AND aid IN(' . $this->_getImplodeString($aid) . ')';
		}
		$sql = 'SELECT *,count(*) as sum FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd.' GROUP BY aid ';
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getPhotosInfoByAid($aid,$ifsingle = 0,$desc = 1){
		$aid = intval($aid);
		if(0 >= $aid){
			return array();
		}
		$sqlAdd  = $desc  ? ' ORDER BY pid DESC ' : ' ORDER BY pid ASC ';
		$sqlAdd .= $ifsingle ? ' LIMIT 1 ' : '';
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE aid='. $this->_addSlashes($aid) .$sqlAdd ;
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getPagePhotosInfoByAid($aid,$page =1,$perpage = 20,$desc = 1){
		$aid = intval($aid);
		if ($page <= 0 || $perpage <= 0 || $aid <= 0){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sqlAdd  = $desc  ? ' ORDER BY pid DESC ' : ' ORDER BY pid ASC ';
		$sqlAdd .=  $this->_Limit($offset,$perpage);
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE aid='. $this->_addSlashes($aid) .$sqlAdd ;
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 获取相册最后上传的图片
	 *
	 * @param int $aid 相册id
	 * @param int $num 获取数量
	 * @return array
	 */
	function getLastPid($aid, $num = 5) {
		$aid = intval($aid);
		if(0 >= $aid){
			return array();
		}
		$lastpid = array();
		$sql = ' SELECT pid FROM '.$this->_tableName.' WHERE aid=' . $this->_addSlashes($aid) . ' ORDER BY pid DESC LIMIT '.$num;
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}

	
	function delPhotosByAid($aid){
		$aid = intval($aid);
		if(0 >= $aid){
			return 0;
		}
		$this->_db->update('DELETE FROM '.$this->_tableName.' WHERE aid=' . $this->_addSlashes($aid));
		return $this->_db->affected_rows();
	}
	
	function getDataByPids($pids) {
		$pids = (is_array($pids)) ? S::sqlImplode($pids) : $pids;
		$query = $this->_db->query("SELECT p.*, a.* FROM $this->_tableName p LEFT JOIN pw_cnalbum a ON p.aid = a.aid
			WHERE p.pid IN (" . $pids . ") ");
		return $this->_getAllResultFromQuery($query);
	}
	
	function getDataByPidsAndPrivate($pids,$private = 0) {
		$pids = (is_array($pids)) ? S::sqlImplode($pids) : $pids;
		$query = $this->_db->query("SELECT p.*, a.* FROM $this->_tableName p LEFT JOIN pw_cnalbum a ON p.aid = a.aid
			WHERE p.pid IN (" . $pids . ") AND a.private=".$this->_addSlashes($private));
		return $this->_getAllResultFromQuery($query);
	}
	
}
