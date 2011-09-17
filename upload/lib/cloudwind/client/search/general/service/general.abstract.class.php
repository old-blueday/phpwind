<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
define ( 'YUN_ROW_SEPARATOR', "\x1F\n" );
define ( 'YUN_SEGMENT_SEPARATOR', "\x1E\n" );
define ( 'YUN_COMMAND_ADD', 'add' );
define ( 'YUN_COMMAND_UPDATE', 'update' );
define ( 'YUN_COMMAND_DELETE', 'delete' );
class CloudWind_General_Abstract {
	var $_perpage = 400;
	var $_period = 3600;
	
	function CloudWind_General_Abstract() {
		$this->_toolsService = $this->getToolsService ();
		$this->_bbsUrl = $this->getSiteUrl ();
	}
	
	function getVersionId() {
		return CloudWind_getConfig ( 'g_timestamp' );
	}
	
	function getVersionInfo($tableName) {
		return '';
	}
	
	function getSiteUrl() {
		return (CloudWind_getConfig ( 'g_bbsurl' )) ? CloudWind_getConfig ( 'g_bbsurl' ) : 'http://' . $_SERVER ['HTTP_HOST'];
	}
	
	function getToolsService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND . '/client/search/general/service/general.tools.class.php';
			$service = new CloudWind_General_Tools ();
		}
		return $service;
	}
	function _getLogsDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$factory = new CloudWind_Dao_Factory ();
			$dao = $factory->getSearchLogsDao ();
		}
		return $dao;
	}
	
	function getGeneralFormatService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND . '/client/search/general/service/general.format.class.php';
			$service = new CloudWind_General_Format ();
		}
		return $service;
	}
	
	function getGeneralLogsService() {
		static $service = null;
		if (! $service) {
			require_once CLOUDWIND . '/client/search/general/service/general.logs.class.php';
			$service = new CloudWind_General_Logs ();
		}
		return $service;
	}
	
	function _markIndex($tablename, $starttime, $endtime) {
		$logsService = $this->getGeneralLogsService ();
		return $logsService->deleteLogsSegment ( $tablename, $starttime, $endtime );
	}

}