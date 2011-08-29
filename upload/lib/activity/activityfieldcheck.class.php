<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('fieldcheck', '', false);
/**
 * 活动相关的特殊字段检查
 * @author zuojie
 *
 */
class PW_ActivityFieldCheck extends Fieldcheck {
	/**
	 * @var array 错误信息数组
	 * @access protected
	 */
	var $errorMessage;
	var $feesArray;
	var $feesDetailArray;
	var $telephones;

	/**
	 * 根据错误key获取对应的中文错误提示
	 * @param string $key 错误信息的代称（英文）
	 * @access public
	 * @return string human-readable的错误信息（中文）
	 */
	function getErrorMessageByKey($key) {
		global $db_actname;
		$db_actname = $this->getErrorValue();
		$errorMessage = $this->getErrorMessage();
		if ($errorMessage[$key] && is_string($errorMessage[$key])) {
			$keyErrorMessage = str_replace('{value}', $db_actname, $errorMessage[$key]);
			return $keyErrorMessage;
		} elseif ($key) {
			return 'unknown error';
		} else {
			return '';
		}
	}
	/**
	 * 获取所有错误提示
	 * @access public
	 * @return array 错误提示
	 */
	function getErrorMessage() {
		if (!$this->errorMessage) {
			$this->_presetErrorMessage();
		}
		return $this->errorMessage;
	}
	/**
	 * 设置$errorMessage初始值
	 * @param array $errorMessage
	 * @access protected
	 * @return bool|FieldCheck 遇错返回false
	 */
	function _presetErrorMessage() {
		require_once S::escapePath(GetLang('fielderror'));
		$errorMessage = $lang['fielderror'];
		if ($errorMessage) {
			$this->_setErrorMessage($errorMessage);
			return $this;
		} else {
			return false;
		}
	}
	/**
	 * 设置$errorMessage
	 * @param array $errorMessage
	 * @access protected
	 * @return bool|FieldCheck 遇错返回false
	 */
	function _setErrorMessage($errorMessage) {
		if (!is_array($errorMessage)) {
			return false;
		} else {
			$this->errorMessage = $errorMessage;
			return $this;
		}
	}
	/**
	 * 开始时间是否早于结束时间
	 * @param string $start 开始时间，如'2010-04-09 11:00:00'
	 * @param string $end 结束时间，如'2010-04-09 12:00:00'
	 * @access protected
	 * @return bool 时间是否有效
	 */
	function _isValidStartAndEndTime ($start, $end) {
		if ($this->getCalendarError($start) || $this->getCalendarError($end)) {
			return false;
		} else {
			$startTimestamp = PwStrtoTime($start);
			$endTimestamp = PwStrtoTime($end);
			if ($startTimestamp > $endTimestamp) {
				return false;
			} else {
				return true;
			}
		}
	}
	/**
	 * 检查开始和结束时间
	 * @param string $start 开始时间，如'2010-04-09 11:00:00'
	 * @param string $end 结束时间，如'2010-04-09 12:00:00'
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getTimeRangeError($start, $end) {
		if (!$this->_isValidStartAndEndTime($start, $end)) {
			return 'start_time_later_than_end_time';
		} else {
			return false;
		}
	}
	/**
	 * 检查报名结束时间和活动开始时间是否有冲突
	 * @param string $signupEnd 报名结束时间，'2010-04-09 11:00:00'
	 * @param string $activityStart 活动开始时间，如'2010-04-09 12:00:00'
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getActivityAndSignupTimeConflictError($signupEnd, $activityStart) {
		if (!$this->_isValidStartAndEndTime($signupEnd, $activityStart)) {
			return 'signup_end_time_later_than_activity_start_time';
		} else {
			return false;
		}
	}
	/**
	 * 是否为有效的金钱数
	 * @param float 金钱数，如1.53
	 * @access protected
	 * @return bool 是否有效
	 */
	function _isValidMoney($money) {
		if (!is_numeric($money) || 0 >= $money) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * 检查金钱值
	 * @param float $money
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getMoneyError($money) {
		if (!$this->_isValidMoney($money) && $money) {
			return 'invalid_money';
		} else {
			return false;
		}
	}
	function getLocationError($locations) {
		
	}
	function getContactError($contacts) {
		
	}
	/**
	 * 返回字符串用，（全角逗号，高优先级）或,（半角逗号）分割后的数组
	 * @param string $string 字符串
	 * @access protected
	 * @return array 数组
	 */
	function _getExplodedArrayFromString($string) {
		if (strpos($string, '，') !== false) {
			$delimiter = '，';
		} else {
			$delimiter = ',';
		}
		$array = explode($delimiter, $string);
		foreach ($array as $key => $element) {
			$array[$key] = trim($element);
			if (!$array[$key]) {
				unset($array[$key]);
			}
		}
		return $array;
	}
	/**
	 * 返回数组用,（半角逗号）分割后的字符串
	 * @param array $array 数组
	 * @access protected
	 * @return string 字符串
	 */
	function _getImplodedStringFromArray($array) {
		return implode(',', $array);
	}
	/**
	 * 检查是否为有效的电话号码
	 * @param string $telephone 电话号码，允许的格式有13123456789, +8613123456789, 0571-12345678, (0571)12345678，0578-12345678p123（p后为分机）等
	 * @access protected
	 * @return bool 是否有效
	 */
	function _isValidTelephoneNumber($telephone) {
		if (preg_match('/^[0-9p\(\)\-\+]+$/i', $telephone)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 检查联系电话字段
	 * @param string $telephones 电话号码，多个号码用','（逗号）分割，如'0571-12345678,13123456789'
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getTelephoneError($telephones) {
		$telephoneArray = $this->_getExplodedArrayFromString($telephones);
		foreach ($telephoneArray as $element) {
			if (!$this->_isValidTelephoneNumber($element)) {
				$this->setErrorValue($element);
				return 'invalid_telephone_format';
			}
		}
		$this->telephones = $this->_getImplodedStringFromArray($telephoneArray);
		return false;
	}
	function getTelephones () {
		return $this->telephones;
	}
	/**
	 * @param int $payMethod 支付方式
	 * @param int|string $min 最小人数
	 * @param int|string $max 最大人数
	 * @param int $peopleAlreadySignup 已报名人数
	 */
	function getParticipantError($payMethod, $min = '', $max = '', $peopleAlreadySignup = 0) {
		//1=支付宝，2=现金
		$errorKey = $this->getPayMethodError($payMethod);
		if ($errorKey) {
			return $errorKey;
		}
		$payMethodIsAlipay = $payMethod == 1 ? true : false;
		foreach (array($min, $max) as $value) {
			if ($value && (!is_numeric($value) || $value != (int)$value || $value < 0)) { //有值且不是大于0的整数
				return 'invalid_participant_number';
			}
		}
		if ($max && $min > $max) {
			return 'minimum_larger_than_maximum';
		} elseif ($peopleAlreadySignup && $max < $peopleAlreadySignup) {
			return 'max_less_than_people_already_signup';
		} else {
			return false;
		}
	}
	function getUserLimitError($onlyFriend, $specificLimit) {
		
	}
	function getGenderLimitError($gender) {
		
	}
	function getFeesError($fees) {
		$feesArray = array();
		if (!is_array($fees)) {
			return 'invalid_fees_format';
		} else {
			foreach ($fees['condition'] as $key => $value) {
				if ($value && $fees['money'][$key]) {
					$feesArray[$key]['condition'] = $value;
					$errorKey = $this->getMoneyError($fees['money'][$key]);
					if ($errorKey) {
						$this->setErrorValue($fees['money'][$key]);
						return $errorKey;
					} else {
						$feesArray[$key]['money'] = $fees['money'][$key];
					}
				}
			}
			
			$this->feesArray = $feesArray;
			return false;
		}
	}
	function getFeesArray () {
		return $this->feesArray;
	}
	function getFeesDetailError($feesDetail) {
		$feesDetailArray = array();
		if (!is_array($feesDetail)) {
			return 'invalid_fees_detail_format';
		} else {
			foreach ($feesDetail['item'] as $key => $value) {
				if ($feesDetail['money'][$key] && $value) {
					$feesDetailArray[$key]['item'] = $value;
					$errorKey = $this->getMoneyError($feesDetail['money'][$key]);
					if ($errorKey) {
						$this->setErrorValue($feesDetail['money'][$key]);
						return $errorKey;
					} else {
						$feesDetailArray[$key]['money'] = $feesDetail['money'][$key];
					}
				}
			}
			$this->feesDetailArray = $feesDetailArray;
			return false;
		}
	}
	function getFeesDetailArray() {
		return $this->feesDetailArray;
	}
	function getPayMethodError($payMethod) {
		if ($payMethod != 1 && $payMethod != 2) {
			return 'invalid_pay_method';
		} else {
			return false;
		}
	}
}
