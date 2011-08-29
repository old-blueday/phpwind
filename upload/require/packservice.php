<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/*
 * 文件打包/压缩类库
 * @author L.IuHu.I@2010/11/13 developer.liuhui@gmail.com
 */
class PW_packService {
	var $_classFiles = array ();
	var $_cacheFiles = array ();
	var $_classStatus = true;
	var $_cacheStatus = true;
	/*
	 * 打包类库文件
	 */
	function packClassFiles() {
		$_packConfigFiles = $this->_getPackConfigFiles ();
		if (! $_packConfigFiles || ! $this->_classStatus) {
			return false;
		}
		foreach ( $_packConfigFiles as $name => $files ) {
			if (! $this->_packClassFile ( $name, $files )) {
				$this->flushClassFiles ();
				return false;
			}
		}
		return true;
	}
	/*
	 * 私有打包类库存文件实现
	 */
	function _packClassFile($name, $classFiles) {
		$packClassFile = $this->_getClassFilePath ( $name );
		if (! $classFiles || ! $packClassFile) {
			return false;
		}
		list ( $classCodes, $method ) = array ('<?php' . PHP_EOL, 'wb+' );
		foreach ( $classFiles as $key => $file ) {
			$classCodes .= $this->_importFileContent ( R_P . $file );
		}
		return $this->_writeover ( $packClassFile, $classCodes, $method );
	}
	/*
	 * 获取打包类库清单
	 */
	function _getPackConfigFiles() {
		require R_P . 'require/packconfig.php';
		return ($packClassFiles) ? $packClassFiles : array ();
	}
	/*
	 * 加载缓存文件路径
	 */
	function loadCachePath($filePath) {
		if (! $this->_cacheStatus || ! $GLOBALS ['db_cachefile_compress']) {
			return $filePath;
		}
		static $_packfile = null;
		if (! $_packfile) {
			$_packfile = $this->_getFilePathByName ( 'cache' );
		}
		static $_fileCacheData = array ();
		if (! $_fileCacheData && is_file ( $_packfile )) {
			include_once $_packfile;
		}
		$key = $this->_getFileKey ( $filePath );
		if ($_fileCacheData && in_array ( $key, array_keys ( $_fileCacheData ) )) {
			$GLOBALS += $_fileCacheData [$key];
			return R_P . 'require/returns.php';
		}
		! defined ( 'PW_PACK_FILES' ) && define ( 'PW_PACK_FILES', 1 );
		$this->_cacheFiles [] = $filePath;
		return $filePath;
	}
	/*
	 * 打包缓存文件
	 */
	function packCacheFiles() {
		if (! $this->_cacheStatus || ! $this->_cacheFiles || ! $GLOBALS ['db_cachefile_compress']) {
			return false;
		}
		$list = $record = '';
		foreach ( $this->_cacheFiles as $filePath ) {
			$vars = $this->getVarsByFilePath ( $filePath );
			$list .= "\$_fileCacheData['" . $this->_getFileKey ( $filePath ) . "'] = " . $this->_createFileContent ( $vars ) . ";";
			$record .= $this->_getCacheRecordInfo ( $filePath ) . "\n";
		}
		$_packfile = $this->_getFilePathByName ( 'cache' );
		if (! is_file ( $_packfile )) {
			$this->_writeover ( $_packfile, "<?php\r\n" . $list . " ", 'wb+' );
		} else {
			$this->_writeover ( $_packfile, " " . $list . " ", 'ab+' );
		}
		$this->_writeover ( $this->_getCacheRecordFile (), $record, 'ab+' );
		return true;
	}
	
	function _getCacheRecordFile() {
		return $this->_getPackDirectory () . 'pack.record.cache.php';
	}
	
	function _getCacheRecordInfo($filePath) {
		return 'cache.' . md5 ( $filePath );
	}
	
	function getVarsByFilePath($filePath) {
		include S::escapePath ( $filePath );
		unset ( $filePath );
		return get_defined_vars ();
	}
	
	function _createFileContent($input) {
		switch (gettype ( $input )) {
			case 'string' :
				return "'" . str_replace ( array ("\\", "'" ), array ("\\\\", "\'" ), $input ) . "'";
			case 'array' :
				$output = "array(";
				foreach ( $input as $key => $value ) {
					$output .= $this->_createFileContent ( $key ) . ' => ' . $this->_createFileContent ( $value );
					$output .= ",";
				}
				$output .= ')';
				return $output;
			case 'boolean' :
				return $input ? 'true' : 'false';
			case 'NULL' :
				return 'NULL';
			case 'integer' :
			case 'double' :
			case 'float' :
				return "'" . ( string ) $input . "'";
		}
		return 'NULL';
	}
	
	function _getFileKey($string) {
		return md5 ( strtolower ( $string ) );
	}
	
	function _getUnique($name, $string) {
		return $name . '.' . md5 ( strtolower ( $string ) );
	}
	
	/**
	 * 生成打包文件的路径
	 * @param $fileName
	 */
	function _getFilePathByName($name) {
		return $this->_getPackDirectory () . 'pack.' . $name . '.' . SCR . '.php';
	}
	
	function _getClassFilePath($name) {
		return $this->_getPackDirectory () . 'pack.class.' . $name . '.php';
	}
	/**
	 * 导出文件内容
	 * @param $file
	 * @param $handler
	 */
	function _importFileContent($file) {
		if (! is_file ( $file )) {
			return false;
		}
		$code = '';
		foreach ( token_get_all ( $this->_readover ( $file ) ) as $token ) {
			if (is_string ( $token )) {
				$code .= $token;
			} else {
				switch ($token [0]) {
					case T_COMMENT :
					case T_DOC_COMMENT :
					case T_OPEN_TAG :
					case T_CLOSE_TAG :
						break;
					case T_WHITESPACE :
						$code .= ' ';
						break;
					default :
						$code .= $token [1];
				}
			}
		}
		return $code;
	}
	/**
	 * 清除全部压缩文件
	 */
	function _getPackFiles() {
		$folder = opendir ( $this->_getPackDirectory () );
		if (false === $folder) {
			return false;
		}
		$files = array ();
		while ( $file = readdir ( $folder ) ) {
			if ($file == '.' || $file == '..' || strpos ( $file, '.' ) === 0)
				continue;
			$files [] = $file;
		}
		closedir ( $folder );
		return $files;
	}
	
	function _getPackDirectory() {
		return D_P . 'data/package/';
	}
	
	function _flushFile($name) {
		P_unlink ( $this->_getFilePathByName ( $name ) );
		return true;
	}
	/*
	 * 清空类库压缩文件
	 */
	function flushClassFiles() {
		$files = $this->_getPackFiles ();
		if (! is_array ( $files )) {
			return false;
		}
		$_packDirectory = $this->_getPackDirectory ();
		foreach ( $files as $file ) {
			if (strpos ( $file, 'pack.class.' ) === 0) {
				P_unlink ( $_packDirectory . $file );
			}
		}
		return true;
	}
	/*
	 * 清空缓存压缩文件
	 */
	function flushCacheFiles() {
		$files = $this->_getPackFiles ();
		if (! is_array ( $files )) {
			return false;
		}
		$_packDirectory = $this->_getPackDirectory ();
		foreach ( $files as $file ) {
			if (strpos ( $file, 'pack.cache' ) === 0) {
				P_unlink ( $_packDirectory . $file );
			}
		}
		P_unlink ( $this->_getCacheRecordFile () );
		return true;
	}
	
	function flushCacheFile($filepath) {
		$record = $this->_readover ( $this->_getCacheRecordFile () );
		$packFiles = ($record) ? explode ( "\n", $record ) : array ();
		if (! $packFiles) {
			return false;
		}
		if (in_array ( $this->_getCacheRecordInfo ( $filepath ), $packFiles )) {
			$this->flushCacheFiles ();
		}
		return false;
	}
	
	function _readover($fileName, $method = 'rb') {
		return readover ( $fileName, $method );
	}
	
	function _writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
		return writeover ( $fileName, $data, $method, $ifLock, $ifCheckPath, $ifChmod );
	}
}
?>