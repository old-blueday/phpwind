<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户前台管理服务层
 * @author liuhui @2010-3-11
 */
class PW_AreaManage {
	
	/**
	 * 推送内容管理
	 * @param $array  
	 * @param $page
	 * @param $url
	 * @param $prePage
	 * @return unknown_type
	 */
	function getPushData($array,$page,$url,$prePage){
		$pushdataService = $this->getPushDataService();
		$lists = $pushdataService->searchPushdatas($array,$page,$prePage);
		$pager = $pushdataService->searchPushdatasCount($array,$page,$url,$prePage);
		return array($lists,$pager);
	}
	
	/**
	 * 根椐用户名和频道ID获取一级下拉联动
	 * @param $userId
	 * @param $channelId
	 * @return 返回频道数组与模块数组 参数说明 $channels 频道数组  $invokes 模块数组  $subInvokes 子模块数组 
	 */
	function getFirstGrade($userId, $channelId = -1,$invoke = '',$ifverify=0){
		$userId = intval($userId);
		if($userId < 0 ){
			return array(false,array(),array(),array(),array());
		}
		if(false == ($areaInfos = $this->getAreaHaytack($userId,$ifverify))){
			return array(false,array(),array(),array(),array());
		}
		$channels = $invokes = array();
		$defaultId = 0;
	
		foreach($areaInfos as $key=>$info){
			( $defaultId < 1 ) && $defaultId = $key;
			$channels[$key] = $info['name'];
			$invokes[$key]  = $info['invokes'];
		}
		if ($channelId === -1) {
			$channelKeys = array_keys($channels);
			$channelId = $channelKeys[0];
		} else {
			$channelId = !$channelId ? $defaultId : $channelId;
		}
		$currentInvokes = (isset($invokes[$channelId])) ? $invokes[$channelId] : array();
		$invoke = $invoke ? $invoke : $this->_getFirstInvoke($currentInvokes);
		$subInvokes = $this->getSecondGrade($userId,$channelId,$invoke);

		$invokes = $currentInvokes;
		return array(true,$channels,$invokes,$subInvokes);
	}
	
	function _getFirstInvoke($invokes) {
		foreach ($invokes as $key=>$value) {
			return $key;
		}
	}
	
	function getSecondGrade($userId,$channelId,$invoke){
		$invokes =  $this->getInvokes($channelId);
		$temp = ($invokes && isset($invokes[$invoke])) ? $invokes[$invoke]['pieces'] : array();
		return $temp;
	}
	
	/**
	 * 根椐用户ID获取频道/模块
	 * @return unknown_type
	 */
	function getAreaHaytack($userId,$ifverify=0){
		$userId = intval($userId);
		if($userId < 0){
			return false;
		}
		$channels = $this->_portalConfigs($ifverify);

		if(!$channels){
			return false;
		}
		$levelService = $this->levelService();
		$userLevel = $levelService->getAreaUser($userId);
		if(!$userLevel){
			return false;
		}
		if($userLevel['super'] == 1 || $ifverify){
			return $channels;
		}
		if($userLevel['level'] == ""){
			return false;
		}
		$levels = unserialize($userLevel['level']);
		$result = $result1 = array();
		foreach($levels as $channelId=>$level){
			isset($channels[$channelId]) && $result1[$channelId] = $channels[$channelId];
			if (!isset($channels[$channelId])) continue;
			$result[$channelId] = array(
				'name' => $channels[$channelId]['name'],
				'invokes' => $this->_getAreaHaytack($level['invokes'],$channels[$channelId]['invokes'])
			);
		}
		return $result;
	}
	
	function _portalConfigs($ifverify) {
		$portalPageService = $this->_getportalPageService();
		return $portalPageService->getPortalInvokes($ifverify);
	}

	function _getAreaHaytack($levelInvokes,$pageInvokes) {
		$temp = array();
		$levelInvokes = is_array($levelInvokes) ? $levelInvokes : array();
		foreach ($levelInvokes as $name=>$title) {
			if (!isset($pageInvokes[$name])) continue;
			$temp[$name] = $title;
		}
		return $temp;
	}

	/**
	 * @param $channelId
	 * @return return array('模块名称1' => array('1'=>'帖子排行1','2'=>'用户排行1'));
	 */
	function getInvokes($channelId){
		$invokeService = L::loadClass('invokeservice', 'area');
		if (is_numeric($channelId)) {
			$channelService = $this->channelService();
			$alias = $channelService->getAliasByChannelid($channelId);
			return ($alias) ? $invokeService->getChannelInvokesForSelect($alias,0,1) : '';
		}
		return $invokeService->getPortalInvokesForSelect($channelId,0,1);
	}
	
	function buildSelect($arrays,$name,$id,$select='',$isEmpty = false,$tip = ""){
		if(!is_array($arrays)){
			return '';
		}
		$html = '<select name="'.$name.'" id="'.$id.'">';
		($isEmpty == true )  &&  $html .= '<option value="">'.$tip.'</option>';
		foreach($arrays as $k=>$v){
			$selected = ($select == $k && $select != null) ? 'selected="selected"' : "";
			$html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	function ifcheck($var,$out) {
		$GLOBALS[$out.'_Y'] = $GLOBALS[$out.'_N'] = '';
		$GLOBALS[$out.'_'.($var ? 'Y' : 'N')] = 'checked';
	}
	
	function getPushDataService(){
		return L::loadclass("PushDataService", 'area');
	}
	function levelService(){
		return L::loadclass("AreaLevel", 'area');
	}
	
	function channelService(){
		return L::loadclass("channelService", 'area');
	}
	
	function _getPortalPageService() {
		return L::loadClass('portalpageservice','area');
	}
}