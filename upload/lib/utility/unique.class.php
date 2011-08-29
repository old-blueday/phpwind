<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/*
 * 键值生成服务中心
 * @author L.IuHu.I developer.liuhui@gmail.com
 * 支持数据表/Memcache/APC生成唯一主键
 */
class PW_Unique {
	var $_strategy = 'db'; //主键生成策略,可选值( db / memcache / apc )
	var $_step = 100; //如果开启(memcache或apc)生成键值的步长值
	var $_prefix = 'primarykey_';
	var $_service = null;
	var $_configs = array ('post' => 'pw_pidtmp' ); //扩展配置(回复表)
	var $_tableName = null; //当前生成主键的数据表名称
	

	function PW_Unique() {
		$this->_strategy = (isset ( $GLOBALS ['db_unique_strategy'] )) ? $GLOBALS ['db_unique_strategy'] : $this->_strategy;
	}
	function getUnique($key) {
		if (! isset ( $this->_configs [$key] ) || ! ($this->_tableName = $this->_configs [$key])) {
			return false;
		}
		switch ($this->_strategy) {
			case 'db' :
				return $this->_getUniqueFromDB ();
				break;
			case 'memcache' :
				return $this->_getUniqueFromMemcache ();
				break;
			case 'apc' :
				return $this->_getUniqueFromApc ();
				break;
			default :
				return $this->_getUniqueFromDB ();
				break;
		}
	}
	function _getUniqueFromDB() {
		$_dbService = $this->_getDbService ();
		return $_dbService->insert ( $this->_tableName );
	}
	function _getUniqueFromMemcache() {
		return $this->_getUniqueFromCache ();
	}
	function _getUniqueFromApc() {
		return $this->_getUniqueFromCache ();
	}
	function _getUniqueFromCache() {
		$this->_getCacheService ();
		$key = $this->_getCurrentKey ();
		if (! ($currentId = $this->_service->increment ( $key ))) {
			$data = $this->_getPermanent ();
			$currentId = intval ( $data ['pid'] ) + 1;
			$this->_service->set ( $key, $currentId );
			$this->_setNextSegmentId ();
			return $currentId;
		}
		if (($currentId % $this->_step) == 0) {
			$this->_setNextSegmentId ();
		}
		return $currentId;
	}
	function _setNextSegmentId() {
		$_dbService = $this->_getDbService ();
		$_dbService->update ( $this->_tableName, $this->_step );
	}
	function _getPermanent() {
		$_dbService = $this->_getDbService ();
		return $_dbService->get ( $this->_tableName );
	}
	function flush($key) {
		if (! isset ( $this->_configs [$key] ) || ! ($this->_tableName = $this->_configs [$key])) {
			return false;
		}
		$_dbService = $this->_getDbService ();
		$_dbService->init ( $this->_tableName );
	}
	function clear($strategy) {
		$this->_strategy = $strategy;
		$this->_getCacheService ();
		if ($this->_service) {
			$this->_service->delete ( $this->_getCurrentKey () );
		}
	}
	function init() {
		$_dbService = $this->_getDbService ();
		foreach ( $this->_configs as $key => $tableName ) {
			if (! $_dbService->get ( $tableName )) {
				$_dbService->insert ( $tableName );
			}
		}
		return true;
	}
	function _getCurrentKey() {
		return $this->_prefix . 'currentid';
	}
	function _getDbService() {
		static $dbService = null;
		if (! $dbService) {
			$dbService = new PW_Unique_DB_Strategy ();
		}
		return $dbService;
	}
	function _getCacheService() {
		switch ($this->_strategy) {
			case 'memcache' :
				$this->_service = new PW_Unique_Memcache_Strategy ();
				break;
			case 'apc' :
				$this->_service = new PW_Unique_Apc_Strategy ();
				break;
		}
	}
}
class PW_Unique_Memcache_Strategy {
	var $_object = null;
	function PW_Unique_Memcache_Strategy() {
		$this->_object = L::loadClass ( 'cacheservice', 'utility' );
	}
	function set($key, $value) {
		return $this->_object->set ( $key, $value, 0 );
	}
	function get($key) {
		return $this->_object->get ( $key );
	}
	function increment($key, $step = 1) {
		return $this->_object->increment ( $key, $step );
	}
	function delete($key) {
		return $this->_object->delete ( $key );
	}
}
class PW_Unique_Apc_Strategy {
	var $_prefix = 'apc_';
	function get($key) {
		return apc_fetch ( $this->hash ( $key ) );
	}
	function set($key, $value) {
		return apc_store ( $this->hash ( $key ), $value, 0 );
	}
	function increment($key, $step = 1) {
		return apc_inc ( $this->hash ( $key ), $step );
	}
	function delete($key) {
		return apc_delete ( $this->hash ( $key ) );
	}
	function hash($key) {
		return $this->_prefix . $key;
	}
}
class PW_Unique_DB_Strategy {
	var $_db = NULL;
	function PW_Unique_DB_Strategy() {
		$this->__construct ();
	}
	function __construct() {
		$this->_db = $GLOBALS ['db'];
	}
	function init($tableName) {
		if (! $tableName) {
			return false;
		}
		$this->_db->query ( "TRUNCATE TABLE `" . $tableName . "`" );
		$this->insert ( $tableName );
	}
	function get($tableName) {
		if (! $tableName) {
			return false;
		}
		return $this->_db->get_one ( "SELECT MAX(pid) AS pid FROM `" . $tableName . "`" );
	}
	function update($tableName, $step) {
		if (! $tableName) {
			return false;
		}
		return $this->_db->query ( "UPDATE `" . $tableName . "` SET pid = pid + $step ORDER BY pid DESC LIMIT 1 " );
	}
	function insert($tableName) {
		if (! $tableName) {
			return false;
		}
		$this->_db->query ( "INSERT INTO `" . $tableName . "` (pid) VALUES (NULL)" );
		return $this->_db->insert_id ();
	}
}