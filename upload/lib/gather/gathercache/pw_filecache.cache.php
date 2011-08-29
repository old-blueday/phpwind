<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherCache_PW_FileCache_Cache extends GatherCache_Base_Cache {
	var $_prefix = 'filecache_';
	/*
	 * 获取文件缓存
	 * @param string $filePath	文件名称
	 */
	function getFileCache($filePath) {
		if (! $GLOBALS ['db_filecache_to_memcache']) {
			return $filePath;
		}
		$key = $this->_getKeyByFilePath ( $filePath );
		if (! ($result = $this->_cacheService->get ( $key )) && ($result = $this->getVarsByFilePath ( $filePath ))) {
			$this->_cacheService->set ( $key, $result );
		}
		if($result){
			foreach($result as $k=>$v){
				$GLOBALS[$k] = $v;
			}
		}
		return (! $result) ? $filePath : R_P . 'require/returns.php';
	}
	/*
	 * 删除文件缓存
	 * @param string $filePath	文件名称
	 */
	function clearFileCache($filePath) {
		$this->_cacheService->delete ( $this->_getKeyByFilePath ( $filePath ) );
	}
	/*
	 * 根椐文件路径生成键值
	 * @param string $filePath	文件名称
	 */
	function _getKeyByFilePath($filePath) {
		return $this->_prefix . md5 ( $filePath );
	}
	/*
	 * 根椐文件路径获取文件内的变量
	 * @param string 		$filePath	文件名称
	 */
	function getVarsByFilePath($filePath) {
		include S::escapePath($filePath);
		unset ( $filePath );
		return get_defined_vars ();
	}
}