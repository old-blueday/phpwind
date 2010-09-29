<?php
!defined('P_W') && exit('Forbidden');

/**
 * 公用验证器
 */
class PW_Validator {
	
	/**
	 * 验证字符串
	 * 
	 * @param string $data 数据字符串
	 * @param string $type 类型
	 * @return bool 是否合法
	 */
	function validate($data, $type) {
		if (empty($type)) return false;
		if (null !== ($reg = PW_Validator::_getRegValidator($type))) {
			return PW_Validator::_validateByReg($data, $reg);
		} elseif (null !== ($specify = PW_Validator::_getSpecifyValidator($type))) {
			return PW_Validator::_validateBySpecify($data, $specify);
		}
		return false;
	}
	
	/**
	 * 获取正则验证器
	 * 
	 * @access protected
	 * @param string $type 类型
	 * @return string|null 正则表达式
	 */
	function _getRegValidator($type) {
		$regValidateConfig = array(
			"username" => "/^[a-zA-Z0-9_]{4,20}$/",
			"email" => "/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",
			"url" => "/^http:\/\/[A-Za-z0-9]*\.[A-Za-z0-9]*[\/=\?%\-&_~@\.A-Za-z0-9]*$/",
		);
		return isset($regValidateConfig[$type]) ? $regValidateConfig[$type] : null;
	}
	
	/**
	 * 获取定制的验证器
	 * @param string $type 类型
	 * @return Object 定制的验证器对象
	 */
	function _getSpecifyValidator($type) {
		return L::loadClass('Validate' . ucfirst(strtolower($type)), 'utility/validate/specify');
	}
	
	/**
	 * 通过正则验证
	 * 
	 * @param string $data 数据
	 * @param string $reg 正则
	 * @return bool 是否合法
	 */
	function _validateByReg($data, $reg) {
		return (bool) preg_match($reg, $data);
	}
	/**
	 * 通过定制的验证器验证
	 * 
	 * @param string $data 数据
	 * @param Object $specify 验证器对象
	 * @return bool 是否合法
	 */
	function _validateBySpecify($data, $specify) {
		return $specify->validate($data);
	}
}
