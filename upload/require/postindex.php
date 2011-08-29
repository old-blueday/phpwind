<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PostIndexDB {
	var $db;
	var $db_perpage;
	var $basename;
	var $p_c;

	function PostIndexDB(){
		$this->__construct();
	}
	/**
	 * @return unknown_type
	 */
	function __construct() {
		global $db;
		global $db_perpage;
		global $basename;
		$this->db = & $db;
		$this->db_perpage = $db_perpage;
		$this->basename = & $basename;
		$this->p_c = 800;
	}

	/**
	 * @param $perpage
	 * @return unknown_type
	 */
	function setPerpage($perpage) {
		$this->db_perpage = $perpage;
	}

	function getALLIndexedThreads($page = 1){
		$sql = "SELECT p.tid FROM pw_postsfloor p GROUP BY p.tid ORDER BY p.tid DESC";
		$query = $this->db->query($sql);
		$count = 0;
		(int)$page == 0 && $page = 1;
		$start = ($page - 1) * $this->db_perpage;
		$end = $start + $this->db_perpage - 1;
		while ($rt = $this->db->fetch_array($query)) {
			if ($count >= $start && $count <= $end) {
				$tid[] = $rt['tid'];
			}
			$count++;
		}
		(! is_numeric ( $page ) || $page < 1) && $page = 1;
		$result ['pages'] = numofpage ( $count, $page, ceil ( $count / $this->db_perpage ),
		$this->basename . "&action=search&" );
		if ($tid) {
			$sql = "SELECT t.tid, t.subject, t.replies, t.postdate, t.fid
					FROM pw_threads t
					WHERE t.tid IN ( ". S::sqlImplode($tid) ." ) ORDER BY t.tid DESC";
			$query = $this->db->query ( $sql );
			while ( $rt = $this->db->fetch_array ( $query ) ) {
				list ( $lastDate ) = PostIndexUtility::getLastDate ( $rt ["postdate"] );
				$rt ["postdate"] = $lastDate;
				$result ['data'] [] = $rt;
			}
		}
		return $result;
	}

	/**
	 * @param $replies
	 * @param $order
	 * @param $isDesc
	 * @param $page
	 * @return unknown_type
	 */
	function getThreadsByReplies($replies, $page) {
		if (! $replies) {
			return;
		}
		$sql = "SELECT p.tid FROM pw_postsfloor p GROUP BY p.tid ORDER BY p.tid DESC";
		$query = $this->db->query($sql);
		while ($rt = $this->db->fetch_array($query)) {
			$tid[] = $rt['tid'];
		}
		if ($tid) {
			$w_tid = " t.tid NOT IN ( ". S::sqlImplode($tid) ." ) AND ";
		}
		$sql = "SELECT COUNT(*) AS sum FROM pw_threads t WHERE $w_tid t.replies > " . S::sqlEscape($replies);
		$rt = $this->db->get_one ( $sql );
		(! is_numeric ( $page ) || $page < 1) && $page = 1;
		$limit = S::sqlLimit ( ($page - 1) * $this->db_perpage, $this->db_perpage );
		$result ['pages'] = numofpage ( $rt ['sum'], $page, ceil ( $rt ['sum'] / $this->db_perpage ),
		$this->basename . "&sub=y&action=search&replies=$replies&" );
		$sql = "SELECT t.tid, t.subject, t.replies, t.postdate, t.fid
				FROM pw_threads t
				WHERE $w_tid t.replies > ".S::sqlEscape($replies)." $limit";
		$query = $this->db->query ( $sql );
		while ( $rt = $this->db->fetch_array ( $query ) ) {
			list ( $lastDate ) = PostIndexUtility::getLastDate ( $rt ["postdate"] );
			$rt ["postdate"] = $lastDate;
			$result ['data'] [] = $rt;
		}
		return $result;
	}

	/**
	 * @param $tid
	 * @return unknown_type
	 */
	function getThreadsById($tid){
		$sql = "SELECT t.tid, t.subject, t.replies, t.postdate, t.fid
				FROM pw_threads t
				WHERE t.tid = ".S::sqlEscape($tid)." AND t.tpcstatus & 2 = '0'";
		$rt = $this->db->get_one($sql);
		if ($rt) {
			list ( $lastDate ) = PostIndexUtility::getLastDate ( $rt ["postdate"] );
			$rt ["postdate"] = $lastDate;
			$result['data'][]=$rt;
		}
		return $result;
	}

	/**
	 * @param $tid
	 * @param $postTable
	 * @return unknown_type
	 */
	function resetPostIndex($tid,$type,$step) {
		if ($type == "1") {
			return $this->deletePostIndex ( $tid,$step );
		}else{
			return $this->addPostIndex ( $tid,$step );
		}
	}

	/**
	 * @param $tid
	 * @return unknown_type
	 */
	function deletePostIndex($tid,$step) {
	/*
		!$step && $step = 1;
		$max_floor = $this->getMaxFloorByTid($tid);
		$end = ($step * $this->p_c - 1) >= $max_floor ? $max_floor : ($step * $this->p_c - 1);
		$next = $step + 1;
		$sql = "DELETE FROM pw_postsfloor WHERE floor <= $end AND tid = ".S::sqlEscape($tid);
		$this->db->update ( $sql );
		if ($end == $max_floor) {
			$sql = "UPDATE pw_threads SET tpcstatus = tpcstatus & conv('fD',16,10) WHERE tid = ".S::sqlEscape($tid);
			$this->db->update ( $sql );
			$next = 0;
		}
		//临时修改，待改进
		$threads = L::loadClass('Threads', 'forum');
		$threads->delThreads($tid);
		return $next;
	*/

		!$step && $step = 1;
		if ($step == 1) {#@cn0zz 先更新帖子标志，避免删除过程中浏览帖子出现错乱
			//$sql = "UPDATE pw_threads SET tpcstatus = tpcstatus & (~2) WHERE tid = ".S::sqlEscape($tid);
			$sql = pwQuery::buildClause("UPDATE :pw_table SET tpcstatus = tpcstatus & (~2) WHERE tid = :tid", array('pw_threads', $tid));
			$this->db->update ( $sql );
			//* $threads = L::loadClass('Threads', 'forum');
			//* $threads->delThreads($tid);
		}
		#@cn0zz 删除帖子索引，是全部清空该帖子的楼层，所以可直接使用LIMIT
		$limit = S::sqlLimit($this->p_c);
		$sql = "DELETE FROM pw_postsfloor WHERE tid = ".S::sqlEscape($tid).$limit;
		$this->db->update ( $sql );
		if ($this->db->affected_rows() < $this->p_c) {
			return 0;
		} else {
			return $step + 1;
		}
	}

	/**
	 * @param $tid
	 * @param $postTable
	 * @return unknown_type
	 */
	function addPostIndex($tid,$step) {
		$ptable = PostIndexDB::getPostTable ( $tid );
		$rt = $this->db->get_one("SELECT count(*) AS sum FROM $ptable p WHERE p.tid = " .S::sqlEscape($tid));
		$count = $rt['sum'];
		!$step && $step = 1;
		$next = $step + 1;
		$start = ($step - 1) * $this->p_c;
		$limit = S::sqlLimit($start,$this->p_c);
		$sql = "SELECT p.tid, p.pid, p.postdate, p.authorid
				FROM $ptable p
				WHERE p.tid = ".S::sqlEscape($tid)." ORDER BY p.postdate $limit";
		$query = $this->db->query ( $sql );
		while ( $rt = $this->db->fetch_array ( $query ) ) {
			$f_data[] = array($rt ["tid"],$rt ["pid"]);
		}
		if (!empty($f_data)) {
			$this->db->update ("REPLACE INTO pw_postsfloor(tid,pid) VALUES " . S::sqlMulti($f_data));
		}
		$floor = $this->getMaxFloorByTid($tid);
		if ($count > $floor) {
			return $next;
		}else{
			#@cn0zz 添加索引完成后，才能修改帖子状态
			//$sql = "UPDATE pw_threads SET tpcstatus = tpcstatus | 2 WHERE tid =".S::sqlEscape($tid);
			$sql = pwQuery::buildClause("UPDATE :pw_table SET tpcstatus = tpcstatus | 2 WHERE tid = :tid", array('pw_threads', $tid));
			$this->db->update ( $sql );
			//* $threads = L::loadClass('Threads', 'forum');
			//* $threads->delThreads($tid);
			return 0;
		}
	}

	function getMaxFloorByTid($tid){
	/*
		$rt = $this->db->get_one("SELECT max(floor) AS floor FROM pw_postsfloor WHERE tid=" . S::sqlEscape($tid));
		!$rt['floor'] && $rt['floor'] = 0;
		return $rt['floor'];
	*/
		#@cn0zz floor 唯一，可直接COUNT
		$count = $this->db->get_value("SELECT COUNT(*) FROM pw_postsfloor WHERE tid=" . S::sqlEscape($tid));
		return $count;
	}

	function getPostTable($tid) {
		$sql = "SELECT t.ptable FROM pw_threads t WHERE t.tid = ".S::sqlEscape($tid);
		$rt = $this->db->get_one ( $sql );
		$ptable = GetPtable ( $rt ['ptable'] );
		return $ptable;
	}

}

class PostIndexUtility {
	function __construct() {
	}
	/**
	 * @param $time
	 * @param $type
	 * @return unknown_type
	 */
	function getLastDate($time, $type = 1) {
		global $timestamp, $tdtime;
		static $timelang = false;
		if ($timelang == false) {
			$timelang = array ('second' => getLangInfo ( 'other', 'second' ), 'yesterday' => getLangInfo ( 'other', 'yesterday' ), 'hour' => getLangInfo ( 'other', 'hour' ), 'minute' => getLangInfo ( 'other', 'minute' ), 'qiantian' => getLangInfo ( 'other', 'qiantian' ) );
		}
		$decrease = $timestamp - $time;
		$thistime = PwStrtoTime ( get_date ( $time, 'Y-m-d' ) );
		$thisyear = PwStrtoTime ( get_date ( $time, 'Y' ) );
		$thistime_without_day = get_date ( $time, 'H:i' );
		$yeartime = PwStrtoTime ( get_date ( $timestamp, 'Y' ) );
		$result = get_date ( $time );
		if ($thistime == $tdtime) {
			if ($type == 1) {
				if ($decrease <= 60) {
					return array ($decrease . $timelang ['second'], $result );
				}
				if ($decrease <= 3600) {
					return array (ceil ( $decrease / 60 ) . $timelang ['minute'], $result );
				} else {
					return array (ceil ( $decrease / 3600 ) . $timelang ['hour'], $result );
				}
			} else {
				return array (get_date ( $time, 'H:i' ), $result );
			}
		} elseif ($thistime == $tdtime - 86400) {
			if ($type == 1) {
				return array ($timelang ['yesterday'] . " " . $thistime_without_day, $result );
			} else {
				return array (get_date ( $time, 'm-d' ), $result );
			}
		} elseif ($thistime == $tdtime - 172800) {
			if ($type == 1) {
				return array ($timelang ['qiantian'] . " " . $thistime_without_day, $result );
			} else {
				return array (get_date ( $time, 'm-d' ), $result );
			}
		} elseif ($thisyear == $yeartime) {
			return array (get_date ( $time, 'm-d' ), $result );
		} else {
			if ($type == 1) {
				return array (get_date ( $time, 'Y-m-d' ), $result );
			} else {
				return array (get_date ( $time, 'y-n-j' ), $result );
			}
		}
	}
}
?>
