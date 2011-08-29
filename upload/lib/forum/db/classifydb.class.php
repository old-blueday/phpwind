<?php
!defined('P_W') && exit('Forbidden');
include_once (R_P . 'lib/base/basedb.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

class PW_ClassifyDB extends BaseDB {
	var $_tableName = 'pw_threads';
	
	/**
	 * 
	 * 获取最新发布分类信息
	 * @param array $modelid 分类信息id
	 * @param string $fid 版块id
	 * @param int $num 调用个数
	 * @return array
	 */
	function newClassifyTopic($modelid, $fid, $num) {
		$posts = array();
		$sqlWhere = $this->_buildCondition($modelid, $fid);
		$query = $this->_db->query('SELECT tid,fid,modelid,author,authorid,subject,postdate,anonymous FROM ' . $this->_tableName . $sqlWhere . ' AND ifshield != 1 AND locked != 2  ORDER BY postdate DESC' . S::sqlLimit(0,$num));
		$posts = $this->_cookData($query);
		return $posts;
	}
	
	/**
	 * 
	 * 获取最新回复分类信息
	 * @param array $modelid 分类信息id
	 * @param string $fid 版块id
	 * @param int $num 调用个数
	 * @return array
	 */
	function newClassifyReply($modelid, $fid, $num) {
		$posts = array();
		$sqlWhere = $this->_buildCondition($modelid, $fid);
		$query = $this->_db->query('SELECT tid,fid,modelid,author,authorid,subject,postdate,anonymous FROM ' . $this->_tableName . $sqlWhere . ' AND ifshield != 1 AND locked != 2  ORDER BY lastpost DESC' . S::sqlLimit(0,$num));
		$posts = $this->_cookData($query);
		return $posts;
	}
	
	/**
	 * 
	 * 获取最新置顶分类信息
	 * @param array $modelid 分类信息id
	 * @param string $fid 版块id
	 * @param int $num 调用个数
	 * @return array
	 */
	function toppedClassifyTopic($modelid, $fid, $num) {
		$posts = array();
		$sqlWhere = $this->_buildCondition($modelid, $fid);
		$sqlWhere .= ' AND topped != 0';
		$query = $this->_db->query('SELECT tid,fid,modelid,author,authorid,subject,postdate,anonymous FROM ' . $this->_tableName . $sqlWhere . ' AND ifshield != 1 AND locked != 2  ORDER BY lastpost DESC' . S::sqlLimit(0,$num));
		$posts = $this->_cookData($query);
		return $posts;
	}
	
	/**
	 * 
	 * 组装搜索条件
	 * @param array $modelid 分类信息id
	 * @param string $fid 版块id
	 * @return string
	 */
	function _buildCondition($modelid, $fid) {
		$sqlWhere = ' WHERE modelid != 0';
		!empty($modelid) && $sqlWhere .= ' AND modelid IN (' . S::sqlImplode($modelid) . ')';
		$fid && $sqlWhere .= ' AND fid IN (' . $fid . ')';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND tid NOT IN (' . $blackListedTids . ')';
		return $sqlWhere;
	}
	
	/**
	 * 
	 * 获取查询结果
	 * @param unknown $query 查询结果
	 * @return array
	 */
	function _cookData($query) {
		//* include pwCache::getPath(D_P . 'data/bbscache/topic_config.php');
		extract(pwCache::getData(D_P . 'data/bbscache/topic_config.php', false));
		while ($row = $this->_db->fetch_array($query)) {
			$row['modelname'] = $topicmodeldb[$row['modelid']]['name'];
			$posts[] = $row;
		}
		return $posts;
	}
	
	function _getBlackListedTids() {
		global $db_tidblacklist;
		return $db_tidblacklist;
	}
}
?>