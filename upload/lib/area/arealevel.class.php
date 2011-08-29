<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户权限服务层
 * @author liuhui @2010-3-8
 */
class PW_AreaLevel {
	
	/**
	 * 根椐频道ID和模块名称获取用户权限
	 * @param $userId 
	 * @param $channel
	 * @param $invoke
	 * @return bool
	 */
	function getAreaLevel($userId,$channel,$invoke=''){
		$userId = intval($userId);
		$invoke = trim($invoke);
		if($userId < 1/* || $channel < 1*/){
			return false;
		}
		$userLevel = $this->getAreaUser($userId);
		if(!$userLevel){
			return false;
		}
		if( 1 == $userLevel['super']){
			return $userLevel;
		}
		if( "" == $userLevel['level'] ){
			return false;
		}
		$levle = unserialize($userLevel['level']);
		if( !$levle || !isset($levle[$channel])){
			return false;
		}
		
		if("" != $invoke && ( !isset($levle[$channel]['invokes']) ||  !isset($levle[$channel]['invokes'][$invoke]))){
			return false;
		}
		return $userLevel;
	}
	
	function getAreaLevelByUserId($userId){
		$userId = intval($userId);
		if($userId < 1 ){
			return false;
		}
		$userLevel = $this->getAreaUser($userId);
		if(!$userLevel){
			return false;
		}
		if( 1 == $userLevel['super']){
			return true;
		}
		if( "" == $userLevel['level'] ){
			return false;
		}
		return true;
	}
	
	function language($key){
		$messages = array(
			"username_empty"      =>"抱歉,请输入用户名",
			"uid_empty"           =>"抱歉,用户ID不正确",
			"username_not_exist"  =>"抱歉,用户名不存在",
			"add_success"         =>"增加用户权限完成",
			"update_success"      =>"更新用户权限完成",
			"update_fail"         =>"更新用户权限失败",
			"userlevel_not_exist" =>"抱歉,用户权限不存在",
			"delete_success"      =>"删除用户权限完成",
			"do_success"          =>"操作完成",
			"area_no_level"       =>"抱歉,你没有管理此模块的权限",
			"area_no_pushto"      =>"抱歉,您没有门户频道的管理权限不能进行推送",
			"area_no_invoke"      =>"抱歉,模块不存在",
		);
		return isset($messages[$key]) ? $messages[$key] : '';
	}
	
	/**
	 * 增加多个门户管理员
	 * @param $fields
	 * @return true/false
	 */
	function addAreaUsers($fields){
		$haystack = trim($fields['username']);
		if("" == $haystack){
			return array(false,$this->language("username_empty"));
		}
		$mows = explode(",",$haystack);
		$userNames = array();
		foreach($mows as $username){
			$userNames[] = strip_tags(trim($username));
		}
		
		$userService = $this->_getUserService();
		$users = $userService->getByUserNames(array_unique($userNames));
		if(!$users){
			return array(false,$this->language("username_not_exist"));
		}
		$needles = array();
		$needles['hasedit'] = $fields['hasedit'];
		$needles['hasattr'] = $fields['hasattr'];
		$needles['super'] = $fields['super'];
		$needles['level'] = $fields['level'];
		foreach($users as $user){
			$needles['uid'] = $user['uid'];
			$needles['username'] = $user['username'];
			$this->addAreaUser($needles);
		}
		$this->_updateAreaUserConfig();
		return array(true,$this->language("add_success"));
	}
	/**
	 * 增加一个门户管理员
	 * @param $fields
	 * @param $areaLevelDB
	 * @return last_insert_id / false
	 */
	function addAreaUser($fields,$areaLevelDB=false){
		$fields['uid'] = intval($fields['uid']);
		$fields['username'] = trim($fields['username']);
		$fields['hasedit'] = intval($fields['hasedit']);
		$fields['hasattr'] = intval($fields['hasattr']);
		$needles['super'] = intval($fields['super']);
		$fields['level'] = (is_array($fields['level'])) ? serialize($fields['level']) : $fields['level'];
		if( 1 > $fields['uid']){
			return false;
		}
		if($this->getAreaUser($fields['uid'])){
			return false;
		}
		$areaLevelDB = ($areaLevelDB) ? $areaLevelDB : $this->_getAreaLevelDB();
		return $areaLevelDB->add($fields);
	}
	
	function updateAreaUserByUserName($fields,$userName){
		if("" == $userName){
			return array(false,$this->language("username_empty"));
		}
		$userService = $this->_getUserService();
		$userId = $userService->getUserIdByUserName($userName);
		if(!$userId){
			return array(false,$this->language("username_empty"));
		}
		$this->_updateAreaUserConfig();
		return $this->updateAreaUser($fields,$userId);
	}
	/**
	 * 更新一个门户管理员
	 * @param $fields
	 * @param $userId
	 * @return unknown_type
	 */
	function updateAreaUser($fields,$userId,$areaLevelDB=false){
		$fields['hasedit'] = intval($fields['hasedit']);
		$fields['hasattr'] = intval($fields['hasattr']);
		$fields['super'] = intval($fields['super']);
		$fields['level'] = (is_array($fields['level'])) ? serialize($fields['level']) : $fields['level'];
		if( 1 > $userId ){
			return array(false,$this->language("uid_empty"));
		}
		$areaLevelDB = ($areaLevelDB) ? $areaLevelDB : $this->_getAreaLevelDB();
		$areaLevelDB->update($fields,$userId);
		$this->_updateAreaUserConfig();
		return array(true,$this->language("update_success"));
	}
	/**
	 * 删除一个门户管理员
	 * @param $userId
	 * @return unknown_type
	 */
	function deleteAreaUser($userId){
		if( 1 > $userId ){
			return array(false,$this->language("uid_empty"));
		}
		$areaLevelDB = $this->_getAreaLevelDB();
		$areaLevelDB->delete($userId);
		$this->_updateAreaUserConfig();
		return array(true,$this->language("delete_success"));
	}
	/**
	 * 根椐用户名获取用户权限
	 * @param $userName
	 * @return unknown_type
	 */
	function getAreaUserByUserName($userName){
		if("" == $userName){
			return array(false,$this->language("username_empty"),'');
		}
		
		$userService = $this->_getUserService();
		$userId = $userService->getUserIdByUserName($userName);
		if(!$userId){
			return array(false,$this->language("username_empty"),'');
		}
		if(!($areaUser = $this->getAreaUser($userId))){
			return array(false,$this->language("userlevel_not_exist"),'');
		}
		return array(true,$this->language("do_success"),$areaUser);
	}
	
	function _updateAreaUserConfig() {
		require_once(R_P.'admin/cache.php');
		$users = $this->getAllAreaUser();
		$temp = array();
		foreach ($users as $value) {
			$temp[] = $value['uid'];
		}
		setConfig('db_portal_admins', $temp);
		updatecache_c();
	}
	
	/**
	 * 获取一个门户管理员
	 * @param $userId
	 * @return unknown_type
	 */
	function getAreaUser($userId){
		if( 1 > $userId ){
			return false;
		}
		$areaLevelDB = $this->_getAreaLevelDB();
		$result = $areaLevelDB->get($userId);
		if ($result) return $result;
		return $this->_getGMLevel();
	}
	/**
	 * 创始人有特殊权限
	 */
	function _getGMLevel() {
		global $manager,$windid;
		if (!S::inArray($windid, $manager)) return false;
		return array('uid'=>$windid,'hasedit'=>1,'hasattr'=>1,'super'=>1);
	}
	/**
	 * 获取多个门户管理员
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getAreaUsers($page,$perpage){
		if( 1 > $page || 1 > $perpage ){
			return false;
		}
		$areaLevelDB = $this->_getAreaLevelDB();
		return $areaLevelDB->gets($page,$perpage);
	}
	/**
	 * 统计门户管理员
	 * @return unknown_type
	 */
	function countAreaUser(){
		$areaLevelDB = $this->_getAreaLevelDB();
		return $areaLevelDB->count();
	}
	function getAllAreaUser() {
		$areaLevelDB = $this->_getAreaLevelDB();
		return $areaLevelDB->getAll();
	}
	/**
	 * 门户权限数据层
	 * @return unknown_type
	 */
	function _getAreaLevelDB() {
        return L::loadDB('AreaLevel', 'area');
    }
    
    /**
     * @return PW_UserService
     */
    function _getUserService() {
    	return L::loadClass('UserService', 'user');
    }
	
}
?>