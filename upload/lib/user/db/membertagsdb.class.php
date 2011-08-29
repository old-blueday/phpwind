<?php
!defined('P_W') && exit('Forbidden');

/**
 * 标签数据层
 * @package  PW_MemberTagsDB
 * @author panjl @2010-12-27
 */
class PW_MemberTagsDB extends BaseDB {
	var $_tableName 	= 	'pw_membertags';
	var $_primaryKey 	= 	'tagid';
	var $_membertags_relations = "pw_membertags_relations";

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int
	 */
	function insert($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		return $this->_insert($fieldsData);
	}

	/**
	 * 更新
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int
	 */
	function update($fieldsData,$tagid) {
		$tagid = intval($tagid);
		if ($tagid < 1 || !S::isArray($fieldsData)) return false;
		return $this->_update($fieldsData,$tagid);
	}
	
	/**
	 * 更新标签使用数量
	 * 
	 * @param int $tagid 标签
	 * @return boolean
	 */
	function updateNumByTagId($tagid,$num) {
		$tagid = intval($tagid);
		$num = intval($num);
		if ($tagid < 1 || !$num) return false;
		return (bool)$this->_db->update("UPDATE " . $this->_tableName . " SET num = num + " . $num . " WHERE tagid = " . $this->_addSlashes($tagid));
	}

	/**
	 * 批量删除标签
	 * 
	 * @param array $tagids
	 * @return boolean
	 */
	function deleteTagsByTagIds($tagids) {
		if(!S::isArray($tagids)) return false;
		return pwQuery::delete($this->_tableName, "tagid in(:tagid)", array($tagids));
	}
	
	/**
	 * 根据标签ID获取标签信息
	 * 
	 * @param int $tagid 标签
	 * @return array
	 */
	function getTagsByTagid($tagid) {
		$tagid = intval($tagid);
		if ($tagid < 1) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE tagid = " . $this->_addSlashes($tagid));
	}

	
	/**
	 * 根据用户uid获取标签信息
	 * 
	 * @param int $tagid 标签
	 * @return array
	 */
	function getTagsByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		$query = $this->_db->query("SELECT t.tagid,t.tagname,mt.userid,mt.userid as uid FROM " . $this->_membertags_relations . " mt LEFT JOIN " . $this->_tableName . " t USING(tagid) WHERE mt.userid = " . $this->_addSlashes($uid) . " ORDER BY mt.crtime DESC");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 根据用户uids批量获取标签信息
	 * 
	 * @param int $uids 用户uids数组
	 * @return array
	 */
	function getTagsByUids($uids) {
		if(!S::isArray($uids)) return array();
		$query = $this->_db->query("SELECT t.tagid,t.tagname,mt.userid FROM " . $this->_membertags_relations . " mt LEFT JOIN " . $this->_tableName . " t USING(tagid) WHERE mt.userid IN(" . S::sqlImplode($uids) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据标签名获取标签信息
	 * 
	 * @param string $tagName 标签名
	 * @return int stid值
	 */
	function getTagsByTagName($tagName) {
		$tagName = trim($tagName);
		if ($tagName == '') return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE tagname = " . $this->_addSlashes($tagName));
	}
	
	/**
	 * 设置是否允许热门标签
	 * 
	 * @param array $tagids
	 * @return boolean
	 */
	function setHotByTagids($tagids,$ifhot) {
		$ifhot = intval($ifhot);
		if($ifhot < 0 || !S::isArray($tagids)) return false;
		return pwQuery::update($this->_tableName, "tagid in(:tagid)", array($tagids), array('ifhot'=>$ifhot));
	}
	
	/**
	 * 热门标签top100
	 * 
	 * @param int $num
	 * @return array
	 */
	function getTagsByNum($num) {
		$num = intval($num);
		if($num < 0) return array();
		$query = $this->_db->query("SELECT tagid,tagname,num FROM  " . $this->_tableName . " WHERE ifhot = 1 ORDER BY num DESC " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 后台根据条件搜索标签信息
	 * @return int
	 */
	function countTagsByCondition($name, $ifhot, $startnum, $endnum) {
		$addsql = " WHERE 1";
		if ($name != '') $addsql .= ' AND tagname like ' . $this->_addSlashes('%'.$name.'%');
		if ($startnum != '') $addsql .= ' AND num >= ' . $this->_addSlashes($startnum);
		if ($endnum != '') $addsql .= ' AND num <= ' . $this->_addSlashes($endnum);
		if ($ifhot != '') $addsql .= ' AND ifhot = ' . $this->_addSlashes($ifhot);
		$sql = 'SELECT count(*) FROM  ' . $this->_tableName . ' ' . $addsql;
		return $this->_db->get_value('SELECT count(*) FROM  ' . $this->_tableName . ' ' . $addsql);
	}

	/**
	 * 后台根据条件搜索标签信息
	 * 
	 * @param string $name 标签名
	 * @param int $ifhot 是否允许热门标签
	 * @param int $startnum min使用数 
	 * @param int $endnum max使用数 
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getTagsByCondition($name, $ifhot, $startnum, $endnum, $start, $num) {
		$start = intval($start);
		$num = intval($num);
		if ($start < 0 || $num < 1) return array();
		$addsql = " WHERE 1";
		if ($name != '') $addsql .= ' AND tagname like ' . $this->_addSlashes('%'.$name.'%');
		if ($startnum != '') $addsql .= ' AND num >= ' . $this->_addSlashes($startnum);
		if ($endnum != '') $addsql .= ' AND num <= ' . $this->_addSlashes($endnum);
		if ($ifhot != '') $addsql .= ' AND ifhot = ' . $this->_addSlashes($ifhot);
		$addsql .= ' ORDER BY num DESC';
		$addsql .= $this->_Limit($start,$num);
		$query = $this->_db->query('SELECT * FROM  ' . $this->_tableName .' '. $addsql);
		return $this->_getAllResultFromQuery($query);
	}
}