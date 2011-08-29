<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 全局聚合服务中心
 * 查询聚合中心/缓存聚合中心/信息聚合中心
 */
define ( 'PW_UPDATE', 'update' );
define ( 'PW_SELECT', 'select' );
define ( 'PW_DELETE', 'delete' );
define ( 'PW_INSERT', 'insert' );
define ( 'PW_REPLACE', 'replace' );
class PW_Gather {
	/*
	 * 聚合缓存服务
	 * string $cacheName 缓存名称,数据表名称,如pw_threads/pw_members
	 */
	function spreadCache($cacheName) {
		if (! $cacheName)
			return false;
		return $this->_loadGatherCache ( $cacheName );
	}
	/*
	 * 聚合查询服务
	 * string $operate 		操作字符,如insert/select/update/replace/delete
	 * array $tableNames 	数据表列表
	 * array $fields where	条件数组或更新数组
	 * array $expand 		扩展数组
	 */
	function spreadQuery($operate, $tableNames, $fields, $expand = array()) {
		if (! S::isArray ( $tableNames )) {
			return false;
		}
		foreach ( $tableNames as $tableName ) {
			$this->_loadGatherQuery ( $operate, $tableName, $fields, $expand );
		}
		return true;
	}
	/*
	 * 聚合信息服务
	 * $gatherName  聚合名称(函数名称)
	 * $information 聚合信息数组
	 * $defaultName	聚合文件前缀,默认为general
	 */
	function spreadInfo($gatherName, $information = null, $defaultName = 'general') {
		if (! $gatherName)
			return false;
		return $this->_loadGatherInfo ( $gatherName, $information );
	}
	function _loadGatherCache($cacheName) {
		static $_cacheNames = null;
		$cacheName = strtolower ( $cacheName );
		if (! isset ( $_cacheNames [$cacheName] )) {
			$filePath = R_P . "lib/gather/gathercache/" . $cacheName . ".cache.php";
			if (! is_file ( $filePath ))
				return false;
				# pack class start
			$className = 'GatherCache_' . $cacheName . '_Cache';
			$filePath = pwPack::classPath ( $filePath, $className );
			# pack class end
			require_once S::escapePath ( $filePath );
			#$className = 'GatherCache_' . $cacheName . '_Cache';
			if (! class_exists ( $className )) {
				return false;
			}
			$_cacheNames [$cacheName] = &new $className ();
		}
		return $_cacheNames [$cacheName];
	}
	function _loadGatherQuery($operate, $tableName, $fields, $expand) {
		static $_classes = null;
		$tmpTableName = $tableName = strtolower ( $tableName );
		$tableName = $this->_convertTableName ( $tableName );
		if (! isset ( $_classes [$tableName] )) {
			$filePath = R_P . "lib/gather/gatherquery/" . $tableName . ".query.php";
			if (! is_file ( $filePath ))
				return false;
			require_once S::escapePath ( $filePath );
			$className = 'GatherQuery_UserDefine_' . $tableName;
			if (! class_exists ( $className )) {
				return false;
			}
			$_classes [$tableName] = &new $className ();
		}
		return $this->dispatchQuery ( $_classes [$tableName], $operate, $tmpTableName, $fields, $expand );
	}
	function dispatchQuery($userDefineClass, $operate, $tableName, $fields, $expand = array()) {
		if (! S::isObj ( $userDefineClass ) || ! $operate || ! S::isArray ( $fields )) {
			return false;
		}
		$userDefineClass->init ();
		switch (strtolower ( $operate )) {
			case 'insert' :
				$userDefineClass->insert ( $tableName, $fields, $expand );
				break;
			case 'replace' :
				$userDefineClass->insert ( $tableName, $fields, $expand );
				break;
			case 'select' :
				$userDefineClass->select ( $tableName, $fields, $expand );
				break;
			case 'update' :
				$userDefineClass->update ( $tableName, $fields, $expand );
				break;
			case 'delete' :
				$userDefineClass->delete ( $tableName, $fields, $expand );
				break;
			default :
				break;
		}
		return true;
	}
	function _loadGatherInfo($gatherName, $information, $defaultName = 'general') {
		$filePath = R_P . "lib/gather/gatherinfo/" . $defaultName . ".service.php";
		if (! is_file ( $filePath ))
			return false;
		require_once S::escapePath ( $filePath );
		$className = 'GatherInfo_' . $defaultName . '_Service';
		if (! class_exists ( $className ) || ! is_callable ( array ($className, $gatherName ) )) {
			return false;
		}
		$object = &new $className ();
		return $object->$gatherName ( $information );
	}
	function _convertTableName($tablename) {
		$extendTableNames = array ();
		if ($GLOBALS ['db_tlist']) {
			foreach ( $GLOBALS ['db_tlist'] as $k => $v ) {
				$extendTableNames ['pw_tmsgs' . ($k ? $k : '')] = 'pw_threads';
			}
		}
		if ($GLOBALS ['db_plist']) {
			foreach ( $GLOBALS ['db_plist'] as $k => $v ) {
				$extendTableNames ['pw_posts' . ($k ? $k : '')] = 'pw_posts';
			}
		}
		$tableNames = array ('pw_tmsgs' => 'pw_threads',  'pw_memberinfo' => 'pw_members', 'pw_memberdata' => 'pw_members', 'pw_singleright' => 'pw_members', 'pw_membercredit' => 'pw_members', 'pw_banuser' => 'pw_members', 'pw_cmembers' => 'pw_members', 'pw_membertags_relations' => 'pw_members', 'pw_forumdata' => 'pw_forums', 'pw_announce' => 'pw_forums');
		$tableNames += $extendTableNames;
		return (isset ( $tableNames [$tablename] )) ? $tableNames [$tablename] : $tablename;
	}
}
class GatherCache_Base_Cache {
	
	var $_cacheService = null;
	
	function GatherCache_Base_Cache() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_cacheService = ($this->_cacheService) ? $this->_cacheService : $this->getCacheService ();
	}
	
	function checkMemcache() {
		static $isMemcache = null;
		if (! isset ( $isMemcache )) {
			$isMemcache = class_exists ( "Memcache" ) && strtolower ( $GLOBALS ['db_datastore'] ) == 'memcache';
		}
		return $isMemcache;
	}
	
	function getUnique() {
		return $GLOBALS ['db_memcache'] ['hash'];
	}
	
	function getCacheService() {
		return L::loadClass ( 'cacheservice', 'utility' );
	}
}