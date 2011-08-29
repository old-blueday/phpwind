<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 自动申请任务系统
 * 
 * @package UserJobs
 */
class PW_AutoJob {
	var $_db = null;
	var $_hour = 3600;
	var $_timestamp = null;
	var $_cache = true;
	function PW_AutoJob() {
		global $db, $timestamp;
		$this->_db = & $db;
		$this->_timestamp = $timestamp;
	}
	
	function run($userid, $groupid) {
		return $this->jobAutoController ( $userid, $groupid );
	}
	
	function jobAutoController($userid, $groupid) {
		$userid = intval ( $userid );
		$groupid = intval ( $groupid );
		if ($groupid < 1 || $userid < 1) {
			return;
		}
		if (! $jobLists = $this->_jobAutoFilterHandler ( $userid, $groupid )) {
			return;
		}
		$current = $this->_timestamp;
		foreach ( $jobLists as $job ) {
			$this->_jobAutoCreateHandler ( $userid, $job, $current );
		}
	}
	function _jobAutoFilterHandler($userid, $groupid) {
		$jobs = $this->getJobsAuto ();
		if (! $jobs) {
			return false;
		}
		$current = $this->_timestamp;
		$jobLists = $jobIds = $periods = $preposes = array ();
		foreach ( $jobs as $job ) {
			if ($job ['isopen'] == 0) {
				continue;
			}
			if ((isset ( $job ['endtime'] ) && $job ['endtime'] != 0 && $job ['endtime'] < $current)) {
				continue;
			}
			if ((isset ( $job ['starttime'] ) && $job ['starttime'] != 0 && $job ['starttime'] > $current)) {
				continue;
			}
			if (isset ( $job ['usergroup'] ) && $job ['usergroup'] != '') {
				$usergroups = explode ( ",", $job ['usergroup'] );
				if (! in_array ( $groupid, $usergroups )) {
					continue;
				}
			}
			if (isset ( $job ['period'] ) && $job ['period'] > 0) {
				$periods [] = $job ['id'];
			}
			if (isset ( $job ['prepose'] ) && $job ['prepose'] > 0) {
				$preposes [$job ['prepose']] = $job ['id'];
			}
			if (isset ( $job ['number'] ) && $job ['number'] != 0) {
				$number = $this->countJoberByJobId ( $job ['id'] );
				if ($number >= $job ['number']) {
					continue;
				}
			}
			//实名认证
			if (S::inArray($job['job'],array('doAuthAlipay','doAuthMobile'))) {
				if (!$GLOBALS['db_authstate']) return false;
				$userService = L::loadClass('UserService','user');
				if ($job['job'] == 'doAuthAlipay' && $userService->getUserStatus($userid, PW_USERSTATUS_AUTHALIPAY)){
					return false;
				}
				if ($job['job'] == 'doAuthMobile' && $userService->getUserStatus($userid, PW_USERSTATUS_AUTHMOBILE)){
					return false;
				}
			}
			$jobLists [$job ['id']] = $job;
			$jobIds [] = $job ['id'];
		}
		if (! $jobLists) {
			return false;
		}
		$joins = $this->getJobersByJobIds ( $userid, $jobIds );
		if ($joins) {
			foreach ( $joins as $join ) {
				$t_job = array ();
				$t_job = $jobLists [$join ['jobid']];
				if (in_array ( $join ['jobid'], $periods )) {
					if ($join ['status'] >= 3 && $join ['total'] > 0) {
						if ($join ['next'] < $current) {
							$this->_jobAutoAgainHandler ( $userid, $t_job, $current );
						}
					}
				}
				unset ( $t_job );
				unset ( $jobLists [$join ['jobid']] );
			}
		}
		if (! $jobLists) {
			return false;
		}
		if ($preposes) {
			$joins = $this->getJobersByJobIds ( $userid, array_keys ( $preposes ) );
			if ($joins) {
				foreach ( $joins as $join ) {
					if ($join ['total'] > 0) {
						unset ( $preposes [$join ['jobid']] );
					}
				}
			}
			if ($preposes) {
				foreach ( $preposes as $jobid ) {
					unset ( $jobLists [$jobid] );
				}
			}
		}
		return $jobLists;
	}
	function _jobAutoAgainHandler($userid, $job, $current) {
		$next = $current;
		if (isset ( $job ['period'] ) && $job ['period'] != 0) {
			$next = $current + $job ['period'] * $this->_hour;
		}
		$job ['next'] = $next ? $next : $current;
		$this->_againJober ( $userid, $job ['id'], $job ['next'], $current );
	}
	
	function _againJober($userId, $jobId, $next, $current, $jober = array()) {
		$jober = $jober ? $jober : $this->getJoberByJobId ( $userId, $jobId );
		if (! $jober) {
			return array ();
		}
		$data = array ();
		$data ['current'] = 1;
		$data ['step'] = 0;
		$data ['last'] = $current;
		$data ['next'] = $next;
		$data ['status'] = 0;
		$result = $this->updateJober ( $data, $jober ['id'] );
		if ($result) {
			$this->increaseJobNum ( $userId );
		}
		return $result;
	}
	
	function updateJober($fields, $id) {
		$joberDao = $this->_getJoberDao ();
		return $joberDao->update ( $fields, $id );
	}
	
	function getJobsAuto() {
		if ($this->_cache) {
			$jobs = $this->getFileCache ();
			if ($jobs) {
				$autos = array ();
				foreach ( $jobs as $job ) {
					if ($job ['auto'] == 1) {
						$autos [] = $job;
					}
				}
				return $autos;
			}
		}
		$jobDao = $this->_getJobDao ();
		return $jobDao->getByAuto ();
	}
	function getFileCache() {
		if (! $this->_cache) {
			return array ();
		}
		//* @include_once pwCache::getPath ( S::escapePath ( $this->getCacheFileName () ), true );
		extract(pwCache::getData(S::escapePath ( $this->getCacheFileName ()), false));
		$jobLists = ($jobLists) ? $jobLists : $GLOBALS ['jobLists'];
		if ($jobLists) {
			return $jobLists;
		}
		return $this->setFileCache ();
	}
	function setFileCache() {
		$jobDao = $this->_getJobDao ();
		$jobs = $jobDao->getAll ();
		$jobLists = "\$jobLists=" . pw_var_export ( $jobs ) . ";";
		pwCache::setData ( $this->getCacheFileName (), "<?php\r\n" . $jobLists . "\r\n?>" );
		return $jobs;
	}
	function _jobAutoCreateHandler($userid, $job, $current) {
		if (isset ( $job ['period'] ) && $job ['period'] != 0) {
			$next = $current + $job ['period'] * $this->_hour;
			$job ['next'] = $next ? $next : $current;
		}
		$this->_createJober ( $userid, $job ['id'], $job ['next'], $current );
	}
	
	function _createJober($userId, $jobId, $next, $current, $jober = array()) {
		$jober = $jober ? $jober : $this->getJoberByJobId ( $userId, $jobId );
		if ($jober) {
			return array ();
		}
		$data = array ();
		$data ['jobid'] = $jobId;
		$data ['userid'] = $userId;
		$data ['current'] = 1;
		$data ['step'] = 0;
		$data ['last'] = $current;
		$data ['next'] = $next;
		$data ['status'] = 0;
		$data ['creattime'] = $current;
		return $this->addJober ( $data );
	}
	function getCacheFileName() {
		return R_P . "data/bbscache/jobs.php";
	}
	function getJobersByJobIds($userid, $ids) {
		$joberDao = $this->_getJoberDao ();
		return $joberDao->getJobersByJobIds ( $userid, $ids );
	}
	function addJober($fields) {
		$fields ['userid'] = intval ( $fields ['userid'] );
		$fields ['jobid'] = intval ( $fields ['jobid'] );
		if ($fields ['userid'] < 1 || $fields ['jobid'] < 1) {
			return null;
		}
		$joberDao = $this->_getJoberDao ();
		$result = $joberDao->add ( $fields );
		if ($result) {
			$this->increaseJobNum ( $fields ['userid'] );
		}
		return $result;
	}
	function increaseJobNum($userid) {
		$this->updateJobNum ( $userid );
	}
	function updateJobNum($userid) {
		$jobnum = $this->countJobnum ( $userid );
		($jobnum > 0) ? $jobnum : 0;
		$userService = L::loadClass ( 'UserService', 'user' );
		return $userService->update ( $userid, array (), array ('jobnum' => $jobnum ) );
	}
	function countJobNum($userId) {
		if (! $userId)
			return false;
		$joblists = $this->getAppliedJobs ( $userId );
		$joblists = $joblists ? $joblists : array ();
		$num = 0;
		foreach ( $joblists as $job ) {
			if ($job ['isopen'] == 0 || $job['isuserguide'])
				continue;
			$num ++;
		}
		return $num;
	}
	function getAppliedJobs($userid) {
		$joberDao = $this->_getJoberDao ();
		$jobers = $joberDao->getAppliedJobs ( $userid );
		if (! $jobers) {
			return array ();
		}
		return $this->buildJobListByIds ( $jobers );
	}
	function buildJobListByIds($jobers) {
		if (! $jobers) {
			return array ();
		}
		$jobIds = $tmp = array ();
		foreach ( $jobers as $job ) {
			$jobIds [] = $job ['jobid'];
			$tmp [$job ['jobid']] = $job;
		}
		$jobs = $this->getJobsByIds ( $jobIds );
		if (! $jobs) {
			return array ();
		}
		$result = array ();
		foreach ( $jobs as $job ) {
			$result [] = array_merge ( $tmp [$job ['id']], $job );
		}
		return $result;
	}
	function getJobsByIds($jobIds) {
		if ($this->_cache) {
			$jobs = $this->getFileCache ();
			if ($jobs) {
				$result = array ();
				foreach ( $jobs as $job ) {
					if (in_array ( $job ['id'], $jobIds )) {
						$result [] = $job;
					}
				}
				return $result;
			}
		}
		$jobDao = $this->_getJobDao ();
		return $jobDao->getByIds ( $jobIds );
	}
	function getJoberByJobId($userId, $jobId) {
		$joberDao = $this->_getJoberDao ();
		return $joberDao->getByJobId ( $userId, $jobId );
	}
	function countJoberByJobId($jobid) {
		$joberDao = $this->_getJoberDao ();
		return $joberDao->countByJobId ( $jobid );
	}
	function _getJobDao() {
		$job = L::loadDB ( 'job', 'job' );
		return $job;
	}
	function _getJoberDao() {
		$job = L::loadDB ( 'jober', 'job' );
		return $job;
	}
	function _getJobDoerDao() {
		$job = L::loadDB ( 'jobdoer', 'job' );
		return $job;
	}
}