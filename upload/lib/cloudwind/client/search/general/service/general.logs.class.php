<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_General_Logs extends CloudWind_General_Abstract {
	
	function getLogsBySegment($tableName, $starttime, $endtime, $page, $perpage) {
		$logsDao = $this->_getLogsDao ();
		if (! ($logs = $logsDao->getLogsBySegment ( $tableName, $starttime, $endtime, $page, $perpage ))) {
			return false;
		}
		return $logs;
	}
	
	function deleteLogsSegment($tablename, $starttime, $endtime) {
		$logsDao = $this->_getLogsDao ();
		return $logsDao->deleteLogsSegment ( $tablename, $starttime, $endtime );
	}

	function countLogsByTypeAndTime($type, $starttime, $endtime) {
		$starttime = intval ( $starttime );
		$endtime = intval ( $endtime );
		$tables = array ('thread' => 'pw_log_threads', 'post' => 'pw_log_posts', 'member' => 'pw_log_members', 'diary' => 'pw_log_diary', 'forum' => 'pw_log_forums', 'colony' => 'pw_log_colonys', 'attach' => 'pw_log_attachs', 'weibo' => 'pw_log_weibos' );
		if (! isset ( $tables [$type] )) {
			return 0;
		}
		$tableName = $tables [$type];
		$logsDao = $this->_getLogsDao ();
		return $logsDao->countLogsByTypeAndTime ( $tableName, $starttime, $endtime);
	}
}