<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索全量/增量索引服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class PW_YunIndex extends PW_YunBase {
	
	function PW_YunIndex() {
		$this->__construct ();
	}
	
	function __construct() {
	}
	
	function detectIndex($type, $minid, $maxid) {
		if (! $this->_checkType ( $type )) {
			exit ( 'cann`t find the type' );
		}
		$minid = intval ( $minid );
		$maxid = intval ( $maxid );
		if (! $minid) {
			exit ( 'minid cann`t be null' );
		}
		$maxid = ($maxid) ? $maxid : $minid + 10000;
		if ($minid >= $maxid) {
			exit ( 'minid cann`t exceed maxid' );
		}
		$_tmpMaxId = $this->getMaxIdByType ( $type );
		$maxid = min ( $maxid, $_tmpMaxId );
		
		$yunsearch = $this->_getYunSearch ();
		$result = $yunsearch->detectIndex ( (($type) ? $type : 'thread'), array ('minid' => $minid, 'maxid' => $maxid ) );
		$this->_outPut ( $result, "cann`t find data,from {$minid} to {$maxid} " );
	}
	
	function getFullOutPut($type, $page, $versionid) {
		if (! $this->_checkType ( $type )) {
			exit ( 'cann`t find the type' );
		}
		$yunsearch = $this->_getYunSearch ();
		$page = ($page > 1) ? intval ( $page ) : 1;
		$result = $yunsearch->createIndex ( (($type) ? $type : 'thread'), array ('page' => $page, 'versionid' => $versionid ) );
		$this->_outPut ( $result, 'cann`t find data,current page is ' . $page );
	}
	
	function getAddOutPut($type, $page, $starttime, $endtime) {
		$page = ($page > 1) ? intval ( $page ) : 1;
		$starttime = intval ( $starttime );
		$endtime = intval ( $endtime );
		if ($endtime < 1) {
			exit ( 'the versionid cann`t be null' );
		}
		$yunsearch = $this->_getYunSearch ();
		$result = $yunsearch->alterIndex ( (($type) ? $type : 'thread'), array ('page' => $page, 'starttime' => $starttime, 'endtime' => $endtime ) );
		$this->_outPut ( $result, 'cann`t find data,current page is ' . $page );
	}
	
	function _outPut($result, $tips = '') {
		return $this->_outPutCompress ( ($result) ? $result : $tips );
	}
	
	function _outPutCompress($content) {
		$setting = $this->getYunSetting ();
		if (! $setting ['searchcompress'] && ! headers_sent () && extension_loaded ( "zlib" ) && strstr ( $_SERVER ["HTTP_ACCEPT_ENCODING"], "gzip" )) {
			$content = gzencode ( $content, 9 );
			header ( "Content-Type:text/html; charset=utf8" );
			header ( "Content-Encoding:gzip" );
			header ( "Content-Length:" . strlen ( $content ) );
		}
		echo ($content);
	}
	
	function _checkType($type) {
		return (in_array ( $type, $this->_getAllType () )) ? true : false;
	}
	
	function _getAllType() {
		return array ('thread', 'member', 'diary', 'forum', 'colony', 'post', 'attach', 'weibo' );
	}
	
	function _getYunSearch() {
		require_once R_P . 'lib/cloudwind/yunsearch.class.php';
		$service = new PW_YunSearch ();
		return $service;
	}
	
	function createLists($type, $hashid = 0) {
		$max = $this->getMaxIdByType ( $type );
		$versionid = $this->getVersionId ();
		if ($max < 1)
			return $this->_outputlist ( $this->_buildListResult ( $versionid ) );
		$total = $this->countByType ( $type );
		return $this->_outputlist ( $this->_buildListResult ( $versionid, $total, $max ) );
	}
	
	function _buildListResult($versionid, $total = 0, $max = 0) {
		return "versionid={$versionid}&total={$total}&max={$max}\r\n";
	}
	
	function createAddLists($type, $starttime, $endtime, $hashid = 0) {
		$count = $this->countLogsByType ( $type, $starttime, $endtime );
		$versionid = ($endtime) ? $endtime : $this->getVersionId ();
		if ($count < 1)
			return $this->_outputlist ( "versionid={$versionid}&total=0&max=0\r\n" );
		$out = "versionid={$versionid}&total={$count}&max={$count}\r\n";
		return $this->_outputlist ( $out );
	}
	
	function createAllAddLists($starttime, $endtime, $hashid = 0) {
		$types = $this->_getAllType ();
		$out = "";
		$versionid = ($endtime) ? $endtime : $this->getVersionId ();
		foreach ( $types as $type ) {
			$count = $this->countLogsByType ( $type, $starttime, $endtime );
			if ($count < 1) {
				$out .= "{$type}:versionid={$versionid}&total=0&max=0\r\n";
			} else {
				$out .= "{$type}:versionid={$versionid}&total={$count}&max={$count}\r\n";
			}
		}
		return $this->_outputlist ( $out );
	}
	
	function createFullAllLists($hashid, $show = true) {
		$types = $this->_getAllType ();
		$out = "";
		$versionid = $this->getVersionId ();
		foreach ( $types as $type ) {
			$max = $this->getMaxIdByType ( $type );
			if ($max < 1) {
				$out .= "{$type}:" . $this->_buildListResult ( $versionid );
			} else {
				$total = $this->countByType ( $type );
				$out .= "{$type}:" . $this->_buildListResult ( $versionid, $total, $max );
			}
		}
		return ($show) ? $this->_outputlist ( $out ) : $out;
	}
	
	function _outputlist($result) {
		echo $result;
	}
	
	function getVersionId() {
		return ($GLOBALS ['timestamp']) ? $GLOBALS ['timestamp'] : time ();
	}
	
	function getMaxIdByType($type) {
		global $db;
		switch ($type) {
			case 'thread' :
				$result = $db->get_one ( "SELECT max(tid) as max FROM pw_threads" );
				break;
			case 'post' :
				global $db_plist;
				$result = ($db_plist && count ( $db_plist ) > 1) ? $db->get_one ( "SELECT max(pid) as max FROM pw_pidtmp" ) : $db->get_one ( "SELECT max(pid) as max FROM pw_posts" );
				break;
			case 'member' :
				$result = $db->get_one ( "SELECT max(uid) as max FROM pw_members" );
				break;
			case 'diary' :
				$result = $db->get_one ( "SELECT max(did) as max FROM pw_diary" );
				break;
			case 'forum' :
				$result = $db->get_one ( "SELECT max(fid) as max FROM pw_forums" );
				break;
			case 'colony' :
				$result = $db->get_one ( "SELECT max(id) as max FROM pw_colonys" );
				break;
			case 'attach' :
				$result = $db->get_one ( "SELECT max(aid) as max FROM pw_attachs" );
				break;
			case 'weibo' :
				$result = $db->get_one ( "SELECT max(mid) as max FROM pw_weibo_content" );
				break;
			default :
				$result = array ();
				break;
		}
		return ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countByType($type) {
		global $db;
		switch ($type) {
			case 'thread' :
				$result = $db->get_one ( "SELECT count(tid) as total FROM pw_threads" );
				break;
			case 'post' :
				global $db_plist;
				$result = ($db_plist && count ( $db_plist ) > 1) ? $db->get_one ( "SELECT max(pid) as total FROM pw_pidtmp" ) : $db->get_one ( "SELECT count(pid) as total FROM pw_posts" );
				break;
			case 'member' :
				$result = $db->get_one ( "SELECT count(uid) as total FROM pw_members" );
				break;
			case 'diary' :
				$result = $db->get_one ( "SELECT count(did) as total FROM pw_diary" );
				break;
			case 'forum' :
				$result = $db->get_one ( "SELECT count(fid) as total FROM pw_forums" );
				break;
			case 'colony' :
				$result = $db->get_one ( "SELECT count(id) as total FROM pw_colonys" );
				break;
			case 'attach' :
				$result = $db->get_one ( "SELECT count(aid) as total FROM pw_attachs" );
				break;
			case 'weibo' :
				$result = $db->get_one ( "SELECT count(mid) as total FROM pw_weibo_content" );
				break;
			default :
				$result = array ();
				break;
		}
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
	
	function countLogsByType($type, $starttime, $endtime) {
		global $db;
		$starttime = intval ( $starttime );
		$endtime = intval ( $endtime );
		$starttime = ($starttime > 0) ? $starttime : 0;
		$endtime = ($endtime > 0) ? $endtime : $this->getVersionId ();
		$tables = array ('thread' => 'pw_log_threads', 'post' => 'pw_log_posts', 'member' => 'pw_log_members', 'diary' => 'pw_log_diary', 'forum' => 'pw_log_forums', 'colony' => 'pw_log_colonys', 'attach' => 'pw_log_attachs', 'weibo' => 'pw_log_weibos' );
		if (! isset ( $tables [$type] )) {
			return 0;
		}
		$result = $db->get_one ( "SELECT count(*) as count FROM `" . $tables [$type] . "` WHERE modified_time >= " . pwEscape ( $starttime ) . " AND modified_time <= " . pwEscape ( $endtime ) );
		return ($result && $result ['count'] > 0) ? $result ['count'] : 0;
	}
	
	function markAllLogs($starttime, $endtime) {
		$types = $this->_getAllType ();
		foreach ( $types as $type ) {
			$this->_markLogs ( $type, $starttime, $endtime );
		}
		exit ( '1' );
	}
	
	function markLogs($type, $starttime, $endtime) {
		$result = $this->_markLogs ( $type, $starttime, $endtime );
		exit ( '1' );
	}
	
	function _markLogs($type, $starttime, $endtime) {
		$yunsearch = $this->_getYunSearch ();
		if ($yunsearch->markIndex ( $type, array ('starttime' => $starttime, 'endtime' => $endtime ) )) {
			return true;
		}
		return false;
	}

}