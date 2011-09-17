<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 全局缓存通用类,缓存配置服务,Memcache、数据库和文件缓存服务
 * @author L.IuHu.I@2010/10/14 developer.liuhui@gmail.com
 * @CopyRight phpwind 
 */
define ( 'PW_CACHE_VERSION', '1.0.0' );
define ( 'PW_CACHE_DIR', R_P . 'lib/utility' ); //当前目录
define ( 'PW_CACHE_CONFIG', 'cache_config' ); //缓存配置名称
! defined ( 'PW_CACHE_MEMCACHE' ) && define ( 'PW_CACHE_MEMCACHE', 'memcache' ); //内存缓存
! defined ( 'PW_CACHE_FILECACHE' ) && define ( 'PW_CACHE_FILECACHE', 'filecache' ); //文件缓存
! defined ( 'PW_CACHE_DBCACHE' ) && define ( 'PW_CACHE_DBCACHE', 'dbcache' ); //数据库缓存
class PW_CacheService {
	var $_services = array ();
	function PW_CacheService() {
		$this->__construct ();
	}
	/*
	 * 初始化缓存服务
	 */
	function __construct() {
		if (! isset ( $this->_services [PW_CACHE_CONFIG] ) || ! $this->_services [PW_CACHE_CONFIG]) {
			$this->_services [PW_CACHE_CONFIG] = new Cache_ServiceFactory ();
		}
	}
	/**
	 * 根椐指定的key存储value
	 * @param $key
	 * @param $value
	 * @param $cacheService
	 */
	function set($key, $value, $expire = 600, $cacheName = null) {
		if (! ($cacheService = $this->_services [PW_CACHE_CONFIG]->getService ( $cacheName ))) {
			return false;
		}
		return $cacheService->set ( $key, $value, $expire );
	}
	/**
	 * 根椐指定的key获取数据
	 * @param $key
	 * @param $cacheService
	 */
	function get($key, $cacheName = null) {
		if (! ($cacheService = $this->_services [PW_CACHE_CONFIG]->getService ( $cacheName ))) {
			return false;
		}
		return $cacheService->get ( $key );
	}
	/**
	 * 根椐指定的key删除数据
	 * @param $key
	 * @param $cacheService
	 */
	function delete($key, $cacheName = null) {
		if (! ($cacheService = $this->_services [PW_CACHE_CONFIG]->getService ( $cacheName ))) {
			return false;
		}
		return $cacheService->delete ( $key );
	}
	
	function increment($key, $value = 1, $cacheName = null) {
		if (! ($cacheService = $this->_services [PW_CACHE_CONFIG]->getService ( $cacheName ))) {
			return false;
		}
		return $cacheService->increment ( $key, $value );
	}
	
	/**
	 * 清空全部缓存数据
	 * @param $cacheService
	 */
	function flush($cacheName = null) {
		if (! ($cacheService = $this->_services [PW_CACHE_CONFIG]->getService ( $cacheName ))) {
			return false;
		}
		return $cacheService->flush ();
	}
}
/*
 * 全局通用缓存工厂类
 */
class Cache_ServiceFactory {
	var $_defaultService = null;
	var $_services = array ();
	function Cache_ServiceFactory() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_defaultService = $this->build ( Cache_Config_Default::defaultCache () );
	}
	
	function build($cacheName) {
		switch ($cacheName) {
			case PW_CACHE_MEMCACHE :
				return $this->getMemcacheService ();
			case PW_CACHE_FILECACHE :
				return $this->getFilecacheService ();
			case PW_CACHE_DBCACHE :
				return $this->getDbCacheService ();
			default :
				return false;
		}
	}
	
	function getService($cacheName) {
		return ($cacheName) ? $this->build ( $cacheName ) : $this->_defaultService;
	}
	/*
	 * 内存缓存
	 */
	function getMemcacheService() {
		if (! isset ( $this->_services [PW_CACHE_MEMCACHE] ) || ! $this->_services [PW_CACHE_MEMCACHE]) {
			$this->_services [PW_CACHE_MEMCACHE] = new PW_MemcacheService ();
		}
		return $this->_services [PW_CACHE_MEMCACHE];
	
	}
	/*
	 * 文件缓存
	 */
	function getFilecacheService() {
		if (! isset ( $this->_services [PW_CACHE_FILECACHE] ) || ! $this->_services [PW_CACHE_FILECACHE]) {
			$this->_services [PW_CACHE_FILECACHE] = new PW_FilecacheService ();
		}
		return $this->_services [PW_CACHE_FILECACHE];
	}
	/*
	 * 数据库缓存
	 */
	function getDbCacheService() {
		if (! isset ( $this->_services [PW_CACHE_DBCACHE] ) || ! $this->_services [PW_CACHE_DBCACHE]) {
			$this->_services [PW_CACHE_DBCACHE] = new PW_DbcacheService ();
		}
		return $this->_services [PW_CACHE_DBCACHE];
	}

}
/**
 * 全局缓存配置文件,可自定义配置与修改,结合phpwind函数或配置扩展
 * @author L.IuHu.I@2010/10/14
 */
class Cache_Config_Default {
	/*
	 * 默认缓存设置
	 */
	function defaultCache() {
		return PW_CACHE_MEMCACHE;
	}
	/**
	 * 唯一键值前缀
	 */
	function getUnique() {
		return $GLOBALS['db_memcache']['hash'];
	}
	
	function getCurrentTime() {
		return $GLOBALS ['timestamp'];
	}
}

/**
 * Memcache缓存配置
 */
class Cache_Config_Memcache {
	/**
	 * Memcache配置
	 */
	function load() {
		return ($GLOBALS ['db_memcache']) ? array ($GLOBALS ['db_memcache'] ) : array (array ('host' => 'localhost', 'port' => 11211 ) );
	}
}
/**
 * 文件缓存配置
 */
class Cache_Config_Filecache {
	/**
	 * 缓存目录配置
	 */
	function getDirectory() {
		return D_P . 'data/gathercache/';
	}
	/**
	 * 缓存后缀配置
	 */
	function getExt() {
		return '.php';
	}
	/**
	 * 创建文件路径
	 * @param $key
	 */
	function createFile($key) {
		return Cache_Config_Filecache::getDirectory () . Cache_Config_Default::getUnique () . $key . Cache_Config_Filecache::getExt ();
	}
	/**
	 * 读取文件[可扩展读服务]
	 */
	function readFile($fileName, $method = 'rb') {
		return readover ( $fileName, $method );
	}
	/**
	 * 写文件[可扩展写服务]
	 */
	function writeFile($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
		return writeover ( $fileName, $data, $method, $ifLock, $ifCheckPath, $ifChmod );
	}
}
/**
 * 数据库缓存配置
 */
class Cache_Config_Dbcache {
	
	function connect() {
		$tmp = $GLOBALS ['db']->getMastdb();
		return $tmp->sql;
	}
	/**
	 * 键值创建表,可自定义hash扩展数据表
	 * @param $key
	 */
	function getTable($key = null) {
		return 'pw_cache_storage';
	}
	
	function createTable() {
		//创建表结构
	}
}
/**
 * Memcache存储服务策略
 * @author L.IuHu.I@2010/10/14 developer.liuhui@gmail.com
 * @CopyRight phpwind 
 */
class PW_MemcacheService {
	var $_object = null;
	
	function PW_MemcacheService() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_load ();
	}
	/**
	 * 存储数据
	 * @param string $key
	 * @param mixture $value
	 * @param int $expire
	 */
	function set($key, $value, $expire = 600) {
		return (! $this->_check ()) ? false : $this->_object->set ( $this->_hash ( $key ), $value, 0, $expire );
	}
	
	function increment($key, $value = 1) {
		return (! $this->_check ()) ? false : $this->_object->increment ( $this->_hash ( $key ), $value );
	}
	/**
	 * 获取数据
	 * @param string|array $key
	 */
	function get($key) {
		return (! $this->_check ()) ? false : $this->_object->get ( $this->_hash ( $key ) );
	}
	/**
	 * 删除数据
	 * @param string|array $key
	 */
	function delete($key) {
		return (! $this->_check ()) ? false : $this->_object->delete ( $this->_hash ( $key ) );
	}
	/**
	 * 清除数据
	 */
	function flush() {
		return (! $this->_check ()) ? false : $this->_object->flush ();
	}
	/**
	 * 关闭服务
	 */
	function close() {
		return (! $this->_check ()) ? false : $this->_object->close ();
	}
	/**
	 * 唯一键值生成(单服务器多网站支持)
	 * @param $key
	 */
	function _hash($key) {
		$unique = Cache_Config_Default::getUnique ();
		if (! is_array ( $key )) {
			return $unique . $key;
		}
		$_tmpKey = array ();
		foreach ( $key as $k ) {
			$_tmpKey [] = $unique . $k;
		}
		return $_tmpKey;
	}
	/**
	 * 检查服务
	 */
	function _check() {
		return (is_object ( $this->_object )) ? true : false;
	}
	/**
	 * 启动服务
	 */
	function _load() {
		if (! class_exists ( 'memcache' ) || ! ($configs = $this->_config ())) {
			return false;
		}
		$this->_object = new Memcache ();
		if (method_exists ( $this->_object, 'addServer' )) {
			foreach ( $configs as $config ) {
				$this->_object->addServer ( $config ['host'], $config ['port'] );
			}
		} else {
			$this->_object->connect ( $configs [0] ['host'], $configs [0] ['port'] );
		}
	}
	/**
	 * 配置服务
	 */
	function _config() {
		return Cache_Config_Memcache::load ();
	}

}
/**
 * 文件存储服务策略[安全策略与服务]
 * @author L.IuHu.I@2010/10/15 developer.liuhui@gmail.com
 * @CopyRight phpwind 
 */
class PW_FilecacheService {
	var $_object = null;
	
	function PW_FilecacheService() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_load ();
	}
	/**
	 * 存储数据
	 * @param string $key
	 * @param string|array $value
	 */
	function set($key, $value, $expire = 600) {
		return ($this->_check ()) ? $this->_object->write ( $this->_hash ( $key ), $value, $expire ) : false;
	}
	/**
	 * 获取数据
	 * @param string|array $key
	 */
	function get($key) {
		return ($this->_check ()) ? $this->_object->read ( $this->_hash ( $key ) ) : false;
	}
	/**
	 * 删除数据
	 * @param string|array $key
	 */
	function delete($key) {
		return ($this->_check ()) ? $this->_object->delete ( $this->_hash ( $key ) ) : false;
	}
	/**
	 * 清除数据
	 * @param string|array $key
	 */
	function flush() {
		return ($this->_check ()) ? $this->_object->flush () : false;
	}
	
	function increment($key, $value = 1) {
	
	}
	/**
	 * 组装键值文件
	 * @param string|array $key
	 */
	function _hash($key) {
		if (! is_array ( $key )) {
			return $key;
		}
		$_tmp = array ();
		foreach ( $key as $k ) {
			$_tmp [] = $k;
		}
		return $_tmp;
	}
	/**
	 * 启动服务
	 */
	function _load() {
		if (! $this->_object) {
			$this->_object = new Cache_File_OperateService ();
		}
	}
	/**
	 * 检查服务
	 */
	function _check() {
		return (is_object ( $this->_object )) ? true : false;
	}

}
/**
 * 文件操作服务类
 */
class Cache_File_OperateService {
	/*
	 * 读取文件操作
	 */
	function read($key, $method = 'rb') {
		if (! is_array ( $key )) {
			return $this->_read ( $key, $method );
		}
		$_tmpData = array ();
		foreach ( $key as $k ) {
			$_tmpData [$k] = $this->_read ( $k, $method );
		}
		return $_tmpData;
	}
	
	function _read($key, $method = 'rb') {
		if (! ($fileName = Cache_Config_Filecache::createFile ( $key ))) {
			return array ();
		}
		$data = unserialize ( Cache_Config_Filecache::readFile ( $fileName, $method ) );
		$currentTime = Cache_Config_Default::getCurrentTime ();
		if (! $data || $data ['expire'] < $currentTime) {
			$this->delete ( $key );
			return array ();
		}
		return $data ['value'];
	}
	/*
	 * 写入文件操作
	 */
	function write($key, $value, $expire = 600) {
		$fileName = Cache_Config_Filecache::createFile ( $key );
		$expire = Cache_Config_Default::getCurrentTime () + $expire;
		return Cache_Config_Filecache::writeFile ( $fileName, serialize ( array ('expire' => $expire, 'value' => $value ) ) );
	}
	/*
	 * 删除文件操作
	 */
	function delete($key) {
		$fileName = Cache_Config_Filecache::createFile ( $key );
		if (! is_file ( $fileName ))
			return false;
		P_unlink ( $fileName );
	}
	
	/*
	 * 清空文件操作
	 */
	function flush() {
		$directory = Cache_Config_Filecache::getDirectory ();
		if (! is_dir ( $directory )) {
			return false;
		}
		if (! ($dh = opendir ( $directory ))) {
			return false;
		}
		while ( (($file = readdir ( $dh )) !== false) ) {
			if (in_array ( $file, array ('.', '..' ) ))
				continue;
			$this->delete ( $directory . $file );
		}
		closedir ( $dh );
	}

}
/**
 * 数据库存储服务策略
 * @author L.IuHu.I@2010/10/15 developer.liuhui@gmail.com
 * @CopyRight phpwind  
 */
class PW_DbcacheService {
	var $_object = null;
	
	function PW_DbcacheService() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_load ();
	}
	/**
	 * 存储数据
	 * @param string $key
	 * @param string|array $value
	 * @param int $expire
	 */
	function set($key, $value, $expire = 600) {
		return ($this->_check ()) ? $this->_object->insert ( $this->_hash ( $key ), $value, $expire ) : false;
	}
	/**
	 * 获取数据
	 * @param string|array $key
	 */
	function get($key) {
		return ($this->_check ()) ? $this->_object->get ( $this->_hash ( $key ) ) : false;
	}
	/**
	 * 删除数据
	 * @param string|array $key
	 */
	function delete($key) {
		return ($this->_check ()) ? $this->_object->delete ( $this->_hash ( $key ) ) : false;
	}
	/**
	 * 清除数据
	 */
	function flush() {
		return ($this->_check ()) ? $this->_object->flush () : false;
	}
	
	function increment($key, $value = 1) {
	
	}
	/**
	 * 生成唯一键值
	 * @param $key
	 */
	function _hash($key) {
		$unique = Cache_Config_Default::getUnique ();
		if (! is_array ( $key )) {
			return $unique . $key;
		}
		$_tmp = array ();
		foreach ( $key as $k ) {
			$_tmp [] = $unique . $k;
		}
		return $_tmp;
	}
	/**
	 * 启动服务
	 */
	function _load() {
		if (! $this->_object) {
			$this->_object = new Cache_Db_OperateService ();
		}
	}
	/**
	 * 检查服务
	 */
	function _check() {
		return (is_object ( $this->_object )) ? true : false;
	}
}
/**
 * 数据库操作服务类
 */
class Cache_Db_OperateService {
	var $_conn = NULL;
	
	function Cache_Db_OperateService() {
		$this->__construct ();
	}
	function __construct() {
		$this->_conn = $this->connect ();
	}
	/*
	 * 写入数据
	 */
	function insert($key, $value, $expire = 600) {
		$expire = Cache_Config_Default::getCurrentTime () + $expire;
		return $this->query ( "REPLACE INTO " . $this->getTableName ( $key ) . " (ckey,cvalue,expire) VALUES (" . $this->escape ( $key ) . "," . $this->escape ( serialize ( $value ) ) . "," . $this->escape ( $expire ) . ")" );
	}
	/*
	 * 获取数据
	 */
	function get($key) {
		$key = (is_array ( $key )) ? $key : array ($key );
		$currentTime = Cache_Config_Default::getCurrentTime ();
		$query = $this->query ( "SELECT ckey,cvalue FROM " . $this->getTableName ( $key ) . " WHERE ckey IN ( " . $this->escape ( $key ) . ") AND expire >= " . $this->escape ( $currentTime ) );
		$result = $this->fetchArray ( $query );
		return $result;
	}
	/*
	 * 删除数据
	 */
	function delete($key) {
		$key = (is_array ( $key )) ? $key : array ($key );
		return $this->query ( "DELETE FROM " . $this->getTableName ( $key ) . " WHERE ckey IN( " . $this->escape ( $key ) . ")" );
	}
	/*
	 * 清空数据
	 */
	function flush() {
		return $this->query ( "TRUNCATE TABLE " . $this->getTableName () );
	}
	/*
	 * 查询
	 */
	function query($sql) {
		return mysql_query ( $sql, $this->_conn );
	}
	/*
	 * 过滤
	 */
	function escape($key) {
		if (! is_array ( $key )) {
			return "'" . $key . "'";
		}
		$_tmp = '';
		foreach ( $key as $k ) {
			$_tmp .= (($_tmp) ? ',' : '') . "'" . $k . "'";
		}
		return $_tmp;
	}
	/*
	 * 组装
	 */
	function fetchArray($result, $type = MYSQL_ASSOC) {
		$rt = array ();
		while ( $row = mysql_fetch_array ( $result, $type ) ) {
			$rt [$row ['ckey']] = unserialize ( $row ['cvalue'] );
		}
		return $rt;
	}
	/*
	 * 获取数据表
	 */
	function getTableName($key = null) {
		return Cache_Config_Dbcache::getTable ( $key );
	}
	/*
	 * 连接
	 */
	function connect() {
		return Cache_Config_Dbcache::connect ();
	}
}