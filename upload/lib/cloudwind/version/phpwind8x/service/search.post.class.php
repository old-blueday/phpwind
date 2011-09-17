<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_Search_Post extends CloudWind_General_Abstract {
	
	var $_filecache = true;
	
	function getPostsByPage($page) {
		$page = ($page > 0) ? intval ( $page ) : 1;
		$start = ($page - 1) * $this->_perpage;
		$end = $this->_perpage * $page;
		list ( $tableName, $tmpStart, $tmpEnd ) = $this->_getTableInfos ( $start, $end );
		if (! $tableName || ! $tmpEnd)
			return false;
		$postDao = $this->_getPostsDao ();
		return $postDao->getPostsByRange ( $tmpStart, $tmpEnd, $tableName );
	}
	
	function getPostsByPids($postIds) {
		if (! $postIds) {
			return false;
		}
		$tableInfo = ($this->_filecache) ? $this->_initPostInfo () : $this->_initPostInfoNoCache ();
		if (! $tableInfo)
			return false;
		$tmp = array ();
		foreach ( $postIds as $postId ) {
			$table = $this->_getPostTableByPostId ( $postId, $tableInfo );
			$table && $tmp [$table] [] = $postId;
		}
		if (! $tmp) {
			return false;
		}
		$postDao = $this->_getPostsDao ();
		$result = array ();
		foreach ( $tmp as $table => $ids ) {
			$posts = $postDao->getsByPostIds ( $ids, $table );
			$posts && $result = array_merge ( $result, $posts );
		}
		return $result;
	}
	
	function getsByPostIds($ids, $table) {
		if (! $table)
			return array ();
		$postDao = $this->_getPostsDao ();
		return $postDao->getsByPostIds ( $ids, $table );
	}
	
	function deletePostByPid($pid, $table) {
		$pid = intval ( $pid );
		if ($pid < 1 || ! $table)
			return false;
		$postDao = $this->_getPostsDao ();
		return $postDao->deletePostByPid ( $pid, $table );
	}
	
	function setPostCheckedByPid($pid, $table) {
		$pid = intval ( $pid );
		if ($pid < 1 || ! $table)
			return false;
		$postDao = $this->_getPostsDao ();
		return $postDao->setPostCheckedByPid ( $pid, $table );
	}
	
	function maxPostId() {
		$table = ($GLOBALS ['db_plist'] && count ( $GLOBALS ['db_plist'] ) > 1) ? 'pw_pidtmp' : 'pw_posts';
		if (! in_array ( $table, array ('pw_posts', 'pw_pidtmp' ) ))
			return false;
		$postDao = $this->_getPostsDao ();
		return $postDao->maxPostId ( $table );
	}
	
	function countPostsNum() {
		$table = ($GLOBALS ['db_plist'] && count ( $GLOBALS ['db_plist'] ) > 1) ? 'pw_pidtmp' : 'pw_posts';
		if (! in_array ( $table, array ('pw_posts', 'pw_pidtmp' ) ))
			return false;
		$postDao = $this->_getPostsDao ();
		return $postDao->countPostsNum ( $table );
	}
	
	function getValidIds($minId, $maxId) {
		list ( $tableName, $tmpStart, $tmpEnd ) = $this->_getTableInfos ( $minId, $maxId );
		if (! $tableName || ! $tmpEnd)
			return false;
		$postDao = $this->_getPostsDao ();
		$ids = $postDao->getIdsByRange ( $tableName, intval ( $minId ), intval ( $maxId ) );
		return $ids;
	}
	
	function _getTableInfos($minId, $maxId) {
		$tableInfo = ($this->_filecache) ? $this->_initPostInfo () : $this->_initPostInfoNoCache ();
		if (! $tableInfo)
			return array ('', 0, 0 );
		$tableName = '';
		$tmpStart = $tmpEnd = 0;
		foreach ( $tableInfo as $table => $info ) {
			list ( $bool, $tmpStart, $tmpEnd ) = $this->_countPage ( $minId, $maxId, $info ['min'], $info ['max'] );
			if ($bool) {
				$tableName = $table;
				break;
			}
		}
		if (! $tableName || ! $tmpEnd)
			return array ('', 0, 0 );
		return array ($tableName, $tmpStart, $tmpEnd );
	}
	
	function _getPostTableByPostId($postId, $tableInfo) {
		foreach ( $tableInfo as $table => $ids ) {
			if ($ids ['min'] <= $postId && $ids ['max'] >= $postId) {
				return $table;
			}
		}
		return false;
	}
	
	function _initPostInfo() {
		$filepath = D_P . 'data/bbscache/cloudwind_postinfo.php';
		if ((! is_file ( $filepath )) || (CloudWind_filemtime ( $filepath ) + 300 <= CloudWind_getConfig ( 'g_timestamp' ))) {
			$postInfo = $this->_initPostInfoNoCache ();
			($postInfo) && CloudWind_writeover ( $filepath, "<?php\r\n\$postInfo=" . CloudWind_varExport ( $postInfo ) . ";\r\n?>" );
		} else {
			require $filepath;
		}
		return $postInfo;
	}
	
	function _initPostInfoNoCache() {
		$dbposts = ($GLOBALS ['db_plist']) ? $GLOBALS ['db_plist'] : array (0 );
		$tables = $tableInfo = array ();
		foreach ( $dbposts as $k => $v ) {
			$k = ($k > 0) ? $k : '';
			$tables [] = 'pw_posts' . $k;
		}
		$postDao = $this->_getPostsDao ();
		foreach ( $tables as $table ) {
			$result = $postDao->getMaxPid ( $table );
			($result ['max']) && $tableInfo [$table] = $result;
		}
		return ($tableInfo) ? $tableInfo : array ();
	}
	
	function createForAdd($post, $command = YUN_COMMAND_ADD) {
		if (! $post)
			return false;
		$out = '';
		$forum = $this->_getForum ( $post ['fid'] );
		$content = $this->_toolsService->_filterString ( $post ['content'] );
		if (! $content) {
			return '';
		}
		$data = array ();
		$data ['tid'] = $post ['tid'];
		$data ['pid'] = $post ['pid'];
		$data ['subject'] = $this->_toolsService->_filterString ( $post ['subject'], 300 );
		$data ['content'] = $content;
		$data ['fid'] = $post ['fid'];
		$data ['forumname'] = $this->_toolsService->_filterString ( $forum ['name'] );
		$data ['forumlink'] = $this->_getForumUrl ( $post ['fid'] );
		$data ['ifcheck'] = $post ['ifcheck'];
		$data ['authorid'] = $post ['authorid'];
		$data ['author'] = $this->_toolsService->_filterString ( $post ['author'] );
		$data ['postdate'] = $post ['postdate'];
		$data ['link'] = $this->_getPostUrl ( $post ['tid'], $post ['pid'] );
		
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getPostFormat ( $data, $command );
	}
	
	function createForDelete($pid) {
		$formatService = $this->getGeneralFormatService ();
		return $formatService->getDeleteFormat ( 'pid', $pid );
	}
	
	function _countPage($start, $end, $min, $max) {
		if ($start >= $min && $end <= $max) {
			return array (true, $start, $end );
		}
		if ($start >= $min && $start < $max && $end > $max) {
			return array (true, $start, $max );
		}
		if ($start < $min && $end >= $min && $end < $max) {
			return array (true, $min, $end );
		}
		if ($start < $min && $start < $max && $end >= $max) {
			return array (true, $min, $max );
		}
		return array (false, 0, 0 );
	}
	
	function _getForum($fid) {
		if (! $fid)
			return array ();
		static $forums = array ();
		if (! $forums [$fid]) {
			$forums [$fid] = L::forum ( $fid );
		}
		return $forums [$fid];
	}
	
	function _getForumUrl($fid) {
		return $this->_bbsUrl . '/thread.php?fid=' . $fid;
	}
	
	function _getThreadUrl($tid) {
		return $this->_bbsUrl . '/read.php?tid=' . $tid;
	}
	
	function _getPostUrl($tid, $pid) {
		return $this->_bbsUrl . '/job.php?action=topost&tid=' . $tid . '&pid=' . $pid;
	}
	
	function _getPostsDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$daoFactory = new CloudWind_Dao_Factory ();
			$dao = $daoFactory->getSearchPostDao ();
		}
		return $dao;
	}
}