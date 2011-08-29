<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索抽象类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
define ( 'YUN_ROW_SEPARATOR', "\x1F\n" );
define ( 'YUN_SEGMENT_SEPARATOR', "\x1E\n" );
define ( 'YUN_COMMAND_ADD', 'add' );
define ( 'YUN_COMMAND_UPDATE', 'update' );
define ( 'YUN_COMMAND_DELETE', 'delete' );
class YUN_Abstract {
	var $_conditions = array ('keyword' );
	var $_arrayResult = array ('total', 'match' );
	var $_indexes = array ('index' );
	var $_services = array ();
	var $_perpage = 400;
	var $_period = 3600;
	function getVersionId() {
		return ($GLOBALS ['timestamp']) ? $GLOBALS ['timestamp'] : time ();
	}
	function getArrayResult($conditions) {
	}
	function createIndex($conditions) {
	}
	function alterIndex($conditions) {
	}
	
	function getVersionInfo($tableName) {
		return '';
	}
	function getToolsService() {
		if (! is_object ( $this->_services ['tools'] )) {
			require_once R_P . 'lib/cloudwind/search/yuntools.class.php';
			$this->_services ['tools'] = new YUN_Tools ();
		}
		return $this->_services ['tools'];
	}
	function getConfigsService() {
		if (! is_object ( $this->_services ['configs'] )) {
			require_once R_P . 'lib/cloudwind/search/yunconfigs.class.php';
			$this->_services ['configs'] = new YUN_Configs ();
		}
		return $this->_services ['configs'];
	}
	function _getLogsDao() {
		static $sLogsDao;
		if (! $sLogsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_logsdb.class.php';
			$sLogsDao = new PW_YUN_LogsDB ();
		}
		return $sLogsDao;
	}
	function getSearchConfig($key) {
		$path = $this->_getSearchConfigPath ();
		if (! is_file ( $path ) || ! ($config = include ($path))) {
			$config = $this->_getSearchDefaultConfigs ();
		}
		return (isset ( $config [$key] )) ? $config [$key] : 0;
	}
	function setSearchConfig($key, $value) {
		$default = $this->_getSearchDefaultConfigs ();
		if (! in_array ( $key, array_keys ( $default ) )) {
			return false;
		}
		$path = $this->_getSearchConfigPath ();
		if (! is_file ( $path ) || ! ($config = include ($path))) {
			$config = $default;
		}
		$config [$key] = strip_tags ( trim ( $value ) );
		$output = "<?php\r\nreturn " . pw_var_export ( $config ) . ";\r\n?>";
		writeover ( $path, $output, 'w' );
		return true;
	}
	function _initSearchConfig() {
	
	}
	function _getSearchDefaultConfigs() {
		return array ('thread_period' => 86400, 'thread_lastlog' => 0, 'diary_lastlog' => 0, 'weibo_lastlog' => 0, 'post_lastlog' => 0 );
	}
	function _getSearchConfigPath() {
		return D_P . 'data/bbscache/yunsearchconfig.php';
	}
}