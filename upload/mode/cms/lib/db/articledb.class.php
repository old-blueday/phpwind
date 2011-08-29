<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_ArticleDB extends BaseDB {
	var $_tableName = "pw_cms_article";
	var $_primaryKey = 'article_id';
	
	var $_extendTableName = "pw_cms_articleextend";
	
	/**
	 * 根据文章ID删除文章
	 * @param array $ids
	 */
	function deleteArticles($ids) {
		$_sql = "DELETE FROM " . $this->_tableName . " WHERE article_id IN (" . S::sqlImplode ( $ids ) . ") ";
		return $this->_db->update ( $_sql );
	}
	
	/**
	 * 移动文章到某一个频道
	 * @param array $ids
	 * @param int $cid
	 */
	function moveArticlesToColumn($ids, $cid) {
		$_sql = "UPDATE " . $this->_tableName . " SET column_id = " . S::sqlEscape ( $cid ) . "  WHERE article_id IN (" . S::sqlImplode ( $ids ) . ")";
		return $this->_db->update ( $_sql );
	}
	
	/**
	 * 删除回收站内的所有文章
	 */
	function cleanArticleRecycle() {
		$_sql = "DELETE FROM " . $this->_tableName . " WHERE ifcheck = '2' ";
		return $this->_db->update ( $_sql );
	}
	
	/**
	 * 根据文章ID获取文章列表
	 * @param array $ids
	 */
	function getArticlesByIds($ids) {
		$_sql = "SELECT a.*,e.hits FROM " . $this->_tableName . " a LEFT JOIN " . $this->_extendTableName . " e ON a.article_id=e.article_id WHERE a.article_id IN (" . S::sqlImplode ( $ids ) . ") AND a.ifcheck != 2";
		return $this->_getAllResultFromQuery ( $this->_db->query ( $_sql ) );
	}
	
	/**
	 * 根据文章ID获取热门文章列表
	 * @param array $ids
	 */
	function getHotArticlesByIds($ids) {
		$_sql = "SELECT a.*,e.hits FROM " . $this->_tableName . " a LEFT JOIN " . $this->_extendTableName . " e ON a.article_id=e.article_id WHERE a.article_id IN (" . S::sqlImplode ( $ids ) . ") AND a.ifcheck != 2 ORDER BY e.hits DESC";
		return $this->_getAllResultFromQuery ( $this->_db->query ( $_sql ) );
	}
	
	/**
	 * 根据条件搜索文章
	 * @param int $cid	栏目ID
	 * @param string $title	标题
	 * @param string $author 作者
	 * @param int $type 是否通过验证 
	 * array('0','1','2')
	 * '0' 未经过审核
	 * '1' 经过审核
	 * '2' 回收站
	 */
	function search($cid, $title, $author, $type, $user, $postdate = '', $start = 0, $perpage = 20) {
		$_where = '';
		if ($cid)
			$_where .= ' AND column_id IN (' . S::sqlImplode ( $cid ) . ")";
		if ($title)
			$_where .= ' AND subject LIKE ' . S::sqlEscape ( '%' . $title . '%' );
		if ($author)
			$_where .= ' AND author LIKE ' . S::sqlEscape ( '%' . $author . '%' );
		if ($user)
			$_where .= ' AND username LIKE ' . S::sqlEscape ( '%' . $user . '%' );
		if ($postdate)
			$_where .= ' AND postdate <= ' . S::sqlEscape ( $postdate );
		if (isset ( $type ) && in_array ( $type, array ('0', '1', '2' ) ))
			$_where .= ' AND ifcheck = ' . S::sqlEscape ( $type );
		$order = " ORDER BY modifydate DESC, postdate DESC ";
		$_sql = "SELECT * FROM " . $this->_tableName . " WHERE article_id " . $_where . " " . $order . " LIMIT " . $start . "," . $perpage;
		$query = $this->_db->query ( $_sql );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function searchCount($cid, $title, $author, $type, $user, $postdate) {
		$_where = '';
		if ($cid)
			$_where .= ' AND column_id IN (' . S::sqlImplode ( $cid ) . ")";
		if ($title)
			$_where .= ' AND subject LIKE ' . S::sqlEscape ( '%' . $title . '%' );
		if ($author)
			$_where .= ' AND author = ' . S::sqlEscape ( $author );
		if ($user)
			$_where .= ' AND username LIKE ' . S::sqlEscape ( '%' . $user . '%' );
		if ($postdate)
			$_where .= ' AND postdate <= ' . S::sqlEscape ( $postdate );
		if (isset ( $type ) && in_array ( $type, array ('0', '1', '2' ) ))
			$_where .= ' AND ifcheck = ' . S::sqlEscape ( $type );
		$_sql = "SELECT COUNT(*) FROM " . $this->_tableName . " WHERE article_id " . $_where;
		return $this->_db->get_value ( $_sql );
	}
	
	function deleteArticleIntoRecycle($aids) {
		$_sql = "UPDATE " . $this->_tableName . " SET ifcheck = '2' WHERE  article_id IN (" . S::sqlImplode ( $aids ) . ")";
		return $this->_db->update ( $_sql );
	}
	
	function revertArticleFromRecycle($aids) {
		$_sql = "UPDATE " . $this->_tableName . " SET ifcheck = '1' WHERE article_id IN (" . S::sqlImplode ( $aids ) . ") ";
		return $this->_db->update ( $_sql );
	}
	
	function updateArticleHits($aid, $count = 1) {
		$_sql = "UPDATE " . $this->_extendTableName . " SET hits = hits + " . S::sqlEscape ( $count, false ) . " WHERE article_id = " . S::sqlEscape ( $aid );
		return $this->_db->update ( $_sql );
	}
	
	function insert($fieldData) {
		$fieldData = $this->_checkAllowField ( $fieldData, $this->getStruct () );
		return $this->_insert ( $fieldData );
	}
	
	function update($fieldData, $id) {
		$fieldData = $this->_checkAllowField ( $fieldData, $this->getStruct () );
		return $this->_update ( $fieldData, $id );
	}
	
	function delete($id) {
		return $this->_delete ( $id );
	}
	
	function get($id) {
		$temp = $this->_get ( $id );
		return $temp;
	}
	
	function count() {
		return $this->_count ();
	}
	
	function getStruct() {
		return array ('article_id', 'subject', 'descrip', 'author', 'username', 'userid', 'jumpurl', 'frominfo', 'fromurl', 'column_id', 'ifcheck', 'postdate', 'modifydate', 'ifattach', 'sourcetype', 'sourceid' );
	}
	
	/**
	 * 注意只提供搜索服务
	 * @param $sql
	 * @return unknown_type
	 */
	function countSearch($sql) {
		$result = $this->_db->get_one ( $sql );
		return ($result) ? $result ['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 * @param $sql
	 * @return unknown_type
	 */
	function getSearch($sql) {
		$query = $this->_db->query ( $sql );
		return $this->_getAllResultFromQuery ( $query );
	}

}