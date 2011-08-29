<?php
!defined('P_W') && exit('Forbidden');

/**
 * 数据分析缓存
 *
 * @package PW_DatanalyseCache
 */
class PW_DatanalyseCache {
	var $fdir;
	var $filepath;
	var $filename;
	var $timestamp;
	function PW_DatanalyseCache() {
		global $timestamp;
		$this->fdir = D_P . "data/bbscache/";
		$this->filepath = $this->fdir . "datanalyse_cache.php";
		$this->filename = "datanalyse_cache";
		$this->timestamp = $timestamp;
	}
	
	/**
	 * @param $tag
	 * @param $result
	 * @return unknown_type
	 */
	function writeCache($result) {
		$cache 	= '';
		$cache .= "<?php\r\n";
		$cache .= "\$_result=" . var_export ( $result, TRUE ) . ";\r\n";
		$cache .= "?>\r\n";
		pwCache::setData($this->filepath,$cache);
	}
	
	/**
	 * @param $tag
	 * @param $result
	 * @return unknown_type
	 */
	function ifUpdateCache() {
		if (is_file($this->filepath) && (pwFilemtime($this->filepath) + 60 * 10) >= $this->timestamp) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * @return unknown_type
	 */
	function getResult() {
		$dcache = L::config(null, $this->filename);
		return $dcache['_result'];
	}

}
?>