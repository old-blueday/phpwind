<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
define ( 'HOOK_PATH', D_P . 'hook/' );

class PW_Hook {
	var $_params;	//内部公用的变量存储
	var $_hookName;
	var $_hookClasses = array();	//一个钩子中所有的扩展类
	var $_hookItems = array();
	function PW_Hook($name) {
		$this->_hookName = $name;
	}
	function __construct($name) {
		$this->PW_Hook($name);
	}
	function setParams($params) {
		$this->_params = $params;
	}
	function getParams() {
		return $this->_params;
	}
	/**
	 * 执行hook
	 */
	function runHook() {
		$this->_requireHookPack();
		$this->_initHookItems();
		foreach ( $this->_hookItems as $value ) {
			call_user_func_array ( array($value,'run'), array() );
		}
	}
	/**
	 * 执行变量过滤的hook
	 * @param unknown_type $result
	 * @return $result
	 */
	function runFilter($result) {
		$this->_requireHookPack();
		$this->_initHookItems($result);
		foreach ( $this->_hookItems as $value ) {
			$result = call_user_func_array ( array($value,'run'), array() );
		}
		return $result;
	}
	
	function _initHookItems($result = null) {
		foreach ( $this->_getHookClasses() as $value ) {
			$this->_hookItems[] = $this->_getHookItemObject($value,$result);
		}
		$this->_hookItems = PW_Hook::quickSort($this->_hookItems);
	}
	function _getHookClasses() {
		return $this->_hookClasses;
	}
	
	/**
	 * 打包该钩子内的所有扩展文件
	 * Enter description here ...
	 */
	function packHookFiles() {
		$hookPack = L::loadClass('hookpack','hook');
		$temp = $hookPack->packHookFile($this->_hookName);
		if (!$temp) return false;
		$file = $hookPack->getPackedFile($this->_hookName);
		$classesBegin = get_declared_classes();
		require_once(S::escapePath ($file));
		$classesEnd = get_declared_classes();
		$difference = array_diff($classesEnd,$classesBegin);
		$temp = $this->_cookDifferenceClasses($difference);
		pwCache::setData($this->_getHookClassesCacheFile(),$temp,true);
	}
	
	/**
	 * 快速排序算法
	 * @param unknown_type $array
	 */
	function quickSort($array) {
		if (count ( $array ) <= 1)
			return $array;
		$key = $array [0];
		$left_arr = array ();
		$right_arr = array ();
		for($i = 1; $i < count ( $array ); $i ++) {
			if ($array [$i]->priority >= $key->priority)
				$left_arr [] = $array [$i];
			else
				$right_arr [] = $array [$i];
		}
		$left_arr = PW_Hook::quickSort ( $left_arr );
		$right_arr = PW_Hook::quickSort( $right_arr );
		return array_merge ( $left_arr, array ($key ), $right_arr );
	}
	
	function _getHookClassesCacheFile() {
		return D_P.'data/bbscache/hookclasses_'.$this->_hookName.'.php';
	}
	
	function _getPackedFile($name) {
		L::loadClass('hookpack','hook',false);
		return Pw_HookPack::getPackedFile($name);
	}
	/**
	 * 加载扩展文件
	 * Enter description here ...
	 */
	function _requireHookPack() {
		global $db_hookmode;
		if ($db_hookmode) {
			return $this->_requireHookFiles();
		}
		$packedFile = $this->_getPackedFile($this->_hookName);
		if (!is_file($packedFile)) {
			$this->packHookFiles();
		}
		require_once($packedFile);
		$this->_hookClasses = $this->_cookDifferenceClasses(pwCache::getData($this->_getHookClassesCacheFile(),false));
	}
	
	function _requireHookFiles() {
		$hookPath = S::escapePath ( HOOK_PATH . $this->_hookName . '/' );
		$classesBegin = get_declared_classes();
				
		$fp = opendir ( $hookPath );
		while ( $filename = readdir ( $fp ) ) {
			$tempFileName = strtolower($filename);
			if ($filename == '..' || $filename == '.' || strpos ( $filename, '.' ) === 0 || !strpos ( $tempFileName, 'item' ) || !strpos ( $tempFileName, '.php' ))
				continue;
			require_once (S::escapePath ( $hookPath . $filename ));
		}
		closedir ( $fp );
		$classesEnd = get_declared_classes();
		$difference = array_diff($classesEnd,$classesBegin);
		$this->_hookClasses = $this->_cookDifferenceClasses($difference);
	}
	/**
	 * 获取所有的hook类
	 * @param array $classes
	 * @return array
	 */
	function _cookDifferenceClasses($classes) {
		$temp = array();
		foreach ($classes as $key=>$value) {
			$className = strtolower($value);
			if (!strpos($className,'filteritem') && !strpos($className,'hookitem')) continue;
			if (function_exists('stripos') && !is_subclass_of($value,'PW_FilterItem') && !is_subclass_of($value,'PW_HookItem')) continue;//since php >5.03
			$temp[$key] = $value;
		}
		return $temp;
	}
	
	function _callHook($className, $result = null) {
		$hook = $this->_getHookItemObject($className,$result);
		if ($result !== null) {
			return call_user_func_array ( array($hook,'run'), array() );
		} else {
			call_user_func_array ( array($hook,'run') , array() );
		}
	}
	
	function _getHookItemObject($className,$result = null) {
		static $items = array();
		if (isset($items[$className])) return $className;
		if (!class_exists($className)) exit($className." is not exit");
		$temp = new $className();
		if ($result !== null) {
			if (!is_subclass_of($temp,'PW_FilterItem')) exit($className." must extends PW_FilterItem");
			$temp->setResult($result);
		} else {
			if (!is_subclass_of($temp,'PW_HookItem')) exit($className." must extends PW_HookItem");
		}
		$temp->setHook($this);
		$temp->init();
		$items[$className] = $temp;
		return $items[$className];
	}
}



class BaseHookItem {
	var $priority = 0;
	var $sequence;
	var $_hook;
	function run() {} //abstruct class
	function init() {} //abstruct class
	function getVar($var) {
		$params = $this->_hook->getParams();
		return isset($params[$var]) ? $params[$var] : (isset($GLOBALS[$var]) ? $GLOBALS[$var] : null);
	}
	
	function setHook($hook) {
		$this->_hook = $hook;
	}
}

class PW_HookItem extends BaseHookItem{
	
}
class PW_FilterItem extends BaseHookItem{
	var $result;
	function setResult($result) {
		$this->result = $result;
	}
}