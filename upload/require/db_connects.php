<?php
class DB {
	var $mastdb;
	var $slavedb;
	var $mastConfig;
	var $slaveConfigs;
	var $lastjudge = 2; //1: slavedb 2: mastdb
	var $query_num = 0;

	function DB($dbhost, $dbuser, $dbpw, $dbname, $dbpre, $charset, $pconnect = 0) {
		$this->mastConfig['dbhost'] = $dbhost;
		$this->mastConfig['dbuser'] = $dbuser;
		$this->mastConfig['dbpw'] = $dbpw;
		$this->mastConfig['dbname'] = $dbname;
		$this->mastConfig['dbpre'] = $dbpre;
		$this->mastConfig['charset'] = $charset;
		$this->mastConfig['pconnect'] = $pconnect;
		$this->slaveConfigs = isset($GLOBALS['slaveConfigs']) ? $GLOBALS['slaveConfigs'] : array();
	}

	/**
	 * 返回主数据库信息
	 */
	function server_info() {
		$mastdb = $this->getMastdb();
		return $mastdb->server_info();
	}

	function pw_update($SQL_1, $SQL_2, $SQL_3) {
		$rt = $this->get_one($SQL_1);
		if ($rt) {
			$this->update($SQL_2);
		} else {
			$this->update($SQL_3);
		}
	}

	function insert_id() {
		$mastdb = $this->getMastdb();
		return $mastdb->insert_id();
	}

	function get_value($SQL, $result_type = null, $field = 0) {
		$query = $this->query($SQL);
		!$result_type && $result_type = 2;
		$rt =& $this->fetch_array($query, $result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}

	function get_one($SQL, $result_type = null) {
		$query = $this->query($SQL, 'Q');
		$rt =& $this->fetch_array($query, $result_type);
		return $rt;
	}

	function update($SQL, $lp = 1) {
		$mastdb = $this->getMastdb();
		return $mastdb->update($SQL, $lp);
	}

	function query($SQL, $method = null, $error = true) {
		$GLOBALS['db_debug'] && list($begintime, $begintime_sec) = explode(" ", microtime());
		$slavedb = $this->getSlavedb();
		$mastdb = $this->getMastdb();
		$query = $this->judgesql($SQL) == 1 ? $slavedb->query($SQL, $method, $error) : $mastdb->query($SQL, $method, $error);
		$method != 'U' && $this->query_num++;
		if ($GLOBALS['db_debug']) {
			list($endtime, $endtime_sec) = explode(" ", microtime());
			$usetime = $endtime + $endtime_sec - $begintime - $begintime_sec;
			$this->arr_query .= $SQL . "\t\ttime:" . $usetime . "\r\n\r\n";
			$this->totaltime += $usetime;
		}
		return $query;
	}

	function fetch_array($query, $result_type = null) {
		$currentDb = $this->getCurrentDb();
		if ($result_type === null) return $currentDb->fetch_array($query);
		return $currentDb->fetch_array($query, $result_type);
	}

	function affected_rows() {
		$currentDb = $this->getCurrentDb();
		return $currentDb->affected_rows();
	}

	function num_rows($query) {
		$currentDb = $this->getCurrentDb();
		return $currentDb->num_rows($query);
	}

	function num_fields($query) {
		$currentDb = $this->getCurrentDb();
		return $currentDb->num_fields($query);
	}

	function escape_string($str) {
		$currentDb = $this->getCurrentDb();
		return $currentDb->escape_string($str);
	}

	function free_result() {
		$currentDb = $this->getCurrentDb();
		$currentDb->free_result();
	}

	function close($linkid) {
		$currentDb = $this->getCurrentDb();
		return $currentDb->close($linkid);
	}

	function halt($msg = null) {
		$currentDb = $this->getCurrentDb();
		$currentDb->halt($msg);
	}

	/**
	 * 返回当前数据库连接
	 */
	function getCurrentDb() {
		if ($this->lastjudge === 1) return $this->getSlavedb();
		return $this->getMastdb();
	}

	/**
	 * 根据sql语句来判断
	 * @param string $sql
	 */
	function judgesql($sql) {
		$sql = trim(strtolower($sql));
		//判断是否为查询
		$this->lastjudge = (strpos($sql, "select") === 0 && strpos($sql, "from") !== false) ? 1 : 2;
		return $this->lastjudge;
	}

	/**
	 * 返回从服务链接
	 */
	function getSlavedb() {
		if (!$this->slavedb || !is_object($this->slavedb)) {
			if (!$this->slaveConfigs || !is_array($this->slaveConfigs) || empty($this->slaveConfigs)) return $this->getMastdb();
			$num = count($this->slaveConfigs);
			$tmp = round(rand(0, ($num - 1) * 100) / 100);
			if ($tmp < 0 || $tmp > ($num - 1)) $tmp = 0;
			$slaveConfig = $this->cookConfig($this->slaveConfigs[$tmp]);
			$this->slavedb = new DBdriver($slaveConfig['dbhost'], $slaveConfig['dbuser'], $slaveConfig['dbpw'], $slaveConfig['dbname'], $slaveConfig['dbpre'], $slaveConfig['charset']);
			$this->lastjudge = 1;
		}
		return $this->slavedb;
	}

	/**
	 * 返回主数据连接
	 * @return DBdriver
	 */
	function getMastdb() {
		if (!$this->mastdb || !is_object($this->mastdb)) {
			$this->mastdb = new DBdriver($this->mastConfig['dbhost'], $this->mastConfig['dbuser'], $this->mastConfig['dbpw'], $this->mastConfig['dbname'], $this->mastConfig['dbpre'], $this->mastConfig['charset'], $this->mastConfig['pconnect']);
			$this->lastjudge = 2;
		}
		return $this->mastdb;
	}

	function cookConfig($config) {
		$_config = array(
			'dbhost' => 'localhost',
			'dbuser' => 'root',
			'dbpw' => 'phpwind.net',
			'dbname' => 'phpwind',
			'dbpre' => 'pw_',
			'charset' => 'gbk',
			'pconnect' => '0'
		);
		return array_merge($_config, $config);
	}
}
?>