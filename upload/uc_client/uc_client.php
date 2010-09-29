<?php

// uc_client 包的根目录
define('UC_CLIENT_ROOT', dirname(__FILE__) . '/');
// uc_client 包使用的 lib 库所在的目录
// define('UC_LIB_ROOT', dirname(__FILE__) . '/../lib/');
// uc_client 包的版本
define('UC_CLIENT_VERSION', '0.1.0');
// uc_client 使用的API规范号
define('UC_CLIENT_API', '20090609');

/**
 用户登录
  @param  string $username   - 用户名
  @param  string $pwd        - 密码(md5)
  @param  int $logintype     - 登录类型 0,1,2分别为 用户名,uid,邮箱登录
  @param  boolean $checkques - 是否要验证安全问题
  @param  string $question   - 安全问题
  @param  string $answer     - 安全回答
  @return array 同步登录的代码
*/
function uc_user_login($username, $password, $logintype, $checkques = 0, $question = '', $answer = '') {
	return uc_data_request('user', 'login', array($username, $password, $logintype, $checkques, $question, $answer));
}

/**
 同步退出
  @return string 同步退出的代码
*/
function uc_user_synlogout() {
	return uc_data_request('user', 'synlogout');
}

/**
 注册
  @param  string $username - 注册用户名
  @param  string $password - 注册密码(md5)
  @param  string $email	   - 邮箱
  @return int 注册用户uid
*/
function uc_user_register($username, $password, $email) {
	$args = func_get_args();
	return uc_data_request('user', 'register', $args);
}
/**
 获取用户信息
  @param  string $username - 用户名
  @param  int $bytype - 获取方式 0,1,2分别为 用户名,uid,邮箱
  @return array uid,用户名,邮箱
*/
function uc_user_get($username, $bytype = 0) {
	return uc_data_request('user', 'get', array($username, $bytype));
}

/**
 验证
  @param  string $uid - 用户名
  @checkstr string password - uc_key+passwrord
  @return array uid,用户名,密码,邮箱
*/
function uc_user_check($uid, $checkstr) {
	$args = func_get_args();
	return uc_data_request('user', 'check', $args);
}

/**
 编辑用户资料
  @param  int $uid - 用户uid
  @param  string $oldname - 原用户名
  @param  string $newname - 新用户名
  @param  string $pwd - 新密码
  @param  string $email - 新邮箱
*/
function uc_user_edit($uid, $oldname, $newname, $pwd, $email) {
	return uc_data_request('user', 'edit', array($uid, $oldname, $newname, $pwd, $email));
}

/**
 删除指定 uid 的用户
  @param  mixed $uids - 用户uid序列，支持单个uid,多个uid数组或者用“,”隔开的字符串序列
  @param  int $del
*/
function uc_user_delete($uids) {
	return uc_data_request('user', 'delete', array($uids));
}

/**
 设置用户积分增减
  @param  array $credit array($uid1 => array($ctype1 => $point1, $ctype2 => $point2), $uid2 => array())
  return array
 */
function uc_credit_add($credit, $isAdd = true) {
	return uc_data_request('credit', 'add', array($credit, $isAdd));
}

function uc_credit_get($uid) {
	return uc_data_request('credit', 'get', array($uid));
}


function uc_data_request($class,$method,$args = array()) {
	static $uc = null;
	if (empty($uc)) {
		require_once UC_CLIENT_ROOT . 'class_core.php';
		$uc = new UC();
	}
	$class = $uc->control($class);

	if (method_exists($class, $method)) {
		return call_user_func_array(array(&$class, $method), $args);
	} else {
		return 'error';
	}
}
?>