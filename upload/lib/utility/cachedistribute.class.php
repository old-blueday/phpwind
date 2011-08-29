<?php
!defined('P_W') && exit('Forbidden');
/*
 * 缓存分发服务,支持缓存分布式扩展
 * @author L.IuHu.I@2011/01/11 developer.liuhui@gmail.com
 */
define ( 'DISTRIBUTE_DATABASE', 'database' );
define ( 'DISTRIBUTE_FILE', 'file' );
define ( 'DISTRIBUTE_ARRAY_TYPEID', 2 );
define ( 'DISTRIBUTE_STRING_TYPEID', 1 );
define ( 'DISTRIBUTE_DEBUG', 1 );
define ( 'DISTRIBUTE_CACHE_DIR', D_P . 'data' );
class PW_CacheDistribute {
	
	var $_distributeType = null;
	var $_distributeService = null;
	
	function PW_CacheDistribute() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_initService ();
	}
	
	function _initService() {
		if (!isset($GLOBALS ['db_distribute'])){
			$data = $this->_getDefinedVariables(D_P . 'data/sql_config.php');
			isset($data['db_distribute']) && $this->_registerGlobals(array('db_distribute'=>$data['db_distribute']));
		}
		if (!isset($GLOBALS ['db_filecache_to_memcache'])){
			$data = $this->_getDefinedVariables(D_P . 'data/bbscache/baseconfig.php');
			is_array($data) && $this->_registerGlobals($data);
		}
		
		if (! $this->_distributeService) {
			$this->_distributeType = (! $GLOBALS ['db_distribute']) ? DISTRIBUTE_FILE : DISTRIBUTE_DATABASE;
			switch ($this->_distributeType) {
				case DISTRIBUTE_DATABASE :
					$this->_distributeService = new PW_CacheDistribute_DataBase ();
					break;
				case DISTRIBUTE_FILE :
					$this->_distributeService = new PW_CacheDistribute_File ();
					break;
				default :
					$this->_distributeService = new PW_CacheDistribute_File ();
					break;
			}
		}
		return true;
	}
	
	function getData($filePath, $isRegister = true, $isReadOver = false) {
		$result = $this->_distributeService->getData ( $filePath, $isRegister, $isReadOver );
		if (! $isReadOver && $isRegister) {
			return $this->_registerGlobals ( $result );
		}
		return ($result) ? $result : array ();
	}
	
	function setData($filePath, $data, $isBuild = false, $method = 'rb+', $ifLock = true) {
		return $this->_distributeService->setData ( $filePath, $data, $isBuild, $method, $ifLock );
	}
	
	function dumpData($directory = null) {
		$_service = new PW_CacheDistribute_DataBase ();
		return $_service->dumpData ( $directory );
	}
	
	function deleteData($filePath) {
		return $this->_distributeService->deleteData ( $filePath );
	}
	
	function _registerGlobals($result) {
		foreach ( ( array ) $result as $k => $v ) {
			$GLOBALS [$k] = $v;
		}
		return true;
	}

	function _getDefinedVariables($filePath) {
		if (! is_file ( $filePath )) {
			return array ();
		}
		include S::escapePath ( $filePath );
		unset ( $filePath );
		unset ( $this );
		return get_defined_vars ();
	}
}

class PW_CacheDistribute_File extends PW_CacheDistribute_Base {
	
	function getData($filePath, $isRegister = true, $isReadOver = false) {
		if( $GLOBALS['db_filecache_to_memcache'] && Perf::checkMemcache() && in_array ( SCR, array ('index', 'read', 'thread' )) && ($result = $this->_getDataFromMemcache ( $filePath )) !== false){
			return $result;
		}
		$result = $isReadOver ? readover ( $filePath ) : $this->_getDefinedVariables ( $filePath );
		if( $GLOBALS['db_filecache_to_memcache'] && Perf::checkMemcache() && in_array ( SCR, array ('index', 'read', 'thread' ))){
			$this->_setDataToMemcache($filePath, $result);
		}
		return $result;
	}
	
	function setData($filePath, $data, $isBuild = false, $method = 'rb+', $ifLock = true) {
		if ($isBuild && is_array ( $data )) {
			$_tmp = '';
			foreach ( $data as $key => $value ) {
				if (! preg_match ( '/^\w+$/', $key ))
					continue;
				if (is_numeric($key)) $key = '_'.$key;
				$_tmp .= "\$" . $key . " = " . pw_var_export ( $value ) . ";\r\n";
			}
			$data = "<?php\r\n" . $_tmp . "\r\n?>";
		}
		if ($GLOBALS ['db_filecache_to_memcache'] && Perf::checkMemcache ()) {
			$this->_clearDataFromMemcache($filePath);
		}
		
		if ($GLOBALS ['db_cachefile_compress']) {
			$_packService = pwPack::getPackService ();
			$_packService->flushCacheFile ( $filePath );
		}
		
		return writeover ( $filePath, $data, $method, $ifLock );
	}
	
	function deleteData($filePath) {
		if ($GLOBALS ['db_filecache_to_memcache'] && Perf::checkMemcache ()) {
			$this->_clearDataFromMemcache($filePath);
		}
		
		if ($GLOBALS ['db_cachefile_compress']) {
			$_packService = pwPack::getPackService ();
			$_packService->flushCacheFiles ();
		}
		
		return P_unlink ( $filePath );
	}

}

class PW_CacheDistribute_DataBase extends PW_CacheDistribute_Base {
	
	var $_db = null;
	
	function PW_CacheDistribute_DataBase() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->_initConnection ();
	}
	
	function _initConnection() {
		global $db;
		if (!$db) {
			list ( $database, $dbhost, $dbuser, $dbpw, $dbname, $PW, $charset, $pconnect ) = $this->_loadConfig ();
			if (!class_exists('DB')){
				require_once S::escapePath ( R_P . "require/db_$database.php" );
			}
			$db = new DB ( $dbhost, $dbuser, $dbpw, $dbname, $PW, $charset, $pconnect );
		}
		$this->_db = $db;
	}
	
	function getData($filePath, $isRegister = true, $isReadOver = false) {
		if (Perf::checkMemcache () && ($result = $this->_getDataFromMemcache ( $filePath )) !== false) {
			return $result;
		}
		if (($result = $this->_getDataFromDataBase ( $filePath )) === false){
			$filedata = readover ( $filePath );
			$this->setData($filePath, $filedata);
			$result = $isReadOver ? $filedata : $this->_getDefinedVariables($filePath);
		}
		if (Perf::checkMemcache ()){
			$this->_setDataToMemcache($filePath, $result);
		}
		return $result;
	}
	
	function setData($filePath, $data, $isBuild = false, $method = 'rb+', $ifLock = true) {
		if (! $isBuild) {
			$data = $this->_getVariablesFromString ( $data );
		}
		
		if (!file_exists($filePath)){
			$_distribute_file = new PW_CacheDistribute_File ();
			$_distribute_file->setData($filePath, $data, $isBuild , $method , $ifLock);
		}
		if (Perf::checkMemcache ()) {
			$this->_clearDataFromMemcache($filePath);
		}
		
		$string = $this->_formatToDatabase ( $data );
		return $this->_db->query ( "REPLACE INTO " . $this->_getTableName () . "(ckey,cvalue,typeid,expire,extra) VALUES (" . S::sqlEscape ( $this->_getFileKey ( $filePath ) ) . "," . S::sqlEscape ( $string ) . "," . S::sqlEscape ( $this->_getDataType ( $data ) ) . ",0," . S::sqlEscape($this->_encodeFilePath($filePath))  . ")" );
	}
	
	function dumpData($directories = null) {
		$directories = ($directories) ? $directories : array ('/bbscache', '/forums', '/groupdb', '/style' );
		foreach ( $directories as $directory ) {
			if (!$this->_dumpData ( DISTRIBUTE_CACHE_DIR . $directory )) return false;
		}
		return true;
	}
	
	function deleteData($filePath) {
		if ($GLOBALS ['db_filecache_to_memcache'] && Perf::checkMemcache ()) {
			$this->_clearDataFromMemcache($filePath);
		}
		
		return $this->_db->query ( "REPLACE INTO " . $this->_getTableName () . "(ckey,cvalue,typeid,expire) VALUES (" . S::sqlEscape ( $this->_getFileKey ( $filePath ) ) . "," . S::sqlEscape ( '' ) . "," . S::sqlEscape ( '' ) . ",0)" );
	}
	
	function _dumpData($directory = null) {
		$directory = ($directory) ? $directory : DISTRIBUTE_CACHE_DIR;
		$files = $this->getDirectoryFiles ( $directory, 'php' );
		if (! S::isArray ( $files )) {
			return false;
		}
		foreach ( $files as $file ) {
			if (! $this->_checkFile ( $file )) {
				continue;
			} 
			if (!$this->setData ( $file, readover ( $file ) )) return false;
		}
		return true;
	}
	
	function _getDataFromDataBase($filePath) {
		$result = $this->_db->get_one ( "SELECT * FROM " . $this->_getTableName () . " WHERE ckey = " . S::sqlEscape ( $this->_getFileKey ( $filePath ) ) );
		if (! $result) {
			return false;
		}
		return ($result ['typeid'] == DISTRIBUTE_ARRAY_TYPEID) ? $this->_formatFromDataBase ( $result ['cvalue'] ) : base64_decode ( $result ['cvalue'] );
	}
	
	function _getVariablesFromString($string) {
		if (S::isArray ( $string ) || ! preg_match ( "/<\?php/", trim ( $string ) )) {
			return $string;
		}
		if (preg_match ( "/<\?php\s+(die|exit)/i", trim ( $string ) )) {
			return $string;
		}
		$string = $this->_filterString ( $string );
		$filePath = $this->_getTmpFilePath ();
		if (! ($result = writeover ( $filePath, $string, 'wb+' ))) {
			return false;
		}
		$variables = $this->_getDefinedVariables ( $filePath );
		P_unlink ( $filePath );
		return $variables;
	}
	
	function _filterString($string) {
		return $string;
	}
	
	function _getDataType($data) {
		return (S::isArray ( $data )) ? DISTRIBUTE_ARRAY_TYPEID : DISTRIBUTE_STRING_TYPEID;
	}
	
	function _formatToDatabase($data) {
		return base64_encode ( (is_array ( $data )) ? serialize ( $data ) : $data );
	}
	
	function _formatFromDataBase($data) {
		return unserialize ( base64_decode ( $data ) );
	}
	
	function _getTableName($key = null) {
		return 'pw_cache_distribute';
	}
	
	function _getTmpFilePath() {
		return D_P . 'data/tmp/tmpfile_' . rand ( 1, 10000 ) . '.php';
	}
	
	function _loadConfig() {
		include D_P . 'data/sql_config.php';
		return array ($database, $dbhost, $dbuser, $dbpw, $dbname, $PW, $charset, $pconnect );
	}
	
	function getDirectoryFiles($directoryName, $extension = "", $excludes = NULL) {
		$excludes = (! is_array ( $excludes )) ? array ($excludes ) : $excludes;
		$directory = opendir ( $directoryName );
		$expression = "/^[^#.].*" . $extension . "$/";
		$files = array ();
		while ( $entry = readdir ( $directory ) ) {
			if ($entry == "." || $entry == "..")
				continue;
			$path = $directoryName . "/" . $entry;
			if (preg_match ( $expression, $entry )) {
				$files [] = $path;
			}
			if (is_dir ( $path )) {
				if (! in_array ( $path, $excludes )) {
					$files = array_merge ( $files, $this->getDirectoryFiles ( $path, $extension, $excludes ) );
				}
			}
		}
		return $files;
	}
	
	function _checkFile($file) {
		$_blackFileName = array('admin_record.php');
		if ( in_array(basename($file), $_blackFileName) || @filesize ( $file ) > pow ( 1024, 2 ) * 2) {
			return false;
		}
		return true;
	}
}

class PW_CacheDistribute_Base {
	var $expire = 600;
	function _getDefinedVariables($filePath) {
		if (! is_file ( $filePath )) {
			return array ();
		}
		include S::escapePath ( $filePath );
		unset ( $filePath );
		unset ( $this );
		$temp = get_defined_vars ();
		if (isset($temp['php_errormsg'])) unset($temp['php_errormsg']); //fix php5.3.5 bug
		return $temp;
	}
	
	function _getFileKey($string) {
		$string = str_replace ( '\\', '/', $string );
		return md5 ( strtolower ( $string ) );
	}

	function _encodeFilePath($filePath){
		return base64_encode($filePath);
	}
	
	function _getDataFromMemcache($filePath) {
		$_cacheService = L::loadClass ( 'cacheservice', 'utility' );
		$key = $this->_getFileKey ( $filePath );
		return $_cacheService->get ( $key );
	}
	
	function _setDataToMemcache($filePath, $data) {
		$_cacheService = L::loadClass ( 'cacheservice', 'utility' );
		$key = $this->_getFileKey ( $filePath );
		//$fileData = $isReadOver ? readover ( $filePath ) : $this->_getDefinedVariables ( $filePath );
		return $_cacheService->set ( $key, $data, $this->expire);
	}
	
	function _clearDataFromMemcache($filePath) {
		$_cacheService = L::loadClass ( 'cacheservice', 'utility' );
		return $_cacheService->delete ($this->_getFileKey ( $filePath ));
	}	
}

