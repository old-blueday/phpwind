<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索数据表安装服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-3-25
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class PW_YunInstall {
	var $db = null;
	
	function PW_YunInstall() {
		$this->__construct ();
	}
	
	function __construct() {
		$this->db = $GLOBALS ['db'];
	}
	
	function installTables() {
		$logTables = $this->_initLogTables ();
		foreach ( $logTables as $tableName ) {
			if (! $this->_checkTable ( $tableName )) {
				$this->db->query ( "DROP TABLE IF EXISTS {$tableName};" );
				$this->db->query ( $this->_getLogTableName ( $tableName ) );
			}
		}
		$tableNames = $this->_getTableNames ();
		foreach ( $tableNames as $tableName => $structure ) {
			if (! $this->_checkTable ( $tableName )) {
				$this->db->query ( "DROP TABLE IF EXISTS {$tableName};" );
				$this->db->query ( $structure );
			}
		}
		$this->_clearSetting ();
		return true;
	}
	
	function clearTables() {
		$logTables = $this->_initLogTables ();
		foreach ( $logTables as $tableName ) {
			$this->db->query ( "TRUNCATE TABLE {$tableName};" );
		}
		$tableNames = $this->_getTableNames ();
		foreach ( $tableNames as $tableName => $structure ) {
			$this->db->query ( "TRUNCATE TABLE {$tableName};" );
		}
		$this->_clearSetting ();
		return true;
	}
	
	function _checkTable($tableName) {
		if (! $tableName) {
			return false;
		}
		$result = $this->db->get_one ( "show tables like '" . $tableName . "'" );
		return ($result) ? true : false;
	}
	
	function _initLogTables() {
		return array ('pw_log_threads', 'pw_log_forums', 'pw_log_colonys', 'pw_log_diary', 'pw_log_posts', 'pw_log_members', 'pw_log_attachs', 'pw_log_weibos' );
	}
	
	function _getLogTableName($tableName) {
		if (! $tableName)
			return "";
		$structure = "";
		$structure .= "CREATE TABLE {$tableName}(	";
		$structure .= "id int(10) unsigned not null auto_increment,	";
		$structure .= "sid int(10) unsigned not null default '0',	";
		$structure .= "operate tinyint(3) not null default '1',	";
		$structure .= "modified_time int(10) unsigned not null default '0',	";
		$structure .= "primary key(id),	";
		$structure .= "unique key idx_sid_operate(sid,operate)	";
		$structure .= ")ENGINE=MyISAM; ";
		return $structure;
	}
	
	function _getTableNames() {
		return array ('pw_log_setting' => "CREATE TABLE pw_log_setting(
								    id int(10) unsigned not null auto_increment,
								    vector varchar(255) not null default '',
								    cipher varchar(255) not null default '',
								    field1 varchar(255) not null default '',
								    field2 varchar(255) not null default '',
								    field3 int(10) unsigned not null default '0',
								    field4 int(10) unsigned not null default '0',
								    primary key(id)
								)ENGINE=MyISAM;" );
	}
	
	function _clearSetting() {
		$verifyService = $this->_getVerifySettingService ();
		return $verifyService->clearSettingCache ();
	}
	
	function _getVerifySettingService() {
		require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
		$factory = new PW_YunExtendFactory ();
		return $factory->getVerifySettingService ();
	}

}