<?php
!defined('P_W') && exit('Forbidden');
/**
 * 公共的字段检查
 * @author zuojie
 *
 */
class FieldCheck {
	/**
	 * @var mix 错误相关的数据值
	 * @access protected
	 */
	var $errorValue;

	/**
	 * 获得错误相关的数据值
	 * @return mix 错误相关的数据值
	 * @access public
	 */
	function getErrorValue() {
		return $this->errorValue;
	}
	/**
	 * 设置错误相关的数据值
	 * @param mix $value 值
	 * @return FieldCheck this
	 * @access public
	 */
	function setErrorValue($value) {
		$this->errorValue = $value;
		return $this;
	}
	/**
	 * 获取默认字段的错误信息
	 * @param string $fieldType 字段类型
	 * @param mix $data 字段值
	 * @param mix $rules 规则
	 */
	function getError($fieldType, $data, $rules = NULL) {
		$errorKey = false;
		switch ($fieldType) {
			case 'number' : 
				$errorKey = $this->getNumberError($data, $rules['minnum'], $rules['maxnum']);
				break;
			case 'text' :
			case 'textarea' :
				break;
			case 'radio' : //radio和select使用同一个验证方法
			case 'select' :
				$errorKey = $this->getSelectionError($data);
				break;
			case 'checkbox' :
				$errorKey = $this->getCheckboxError($data);
				break;
			case 'calendar' :
				$errorKey = $this->getCalendarError($data);
				break;
			case 'email' : 
				$errorKey = $this->getEmailError($data);
				break;
			case 'url' : 
			case 'img' : 
				break;
			case 'upload' :
				break;
			case 'range' : 
				$errorKey = $this->getRangeError($data);
				break;
			default :
				break;
		}
		return $errorKey;
	}
	/**
	 * 检查带取值范围的数值字段
	 * @param float $value 数值
	 * @param float $min 允许的最小值
	 * @param float $max 允许的最大值
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getRangeError($value, $min = '', $max = '') {
		$errorKey = $this->getNumberError($value);
		if ($errorKey) {
			return $errorKey;
		} elseif (is_numeric($min) && is_numeric($max) && ($value < $min || $value > $max)) {
			$this->setErrorValue($value);
			return 'act_number_limit';
		} else {
			return false;
		}
	}
	/**
	 * 检查数值字段
	 * @param float $value 数值
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getNumberError($value) {
		if (!is_numeric($value) && $value) {
			return 'act_number_error';
		} else {
			return false;
		}
	}
	/**
	 * 检查email字段
	 * @param string $email email地址
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getEmailError($email) {
		if (!preg_match('/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/', $email)) {
			$this->setErrorValue($email);
			return 'illegal_email';
		} else {
			return false;
		}
	}
	/**
	 * 检查选项类（radio, checkbox, select）的值
	 * @param int $value 值
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getSelectionError($value) {
		if (!is_numeric($value) || $value != (int)$value) { //为整数值
			return 'selection_not_int';
			$this->setErrorValue($value);
		} else {
			return false;
		}
	}
	/**
	 * 检查checkbox的所有值
	 * @param array $values checkbox的值
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getCheckboxError($values) {
		if (!is_array($values)) {
			return 'checkbox_not_array';
		} else {
			foreach ($values as $value) {
				$errorKey = $this->getSelectionError($value);
				if ($errorKey) {
					return $errorKey;
				}
			}
			return false;
		}
	}
	/**
	 * 检查时间字段
	 * @param string $string 时间字符，如'2010-4-9 13:00:00'
	 * @return string|bool 遇错返回错误key，否则返回false
	 */
	function getCalendarError ($string) {
		if ($string) {
			$time = strtotime($string);
			if (!$time || -1 == $time) { //strtotime()在PHP 5.1.0以前失败时返回-1
				$this->setErrorValue($string);
				return 'calendar_wrong_format';
			} else {
				return false;
			}
		}
	}
	/**
	 * 生成字段保存于数据库的值
	 * @param string $fieldType 字段类型
	 * @param mix $data 值
	 * @return string 保存于数据库的值
	 */
	function getValueForDb($fieldType, $data) {
		$returnValue = $data;
		switch ($fieldType) {
			case 'number' : 
			case 'range' : 
				break;
			case 'text' :
			case 'textarea' :
				break;
			case 'radio' : //radio和select使用同一方法
			case 'select' :
				$returnValue = (int)$data;
				break;
			case 'checkbox' :
				$returnValue = '';
				foreach ($data as $selection) {
					$returnValue .= (int)$selection.',';
				}
				break;
			case 'calendar' :
				$returnValue = PwStrtoTime($data);
				break;
			case 'email' : 
			case 'url' : 
			case 'img' : 
			case 'upload' :
			default :
				break;
		}
		return $returnValue;
	}
}