<?php
/**
 * 导航配置服务类文件
 * 
 * @package Nav
 */

!defined('P_W') && exit('Forbidden');

define('PW_NAV_TYPE_MAIN', 'main');
define('PW_NAV_TYPE_HEAD_LEFT', 'head_left');
define('PW_NAV_TYPE_HEAD_RIGHT', 'head_right');
define('PW_NAV_TYPE_FOOT', 'foot');

/**
 * 导航配置服务对象
 * 
 * @package Nav
 */
class PW_NavConfig {
	
	function add($navType, $fieldsData) {
		if (!$this->_checkNavType($navType)) return 0;
		$navConfigDb = $this->_getNavConfigDB();
		$fieldsData['type'] = $navType;
		isset($fieldsData['style']) && $fieldsData['style'] = $this->_encodeStyleField($fieldsData['style']);
		isset($fieldsData['pos']) && $fieldsData['pos'] = $this->_encodePosField($fieldsData['pos']);
		return $navConfigDb->add($fieldsData);
	}
	
	function get($navId) {
		$navConfigDb = $this->_getNavConfigDB();
		$data = $navConfigDb->get($navId);
		if (!$data) return null;
		$data['style'] = $this->_decodeStyleField($data['style']);
		$data['pos'] = $this->_decodePosField($data['pos']);
		return $data;
	}
	
	function getByKey($navKey, $navType = '') {
		$navKey = trim($navKey);
		if ('' == $navKey) return null;
		$navConfigDb = $this->_getNavConfigDB();
		return $navConfigDb->getByKey($navKey, $navType);
	}
	
	function update($navId, $fieldsData) {
		if ($navId <= 0) return 0;
		$navConfigDb = $this->_getNavConfigDB();
		isset($fieldsData['style']) && $fieldsData['style'] = $this->_encodeStyleField($fieldsData['style']);
		isset($fieldsData['pos']) && $fieldsData['pos'] = $this->_encodePosField($fieldsData['pos']);
		return $navConfigDb->update($navId, $fieldsData);
	}
	
	function controlShowByKey($navKey, $isShow = false) {
		$navKey = trim($navKey);
		if ('' == $navKey) return 0;
		$navConfigDb = $this->_getNavConfigDB();
		return $navConfigDb->updateByKey($navKey, array('isshow'=>$isShow ? 1 : 0));
	}
	
	function delete($navId) {
		if ($navId <= 0) return 0;
		$navIds = array($navId);
		$navConfigDb = $this->_getNavConfigDB();
		
		$data = $navConfigDb->get($navId);
		if (!$data) return 0;
		foreach ($navConfigDb->findSubNavsByType($data['type'], $navId) as $nav) {
			$navIds[] = $nav['nid'];
		}
		
		return $navConfigDb->deletes($navIds);
	}
	
	function deleteByType($navType) {
		if (!$this->_checkNavType($navType)) return array();
		$navConfigDb = $this->_getNavConfigDB();
		return $navConfigDb->deleteByType($navType);
	}
	
	function deleteByKey($navKey) {
		$navKey = trim($navKey);
		if ('' == $navKey) return 0;
		
		$navConfigDb = $this->_getNavConfigDB();
		$exist = $navConfigDb->getByKey($navKey);
		if (!$exist) return 0;
		return $this->delete($exist['nid']);
	}
	
	function findNavListByType($navType) {
		if (!$this->_checkNavType($navType)) return array();
		$navConfigDb = $this->_getNavConfigDB();
		return $navConfigDb->findByType($navType);
	}
	
	function relateNavList($navList) {
		$relativeNavs = array();
		foreach ($navList as $nav) {
			$nav['pos'] = $this->_decodePosField($nav['pos']);
			if ($nav['upid']) {
				$relativeNavs[$nav['upid']]['subs'][$nav['nid']] = $nav;
			} else {
				$relativeNavs[$nav['nid']]['data'] = $nav;
			}
		}
		return $relativeNavs;
	}
	
	function findSubNavListByType($navType, $parentNavId = 0) {
		if (!$this->_checkNavType($navType)) return array();
		$navConfigDb = $this->_getNavConfigDB();
		return $navConfigDb->findSubNavsByType($navType, $parentNavId);
	}
	
	function findNavConfigs(){
		static $navConfigData = array();
		if(!$navConfigData){
			//* @include_once pwCache::getPath(D_P . 'data/bbscache/navcache.php',true);
			extract(pwCache::getData(D_P . 'data/bbscache/navcache.php',false));
			//* $navConfigData = ($navConfigData) ? $navConfigData : $GLOBALS['navConfigData'];
		}
		if(!$navConfigData){
			$navConfigDb = $this->_getNavConfigDB();
			$navConfigData = $navConfigDb->findNavConfigs();
		}
		return $navConfigData;
	}
	
	function findValidNavListByTypeAndPostion($navType, $postion, $currentPostion = array()) {
		$relativeNavs = array();
		$notValidParent = array();
		$currentPostionsKeeper = array();
		$navConfigData = $this->findNavConfigs();
		foreach ((array)$navConfigData[$navType] as $nav) {
			if (!$nav['isshow']) continue;
			
			$nav['iscurrent'] = false;
			$nav['pos'] = $this->_decodePosField($nav['pos']);
			$nav['style'] = $this->_decodeStyleField($nav['style']);
				
			if ($nav['upid']) {
				$relativeNavs[$nav['upid']]['subs'][$nav['nid']] = $nav;
				continue;
			}
			
			if ($this->_isInPos($postion, $nav['pos'])) {
				$currentPostionsKeeper[$nav['nid']] = $this->_compareIsCurrent($currentPostion, $nav['nkey']);
				$relativeNavs[$nav['nid']]['data'] = $nav;
			} else {
				$notValidParent[$nav['nid']] = $nav['nid'];
			}
		}
		$maxCurrentStatus = !empty($currentPostionsKeeper) ? max($currentPostionsKeeper) : 0;
		if ($maxCurrentStatus) {
			$relativeNavs[array_search($maxCurrentStatus, $currentPostionsKeeper)]['data']['iscurrent'] = true;
		}

		foreach ($notValidParent as $navId) {
			unset($relativeNavs[$navId]);
		}
		return $relativeNavs;
	}
	
	/**
	 * 判断是否是当前导航项
	 * 
	 * @param array $currentPostion 数组，array('mode'=>当前模式, ['alias'=>门户模式频道])
	 * @param string $navKey 导航的代码，mode_sub格式
	 * @return int 0为非当前导航，>0表示是当前导航，值越大越明显（优先级）
	 */
	function _compareIsCurrent($currentPostion, $navKey) {
		if (!is_array($currentPostion) || !count($currentPostion) || '' == $navKey) return 0;
		if ($currentPostion['mode'] == $navKey) return 1;
		if (implode("_", $currentPostion) == $navKey) return 2;
		return 0;
	}
	
	/**
	 * get PW_NavConfigDB
	 * 
	 * @access protected
	 * @return PW_NavConfigDB
	 */
	function _getNavConfigDB() {
		return L::loadDB('navconfig', 'site');
	}
	
	function _checkNavType($navType) {
		return in_array($navType, array(PW_NAV_TYPE_MAIN, PW_NAV_TYPE_HEAD_LEFT, PW_NAV_TYPE_HEAD_RIGHT, PW_NAV_TYPE_FOOT));
	}
	
	function _encodePosField($posValue) {
		if ('-1' == $posValue) return $posValue;
		if (is_array($posValue)) return implode(',', $posValue);
		return '';
	}
	function _decodePosField($posField) {
		if ('-1' == $posField) return $posField;
		if ($posField) return explode(',', $posField);
		return array();
	}
	function _isInPos($postion, $posValue) {
		if ('-1' == $posValue) return true;
		if (is_array($posValue)) return in_array($postion, $posValue);
		return false;
	}
	
	function _encodeStyleField($styleData) {
		return implode("|", array($styleData['color'], $styleData['b'], $styleData['i'], $styleData['u']));
	}
	function _decodeStyleField($styleField) {
		$styleField = explode('|', $styleField);
		return array('color'=>$styleField[0], 'b'=>$styleField[1], 'i'=>$styleField[2], 'u'=>$styleField[3]);
	}

	/**
	 * 个人中心导航
	 * 
	 * @param string $type 导航类型
	 * @param string $model 导航位置
	 * @return array $homenavigation
	 */
	function userHomeNavigation($type,$model) {
		if (!$type || !$model) return array();
		$homenavigations = $this->findValidNavListByTypeAndPostion($type, $model);
		if (!S::isArray($homenavigations)) return array();
		$homenavigation = array();
		$homenavigation['linkup'][] = array_shift($homenavigations);
		$homenavigation['linkup'][] = array_shift($homenavigations);
		$homenavigation['linkup'][] = array_shift($homenavigations);	
		foreach ($homenavigations as $value) {
			$homenavigation['linkdown'][] = $value;
		}
		return $homenavigation;
	}
}
