<?php
!function_exists('readover') && exit('Forbidden');

Class DB {

	var $sql = 0;
	var $dbhost;
	var $dbuser;
	var $dbpw;
	var $dbname;
	var $dbpre;
	var $charset;
	var $pconnect = 0;
	var $query_num = 0;

	var $lp;

	

	function DB($dbhost, $dbuser, $dbpw, $dbname, $dbpre, $charset, $pconnect=0) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw   = $dbpw;
		$this->dbname = $dbname;
		$this->dbpre = $dbpre;
		$this->charset = $charset;
		$this->pconnect = $pconnect;
		$this->lp =& $GLOBALS['db_lp'];
		$this->connect();
		//$this->connect($dbhost,$dbuser,$dbpw,$dbname);
	}

	function connect() {
		$this->sql = $this->pconnect==0 ? @mysql_connect($this->dbhost, $this->dbuser, $this->dbpw, true) : @mysql_pconnect($this->dbhost, $this->dbuser, $this->dbpw);
		mysql_errno($this->sql) != 0 && $this->halt('Connect('.$this->pconnect.') to MySQL failed');
		$serverinfo = mysql_get_server_info($this->sql);
		if ($serverinfo > '4.1' && $this->charset) {
			mysql_query("SET character_set_connection=" . $this->charset . ",character_set_results=" . $this->charset . ",character_set_client=binary", $this->sql);
		}
		if ($serverinfo > '5.0') {
			mysql_query("SET sql_mode=''", $this->sql);
		}
		if ($this->dbname && !@mysql_select_db($this->dbname, $this->sql)) {
			$this->halt('Cannot use database');
		}
	}

	function select_db($dbname) {
		if (!@mysql_select_db($dbname,$this->sql)) {
			$this->halt('Cannot use database');
		}
	}

	function server_info() {
		return mysql_get_server_info($this->sql);
	}

	function pw_update($SQL_1,$SQL_2,$SQL_3) {
		$rt = $this->get_one($SQL_1,MYSQL_NUM);
		if (isset($rt[0])) {
			$this->update($SQL_2);
		} else {
			$this->update($SQL_3);
		}
	}

	function insert_id() {
		return $this->get_value('SELECT LAST_INSERT_ID()');
	}

	function get_value($SQL,$result_type = MYSQL_NUM,$field=0) {
		$query = $this->query($SQL);
		$rt =& $this->fetch_array($query,$result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}

	function get_one($SQL,$result_type = MYSQL_ASSOC) {
		$query = $this->query($SQL,'Q');
		$rt =& $this->fetch_array($query,$result_type);
		return $rt;
	}

	function update($SQL,$lp = 1) {
		if ($this->lp == 1 && $lp) {
			$tmpsql6 = substr($SQL, 0, 6);
			if (strtoupper($tmpsql6.'E') == 'REPLACE') {
				$SQL = 'REPLACE LOW_PRIORITY'.substr($SQL,7);
			} else {
				$SQL = $tmpsql6.' LOW_PRIORITY'.substr($SQL,6);
			}
		}
		return $this->query($SQL,'U');
	}
	function query($SQL,$method = null,$error = true) {
		if ($this->dbpre != 'pw_') {
			$SQL = str_replace(array(' pw_','`pw_'," 'pw_"),array(" $this->dbpre","`$this->dbpre"," '$this->dbpre"), $SQL);
		}
		
		if ($method && function_exists('mysql_unbuffered_query')) {
			$query = @mysql_unbuffered_query($SQL,$this->sql);
		} else {
			$query = @mysql_query($SQL,$this->sql);
		}
		if (in_array(mysql_errno($this->sql),array(2006,2013)) && empty($query) && $this->pconnect==0 && !defined('QUERY')) {
			define('QUERY',true); @mysql_close($this->sql); sleep(2);
			//include(D_P.'data/sql_config.php');
			//$this->connect($dbhost,$dbuser,$dbpw,$dbname);
			$this->connect();
			$query = $this->query($SQL);
		}
		
		if ($method<>'U') {
			$this->query_num++;
		}
		
		!$query && $error && $this->halt('Query Error: '.$SQL);
		return $query;
	}
	function fetch_array($query, $result_type = MYSQL_ASSOC){
		return mysql_fetch_array($query,$result_type);
	}
	function affected_rows(){
		return mysql_affected_rows($this->sql);
	}
	function num_rows($query){
		if (!is_bool($query)) {
			return mysql_num_rows($query);
		}
		return 0;
	}
	function num_fields($query){
		return mysql_num_fields($query);
	}
	function escape_string($str){
		return mysql_escape_string($str);
	}
	function free_result(){
		$void = func_get_args();
		foreach ($void as $query) {
			if (is_resource($query) && get_resource_type($query)==='mysql result') {
				mysql_free_result($query);
			}
		}
		unset($void);
	}
	function close($linkid){
		return @mysql_close($linkid);
	}
	function halt($msg=null){
		require_once(R_P.'require/db_mysql_error.php');
		new DB_ERROR($msg);
	}
}
?>