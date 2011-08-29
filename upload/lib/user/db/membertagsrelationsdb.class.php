<?php
!defined('P_W') && exit('Forbidden');

/**
 * 用户标签数据层
 * @package  PW_MemberTagsRelationsDB
 * @author phpwind @2010-12-27
 */
class PW_MemberTagsRelationsDB extends BaseDB {
	var $_tableName 	= 	'pw_membertags_relations';

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 */
	function insertMemberTags($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		pwQuery::insert($this->_tableName, $fieldsData);
		return true;
	}

	/**
	 * 删除
	 * 
	 * @param int $tagid  标签ID
	 * @param int $userid
	 * @return boolean
	 */
	function deleteMemberTags($tagid,$userid){
		$tagid = intval($tagid);
		$userid = intval($userid);
		if ($tagid < 1 || $userid < 1) return false;
		return (bool)pwQuery::delete($this->_tableName, "tagid=:tagid and userid=:userid", array($tagid,$userid));
	}

	/**
	 * 根据标签ID批量删除
	 * 
	 * @param int $tagids  标签ID数组
	 * @return boolean
	 */
	function deleteMemberTagsByTagId($tagids){
		if(!S::isArray($tagids)) return false;
		return (bool)pwQuery::delete($this->_tableName, "tagid in(:tagid)", array($tagids));
	}
	
	/**
	 * 根据用户获取标签tagids
	 * 
	 * @param int $userid
	 * @return array
	 */
	function getTagIdsByUid($userid) {
		$userid = intval($userid);
		if ($userid <= 0) return array();
		$query = $this->_db->query("SELECT tagid FROM $this->_tableName WHERE userid = " . $this->_addSlashes($userid));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 模糊统计
	 * @return int
	 */
	function countUidsByTagid($tagid) {
		$tagid = intval($tagid);
		if ($tagid < 1) return false;
		return $this->_db->get_value('SELECT count(*) FROM  ' . $this->_tableName . ' WHERE tagid = ' . $this->_addSlashes($tagid));
	}
	
	/**
	 * 根据标签ID查询用户 
	 * 
	 * @param int $tagid
	 * @return int
	 */
	function getUidsByTagid($tagid, $start, $num) {
		$tagid = intval($tagid);
		$start = intval($start);
		$num = intval($num);
		if ($tagid <= 0 || $start < 0 || $num < 1) return array(0,array());
		$total =  $this->countUidsByTagid($tagid);
		$query = $this->_db->query('SELECT userid FROM ' . $this->_tableName . ' WHERE tagid = ' . $this->_addSlashes($tagid) . ' ' . $this->_Limit($start,$num));
		return array($total,$this->_getAllResultFromQuery($query));
	}

	/**
	 * 根据标签tagids批量获取用户uids
	 * 
	 * @param int $tagids
	 * @return array
	 */
	function getUidsByTagids($tagids) {
		if(!S::isArray($tagids)) return array();
		$query = $this->_db->query('SELECT distinct(userid) FROM ' . $this->_tableName . ' WHERE tagid IN(' . S::sqlImplode($tagids) . ')');
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 统计用户标签
	 * 
	 * @param int $userid
	 * @return bool|int
	 */
	function countTagsByUid($userid) {
		$userid = intval($userid);
		if ($userid <= 0) return false;
		return $this->_db->get_value("SELECT count(*) FROM $this->_tableName WHERE userid = " . $this->_addSlashes($userid));
	}
	
	/**
	 * 根据uid和tagid查找
	 * 
	 * @param int $userid
	 * @param int $tagid
	 * @return array
	 */
	function getTagsByTagidAndUid($tagid,$userid) {
		$tagid = intval($tagid);
		$userid = intval($userid);
		if ($tagid < 1 || $userid < 1) return array();
		return $this->_db->get_one("SELECT tagid,userid FROM $this->_tableName WHERE tagid = " . $this->_addSlashes($tagid) . " AND userid = " . $this->_addSlashes($userid));
	}
}