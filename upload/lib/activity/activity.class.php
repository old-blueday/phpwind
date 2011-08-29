<?php
!defined('P_W') && exit('Forbidden');
class PW_Activity {
	var $activitycatedb;
	var $activitymodeldb;

	function setActCache() {
		//* include pwCache::getPath(D_P.'data/bbscache/activity_config.php');
		extract(pwCache::getData(D_P.'data/bbscache/activity_config.php', false));
		$this->activitycatedb = $activity_catedb;
		$this->activitymodeldb = $activity_modeldb;
	}
	
	function getActivityCateDb() {
		if (!$this->activitycatedb) {
			$this->setActCache();
		}
		return $this->activitycatedb;
	}
	
	function getActivityModelDb() {
		if (!$this->activitymodeldb) {
			$this->setActCache();
		}
		return $this->activitymodeldb;
	}
	/**
	 * 返回活动子分类select的HTML
	 * @param int $selectedActmid 选中的活动分类
	 * @param bool $withEmptySelection 是否包含“所有分类”选项
	 * @param string $selectName select的name的值，如无，返回的HTML不包含select这个Tag
	 * @return HTML
	 */
	function getActmidSelectHtml ($selectedActmid = 0, $withEmptySelection = 1, $selectTagName = 'actmid') {
		$options = array();
		if ($withEmptySelection) {
			$options['0'] = getLangInfo('other','act_activity_class');
		}
		$activityCateDb = $this->getActivityCateDb();
		$activityModelDb = $this->getActivityModelDb();
		$newModelDb = array();
		foreach ($activityModelDb as $value) {
			$newModelDb[$value['actid']][] = $value;
		}

		foreach($activityCateDb as $value) {
			foreach($newModelDb[$value['actid']] as $val){
				$options[$value['name']][$val['actmid']] = $val['name'];
			}
		}
		
		$return = getSelectHtml($options, $selectedActmid, $selectTagName);
		return $return;
	}
	/**
	 * 获取活动状态的Key
	 * @param array $data 活动数据
	 * @param int $currentTimestamp 当前时间戳
	 * @param int $numberOfPeopleAlreadySignup 已报名人数
	 * @return string 活动状态Key
	 */
	function getActivityStatusKey ($data, $currentTimestamp, $numberOfPeopleAlreadySignup) {
		if ($data['iscancel']) {
			return 'activity_is_cancelled';//活动取消
		} elseif ($data['signupstarttime'] > $currentTimestamp) {
			return 'signup_not_started_yet';// 报名未开始
		} elseif ($data['endtime'] < $currentTimestamp) {
			return 'activity_is_ended';//活动结束
		} elseif ($currentTimestamp > $data['starttime'] && $currentTimestamp < $data['endtime']) {
			return 'activity_is_running';//活动进行中
		} elseif ($data['signupendtime'] < $currentTimestamp) {
			return 'signup_is_ended';//报名结束，活动未开始
		} elseif ($numberOfPeopleAlreadySignup >= $data['maxparticipant'] && $data['maxparticipant']) {
			return 'signup_number_limit_is_reached';//报名人数已满
		} else {
			return 'signup_is_available';
		}
	}
}