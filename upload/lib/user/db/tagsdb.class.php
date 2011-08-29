<?php
!defined('P_W') && exit('Forbidden');

/**
 * 标签数据层
 * @package  PW_TagsDB
 * @author panjl @2010-12-27
 */
class PW_TagsDB extends BaseDB {
	var $_tableName 	= 	'pw_membertags';
	var $_primaryKey 	= 	'tagid';
	var $_membertags_detail = "pw_membertags_relations";
	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int
	 */
	function insert($fieldsData) {
		$fieldsData = $this->_checkData($fieldsData);
		return $this->_insert($fieldsData);
	}

	/**
	 * 更新
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * 
	 */
	function update($fieldsData,$tagid) {
		$fieldsData = $this->_checkData($fieldsData);
		return $this->_update($fieldsData,$tagid);
	}
	
	/**
	 * 更新标签使用数量
	 * 
	 * @param int $tagid 标签
	 * @return boolean
	 */
	function updateNumByTagId($tagid,$num) {
		if(!$tagid) return false;
		return (bool)$this->_db->update("update " . $this->_tableName . " SET num = num + " . $num . " WHERE tagid = " . $this->_addSlashes($tagid));
	}
	
	/**
	 * 批量删除标签
	 * 
	 * @param array $tagids
	 * @return boolean
	 */
	function deleteTagsByTagIds($tagids) {
		if(!S::isArray($tagids)) return false;
		pwQuery::delete($this->_tableName, "tagid in(:tagid)", array($tagids));
		return (bool)$this->_db->affected_rows();
	}
	
	/**
	 * 根据标签ID获取标签信息
	 * 
	 * @param int $tagid 标签
	 * @return array
	 */
	function getTagsByTagid($tagid) {
		if(!$tagid) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE tagid = " . $this->_addSlashes($tagid));
	}


	/**
	 * 根据标签ID批量获取标签信息
	 * 
	 * @param int $uid 用户ID
	 * @return array
	 */
/*
	function getTagsByTagids($tagids) {
		if(!S::isArray($tagids)) return array();
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE tagid IN(" . S::sqlImplode($tagids) . ")");
		return $this->_getAllResultFromQuery($query);
	}
*/
	function getTagsByUid($uid) {
		if(!$uid) return array();
		$query = $this->_db->query("SELECT t.tagid,t.tagname FROM " . $this->_tableName . " t LEFT JOIN " . $this->_membertags_detail . " mt USING(tagid) WHERE mt.userid = " . $this->_addSlashes($uid) . " ORDER BY crtime DESC");
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
		$query = $this->_db->query("SELECT t.tagid,t.tagname,mt.userid FROM " . $this->_tableName . " t LEFT JOIN " . $this->_membertags_detail . " mt USING(tagid) WHERE mt.userid IN(" . S::sqlImplode($uids) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据标签名获取标签信息
	 * 
	 * @param string $name 标签名
	 * @return int stid值
	 */
	function getTagsByName($name) {
		if (!is_string($name)) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE tagname = " . $this->_addSlashes($name));
	}
	
	/**
	 * 设置是否允许热门标签
	 * 
	 * @param array $tagids
	 * @return boolean
	 */
	function setHotByTagids($tagids,$ifhot) {
		if(!S::isArray($tagids)) return false;
		pwQuery::update($this->_tableName, "tagid in(:tagid)", array($tagids), array('ifhot'=>$ifhot));
		return $this->_db->affected_rows();
	}
	
	/**
	 * 热门标签top100
	 * 
	 * @param int $num
	 * @return int stid值
	 */
	function getTagsByNum($num = 100) {
		$num = ($num < 100) ? $num : 100;
		$query = $this->_db->query("SELECT tagid,tagname,num FROM  " . $this->_tableName . " WHERE ifhot = 1 ORDER BY num DESC " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据条件搜索标签信息
	 * 
	 * @param string $name 标签名
	 * @param int $ifhot 是否允许热门标签
	 * @param int $startnum min使用数 
	 * @param int $endnum max使用数 
	 * @param int $page
	 * @param int $perpage
	 * @return array
	 */
	function getTagsByCondition($name, $ifhot, $startnum, $endnum, $page = 1, $perpage = 20, $precision = null) {
		$addsql = " WHERE 1";
		if ($name != '' && !$precision) $addsql .= ' AND tagname = ' . $this->_addSlashes($name);
		if ($name != '' && ($precision == 'like')) $addsql .= ' AND tagname like ' . $this->_addSlashes('%'.$name.'%');
		if ($startnum != '') $addsql .= ' AND num >= ' . $this->_addSlashes($startnum);
		if ($endnum != '') $addsql .= ' AND num <= ' . $this->_addSlashes($endnum);
		if ($ifhot != '') $addsql .= ' AND ifhot = ' . $this->_addSlashes($ifhot);
		$total =  $this->_db->get_value('SELECT count(*) FROM  ' . $this->_tableName . ' ' . $addsql);
		$addsql .= ' ORDER BY num DESC';
		$offset = ($page - 1) * $perpage;
		$addsql .= $this->_Limit($offset,$perpage);
		$query = $this->_db->query('SELECT * FROM  ' . $this->_tableName .' '. $addsql);
		return array($total,$this->_getAllResultFromQuery($query));
	}

	/**
	 * 加载数据表key
	 * 
	 * @return array key
	 */
	function _checkData($data) {
		if(!S::isArray($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}

	/**
	 * 加载数据表key
	 * 
	 * @return array key
	 */
	function getStruct() {
		return array('tagid','tagname','num','ifhot');
	}
}