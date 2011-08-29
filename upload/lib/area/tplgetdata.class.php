<?php
!defined('P_W') && exit('Forbidden');
class PW_tplGetData{
	var $cache = array();
	var $invokepieces = array();
	var $updates;
	function PW_tplGetData(){
		
	}
	
	function init($pieces){
		$this->invokepieces = array();
		//$pieces	= $this->_getPagePieces();
		$pw_invokepiece	= L::loadDB('invokepiece', 'area');
		$invokepieces	= $pw_invokepiece->getDatasByIds($pieces);
		foreach ($invokepieces as $key=>$value) {
			$temp = md5($value['invokename'].$value['title']);
			$this->invokepieces[$temp] = $value;
		}
		$this->_initCache($invokepieces);
	}
	
	function _initCache($invokepieces) {
		$pw_cachedata	= L::loadDB('cachedata', 'area');
		$config = array_keys($invokepieces);
		$this->cache	= $pw_cachedata->getDatasByInvokepieceids($config);
		$this->_cookCacheForCacheTime();
	}
	
	function _cookCacheForCacheTime() {
		global $timestamp;
		$temp = $this->cache;
		$cacheTimes = array();
		foreach ($temp as $key=>$value) {
			$cacheTimes[$key] = $value['cachetime'];
		}
		array_multisort($cacheTimes, SORT_ASC,$temp);
		$count = 0;
		foreach ($temp as $value) {
			if ($value['cachetime'] == 0) continue;
			if ($value['cachetime']>$timestamp || $count>4) break;
			$id = $value['invokepieceid'];
			$this->cache[$id]['ifupdate'] = 1;
			$count++;
		}
	}

	function getData($invokename,$title){
		if (!$invokename || !$title) return array();
		$temp = $this->_getDataFromCache($invokename,$title);

		if ($temp === false) {
			$temp = $this->_getDataFromBBS($invokename,$title);
		}
		return $temp;
	}

	/*
	 * private functions
	 */
	
	function _getPieceIdByInvokeNameAndTitle($invokename,$title) {
		$encode = md5($invokename.$title);
		return isset($this->invokepieces[$encode]) ? $this->invokepieces[$encode]['id'] : false;
	}
	function _getPieceConfigByInovkeNameAndTitle($invokename,$title) {
		$encode = md5($invokename.$title);
		if (isset($this->invokepieces[$encode])) {
			return $this->invokepieces[$encode];
		}
		$pw_invkoepiece = L::loadDB('invokepiece', 'area');
		return $pw_invkoepiece->getDataByInvokeNameAndTitle($invokename,$title);
	}

	function _getDataFromCache($invokename,$title){
		global $timestamp;
		$key = $this->_getPieceIdByInvokeNameAndTitle($invokename,$title);
		if (isset($this->cache[$key]) && ($this->cache[$key]['cachetime'] == 0 || !isset($this->cache[$key]['ifupdate']))) {
			return $this->cache[$key]['data'];
		}
		return false;
	}

	function _getDataFromBBS($invokename,$title){
		$pw_invkoepiece = L::loadDB('invokepiece', 'area');
		$config	= $this->_getPieceConfigByInovkeNameAndTitle($invokename,$title);
		$data	= $this->_getDataFromPush($config);
		if (count($data)<$config['num'] && !$config['ifpushonly']) {
			$tempElement= $this->_getDataFromSystem($config);
			$data		= $this->_combinElementAndPush($tempElement,$data,$config['num']);
		}
		//如果是图片模块，没有数据的情况下调用系统默认图片
		if (!$data && $config['action']=='image') {
			global $imgpath;
			$data[]	= array('image'=>"$imgpath/nopic.gif");
		}
		
		$this->_updateCache($config,$data);
		return $data;
	}

	function _updateCache($config,$data){
		global $timestamp;
		$invokename = $config['invokename'];
		$title	= $config['title'];
		
		$config['cachetime'] = (int) $config['cachetime'];
		$invokepieceid = $config['id'];
		$cachetime = $config['cachetime'] ? $timestamp+$config['cachetime'] : 0;
		$this->cache[$invokepieceid] = $this->updates[] = array(
			'invokepieceid'=>$invokepieceid,
			'data'	=> $data,
			'cachetime'	=> $cachetime,
		);
	}

	function _getDataFromSystem($config) {
		$dataSourceService = L::loadClass('datasourceservice', 'area');
		
		return $dataSourceService->getSourceData($config);
	}


	function _getDataFromPush($config){
		$pushdataService = L::loadClass('pushdataservice', 'area');
		
		$invokepieceid	= $config['id'];
		$num	= $config['num'];
		return $pushdataService->getEffectData($invokepieceid,$num);
	}

	function _combinElementAndPush($elements,$pushs,$num){
		$temp = array_merge($pushs,$elements);
		$new_array=array_slice($temp,0,$num);
		//ksort($new_array);
		return $new_array;
	}

}
?>