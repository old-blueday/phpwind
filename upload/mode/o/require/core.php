<?php
!defined('P_W') && exit('Forbidden');
class C extends PW_BaseLoader {

	/**
	 * 类文件的加载入口
	 * 
	 * @param string $className 类的名称
	 * @param string $dir 目录：末尾不需要'/'
	 * @param boolean $isGetInstance 是否实例化
	 * @return mixed
	 */
	function loadClass($className, $dir = '', $isGetInstance = true) {
		return parent::_loadClass($className, 'mode/o/lib/' . parent::_formatDir($dir), $isGetInstance);
	}

	/**
	 * 加载db类
	 * @param $className
	 */
	function loadDB($dbName, $dir = '') {
		parent::_loadBaseDB();
		return C::loadClass($dbName . 'DB', parent::_formatDir($dir) . 'db');
	}
}
