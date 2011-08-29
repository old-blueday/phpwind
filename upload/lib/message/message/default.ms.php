<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 消息中心默认服务层/配置/历史消息/搜索等服务
 * @2010-4-6 liuhui
 */
class MS_Default extends MS_Base {
	
	function getConst($typeName){
		return $this->getMap($typeName);
	}
	function getReverseConst($id){
		$maps = array_flip($this->maps());
		return isset($maps[$id]) ? $maps[$id] : '';
	}
	function getBlackColony($userId){
		$userId = intval($userId);
		if( 1 > $userId ) return false;
		if(!($config =  $this->getMsConfig($userId,$this->_c_blackcolony))){
			return false;
		}
		return $config ? unserialize($config) : false;
	}
	function getMsConfigs($userId){
		$userId = intval($userId);
		if( 1 > $userId ) return false;
		$configsDao = $this->getConfigsDao();
		return $configsDao->get($userId);
	}
	function setMsConfig($fieldData,$userId){
		$userId = intval($userId);
		if( 1 > $userId || !$fieldData ) return false;
		return $this->_setMsConfig($fieldData,$userId);
	}
	function getMsConfig($userId,$mKey){
		$userId = intval($userId);
		if( 1 > $userId || !$mKey ) return false;
		return $this->_getMsConfig($userId,$mKey);
	}
	/**
	 * 设置基础消息中心键值
	 * @return unknown_type
	 */
	function setMsKeys(){
		$msConfigs = array(  );
		return array_merge($msConfigs,$this->_msConfigs());
	}
	function getMsKey($key){
		$configs = $this->setMsKeys();
		return in_array($key,$configs) ? $key : '';
	}
	function getUserStatistics($userId){
		$configsDao = $this->getConfigsDao();
		$config = $configsDao->get($userId);
		if(!$config) return array(0,0,0,0);
		return array($config[$this->_c_sms_num],$config[$this->_c_notice_num],$config[$this->_c_request_num],$config[$this->_c_groupsms_num]);
	}
	function getUserSpecialStatistics($userId){
		$configsDao = $this->getConfigsDao();
		$config = $configsDao->get($userId);
		if(!$config) return array(0,0,0,0);
		return array( $this->_sms     => $config[$this->_c_sms_num],
					  $this->_notice   => $config[$this->_c_notice_num],
					  $this->_request  => $config[$this->_c_request_num],
					  $this->_groupsms => $config[$this->_c_groupsms_num]
		);
	}
	
	function resetStatistics($userIds,$mKey){
		if( !$userIds || "" == $mKey ) return false;
		$configsDao = $this->getConfigsDao();
		switch ($mKey){
			case $this->_c_sms_num :
				$fieldData = array($this->_c_sms_num      => 0 );
				break;
			case $this->_c_notice_num :
				$fieldData = array($this->_c_notice_num   => 0 );
				break;
			case $this->_c_request_num :
				$fieldData = array($this->_c_request_num  => 0 );
				break;
			case $this->_c_groupsms_num :
				$fieldData = array($this->_c_groupsms_num => 0 );
				break;
			default:
				break;
		}
		return $fieldData ? $configsDao->updateByUserIds($fieldData,$userIds) : false;
	}
	function setDefaultShield($app_array = array()){
		$default = array(
				    //'sms_message'       	 => 1,//留言
				    //'sms_comment_write' 	 => 1,//评论记录
				    //'sms_comment_diary' 	 => 1,//评论日志
				    //'sms_comment_photo' 	 => 1,//照片相册
					//'sms_comment_share' 	 => 1,//分享相册
				    'sms_share_diary' 		 => 1,//日志分享
				    'sms_share_photo' 		 => 1,//照片分享
				    'sms_share_post' 		 => 1,//帖子分享
				    'sms_share_group' 		 => 1,//群组分享
				    'sms_share_video' 		 => 1,//视频分享
				    'sms_share_music' 		 => 1,//音乐分享
				    'sms_share_link' 		 => 1,//链接分享
				    'sms_ratescore'          => 1,//评分
					'sms_reply'              => 1,//帖子回复
				    'notice_postcate'        => 1,//团购通知
				    'notice_active'          => 1,//活动通知
					'notice_apps'            => 1,//应用通知
				    $this->_s_notice_system  => 1,//系统通知
				    //$this->_notice_comment   => 1,//评论通知
				    'notice_comment_write' 	 => 1,//评论记录
				    'notice_comment_diary' 	 => 1,//评论日志
				    'notice_comment_photo' 	 => 1,//照片相册
					'notice_comment_share' 	 => 1,//分享相册
				    $this->_notice_guestbook => 1,//留言通知
				    'request_friend' 		 => 1,//好友请求
				    'request_group' 		 => 1,//群组请求
				    'request_apps' 			 => 1,//应用请求
		);
		if(!empty($app_array)){
		 	foreach($app_array as $key=>$value){
		 		$default['notice_app_'.$value['appid']] = 1;
		 	}
		}
		return $default;
	}
	function getMessageShield($userId,$key,$app_array=array()){
		$defaultShield = $this->setDefaultShield($app_array);
		$shieldinfo    = $this->getMsConfig($userId,'shieldinfo');
		$newShield     = $shieldinfo ? array_merge($defaultShield,unserialize($shieldinfo)) : $defaultShield;
		return in_array($key,array_keys($newShield)) ? $newShield[$key] : '';		
	}
	function getMessageShieldByUserName($userName,$key,$app_array=array()){
		$userService = $this->_getUserService();
		$member = $userService->getByUserName($userName);
		if (!$member) return false;
		
		return $this->getMessageShield($member['uid'],$key,$app_array);
	}
}