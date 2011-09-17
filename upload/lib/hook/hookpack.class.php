<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * hook文件打包类
 * @author xiejin
 *
 */
class Pw_HookPack {
	/**
	 * 打包一个hook的所有文件
	 * @param string $name
	 * @return bool
	 */
	function packHookFile($name) {
		$packFile = $this->getPackedFile ( $name );
		$hookFiles = $this->_getHookFiles ( $name );
		if (! $hookFiles)
			return false;
		$codes = '<?php' . PHP_EOL;
		$codes .= "! defined ( 'P_W' ) && exit ( 'Forbidden' );" . PHP_EOL;
		$method = 'wb+';
		foreach ( $hookFiles as $key => $file ) {
			$codes .= $this->_importFileContent ( $file );
		}
		$this->_writeover ( $packFile, $codes, $method );
		return true;
	}
	
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
	
	function getPackedFile($name) {
		return S::escapePath ( self::_getPackDirectory () . 'hooks_' . $name.'.php' );
	}
	
	function _getPackDirectory() {
		pwCreateFolder(D_P . 'data/package');
		return D_P . 'data/package/';
	}
	
	function _getHookFiles($hook) {
		$hookPath = S::escapePath ( HOOK_PATH . $hook . '/' );
		$result = array ();
		$fp = opendir ( $hookPath );
		while ( $filename = readdir ( $fp ) ) {
			$tempFileName = strtolower($filename);
			if ($filename == '..' || $filename == '.' || strpos ( $filename, '.' ) === 0 || !strpos ( $tempFileName, 'item' ) || !strpos ( $tempFileName, '.php' ))
				continue;
			$temp = S::escapePath ( $hookPath . $filename );
			$result [] = $temp;
		}
		closedir ( $fp );
		return $result;
	}
	
	function _readover($fileName, $method = 'rb') {
		return readover ( $fileName, $method );
	}
	
	function _writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
		return writeover ( $fileName, $data, $method, $ifLock, $ifCheckPath, $ifChmod );
	}
}