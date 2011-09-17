<?php
!defined('P_W') && exit('Forbidden');

class PW_MembersDB extends BaseDB {
	var $_tableName = "pw_members";
	var $_memberDataTableName = "pw_memberdata";
	var $_memberInfoTableName = "pw_memberinfo";
	var $_singleRightTableName = 'pw_singleright';
	var $_userEducation = 'pw_user_education';
	var $_userCareer = 'pw_user_career';
	var $_primaryKey = 'uid';
	
	function get($id) {
		return $this->_get($id);
	}
	
	function getWithJoin($userId, $withMainTable = true, $withMemberDataTable = false, $withMemberInfoTable = false) {
		$userId = intval($userId);
		if ($userId <= 0) return null;
		if (!$withMainTable && !$withMemberDataTable && !$withMemberInfoTable) return null;
		
		$tables = array('a' => $this->_tableName, 'b' => $this->_memberDataTableName, 'c' => $this->_memberInfoTableName);
		$selects = array('a' => $withMainTable, 'b' => $withMemberDataTable, 'c' => $withMemberInfoTable);
		
		$fields = array();
		$firstTable = null;
		$firstAlias = null;
		$leftJoins = array();
		foreach ($tables as $alias => $tableName) {
			if (!$selects[$alias]) continue;
			$fields[$alias] = $alias . ".*";
			if (null === $firstTable) {
				$firstTable = $tableName;
				$firstAlias = $alias;
			} else {
				$leftJoins[] = " LEFT JOIN " . $tableName . " AS " . $alias . " ON " . $firstAlias . ".uid=" . $alias . ".uid ";
			}
		}
		if ($withMemberDataTable && $withMemberInfoTable) { //TODO refactor
			unset($fields['b']);
			$fields['b'] = "b.*, c.credit AS creditinfo";
		}
		return $this->_db->get_one("SELECT " . implode(',', $fields) . " FROM " . $firstTable . " AS " . $firstAlias . " " . implode(' ', $leftJoins) . " WHERE " . $firstAlias . ".uid=" . $this->_addSlashes($userId));
	}
	
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function update($fieldData, $id) {
		return $this->_update($fieldData, $id);
	}
	
	function updates($fieldData, $ids) {
		if (!$this->_check() || !$fieldData || empty($ids)) return false;
		/**
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE " . $this->_primaryKey . " IN (" . $this->_getImplodeString($ids) . ")");
		**/
		pwQuery::update('pw_members', "uid IN(:uid)" , array($ids), $fieldData);
		return $this->_db->affected_rows();
	}
	
	function increase($userId, $increments) {
		$userId = intval($userId);
		if ($userId <= 0 || !is_array($increments)) return 0;
		
		$incrementStatement = array();
		foreach ($increments as $field => $offset) {
			$offset = intval($offset);
			if (!$offset) continue;
			if ($offset<0){
				$incrementStatement[] = $field . "=" . $field   . $offset;
			}else{
				$incrementStatement[] = $field . "=" . $field . "+" . $offset;
			}
		}
		if (empty($incrementStatement)) return 0;
		
		//* $this->_db->update("UPDATE " . $this->_tableName . " SET " . implode(", ", $incrementStatement) . " WHERE uid=" . $this->_addSlashes($userId));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET " . implode(", ", $incrementStatement) . " WHERE uid=:uid", array($this->_tableName, $userId)));
		return $this->_db->affected_rows();
	}
	
	function delete($id) {	
		return $this->_delete($id);
	}
	
	function count() {
		return $this->_count();
	}
	
	/**
	 * 更新userstatus字段
	 * 
	 * @param int $userId 用户id
	 * @param int $bit 用户状态类型 常量：PW_USERSTATUS_*
	 * @param bool|int $status 状态值，0-false, 1-true, other
	 * @param int $num 所占bit位数
	 * @return int 更新条数
	 */
	function setUserStatus($userId, $bit, $status = true, $num = 1) {
		list($userId, $bit, $num) = array(intval($userId), intval($bit), intval($num));
		if ($userId <= 0 || $bit <= 0 || $num <= 0) return false;
		
		$status = sprintf('%0' . $num . 'b', $status); // to binary
		

		--$bit;
		$userstatus = array();
		$userstatus[] = '&~((pow(2, ' . $num . ') - 1)<<' . $bit . ')'; //alacner said: clean all bits
		for ($i = $num - 1; $i >= 0; $i--) {
			if (isset($status[$i]) && $status[$i]) {
				$userstatus[] = '|(1<<' . $bit . ')';
			} else {
				$userstatus[] = '&~(1<<' . $bit . ')';
			}
			++$bit;
		}
		
		$userstatus = 'userstatus=userstatus' . implode('', $userstatus);
		//* $this->_db->update("UPDATE " . $this->_tableName . " SET $userstatus WHERE uid=" . $this->_addSlashes($userId));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET $userstatus WHERE uid=:uid", array($this->_tableName, $userId)));		
		return $this->_db->affected_rows();
	}
	
	function getUsersByUserNames($userNames) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE username IN(" . S::sqlImplode($userNames) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	function getUsersByUserIds($userIds) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid IN(" . S::sqlImplode($userIds) . ")");
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	function getUserByUserName($userName, $fields = '*') {
		if (!$userName) return false;
		return $this->_db->get_one("SELECT $fields FROM " . $this->_tableName . " WHERE username = " . $this->_addSlashes($userName));
	}
	
	/**
	 * 根据邮件内容获得论坛注册用户
	 * @author papa
	 * @param Array $emails
	 * @return Array:
	 */
	function getUserByUserEmails($emails) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE email IN (" . S::sqlImplode($emails) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据groupid获取用户
	 * 
	 * @param array $groupIds groupId数组
	 * @return array
	 */
	function getUsersByGroupIds($groupIds) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE groupid IN(" . S::sqlImplode($groupIds) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据groupid获取用户
	 * 
	 * @param array $groupIds groupId
	 * @return array
	 */
	function getUsersByGroupId($groupId) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE groupid = " . $this->_addSlashes($groupId));
		return $this->_getAllResultFromQuery($query);
	}
	
	function getUserInfosByUserIds($userIds) {
		$userIds = (is_array($userIds)) ? S::sqlImplode($userIds) : $userIds;
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName . " md ON m.uid=md.uid WHERE m.uid IN(" . $userIds . ")");
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	function findUsersOrderByUserId($limit = 1) {
		$limit = intval($limit);
		if ($limit <= 0) return array();
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " ORDER BY uid DESC LIMIT " . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	
	function findNotBannedUsersOrderByUserId($limit = 1) {
		global $db_uidblacklist;
		$limit = intval($limit);
		if ($limit <= 0) return array();
		$db_uidblacklist && $sqlWhere .= ' AND uid NOT IN (' . $db_uidblacklist . ')';
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE groupid <> 6 ".$sqlWhere." ORDER BY uid DESC LIMIT " . $limit);
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 注意只提供搜索服务
	 * @version phpwind 8.0
	 */
	function countSearch($keywords) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE username like " . S::sqlEscape("%$keywords%") . " LIMIT 1");
		return ($result) ? $result['total'] : 0;
	}
	
	/**
	 * 注意只提供搜索服务
	 * @version phpwind 8.0
	 */
	function getSearch($keywords, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE username like " . S::sqlEscape("%$keywords%") . " LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getMemberAndData($userIds){
		$query = $this->_db->query("SELECT m.uid,m.username,m.gender,m.oicq,m.aliww,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.lastvisit,md.onlinetime,md.starttime FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid WHERE m.uid IN (".S::sqlImplode($userIds).") ");
		return $this->_getAllResultFromQuery($query);
	}
	
	function getLatestUsersCount() {
		$total = $this->_db->get_value("SELECT COUNT(*) as total FROM " . $this->_tableName . " LIMIT 1");
		return ($total<500) ? $total :500;
	}
	
	function getLatestUsers($offset, $limit) {
		$query = $this->_db->query ("SELECT * FROM ".$this->_tableName." ORDER BY uid DESC " .$this->_Limit($offset, $limit));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getMembersAndMemberDataAndMemberInfoByUserIds($userIds, $fieldinfo = ''){
		$query = $this->_db->query (
		"SELECT m.*, m.icon AS micon,
		md.uid as `md.uid`, md.lastmsg,md.postnum,md.rvrc,md.money,md.credit,md.currency,md.lastvisit,md.thisvisit,md.onlinetime,md.lastpost,md.todaypost,
		md.monthpost,md.onlineip,md.uploadtime,md.uploadnum,md.starttime,md.pwdctime,md.monoltime,md.digests,md.f_num,md.creditpop,
		md.jobnum,md.lastgrab,md.follows,md.fans,md.newfans,md.newreferto,md.newcomment,md.postcheck,md.punch,md.shafa,md.newnotice,md.newrequest,md.bubble,
		mi.customdata $fieldinfo FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid LEFT JOIN pw_memberinfo mi ON mi.uid=m.uid 
		WHERE m.uid IN (".S::sqlImplode($userIds,false).")"	);	
		return $this->_getAllResultFromQuery ( $query, 'uid' );
	}
	
	/**
	 * 根据所在地apartment和userIds统计用户
	 * 
	 * @param int $apartment 所在地
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function countUsersByApartmentAndUserIds($apartment,$userIds) {
		$apartment = intval($apartment);
		if ($apartment < 1 || !s::isArray($userIds)) return 0;
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND m.apartment = " . $this->_addSlashes($apartment));
	}
	
	/**
	 * 根据所在地apartment和userIds获取用户
	 * 
	 * @param int $apartment 所在地
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersByApartmentAndUserIds($apartment,$userIds,$num) {
		$apartment = intval($apartment);
		$num = intval($num);
		if ($apartment < 1 || $num < 1 || !s::isArray($userIds)) return array();
		$query = $this->_db->query("SELECT m.uid FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND m.apartment = " . $this->_addSlashes($apartment) . ' ' . $this->_Limit(0, $num));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据家乡home和userIds统计用户
	 * 
	 * @param int $home 家乡
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function countUsersByHomeAndUserIds($home,$userIds) {
		$home = intval($home);
		if ($home < 1 || !s::isArray($userIds)) return 0;
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND m.home = " . $this->_addSlashes($home));
	}
	
	/**
	 * 根据家乡home和userIds获取用户
	 * 
	 * @param int $home 家乡
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersByHomeAndUserIds($home,$userIds,$num) {
		$home = intval($home);
		if ($home < 1 || !s::isArray($userIds)) return array();
		$query = $this->_db->query("SELECT m.uid FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND m.home = " . $this->_addSlashes($home) . " " . $this->_Limit(0, $num));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据工作经历companyids和userIds统计用户
	 * 
	 * @param array $companyids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function countUsersByCompanyidAndUserIds($companyids,$userIds) {
		if (!s::isArray($companyids) || !s::isArray($userIds)) return 0;
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) LEFT JOIN " . $this->_userCareer. " mc USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND mc.companyid IN(" . S::sqlImplode($companyids) . ")");
	}
	
	/**
	 * 根据工作经历companyids和userIds获取用户
	 * 
	 * @param array $companyids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersByCompanyidAndUserIds($companyids,$userIds,$num) {
		if (!s::isArray($companyids) || !s::isArray($userIds)) return array();
		$query = $this->_db->query("SELECT m.uid FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) LEFT JOIN " . $this->_userCareer. " mc USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND mc.companyid IN(" . S::sqlImplode($companyids) . ") " . $this->_Limit(0, $num));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据教育经历schoolids和userIds统计用户
	 * 
	 * @param array $schoolids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function countUsersBySchoolidsAndUserIds($schoolids,$userIds) {
		if (!s::isArray($schoolids) || !s::isArray($userIds)) return 0;
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) LEFT JOIN " . $this->_userEducation. " me USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND me.schoolid IN(" . S::sqlImplode($schoolids) . ")");
	}
	
	/**
	 * 根据教育经历companyids和userIds获取用户
	 * 
	 * @param array $companyids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersBySchoolidsAndUserIds($schoolids,$userIds,$num) {
		if (!s::isArray($schoolids) || !s::isArray($userIds)) return array();
		$query = $this->_db->query("SELECT m.uid FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName. " md USING(uid) LEFT JOIN " . $this->_userEducation. " me USING(uid) WHERE m.uid IN(" . S::sqlImplode($userIds) . ") AND me.schoolid IN(" . S::sqlImplode($schoolids) . ") " . $this->_Limit(0, $num));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 获取单条用户、教育、所在地、家乡、工作经历等信息
	 * 
	 * @param int $userId 用户id
	 * @return array
	 */
	function getUserInfoByUserId($userId) {
		$userId = intval($userId);
		if ($userId < 1) return array();
		return $this->_db->get_one("SELECT m.uid,m.apartment,m.home,me.schoolid,mc.companyid FROM " . $this->_tableName . " m LEFT JOIN " . $this->_userEducation. " me USING(uid) LEFT JOIN " . $this->_userCareer. " mc USING(uid) WHERE m.uid = " . $this->_addSlashes($userId));
	}
	/**
	function getMemberAndDataAndInfo($userIds){
		$query = $this->_db->query("SELECT m.uid,m.username,m.gender,m.oicq,m.aliww,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.lastvisit,md.onlinetime,md.starttime,mi.customdata FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid LEFT JOIN pw_memberinfo mi ON mi.uid=m.uid WHERE m.uid IN (".S::sqlImplode($userIds).") ");
		return $this->_getAllResultFromQuery($query);		
	}
	**/
	
}
?>