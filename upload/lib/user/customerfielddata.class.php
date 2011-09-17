<?php
!defined('P_W') && exit('Forbidden');

class PW_CustomerFieldData{
	
	var $customerField;
	
	var $memberData;
	var $customerEdit = false;
	var $defaultMemberData = array();
	var $returnint;
	
	function PW_CustomerFieldData(){}
	
	/**
	 * 
	 * 保存用户信息
	 * @param array $fieldInfo
	 * @return bool
	 */
	function setData($fieldInfo,$uid = 0){
		$uid = intval($uid);
		!is_a($this->customerField , 'PW_CustomerField') && $this->customerField = L::loadClass('CustomerField','user');
		$fieldInfo = $this->customerField->formatFieldInfo($fieldInfo);
		if (!$fieldInfo['editable'] && $this->customerEdit && (($fieldInfo['fieldname'] != 'bday' && $this->getCustomerData($fieldInfo, $uid)) || ($fieldInfo['fieldname'] == 'bday' && '0000-00-00' != $this->getCustomerData($fieldInfo, $uid)))) {
			//不允许修改的字段
			return false;
		}
		$tips = '填写有误';
		if (S::inArray($fieldInfo['type'], array($this->customerField->typeMap['input'],$this->customerField->typeMap['textarea']))) {
			!$fieldInfo['maxlen'] && $fieldInfo['maxlen'] = 255;
			$tips = "最大长度为$fieldInfo[maxlen]字节";
		}
		$showTips = array(1 => '填写有误', 2 => '格式不正确', 3 => $tips);
		$systemFields = $this->customerField->fieldMap;
		$fieldName = $fieldInfo['fieldname'];
		$tableName = isset($systemFields[$fieldName]) ? $this->customerField->fieldMap[$fieldName] : 'pw_memberinfo';
		switch ($fieldName) {
			case 'education':
				$setCheck = $this->setDataEducation($uid, $tableName);
				$check = $fieldInfo['required']? $setCheck : true;
				break;
			case 'career':
				$setCheck = $this->setDataCareer($uid, $tableName);
				$check = $fieldInfo['required']? $setCheck : true;
				break;
			case 'alipay':
				$userService = L::loadClass('userservice','user'); /* @var $userService PW_UserService */
				if($userService->getUserStatus($uid,PW_USERSTATUS_AUTHALIPAY)) return true;
				$setCheck = $this->setDataAlipay($uid, $tableName, $fieldName,$fieldInfo['required']);
				$check = $fieldInfo['required']? $setCheck : true;
				break;
			default:
				$data = S::escapeChar(S::getGP($fieldName, 'P'));
				switch ($fieldInfo['type']) {
					case $this->customerField->typeMap['checkbox']://checkbox
						is_array($data) && $data = implode("\t",$data);
						break;
					case $this->customerField->typeMap['year']:
						S::gp(array("{$fieldName}_year","{$fieldName}_month","{$fieldName}_day"));
						global ${$fieldName.'_year'},${$fieldName.'_month'},${$fieldName.'_day'};
						$dateString = sprintf('%04d-%02d-%02d',${$fieldName.'_year'},${$fieldName.'_month'},${$fieldName.'_day'});
						$data = date('Y-m-d',strtotime($dateString));
						break;
					default:
					break;
				}
				//字段校验
				$check = $this->checkData($fieldInfo, $data, true);
				//非必填,检查不通过不保存
				if (!$fieldInfo['required'] && $check !== true) return false;
				$check === true && $this->memberData[$uid][$tableName][$fieldName] = $data;
				break;
		}
		if ($check !== true && $this->customerEdit) {
			$check === false && $check = 1;
			Showmsg("字段 '{$fieldInfo['title']}' $showTips[$check]");
		}
		return true;
	}
	
	function setDataEducation($uid,$tableName) {
		S::gp(array('new_education_level','new_education_schoolid','new_education_year'));
		global $new_education_level,$new_education_schoolid,$new_education_year;
		foreach ($new_education_level as $k=>$v) {
			$level = intval($v);
			$schoolid = intval($new_education_schoolid[$k]);
			$year = intval($new_education_year[$k]);
			if (!$level || !$schoolid || !$year) continue;
			$starttime = strtotime(sprintf('%04d-01-01',$year));
			$this->memberData[$uid][$tableName]['add'][] = array('uid'=>$uid,'schoolid'=>$schoolid,'educationlevel'=>$level,'starttime'=>$starttime);
		}
		/*编辑*/
		S::gp(array('education_level','education_schoolid','education_year'));
		global $education_level,$education_schoolid,$education_year;
		if (S::isArray($education_level)){
			foreach ($education_level as $k=>$v) {
				$level = intval($v);
				$schoolid = intval($education_schoolid[$k]);
				$year = intval($education_year[$k]);
				if (!$level || !$schoolid || !$year) continue;
				$starttime = strtotime(sprintf('%04d-01-01',$year));
				$this->memberData[$uid][$tableName]['edit'][$k] = array('uid'=>$uid,'schoolid'=>$schoolid,'educationlevel'=>$level,'starttime'=>$starttime);
			}
		}
		if (!$this->memberData[$uid][$tableName]['add'] && !$this->memberData[$uid][$tableName]['edit']) return false;
		return true;
	} 

	function setDataCareer($uid,$tableName){
		/*新增*/
		S::gp(array('new_career_companyname','new_career_year','new_career_month','new_career_day'));
		global $new_career_companyname,$new_career_year,$new_career_month,$new_career_day;
		if (S::isArray($new_career_companyname)){
			foreach ($new_career_companyname as $k=>$v) {
				if(!$v) continue;
				$starttime = strtotime("{$new_career_year[$k]}-{$new_career_month[$k]}-{$new_career_day[$k]}");
				$this->memberData[$uid][$tableName]['add'][] = array('uid'=>$uid,'companyname'=>$v,'starttime'=>$starttime);
			}
		}
		/*编辑*/
		S::gp(array('career_companyname','career_year','career_month','career_day'));
		global $career_companyname,$career_year,$career_month,$career_day;
		if (S::isArray($career_companyname)){
			foreach ($career_companyname as $k=>$v) {
				if(!$v) continue;
				$starttime = strtotime("{$career_year[$k]}-{$career_month[$k]}-{$career_day[$k]}");
				$this->memberData[$uid][$tableName]['edit'][$k] = array('uid'=>$uid,'companyname'=>$v,'starttime'=>$starttime);
			}
		}
		if (!$this->memberData[$uid][$tableName]['add'] && !$this->memberData[$uid][$tableName]['edit']) return false;
		return true;
	}

	function setDataAlipay($uid,$tableName,$fieldName,$required = false){
		if (!$this->memberData[$uid][$tableName]['tradeinfo']){
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userInfo = $userService->get($uid, true, false, true);
			if (!$userInfo) return false;
			$this->memberData[$uid][$tableName]['tradeinfo'] = $userInfo['tradeinfo'];
		}
		$tradeInfo = @(array)unserialize($userInfo['tradeinfo']);
		$tradeInfo[$fieldName] = S::escapeChar(S::getGP($fieldName, 'P'));
		if (!$required && !$tradeInfo[$fieldName] || $tradeInfo[$fieldName] && $this->checkAlipay($tradeInfo[$fieldName]) === true) {
			$this->memberData[$uid][$tableName]['tradeinfo'] = serialize($tradeInfo);
			return true;
		} else {
			return false;
		}
	}
	function updateData(){
		if (!S::isArray($this->memberData)) return false;
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($this->memberData as $uid => $data) {
			$userInfo = $userService->get($uid);
			if (!$userInfo) continue;
			$mainFields = $memberDataFields = $memberInfoFields = array();
			foreach ($data as $tableName=>$fieldsData) {
				switch ($tableName) {
					case 'pw_memberinfo' :
						$memberInfoFields = $fieldsData;
						break;
					case 'pw_memberdata' :
						$memberDataFields = $fieldsData;
						break;
					case 'pw_user_education':
						!$educationService && $educationService = L::loadClass('EducationService', 'user'); /* @var $educationService PW_EducationService */
						S::isArray($fieldsData['add']) && $educationService->addEducations($fieldsData['add']);
						if (S::isArray($fieldsData['edit']) ) {
							foreach ($fieldsData['edit'] as $k=>$v) {
								$educationService->editEducation($k,$v['educationlevel'],$v['schoolid'],$v['starttime']);
							}	
						}
						break;
					case 'pw_user_career':
						$careerService = L::loadClass('CareerService', 'user'); /* @var $careerService PW_CareerService */
						S::isArray($fieldsData['add']) && $careerService->addCareers($fieldsData['add']);
						if (S::isArray($fieldsData['edit']) ) {
							foreach ($fieldsData['edit'] as $k=>$v) {
								$careerService->editCareer($k,$v['companyname'],$v['starttime']);
							}	
						}
						break;
					default:
						$mainFields = $fieldsData;
						break;
				}
			}
			$userService->update($uid, $mainFields, $memberDataFields, $memberInfoFields);
		}
		return true;
	}
	
	/**
	 * 获取用户数据
	 */
	function getCustomerData($fieldInfo,$uid){
		!is_a($this->customerField , 'PW_CustomerField') && $this->customerField = L::loadClass('CustomerField','user');
		$uid = intval($uid);
		$fieldInfo = $this->customerField->formatFieldInfo($fieldInfo);
		if(!S::isArray($fieldInfo) || !$uid) return false;
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		isset($this->defaultMemberData[$uid]) or $this->defaultMemberData[$uid] = $userService->get($uid, true, true, true);
		$customFieldValue = '';
		switch ($fieldInfo['type']) {
			case $this->customerField->typeMap['area']:
				if ($this->defaultMemberData[$uid][$fieldInfo['fieldname']] && $this->defaultMemberData[$uid][$fieldInfo['fieldname']] != -1){
					$areaids = array($fieldInfo['id'] => $this->defaultMemberData[$uid][$fieldInfo['fieldname']]);
					$areaService = L::loadClass('AreasService', 'utility');
					list($upids, $upperids) = $areaService->getParentidByAreaids($areaids);
					$provinceid = isset($upperids[$upids[$areaids[$fieldInfo['id']]]]) ? $upperids[$upids[$areaids[$fieldInfo['id']]]] : 0;
					$customFieldValue = $areaids[$fieldInfo['id']] ? array('province'=>$provinceid, 'city'=>$upids[$areaids[$fieldInfo['id']]], 'area'=>$areaids[$fieldInfo['id']]) : array();
				} else {
					$customFieldValue = ($fieldInfo['options'] && !$fieldInfo['ifsys']) ? $fieldInfo['options'] : '';
				}
				break;
			case $this->customerField->typeMap['education']:
				$educationService = L::loadClass('EducationService', 'user');
				$customFieldValue = $educationService->getEducationsByUid($uid);
				$customFieldValue or $customFieldValue = array();
				break;
			case $this->customerField->typeMap['career']:
				$careerService = L::loadClass('CareerService', 'user');
				$customFieldValue = $careerService->getCareersByUid($uid);
				$customFieldValue or $customFieldValue = array();
				break;
			default:
				$customFieldValue = $this->defaultMemberData[$uid][$fieldInfo['fieldname']];
				if ($fieldInfo['fieldname'] == 'alipay') {
					$tradeinfo = unserialize( $this->defaultMemberData[$uid]['tradeinfo']);
					$customFieldValue = $tradeinfo['alipay'];
				}
				break;
		}
		return $customFieldValue;
	}
	
	/**
	 * 
	 * 数据校验
	 * @param int $fieldId
	 * @param mixed $data
	 * @return bool
	 */
	function checkData($fieldInfo,$data,$returnint = false){
		!is_a($this->customerField , 'PW_CustomerField') && $this->customerField = L::loadClass('CustomerField','user');
		$this->returnint = $returnint;
		if (!S::isArray($fieldInfo)) return false;
		$fieldInfo = $this->customerField->formatFieldInfo($fieldInfo);
		if ($data === '' && !$fieldInfo['required']) return true;
		if($fieldInfo['required'] && ($data === '' || !isset($data))) return false;
		if (S::inArray($fieldInfo['type'], array($this->customerField->typeMap['input'],$this->customerField->typeMap['textarea']))) {
			!$fieldInfo['maxlen'] && $fieldInfo['maxlen'] = 255;
			if ($fieldInfo['maxlen'] < strlen($data)) return $this->returnint ? 3 : false;
		}
		//默认字段
		if ($fieldInfo['ifsys']) {
			$checkMethod = 'check'.ucfirst($fieldInfo['fieldname']);
			if (method_exists($this, $checkMethod)) return $this->$checkMethod($data);
			return false;
		}
		switch ($fieldInfo['type']) {
			case $this->customerField->typeMap['radio']://radio 单选框
			case $this->customerField->typeMap['select']://select 下拉框
				if (!isset($fieldInfo['options'][$data])) return false;
				break;
			case $this->customerField->typeMap['checkbox']://checkbox 复选框 data:array(1,2,3)
				if ($data == '' && !$fieldInfo['required']) return true;
				S::isArray($data) or $data = explode("\t",$data);
				if (array_diff($data, array_keys($fieldInfo['options']))) return false;
				break;
			case $this->customerField->typeMap['year']: //年限填写类型 data:int
				if (isset($fieldInfo['options']['min'])) {
					$t = preg_match('/^\d+$/', $data) ? $data : strtotime($data);
					$date = getdate($t);
					if ($date['year'] < $fieldInfo['options']['min'] || $fieldInfo['options']['max'] > $date['year']) return false;
				}
				break;
			case $this->customerField->typeMap['area']: //地区填写类型
				return $this->checkArea($data);
				break;
			default:
				if ($fieldInfo['maxlen'] < strlen($data)) return $this->returnint ? 3 : false;
				break;
		}
		return true;
	}
	
	//数据校验
	function checkOicq($subject){
		return preg_match('/^[1-9]\d{4,11}$/', $subject) ? true : ($this->returnint ? 2 : false);
	}
	
	function checkGender($subject) {
		return in_array($subject, array(0,1,2));
	}
	function checkBday($subject){
		//return preg_match('/^(19|20)\d{2}-\d{2}-\d{2}$/', $subject);
		return true;
	}
	function checkApartment($subject) {
		return $this->checkArea($subject);
	}
	
	function checkHome($subject){
		return $this->checkArea($subject);
	}
	
	function checkEducation($subject) {
		global $timestamp;
		if (!S::isArray($subject)) return false;
		if ($subject['educationlevel'] < 1 || $subject['educationlevel'] > 8) return false;	
		$year = get_date($timestamp,'year');
		if ($subject['educationyear'] < $year - 100 || $subject['educationyear'] > $year) return false;
		$schoolService = L::loadClass('SchoolService','user');
		$schoolInfo = $schoolService->getBySchoolId($subject['schoolid']);
		if (!S::isArray($schoolInfo)) return false;
		switch ($schoolInfo['type']) {
			case 1:
				if ($subject['educationlevel'] > 1) return false;
				break;
			case 2:
				if (!S::inArray($subject['educationlevel'],array(2,3))) return false;
				break;
			case 3:
				if (!S::inArray($subject['educationlevel'],array(4,5,6,7,8))) return false;
				break;
		}
		return true;
	}
	
	function checkCareer($subject){
		global $timestamp;
		if (!S::isArray($subject)) return false;
		if (!preg_match('/^\w{2,20}$/', $subject['companyname'])) return false;
		if ($subject['starttime'] > $timestamp || $subject['starttime'] < $timestamp - 86400 * 100) return false;
		return true;
	}
	
	function checkAlipay($subject){
		return (preg_match('/^1\d{10}$/', $subject) || $this->checkEmail($subject)) ? true : ($this->returnint ? 2 : false);
	}
	
	function checkRealname($subject){
		return preg_match('/^[\x80-\xff]{4,20}$/', $subject) ? true : false;
	}
	
	function checkAliww($subject){
		return (preg_match('/^\w{2,20}$/', $subject) || $this->checkAlipay($subject)) ? true : ($this->returnint ? 2 : false);
	}
	function checkMsn($subject){
		return $this->checkEmail($subject) ? true : ($this->returnint ? 2 : false);
	}
	function checkYahoo($subject){
		//return preg_match('/^\w{2,20}$/', $subject) ? true : ($this->returnint ? 2 : false);
		return $subject ? true : ($this->returnint ? 2 : false);
	}
	function checkArea($subject) {
		$areaService = L::loadClass('AreasService','utility');
		$areaInfo = $areaService->getAreaByAreaId($subject);
		if (!S::isArray($areaInfo)) return false;
		return true;
	}
	function checkEmail($subject){
		return preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i', $subject);
	}
	//end 数据校验
}
