<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 用户任务系统
 * 
 * @package UserJobs
 */
class PW_Job {
	var $_db = null;
	var $_hour = 3600;
	var $_timestamp = null;
	var $_cache = true;
	function PW_Job() {
		global $db, $timestamp;
		$this->_db = & $db;
		$this->_timestamp = $timestamp;
	}
	/*
	function run($userid, $groupid) {
		//$this->jobAutoController($userid, $groupid);
	}
	*/
	/**
	 * 检查是否可以申请任务
	 */
	function checkApply($id, $userId, $groupid, $job = array()) {
		$id = intval($id);
		if ($id < 1) {
			return array(
				false,
				$this->getLanguage("job_id_error"),
				''
			);
		}
		$job = $job ? $job : $this->getJob($id);
		if (!$job) {
			return array(
				false,
				$this->getLanguage("job_not_exist"),
				''
			);
		}
		/*任务是否关闭*/
		if ($job['isopen'] == 0) {
			return array(
				false,
				$this->getLanguage("job_close"),
				''
			);
		}
		/*用户组限制*/
		if (isset($job['usergroup']) && $job['usergroup'] != "") {
			if (!in_array($groupid, explode(",", $job['usergroup']))) {
				return array(
					false,
					$this->getLanguage("job_usergroup_limit"),
					''
				);
			}
		}
		/*人数限制 当前申请人数*/
		if (isset($job['number']) && $job['number'] != 0) {
			$number = $this->countJoberByJobId($job['id']);
			if ($number >= $job['number']) {
				return array(
					false,
					$this->getLanguage("job_apply_number_limit"),
					''
				);
			}
		}
		$current = $next = $this->_timestamp;
		/*时间限制 当前申请人数*/
		if ((isset($job['endtime']) && $job['endtime'] != 0 && $job['endtime'] < $current)) {
			return array(
				false,
				$this->getLanguage("job_time_limit"),
				''
			);
		}
		if ((isset($job['starttime']) && $job['starttime'] != 0 && $job['starttime'] > $current)) {
			return array(
				false,
				$this->getLanguage("job_time_early"),
				''
			);
		}
		/*是否存在前置任务*/
		if (isset($job['prepose']) && $job['prepose'] != 0) {
			$prepose = $this->getJob($job['prepose']);
			if ($prepose) {
				/*是否已经完成前置任务*/
				$jober = $this->getJoberByJobId($userId, $prepose['id']);
				if (!$jober) {
					return array(
						false,
						$this->getLanguage("job_has_perpos") . $prepose['title'],
						''
					);
				}
				if ($jober['status'] != 3) {
					return array(
						false,
						$this->getLanguage("job_has_perpos_more") . $prepose['title'],
						''
					);
				}
			}
		}
		//是否已经申请
		$hasApply = $this->getJoberByJobId($userId, $id);
		//一次性任务过滤
		if ($hasApply && $hasApply['total'] > 0 && $job['period'] < 1) {
			return array(
				false,
				"你已经完成这个任务",
				""
			);
		}
		//任务是否为周期性任务 用用户是否已经申请，下次开始的时间
		$again = 0;
		if (isset($job['period']) && $job['period'] != 0) {
			//如果已经申请，检查是否到下次申请时间
			if ($hasApply && $hasApply['next'] > $current) {
				return array(
					false,
					$this->getLanguage("job_apply_next_limit"),
					''
				);
			}
			if ($hasApply && $hasApply['next'] < $current) {
				$again = 1;
			}
			$next = $current + $job['period'] * $this->_hour;
		}
		$job['next'] = $next;
		//老用户申请限制
		if (($hasApply && $again == 0)) {
			return array(
				false,
				$this->getLanguage("job_has_apply"),
				''
			);
		}
		return array(
			true,
			$this->getLanguage("job_apply_success"),
			$job
		);
	}
	
	function appendJobDetailInfo($jobs) {
		$list = array();
		foreach ($jobs as $key => $job) {
			if ($job['isopen'] == 0)  continue;

			$job['icon'] = (isset($job['icon']) && $job['icon'] != "") ? "attachment/job/" . $job['icon'] : "images/job/" . strtolower($job['job']) . ".gif";
			
			$reward = '';
			if (isset($job['reward'])) $reward = $this->getCategoryInfo($job['reward']);
			$job['reward'] = $reward ? $reward : array();
		
			if ($job['status'] < 2) {
				$jobClass = $this->loadJob(strtolower($job['job']));
				$job['link'] = $jobClass ? $jobClass->getUrl($job) : "";
			}
			
			if (isset($job['status'])) {
				$operationTypes = array(0 => 'start', 1 => 'start', 2 => 'gain');
				$job['operationType'] = isset($operationTypes[$job['status']]) ? $operationTypes[$job['status']] : 'start';
				if (!$job['factor']) $job['operationType'] = 'gain';
			} else {
				$job['operationType'] = 'apply';
			}
			
			$job['condition'] = $this->getCondition($job);
			$list[$key] = $job;
		}

		return $list;
	}
	
	function buildLists($joblists, $action, $userId, $groupid) {
		if (!$joblists) {
			return array();
		}
		$jobs = array();
		foreach($joblists as $job) {
			//显示条件 是否显示
			if ($job['isopen'] == 0) {
				continue;
			}
			$lists = array();
			$lists['id'] = $job['id'];
			$lists['title'] = $job['title'];
			$lists['description'] = html_entity_decode($job['description']);
			$lists['period'] = ($job['period']) ? '每隔' . $job['period'] . '小时可以申请一次' : "一次性任务";
			$reward = '';
			if (isset($job['reward'])) {
				$reward = implode(' ', $this->getCategoryInfo($job['reward']));
			}
			$lists['reward'] = $reward ? $reward : "无";
			$lists['number'] = (isset($job['number']) && $job['number'] != 0) ? $job['number'] . "人" : "";
			$isFactor = (isset($job['factor']) && $job['factor'] != '') ? true : false;
			if ($isFactor) {
				$factor = unserialize($job['factor']);
			}
			$lists['timelimit'] = (isset($factor['limit']) && $factor['limit'] != "") ? $factor['limit'] : "不限制";
			/*前置任务*/
			$prepose = $doPrepose = '';
			if (isset($job['prepose']) && $job['prepose'] != 0) {
				$prepose = $this->getJob($job['prepose']); /*是否完成*/
				$prepose = "(必须完成 " . $prepose['title'] . " 才能申请)";
				$preposeJob = $this->getJoberByJobId($userId, $job['prepose']);
				$doPrepose = ($preposeJob && $preposeJob['total'] > 0) ? true : false;
			}
			$lists['prepose'] = $prepose ? $prepose : "";
			$lists['icon'] = (isset($job['icon']) && $job['icon'] != "") ? "attachment/job/" . $job['icon'] : "images/job/" . strtolower($job['job']) . ".gif";
			$lists['condition'] = $this->getCondition($job);
			$lists['usergroup'] = (isset($job['usergroup']) && $job['usergroup'] != '') ? $this->getUserGroup($job['usergroup']) : '';
			if ($action == "list") { /*可申请*/
				/*任务还没有开始*/
				if (!$this->checkJobCondition($userId, $groupid, $job) || ($prepose && !$doPrepose)) { /*申请条件*/
					$lists['btn'] = $this->getJobBtn($job['id'], "apply_old");
				} else {
					$lists['btn'] = $this->getJobBtn($job['id'], "apply");
				}
			} elseif (empty($action) || $action == "applied") { /*已申请*/
				$lists['gain'] = $lists['qbtn'] = '';
				if (isset($job['status'])) {
					$status = $job['status'];
					$info = ($job['period'] > 0) ? "是否确认放弃本次任务" : "本次任务为一次性任务，放弃后将不可再次申请。是否确认放弃本次任务";
					if ($status < 2) { /*url 引导*/
						$jobClass = $this->loadJob(strtolower($job['job']));
						$link = $jobClass ? $jobClass->getUrl($job) : "";
					}
					if ($status == 0) {
						/*是否周期性*/
						$lists['btn'] = $this->getJobBtn($job['id'], "start", $link);
						($job['finish'] == 0) && $lists['qbtn'] = $this->getJobBtn($job['id'], "quit", $info); /*是否可放弃*/
					} elseif ($status == 1) {
						$lists['btn'] = $this->getJobBtn($job['id'], "start_old", $link);
						($job['finish'] == 0) && $lists['qbtn'] = $this->getJobBtn($job['id'], "quit", $info);
					} elseif ($status == 2) {
						$lists['gain'] = "(100%)"; /*完成提示*/
						$lists['btn'] = $this->getJobBtn($job['id'], "gain");
					}
				}
				if (!$isFactor) {
					$lists['gain'] = "(100%)";
					$lists['btn'] = $this->getJobBtn($job['id'], "gain");
					$lists['qbtn'] = '';
				}
				//任务完成进度条
				$lists['degree'] = '100%';
			} elseif ($action == "finish") { /*已完成*/
				$lists['lastfinish'] = "最后完成于 " . get_date($job['last'], "Y-m-d H:i");
				$lists['btn'] = '';
				if (isset($job['period']) && $job['period'] != 0) {
					$lists['btn'] = '<a  href="javascript:;" id="apply_' . $job['id'] . '" class="tasks_again">再次申请</a>';
				}
			} elseif ($action == "quit") { /*已放弃*/
				if (isset($job['period']) && $job['period'] != 0) {
					$lists['btn'] = $this->getJobBtn($job['id'], "apply");
				}
				/*失败或放弃*/
				$lists['info'] = ($job['status'] == 5) ? "任务失败" : "放弃于 " . get_date($job['last'], "Y-m-d H:i");
			}
			$jobs[] = $lists;
		}
		return $jobs;
	}
		
	function getJobBtn($id, $k, $info = '') {
		$job && $job = strtolower($job);
		$btn = array();
		$btn['apply'] = '<a href="javascript:;" class="tasks_apply" hidefocus="true" id="apply_' . $id . '">立即申请</a>';
		$btn['apply_old'] = '<a href="javascript:;" class="tasks_apply_old" hidefocus="true" title="不满足申请条件" id="apply_' . $id . '">立即申请</a>';
		$btn['start'] = '<a href="javascript:;" link="' . $info . '" class="tasks_startB" hidefocus="true" id="start_' . $id . '">立即开始</a>';
		$btn['start_old'] = '<a href="javascript:;" link="' . $info . '" class="tasks_startB_old" hidefocus="true" id="start_' . $id . '">立即开始</a>';
		$btn['quit'] = '<a href="javascript:;" class="tasks_quit" hidefocus="true" id="quit_' . $id . '" info="' . $info . '">放弃</a>';
		$btn['gain'] = '<a href="javascript:;"  hidefocus="true" id="gain_' . $id . '" class="tasks_receiving">领取奖励</a>';
		return $btn[$k];
	}
	/*
	* 任务开始控制中心，获取任务开始后链接
	*/
	function jobStartController($userid, $jobid) {
		$jobid = intval($jobid);
		if ($jobid < 1) {
			return array(
				false,
				"抱歉，任务ID无效",
				''
			);
		}
		//是否存在这个任务
		$job = $this->getJob($jobid);
		if (!$job) {
			return array(
				false,
				"抱歉，任务不存在",
				''
			);
		}
		$current = $this->_timestamp;
		if (isset($job['end']) && $job['end'] != 0 && $job['end'] > $current) {
			return array(
				false,
				"抱歉，你申请的任务已经结束",
				''
			);
		}
		$jober = $this->getJoberByJobId($userid, $jobid);
		if (!$jober) {
			return array(
				false,
				"抱歉，你还没有申请这个任务",
				''
			);
		}
		if ($jober['status'] > 1) {
			return array(
				false,
				"抱歉，你已经开始了这个任务",
				''
			);
		}
		//if($jober['next']>$current){
		//	return array(false,"抱歉，还没有到执行任务的时间",'');
		//}
		$jobClass = $this->loadJob(strtolower($job['job']));
		if (!$jobClass) {
			return array(
				false,
				"抱歉，任务有误，请重试",
				''
			);
		}
		$link = $jobClass->getUrl($job);
		//更新任务状态
		if ($jober['status'] == 0) {
			$this->updateJober(array(
				"status" => 1
			), $jober['id']);
		}
		return array(
			true,
			"",
			$link
		);
	}
	/*
	* 任务调度控制中心
	*/
	function jobController($userid, $jobName, $factor = array()) {
		$jobName = trim($jobName);
		$jobs = $this->getJobByJobName($jobName);
		if (!$jobs) {
			return array();
		}
		$jobIds = $tmp = array();
		foreach($jobs as $job) {
			$jobIds[] = $job['id'];
			$tmp[$job['id']] = $job;
		}
		$jober = $this->getJoberByJobIds($userid, $jobIds);
		if ($jober) {
			//任务完成
			if ($jober['status'] >= 2) {
				return array();
			}
			$job = $tmp[$jober['jobid']]; /*当前任务*/
			if ($jober['total'] > 0 && $job['period'] < 1) {
				return array();
			}
			$current = $next = $this->_timestamp;
			/*是否周期性任务*/
			if (isset($job['period']) && $job['period'] != 0) {
				$next = $current + $job['period'] * $this->_hour;
			}
			$status = $this->jobFinishController($job, $jober, $factor); /*任务完成状态*/
			if ($status == 0) {
				return array();
			}
			$data = array();
			//($status > 2 ) &&  $data['last'] = $current;
			$data['current'] = $jober['current'] + 1; /*当前步数*/
			$data['step'] = $jober['step'] + 1; /*总步数*/
			$data['next'] = $next; /*周期性任务下一个时间开始点*/
			$data['status'] = $status;
			$this->updateJober($data, $jober['id']);
		}
	}
	function jobFinishController($job, $jober, $factor = array()) {
		if (!$factor) {
			return 2; /*为空表示直接完成任务*/
		}
		/*任务状态*/
		/*没有条件的任务*/
		$isFactor = (isset($job['factor']) && $job['factor'] != "") ? true : false;
		if (!$isFactor) {
			return 2;
		}
		$jobClass = $this->loadJob($job['job']);
		return $jobClass->finish($job, $jober, $factor);
	}
	function jobApplyController($userId, $jobId) {
		$job = $this->getJob($jobId);
		if (!$job) {
			return array();
		}
		$current = $this->_timestamp;
		$jober = $this->getJoberByJobId($userId, $jobId);
		if (isset($job['period']) && $job['period'] != 0) {
			$next = $current + $job['period'] * $this->_hour;
		}
		//检查任务是否存在
		if ($jober && $jober['total'] > 0 && $job['period'] < 1) {
			return array();
		}
		if ($jober && $job['period'] > 0 && $jober['status'] > 1 && ($jober['total'] > 0 || ($jober['status'] == 4))) {
			return $this->_againJober($userId, $jobId, $next, $current, $jober);
		}
		if (!$jober) {
			return $this->_createJober($userId, $jobId, $next, $current, $jober);
		}
		return array();
	}
	function _createJober($userId, $jobId, $next, $current, $jober = array()) {
		$jober = $jober ? $jober : $this->getJoberByJobId($userId, $jobId);
		if ($jober) {
			return array();
		}
		$data = array();
		$data['jobid'] = $jobId;
		$data['userid'] = $userId;
		$data['current'] = 1; /*当前步数*/
		$data['step'] = 0; /*总步数*/
		$data['last'] = $current;
		$data['next'] = $next; /*周期性任务下一个时间开始点*/
		$data['status'] = 0;
		$data['creattime'] = $current;
		return $this->addJober($data);
	}
	function _againJober($userId, $jobId, $next, $current, $jober = array()) {
		$jober = $jober ? $jober : $this->getJoberByJobId($userId, $jobId);
		if (!$jober) {
			return array();
		}
		$data = array();
		$data['current'] = 1; /*当前步数*/
		$data['step'] = 0; /*总步数*/
		$data['last'] = $current;
		$data['next'] = $next; /*周期性任务下一个时间开始点*/
		$data['status'] = 0;
		$result = $this->updateJober($data, $jober['id']);
		if ($result) {
			$this->increaseJobNum($userId);
		}
		return $result;
	}
	/*
	* 展示控制
	*/
	function jobDisplayController($userid, $groupid, $action) {
		return $this->buildLists($this->getCanApplyJobs($userid, $groupid), $action, $userid, $groupid);
	}
	function getCanApplyJobs($userid, $groupid) {
		$joblists = $this->getJobAll();
		if (!$joblists) {
			return array();
		}
		$current = $this->_timestamp;
		$jobs = array();
		/*过滤部分*/
		$jobIds = array();
		foreach($joblists as $job) {
			$jobIds[] = $job['id'];
		}
		/*是否已经参加*/
		$joins = $this->getJobersByJobIds($userid, $jobIds);
		$jobers = array();
		if ($joins) {
			foreach($joins as $join) {
				$jobers[$join['jobid']] = $join;
			}
		}
		foreach($joblists as $job) {
			/*是否开启任务*/
			if ($job['isopen'] == 0) {
				continue;
			}
			/*开启符合条件才显示*/
			if ($job['display'] == 1) {
				if (!$this->checkJobCondition($userid, $groupid, $job)) {
					continue;
				}
			}
			/*任务是否已经申请*/
			//$isApplied = $this->getJoberByJobId($userid,$job['id']);
			$isApplied = (isset($jobers[$job['id']])) ? $jobers[$job['id']] : '';
			if ($isApplied && $isApplied['status'] <= 2) {
				continue;
			}
			/*周期性任务*/
			if ($isApplied && $job['period'] == 0) {
				continue;
			}
			if ((isset($job['endtime']) && $job['endtime'] != 0 && $job['endtime'] < $current)) {
				continue;
			}
			$jobs[] = $job;
		}
		return $jobs;
	}
	/*检查任务条件是否符合*/
	function checkJobCondition($userId, $groupid, $job) {
		//用户组条件限制
		if (isset($job['usergroup']) && $job['usergroup'] != '') {
			$usergroups = explode(",", $job['usergroup']);
			if (!in_array($groupid, $usergroups)) {
				return false;
			}
		}
		//申请人数条件限制
		if (isset($job['number']) && $job['number'] > 0) {
			$number = $this->countJoberByJobId($job['id']);
			if ($number >= $job['number']) {
				return false;
			}
		}
		//前置任务
		if (isset($job['prepose']) && $job['prepose'] > 0) {
			$prepose = $this->getJob($job['prepose']);
			if ($prepose) {
				$jober = $this->getJoberByJobId($userId, $prepose['id']);
				if (!$jober) {
					return false; /*前置任务没完成*/
				}
				if ($jober['status'] != 3) {
					return false;
				}
			}
		}
		return true;
	}
	/*
	* 自动申请控制
	*/
	/*
	function jobAutoController($userid, $groupid) {
		$userid = intval($userid);
		$groupid = intval($groupid);
		if ($groupid < 1 || $userid < 1) {
			return;
		}
		if (!$jobLists = $this->_jobAutoFilterHandler($userid, $groupid)) {
			return;
		}
		$current = $this->_timestamp;
		foreach($jobLists as $job) {
			$this->_jobAutoCreateHandler($userid, $job, $current);
		}
	}
	*/
	
	/*
	* 自动申请增加一条可重复申请的用户任务
	*/
	/*
	function _jobAutoAgainHandler($userid, $job, $current) {
		$next = $current;
		if (isset($job['period']) && $job['period'] != 0) {
			$next = $current + $job['period'] * $this->_hour;
		}
		$job['next'] = $next ? $next : $current;
		$this->_againJober($userid, $job['id'], $job['next'], $current);
	}
	*/
	/*
	* 自动申请任务过滤
	* 简化SQL查询次数
	* 周期性自动申请任务则直接更新/周期性人数限制任务则直接查询
	*/
//	function _jobAutoFilterHandler($userid, $groupid) {
//		$jobs = $this->getJobsAuto();
//		if (!$jobs) {
//			return false;
//		}
//		$current = $this->_timestamp;
//		$jobLists = $jobIds = $periods = $preposes = array();
//		/*过滤任务申请*/
//		foreach($jobs as $job) {
//			/*任务状态过滤*/
//			if ($job['isopen'] == 0) {
//				continue;
//			}
//			/*时间限制过滤*/
//			if ((isset($job['endtime']) && $job['endtime'] != 0 && $job['endtime'] < $current)) {
//				continue;
//			}
//			if ((isset($job['starttime']) && $job['starttime'] != 0 && $job['starttime'] > $current)) {
//				continue;
//			}
//			if (isset($job['usergroup']) && $job['usergroup'] != '') { /*用户组过滤*/
//				$usergroups = explode(",", $job['usergroup']);
//				if (!in_array($groupid, $usergroups)) {
//					continue;
//				}
//			}
//			if (isset($job['period']) && $job['period'] > 0) {
//				$periods[] = $job['id']; /*周期性任务过滤*/
//			}
//			if (isset($job['prepose']) && $job['prepose'] > 0) {
//				$preposes[$job['prepose']] = $job['id']; /*前置任务过滤*/
//			}
//			/*人数限制 当前申请人数*/
//			if (isset($job['number']) && $job['number'] != 0) {
//				$number = $this->countJoberByJobId($job['id']);
//				if ($number >= $job['number']) {
//					continue;
//				}
//			}
//			$jobLists[$job['id']] = $job;
//			$jobIds[] = $job['id'];
//		}
//		if (!$jobLists) {
//			return false;
//		}
//		/*是否已经参加过，并结合是否是周期性任务*/
//		$joins = $this->getJobersByJobIds($userid, $jobIds);
//		if ($joins) {
//			foreach($joins as $join) {
//				//如果是周期性的重复任务，则直接自动更新申请
//				$t_job = array();
//				$t_job = $jobLists[$join['jobid']];
//				if (in_array($join['jobid'], $periods)) {
//					if ($join['status'] >= 3 && $join['total'] > 0) {
//						/*时间间隔计算 下一次执行时间*/
//						if ($join['next'] < $current) {
//							$this->_jobAutoAgainHandler($userid, $t_job, $current);
//						}
//					}
//				}
//				unset($t_job);
//				unset($jobLists[$join['jobid']]); /*清除已经参加的记录，不是周期性任务*/
//			}
//		}
//		if (!$jobLists) {
//			return false;
//		}
//		/*是否有前置任务*/
//		if ($preposes) {
//			$joins = $this->getJobersByJobIds($userid, array_keys($preposes));
//			if ($joins) {
//				foreach($joins as $join) {
//					if ($join['total'] > 0) {
//						unset($preposes[$join['jobid']]); /*放过已经完成的任务*/
//					}
//				}
//			}
//			/*剩下都是些没有完成前置任务的*/
//			if ($preposes) {
//				foreach($preposes as $jobid) {
//					unset($jobLists[$jobid]); /*过滤*/
//				}
//			}
//		}
//		return $jobLists;
//	}
	/*
	* 放弃任务控制
	*/
	function jobQuitController($userid, $jobId) {
		$jobId = intval($jobId);
		if ($jobId < 1) {
			return array(
				false,
				"任务ID无效"
			);
		}
		$job = $this->getJob($jobId);
		if (!$job) {
			return array(
				false,
				"任务不存在"
			);
		}
		if ($job['finish'] == 1) {
			return array(
				false,
				"该任务必须完成，不能放弃"
			);
		}
		$jober = $this->getJoberByJobId($userid, $jobId);
		if (!$jober) {
			return array(
				false,
				"抱歉，你还没有申请这个任务"
			);
		}
		if ($jober && $jober['total'] > 0 && $job['period'] < 1) {
			return array(
				false,
				"抱歉，任务为一次性任务，你已经完成"
			);
		}
		if ($jober && $jober['status'] == 3) {
			return array(
				false,
				"抱歉，你已经完成这个任务"
			);
		}
		if ($jober && $jober['status'] > 1) {
			return array(
				false,
				"抱歉，请检查是否完成任务"
			);
		}
		$result = $this->updateJoberByJobId(array(
			'status' => 4
		), $jobId, $userid);
		if (!$result) {
			return array(
				false,
				"放弃任务失败，请重试"
			);
		}
		$this->reduceJobNum($userid);
		return array(
			true,
			"放弃任务完成"
		);
	}
	/*
	* 领取奖励控制
	*/
	function jobGainController($userid, $jobid) {
		$jobid = intval($jobid);
		if ($jobid < 1) {
			return array(
				false,
				"抱歉，任务ID无效"
			);
		}
		//是否存在这个任务
		$job = $this->getJob($jobid);
		if (!$job) {
			return array(
				false,
				"抱歉，任务不存在"
			);
		}
		if (procLock('job_save', $userid)) {
			if ($userid != '5') {
				$jober = $this->getJoberByJobId($userid, $jobid);
				if (!$jober) {
					return $this->_unlockUserJob($userid, array(
						false,
						"抱歉，你还没有申请这个任务"
					));
				}
			}
			/*检查是否是一次性任务或完成*/
			if (!$job['period'] && $jober['total'] > 1) {
				return $this->_unlockUserJob($userid, array(
					false,
					"抱歉，你已经完成这个任务"
				));
			}
			/*任务时间限制 start*/
			$timeout = 0;
			$factor = (isset($job['factor']) && $job['factor'] != "") ? unserialize($job['factor']) : array();
			if ($factor && isset($factor['limit']) && $factor['limit'] > 0) {
				if ($jober['last'] + $factor['limit'] * $this->_hour < $this->_timestamp) {
					$timeout = 1;
				}
			}
			/*下次执行时间*/
			if (isset($job['period']) && $job['period'] > 0) {
				$next = $this->_timestamp + $job['period'] * $this->_hour;
				$next = $next ? $next : $this->_timestamp;
			}
			if ($timeout) {
				$this->updateJober(array(
					'status' => 5,
					'next' => $next
				), $jober['id']);
				$this->reduceJobNum($userid);
				return $this->_unlockUserJob($userid, array(
					true,
					"抱歉，任务没有在规定的时间内完成"
				));
			}
			/*任务时间限制 end */
			if ($factor) {
				if ($jober['status'] < 2) {
					return $this->_unlockUserJob($userid, array(
						true,
						"抱歉，你还没有完成任务"
					));
				}
				if ($jober['status'] > 3) {
					return $this->_unlockUserJob($userid, array(
						true,
						"抱歉，数据错误，请重试"
					));
				}
			}
			if ($jober['status'] == 3) {
				return $this->_unlockUserJob($userid, array(
					true,
					"抱歉，你已经领取过奖励，不能重复领取"
				));
			}
			$data = array();
			$data['status'] = 3; /*任务完成*/
			$data['total'] = $jober['total'] + 1;
			$data['next'] = $next;
			$result = $this->updateJober($data, $jober['id']);
			if (!$result) {
				return $this->_unlockUserJob($userid, array(
					false,
					"抱歉，领取奖励失败，请重试"
				));
			}
			if (isset($job['reward'])) {
				$this->jobRewardHandler($userid, $job);
			}
			$this->reduceJobNum($userid); /*任务完成*/
			$information = implode(' ', $this->getCategoryInfo($job['reward']));
			$information = $information ? "，" . $information : "";
			return $this->_unlockUserJob($userid, array(
				true,
				"恭喜你完成任务" . $information
			));
		} else {
			return array(
				false,
				"抱歉，领取奖励失败，请重试"
			);
		}
	}
	
	function _unlockUserJob($userId, $returnData) {
		procUnLock('job_save', $userId);
		return $returnData;
	}
	
	/*获取任务奖励*/
	function jobRewardHandler($userid, $job) {
		if (!isset($job['reward'])) {
			return array();
		}
		$reward = unserialize($job['reward']);
		$category = $reward['category'];
		switch ($category) {
			case "credit":
				$this->jobRewardCredit($userid, $reward, $job);
				break;

			case "tools":
				$this->jobRewardTools($userid, $reward);
				break;

			case "medal":
				$this->jobRewardMedal($userid, $reward);
				break;

			case "usergroup":
				$this->jobRewardUsergroup($userid, $reward);
				break;

			case "invitecode":
				$this->jobRewardInviteCode($userid, $reward);
				break;

			default:
				return "无";
				break;
		}
	}
	/*积分奖励*/
	function jobRewardCredit($userid, $reward, $job) {
		global $credit;
		(!S::isObj($credit)) && require_once R_P . "require/credit.php";
		$userService = $this->_getUserService();
		$user = $userService->get($userid);
		$GLOBALS[job] = $job['title']; /*任务名称*/
		$credit->addLog('other_finishjob', array(
			$reward['type'] => $reward['num']
		), array(
			'uid' => $userid,
			'username' => $user['username'],
			'ip' => $GLOBALS['onlineip']
		));
		$credit->set($userid, $reward['type'], $reward['num']);
	}
	/*道具奖励*/
	function jobRewardTools($userid, $reward) {
		/*数据初始化*/
		$toolid = $reward['type'];
		$nums = $reward['num'];
		$this->_db->pw_update("SELECT uid FROM pw_usertool WHERE uid=" . S::sqlEscape($userid) . " AND toolid=" . S::sqlEscape($toolid), "UPDATE pw_usertool SET nums=nums+" . S::sqlEscape($nums) . " WHERE uid=" . S::sqlEscape($userid) . " AND toolid=" . S::sqlEscape($toolid), "INSERT INTO pw_usertool SET " . S::sqlSingle(array(
			'nums' => $nums,
			'uid' => $userid,
			'toolid' => $toolid,
			'sellstatus' => 0
		)));
	}
	/*勋章奖励*/
	function jobRewardMedal($userid, $reward) {
		$medalId = $reward['type'];
		//检查用户是否存在同样勋章
		$userService = $this->_getUserService();
		$user = $userService->get($userid);
		if (!$user) {
			return false;
		}
		
		$medals = array();
		if (isset($user['medals'])) {
			$medals = explode(",", $user['medals']);
			if (in_array($medalId, $medals)) {
				return true;
			}
			$medals[] = $medalId;
			$medalIds = implode(",", $medals);
		} else {
			$medalIds = $medalId;
		}
		$userService->update($userid, array('medals' => $medalIds));
		
		$medaluser = array(
			'uid' => $userid,
			'mid' => $medalId
		);
		$this->_db->update("INSERT INTO pw_medaluser SET " . S::sqlSingle($medaluser));
	}
	/*用户组奖励*/
	function jobRewardUsergroup($userid, $reward) {
		global $winddb;
		$gid = $reward['type'];
		$days = $reward['day'];
		$timestamp = $this->_timestamp;
		
		$userService = $this->_getUserService();
		$mb = $userService->get($userid);
		$groups = $mb['groups'] ? $mb['groups'] . $gid . ',' : ",$gid,";
		$userService->update($userid, array('groups' => $groups));
		
		$this->_db->pw_update("SELECT uid FROM pw_extragroups WHERE uid=" . S::sqlEscape($userid) . " AND gid=" . S::sqlEscape($gid), "UPDATE pw_extragroups SET " . S::sqlSingle(array(
			'togid' => $winddb['groupid'],
			'startdate' => $timestamp,
			'days' => $days
		)) . " WHERE uid=" . S::sqlEscape($userid) . "AND gid=" . S::sqlEscape($gid), "INSERT INTO pw_extragroups SET " . S::sqlSingle(array(
			'uid' => $userid,
			'togid' => $winddb['groupid'],
			'gid' => $gid,
			'startdate' => $timestamp,
			'days' => $days
		)));
	}
	/*注册邀请码奖励*/
	function jobRewardInviteCode($userid, $reward) {
		$timestamp = $this->_timestamp;
		$invnum = $reward['num'];
		$day = $reward['day'];
		for ($i = 0; $i < $invnum; $i++) {
			$invcode = randstr(16);
			$this->_db->update("INSERT INTO pw_invitecode" . " SET " . S::sqlSingle(array(
				'invcode' => $invcode,
				'uid' => $userid,
				'usetime' => $day,
				'createtime' => $timestamp
			)));
		}
	}
	/*
	* 获取他人完成任务情况
	*/
	function jobDetailHandler($userid, $jobid) {
		$total = $this->countJobersByJobIdAndUserId($userid, $jobid);
		if (!$total) {
			return array(
				'',
				0
			);
		}
		$others = $this->getJobersByJobIdAndUserId($userid, $jobid);
		$userIds = array();
		foreach($others as $other) {
			$userIds[] = $other['userid'];
		}
		if (!$userIds) {
			return array(
				'',
				0
			);
		}
		/*获取用户信息*/
		require_once (R_P . 'require/showimg.php');
		$userService = $this->_getUserService();
		$users = array();
		foreach ($userService->getByUserIds($userIds) as $rs) {
			list($rs['face']) = showfacedesign($rs['icon'], 1, 's');//统一小图标
			$users[] = $rs;
		}
		return array(
			$users,
			$total
		);
	}
	function getUserGroup($usergroup) {
		//list($result , $selects) = $this->getLevels();
		list($result, $selects) = $this->getCacheLevels();
		$usergroups = explode(",", $usergroup);
		$groupinfo = '';
		foreach($usergroups as $usergroup) {
			if (isset($selects[$usergroup])) {
				$groupinfo .= $selects[$usergroup] . ',';
			}
		}
		$groupinfo = trim($groupinfo, ',');
		return $groupinfo;
	}
	/* 数据操作部分 */
	function addJob($fields) {
		$jobDao = $this->_getJobDao();
		$result = $jobDao->add($fields);
		if ($result) {
			$this->setFileCache();
		}
		return $result;
	}
	function updateJob($fields, $id) {
		$jobDao = $this->_getJobDao();
		$result = $jobDao->update($fields, $id);
		if ($result) {
			$this->setFileCache();
		}
		return $result;
	}
	function getJobs($page, $prepage) {
		if ($page < 1) return false;
		$start = ($page - 1) * $prepage;
		$jobDao = $this->_getJobDao();
		return $jobDao->gets($start, $prepage);
	}
	function countJobs() {
		$jobDao = $this->_getJobDao();
		return $jobDao->count();
	}
	function getJobAll() {
		//file cache
		if ($this->_cache) {
			$jobs = $this->getFileCache();
			if ($jobs) {
				return $jobs;
			}
		}
		$jobDao = $this->_getJobDao();
		return $jobDao->getAll();
	}
	function getJob($id) {
		//file cache
		if ($this->_cache) {
			$jobs = $this->getFileCache();
			if ($jobs) {
				foreach($jobs as $job) {
					if ($job['id'] == $id) {
						return $job;
					}
				}
			}
		}
		$jobDao = $this->_getJobDao();
		return $jobDao->get($id);
	}
	/*
	function getJobsAuto() {
		//file cache
		if ($this->_cache) {
			$jobs = $this->getFileCache();
			if ($jobs) {
				$autos = array();
				foreach($jobs as $job) {
					if ($job['auto'] == 1) {
						$autos[] = $job;
					}
				}
				return $autos;
			}
		}
		$jobDao = $this->_getJobDao();
		return $jobDao->getByAuto();
	}
	*/
	function getJobByJobName($jobName) {
		//file cache
		if ($this->_cache) {
			$jobs = $this->getFileCache();
			if ($jobs) {
				$result = array();
				foreach($jobs as $job) {
					if ($job['job'] == $jobName) {
						$result[] = $job;
					}
				}
				return $result;
			}
		}
		$jobDao = $this->_getJobDao();
		return $jobDao->getByJobName($jobName);
	}
	function getJoberByJobIds($userid, $jobIds) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->getsByJobIds($userid, $jobIds);
	}
	function getJobersByJobIds($userid, $ids) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->getJobersByJobIds($userid, $ids);
	}
	function countJobersByJobIdAndUserId($userid, $jobid) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->countJobersByJobIdAndUserId($userid, $jobid);
	}
	function getJobersByJobIdAndUserId($userid, $jobid) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->getJobersByJobIdAndUserId($userid, $jobid);
	}
	function deleteJob($id) {
		$jobDao = $this->_getJobDao();
		$result = $jobDao->delete($id);
		if ($result) {
			$this->setFileCache();
		}
		return $result;
	}
	function addJober($fields) {
		$fields['userid'] = intval($fields['userid']);
		$fields['jobid'] = intval($fields['jobid']);
		if ($fields['userid'] < 1 || $fields['jobid'] < 1) {
			return null;
		}
		$joberDao = $this->_getJoberDao();
		$result = $joberDao->add($fields);
		if ($result) {
			$this->increaseJobNum($fields['userid']);
		}
		return $result;
	}
	function getJoberByJobId($userId, $jobId) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->getByJobId($userId, $jobId);
	}
	function updateJober($fields, $id) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->update($fields, $id);
	}
	function countJoberByJobId($jobid) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->countByJobId($jobid);
	}
	function updateJoberByJobId($fieldData, $jobid, $userid) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->updateByJobId($fieldData, $jobid, $userid);
	}
	/*
	* 已申请任务列表
	*/
	function getAppliedJobs($userid) {
		$joberDao = $this->_getJoberDao();
		$jobers = $joberDao->getAppliedJobs($userid);
		if (!$jobers) {
			return array();
		}
		return $this->buildJobListByIds($jobers);
	}
	/*
	* 已完成任务
	*/
	function getFinishJobs($userid) {
		$joberDao = $this->_getJoberDao();
		$jobers = $joberDao->getFinishJobs($userid);
		if (!$jobers) {
			return array();
		}
		return $this->buildJobListByIds($jobers);
	}
	/*
	* 已放弃任务
	*/
	function getQuitJobs($userid) {
		$joberDao = $this->_getJoberDao();
		$jobers = $joberDao->getQuitJobs($userid);
		if (!$jobers) {
			return array();
		}
		return $this->buildJobListByIds($jobers);
	}
	function buildJobListByIds($jobers) {
		if (!$jobers) {
			return array();
		}
		$jobIds = $tmp = array();
		foreach($jobers as $job) {
			$jobIds[] = $job['jobid'];
			$tmp[$job['jobid']] = $job;
		}
		$jobs = $this->getJobsByIds($jobIds);
		if (!$jobs) {
			return array();
		}
		$result = array();
		foreach($jobs as $job) {
			$result[] = array_merge($tmp[$job['id']], $job);
		}
		return $result;
	}
	/*任务IDs获取任务列表*/
	function getJobsByIds($jobIds) {
		//file cache
		if ($this->_cache) {
			$jobs = $this->getFileCache();
			if ($jobs) {
				$result = array();
				foreach($jobs as $job) {
					if (in_array($job['id'], $jobIds)) {
						$result[] = $job;
					}
				}
				return $result;
			}
		}
		$jobDao = $this->_getJobDao();
		return $jobDao->getByIds($jobIds);
	}
	/*
	* 任务图片上传
	*/
	function upload($fileArray) {
		$pictureClass = L::loadClass('updatepicture');
		$pictureClass->init($this->_getDicroty());
		$filename = $pictureClass->upload($fileArray);
		return $filename;
	}
	/*
	* 任务图片上传目录
	*/
	function _getDicroty() {
		global $db_attachname;
		$attachment = $db_attachname ? $db_attachname : 'attachment';
		return R_P . $attachment . "/job/";
	}
	function _getJobDao() {
		$job = L::loadDB('job', 'job');
		return $job;
	}
	function _getJoberDao() {
		$job = L::loadDB('jober', 'job');
		return $job;
	}
	function _getJobDoerDao() {
		$job = L::loadDB('jobdoer', 'job');
		return $job;
	}
	function getConfig() {
		$config = $this->loadJob('config');
		return $config;
	}
	function getJobTypes($k = null) {
		$config = $this->getConfig();
		return $config->getJobType($k);
	}
	function getJobType($k = null) {
		$config = $this->getConfig();
		return $config->jobs($k);
	}
	function getCondition($job) {
		$config = $this->getConfig();
		return $config->condition($job);
	}
	function getJobLists($checked) {
		$config = $this->getConfig();
		$jobs = $config->jobs();
		$jobHtml = $jobInfo = "";
		foreach($jobs as $k => $v) {
			$jobHtml .= '<li id="' . $k . '"><a href="javascript:;" hidefocus="true">' . $v . '</a></li>';
			$jobInfo .= $this->getJobData($k, $config->$k(), $checked);
		}
		return array(
			$jobHtml,
			$jobInfo
		);
	}
	function getJobData($id, $data, $checked) {
		$html = '<ul id="job_' . $id . '" style="display:none;" class="list_A">';
		foreach($data as $k => $v) {
			$checkHtml = ($checked == $k) ? "checked" : "";
			$html .= '<li><input name="factor[job]" type="radio" value="' . $k . '" ' . $checkHtml . '/>' . $v . '</li>';
		}
		$html .= '</ul>';
		return $html;
	}
	/*
	* 任务下拉选择
	*/
	function getJobsSelect($select, $name, $id) {
		$jobs = $this->getJobAll();
		$result = array();
		foreach($jobs as $job) {
			$result[$job['id']] = $job['title'];
		}
		$result = ($result) ? $result : array(
			'-1' => "暂无任务"
		);
		return $this->_buildSelect($result, $name, $id, $select, true);
	}
	/*
	* 获取用户组复选框
	*/
	function getLevelCheckbox($checkeds = array(), $name = 'usergroup[]', $id = 'usergroup') {
		list($result) = $this->getLevels();
		$html .= "<div class=\"admin_table_c\"><table cellpadding=\"0\" cellspacing=\"0\">";
		$html .= "<tr class=\"vt\"><th class=\"s4\">系统组</th><td><ul class=\"cc list_A list_120 fl\">" . $this->_buildCheckbox($result['system'], $name, $id, $checkeds) . "</ul></td></tr>";
		$html .= "<tr class=\"vt\"><th class=\"s4\">会员组</th><td><ul class=\"cc list_A list_120 fl\">" . $this->_buildCheckbox($result['member'], $name, $id, $checkeds) . "</ul></td></tr>";
		$html .= "<tr class=\"vt\"><th class=\"s4\">特殊组</th><td><ul class=\"cc list_A list_120 fl\">" . $this->_buildCheckbox($result['special'], $name, $id, $checkeds) . "</ul></td></tr>";
		$html .= "</table></div>";
		//$html .= "默认组：".$this->_buildCheckbox($result['default'],$name,$id,$checkeds)."<br />";
		return $html;
	}
	/*
	* 获取用户组下拉框
	*/
	function getLevelSelect($select, $name, $id, $gptype = '') {
		list($result, $selects) = $this->getLevels();
		if ($gptype) {
			$selects = $result[$gptype];
		}
		return $this->_buildSelect($selects, $name, $id, $select);
	}
	/*
	* 获取用户组
	*/
	function getLevels() {
		$query = $this->_db->query("SELECT * FROM pw_usergroups");
		$result = $selects = array();
		while ($rs = $this->_db->fetch_array($query)) {
			$result[$rs['gptype']][$rs['gid']] = $rs['grouptitle'];
			$selects[$rs['gid']] = $rs['grouptitle'];
		}
		return array(
			$result,
			$selects
		);
	}
	function getCacheLevels() {
		@include pwCache::getPath(R_P . "data/bbscache/level.php");
		if ($ltitle) {
			return array(
				'',
				$ltitle
			);
		}
		return $this->getLevels();
	}
	/*
	* 获取积分下拉框
	*/
	function getCreditSelect($select, $name, $id) {
		$credits = pwCreditNames();
		return $this->_buildSelect($credits, $name, $id, $select);
	}
	/*
	* 任务奖励提示 前台
	*/
	function getCategoryInfo($reward, $num = 1) {
		$reward = unserialize($reward);
		$category = $reward['category'];
		switch ($category) {
			case "credit":
				$title = explode(" ", $reward['information']);
				return array('prefix'=>$title[0], 'title'=>$title[1], 'num'=>$reward['num'] * $num, 'unit'=>pwCreditUnits($reward['type']));
				break;

			case "tools":
				$title = explode(" ", $reward['information']);
				return array('prefix'=>$title[0], 'title'=>$title[1], 'num'=>$reward['num'] * $num, 'unit'=>'个');
				break;

			case "medal":
				$title = explode(" ", $reward['information']);
				return array('prefix'=>$title[0], 'title'=>$title[1], 'suffix'=>$title[2], 'num'=>$reward['day'] * $num, 'unit'=>'天');
				break;

			case "usergroup":
				$title = explode(" ", $reward['information']);
				return array('prefix'=>$title[0], 'title'=>$title[1], 'suffix'=>$title[2], 'num'=>$reward['day'] * $num, 'unit'=>'天');
				break;

			case "invitecode":
				$title = explode(" ", $reward['information']);
				return array('prefix'=>$title[0], 'title'=>$title[1], 'num'=>$reward['num'] * $num, 'unit'=>'个');
				break;

			default:
				return array();
				break;
		}
	}
	/*组装奖励前缀信息 后台*/
	function buildCategoryInfo($reward, $num = 1) {
		$category = $reward['category'];
		switch ($category) {
			case "credit":
				return "可获得 " . pwCreditNames($reward['type']) . " ";
				break;

			case "tools":
				$tools = $this->getTools();
				return "可获得道具 " . $tools[$reward['type']] . " ";
				break;

			case "medal":
				$medals = $this->getMedals();
				return "可获得勋章 " . $medals[$reward['type']] . " 有效期 ";
				break;

			case "usergroup":
				list($result, $selects) = $this->getLevels();
				return "成为 " . $selects[$reward['type']] . " 有效期 ";
				break;

			case "invitecode":
				return "可获得 邀请注册码 ";
				break;

			default:
				return "";
				break;
		}
	}
	/*组装前台完成任务详细页总数*/
	function buildCountCategoryInfo($reward, $num = 1) {
		$reward = unserialize($reward);
		$category = $reward['category'];
		switch ($category) {
			case "credit":
				return "共获得 " . pwCreditNames($reward['type']) . " " . $reward['num'] * $num . " " . pwCreditUnits($reward['type']);
				break;

			case "tools":
				$tools = $this->getTools();
				return "共获得道具 " . $tools[$reward['type']] . " " . $reward['num'] * $num . " 个";
				break;

			case "medal":
				$medals = $this->getMedals();
				return "共获得勋章 " . $medals[$reward['type']] . " 有效期 " . $reward['day'] * $num . " 天";
				break;

			case "usergroup":
				list($result, $selects) = $this->getLevels();
				return "成为 " . $selects[$reward['type']] . " 有效期 " . $reward['day'] * $num . " 天";
				break;

			case "invitecode":
				return "共获得邀请注册码 " . $reward['num'] * $num . " 个";
				break;

			default:
				return "";
				break;
		}
	}
	/*
	* 获取勋章下拉框
	*/
	function getMedalSelect($select, $name, $id) {
		$medials = $this->getMedals();
		return $this->_buildSelect($medials, $name, $id, $select);
	}
	/*
	* 获取勋章
	*/
	function getMedals() {
		$query = $this->_db->query("SELECT * FROM pw_medalinfo");
		$result = array();
		while ($rs = $this->_db->fetch_array($query)) {
			$result[$rs['id']] = $rs['name'];
		}
		return $result;
	}
	/*
	* 语言包
	*/
	function getLanguage($k) {
		$data = array();
		$data['job_title_null'] = "任务名称不能为空";
		$data['job_description_null'] = "任务描述不能为空";
		$data['upload_icon_fail'] = "任务图标上传失败";
		$data['add_job_success'] = "增加任务完成";
		$data['job_id_null'] = "任务ID无效";
		$data['job_sequence_null'] = "任务顺序不能小于0";
		$data['job_id_error'] = "抱歉，任务ID有误"; /*申请任务*/
		$data['job_not_exist'] = "抱歉，你申请的任务不存在";
		$data['job_usergroup_limit'] = "抱歉，你所在的用户组不能申请";
		$data['job_time_limit'] = "抱歉，你申请的任务已经结束";
		$data['job_time_early'] = "抱歉，你申请的任务还没有开始";
		$data['job_close'] = "抱歉，你申请的任务已经关闭";
		$data['job_has_perpos'] = "抱歉，申请这个任务你必须先完成这个任务：";
		$data['job_has_apply'] = "抱歉，你已经申请了这个任务";
		$data['job_apply_next_limit'] = "抱歉，还没到下一次申请任务时间";
		$data['job_apply_success'] = "恭喜，任务申请完成";
		$data['job_apply_number_limit'] = "抱歉，该任务申请人数已满";
		$data['job_apply_fail'] = "抱歉，任务申请失败";
		$data['job_has_perpos_more'] = "抱歉，你还没有完成这个任务：";
		$data['job_stime_r_etime'] = "抱歉，任务开始时间大于结束时间";
		$data['use_not_exists'] = "抱歉，要选择发消息的指定会员不能为空";
		return $data[$k];
	}
	/*
	* 获取道具下拉框
	*/
	function getToolsSelect($select, $name, $id) {
		$tools = $this->getTools();
		return $this->_buildSelect($tools, $name, $id, $select);
	}
	/*
	* 获取道具
	*/
	function getTools() {
		$query = $this->_db->query("SELECT * FROM pw_tools");
		$result = $special = $member = $default = $system = array();
		while ($rs = $this->_db->fetch_array($query)) {
			$result[$rs['id']] = $rs['name'];
		}
		return $result;
	}
	/*
	* 组装下拉框
	*/
	function _buildSelect($arrays, $name, $id, $select = '', $isEmpty = false) {
		if (!is_array($arrays)) {
			return '';
		}
		$html = '<select name="' . $name . '" id="' . $id . '" class="select_wa">';
		($isEmpty == true) && $html .= '<option value=""></option>';
		foreach($arrays as $k => $v) {
			$selected = ($select == $k && $select != null) ? 'selected="selected"' : "";
			$html .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	/*
	* 组装复选框
	*/
	function _buildCheckbox($arrays, $name, $id, $checkeds = array()) {
		if (!is_array($arrays)) {
			return '';
		}
		$html = '';
		foreach($arrays as $k => $v) {
			$checked = (in_array($k, $checkeds)) ? "checked" : "";
			$html .= '<li><input type="checkbox" value="' . $k . '" name="' . $name . '" id="' . $id . '" ' . $checked . '/>' . $v . "</li>";
		}
		return $html;
	}
	/*
	* 设置文件缓存
	*/
	function setFileCache() {
		$jobDao = $this->_getJobDao();
		$jobs = $jobDao->getAll();
		$jobLists = "\$jobLists=" . pw_var_export($jobs) . ";";
		pwCache::setData($this->getCacheFileName(), "<?php\r\n" . $jobLists . "\r\n?>");
		return $jobs;
	}
	/*
	* 获取文件缓存
	*/
	function getFileCache() {
		if (!$this->_cache) {
			return array(); /*not open cache*/
		}
		static $jobLists = null;
		if(!isset($jobLists)){
			@include_once pwCache::getPath(S::escapePath($this->getCacheFileName()),true);
			$jobLists = ($jobLists) ? $jobLists : $GLOBALS['jobLists'];
		}
		if ($jobLists) {
			return $jobLists;
		}
		return $this->setFileCache();
	}
	/*获取缓存文件路径*/
	function getCacheFileName() {
		return R_P . "data/bbscache/jobs.php";
	}
	function checkIsOpenMedal() {
		$fileName = D_P . 'data/bbscache/md_config.php';
		if (!is_file($fileName)) {
			return false;
		}
		@include pwCache::getPath(S::escapePath($fileName));
		if ($md_ifopen) {
			return true;
		}
		return false;
	}
	function checkIsOpenInviteCode() {
		$fileName = D_P . 'data/bbscache/dbreg.php';
		if (!is_file($fileName)) {
			return false;
		}
		@include pwCache::getPath(S::escapePath($fileName));
		if ($rg_allowregister == 2) {
			return true;
		}
		return false;
	}
	function countAppliedJobs($userid) {
		$joberDao = $this->_getJoberDao();
		return $joberDao->countAppliedJobs($userid);
	}
	/*更新任务数*/
	function updateJobNum($userid) {
		$jobnum = $this->countJobnum($userid); /*直接查询更新*/
		($jobnum > 0) ? $jobnum : 0;
		$userService = $this->_getUserService();
		return $userService->update($userid, array(), array('jobnum' => $jobnum));
	}
	
	/**
	 * 统计我申请的任务数
	 * @param int $userId
	 * 
	 */
	function countJobNum($userId) {
		if(!$userId) return false;
		$joblists = $this->getAppliedJobs($userId); 
		$joblists = $joblists ? $joblists : array();
		$num = 0;
		foreach($joblists as $job) {//显示条件 是否显示
			if ($job['isopen'] == 0) continue;
			$num++;
		}
		return $num;
	}
	
	/*增加一个任务数*/
	function increaseJobNum($userid) {
		$this->updateJobNum($userid);
	}
	/*减少一个任务数*/
	function reduceJobNum($userid) {
		$this->updateJobNum($userid);
	}
	/*组装已申请任务列表*/
	function buildApplieds($winduid, $groupid) {
		$joblists = $this->getAppliedJobs($winduid);
		$jobs = $this->buildLists($joblists, 'applied', $winduid, $groupid);
		if (!$jobs) {
			return '';
		}
		$html = '';
		foreach($jobs as $job) {
			$html .= $this->buildApplied($job);
		}
		return $html;
	}
	/*组装弹出框已申请任务*/
	function buildApplied($list) {
		$list['title'] = substrs($list['title'],56);
		$html = '';
		$html .= '<div id="applied_' . $list[id] . '">';
		$html .= '<div class="jobpop_h current"><a href="javascript:;" class="menu_tasksA_title" hidefocus="true"><b></b>' . $list[title] . ' <span>' . $list[gain] . '</span></a></div>';
		$html .= '	<dl class="cc taskA_dl" style="display:none;">';
		$html .= '    <dt><img src="' . $list[icon] . '" /></dt>';
		$html .= '    <dd>';
		$html .= '    <table width="100%" style="table-layout:fixed;">';
		$html .= '        <tr class="vt">';
		$html .= '			<td width="80">完成条件:</td>';
		$html .= '    		<td id="job_condition_' . $list[id] . '">' . $list[condition] . '</td>';
		$html .= '		  </tr>';
		$html .= '        <tr class="vt">';
		$html .= '            <td>完成奖励:</td>';
		$html .= '            <td class="s3">' . $list[reward] . '</td>';
		$html .= '        </tr>';
		$html .= '            <tr class="vt">';
		$html .= '            <td>任务描述:</td>';
		$html .= '                <td>' . $list[description] . '</td>';
		$html .= '         </tr>';
		$html .= '         <tr class="vt">';
		$html .= '            <td></td>';
		$html .= '                <td><span class="fr">' . $list[btn] . '</span></td>';
		$html .= '         </tr>';
		$html .= '   </table>';
		$html .= '   </dd>';
		$html .= '  </dl>';
		$html .= '</div>';
		return $html;
	}
	/*
	* 加载任务类
	*/
	function loadJob($name) {
		static $classes = array();
		$name = strtolower($name);
		$filename = R_P . "lib/job/job/" . $name . ".job.php";
		if (!is_file($filename)) {
			return null;
		}
		$class = 'JOB_' . ucfirst($name);
		if (isset($classes[$class])) {
			return $classes[$class];
		}
		include S::escapePath($filename);
		$classes[$class] = new $class();
		return $classes[$class];
	}
	
	/**
	 * @return PW_UserService
	 */
	function _getUserService() {
		return L::loadClass('UserService', 'user');
	}
}
