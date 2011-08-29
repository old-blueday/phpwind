<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 扩展搜索服务层
 * @author L.IuHu.I@2010-9-7
 */
class PW_ExtendSearcher {
	
	var $_services = array();
	var $_configs = array();
	
	/**
	 * 扩展自定义搜索服务通用接口
	 */
	function extendSearcher($type){
		static $classes = array ();
		$type = strtolower ( $type );
		if (! $classes [$type]) {
			$filePath = R_P . "lib/search/userdefine/" . $type . "searcher.extend.php";
			if (! is_file ( $filePath ))
				return false;
			if (!class_exists('Search_Base')) require_once (R_P . 'lib/search/search/base.search.php');
			require_once S::escapePath ( $filePath );
			$className = 'PW_SearchExtend_' . $type . '_Searcher';
			if (! class_exists ( $className )) {
				return false;
			}
			$classes [$type] = &new $className ();
		}
		return $classes [$type];
	}
	/**
	 * 加载扩展搜索服务
	 * @param $invoke 
	 * @param $condition
	 * @param $array 
	 * @return 数组/字符串
	 */
	function invokeSearcher($invoke,$condition = array(),$array = true){
		$invoke = trim($invoke);
		if($invoke == '') return false;
		$configs = $this->_getConfigs($invoke);
		if(!S::isArray($configs)) return false;
		$result = array();
		foreach($configs as $invokename=>$config){
			$service = $this->_getService($invokename,$config);
			$result[$invokename] = $service->getSearchResult($condition);
		}
		return ($array) ? $result : $this->_buildStringResult($result);
	}
	
	/**
	 * 将结果集转化为字符输出
	 * @param $arrays
	 */
	function _buildStringResult($arrays){
		if(!S::isArray($arrays)) return '';
		$string = '';
		foreach($arrays as $key=>$value){
			$string .= is_string($value) ? $value : '';
		}
		return $string;
	}
	
	/**
	 * 公共搜索接口
	 * @param $condition
	 */
	function getSearchResult($condition){
		return '';
		//return 'ERROR 0002: you get Default Result!';
	}
	/**
	 * 设置扩展搜索服务
	 * @param $register
	 */
	function _setService($invokename,$config){
		if( !S::isArray($config) || !is_file($config['path']) || !isset($config['classname'] )) return false;
		require_once S::escapePath($config['path']);
		$className = 'PW_'.ucfirst(trim($config['classname'])).'Searcher';
		if(!class_exists($className,true) || !class_exists('PW_ExtendSearcherAbstract',true)){
			return false;
		}
		$this->_services[$invokename] = new $className();
	}
	/**
	 * 获取扩展搜索服务
	 * @param $register
	 */
	function _getService($invokename,$config){
		if(!$this->_services[$invokename]){
			$this->_setService($invokename,$config);
		}
		return is_object($this->_services[$invokename]) ? $this->_services[$invokename] : $this;
	}
	
	/**
	 * 根椐KEY获取配置信息
	 * @param $key
	 */
	function _getConfigs($invoke){
		$configs = $this->_setConfigs();
		return ( $invoke && in_array($invoke,array_keys($configs))) ? $configs[$invoke] : array();
	}
	/**
	 * 获取扩展搜索配置
	 */
	function _setConfigs(){
		global $db_modes;
		if(!$this->_configs){
			require_once R_P.'lib/search/extend/extendconfigs.php';
			$this->_configs = (S::isArray($configs)) ? $configs : array();
		}
		return $this->_configs;
	}
}