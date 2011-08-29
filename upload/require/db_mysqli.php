<?php
!function_exists('readover') && exit('Forbidden');
require_once R_P . 'require/db_connects.php';
class DBdriver {
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

	function DBdriver($dbhost, $dbuser, $dbpw, $dbname, $dbpre, $charset, $pconnect = 0) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		$this->dbpre = $dbpre;
		$this->charset = $charset;
		$this->pconnect = $pconnect;
		$this->lp = & $GLOBALS['db_lp'];
		$this->connect();
	}

	function connect() {
		list($dbhost, $dbport) = explode(':', $this->dbhost);
		!$dbport && $dbport = 3306;
		$this->sql = mysqli_init();
		mysqli_real_connect($this->sql, $dbhost, $this->dbuser, $this->dbpw, false, $dbport);
		mysqli_errno($this->sql) != 0 && $this->halt('Connect(' . $this->pconnect . ') to MySQL failed');
		$serverinfo = mysqli_get_server_info($this->sql);
		if ($serverinfo > '4.1' && $this->charset) {
			mysqli_query($this->sql, "SET character_set_connection=" . $this->charset . ",character_set_results=" . $this->charset . ",character_set_client=binary");
		}
		if ($serverinfo > '5.0') {
			mysqli_query($this->sql, "SET sql_mode=''");
		}
		if ($this->dbname && !@mysqli_select_db($this->sql, $this->dbname)) {
			$this->halt('Cannot use database');
		}
	}

	function select_db($dbname) {
		if (!@mysqli_select_db($this->sql, $dbname)) {
			$this->halt('Cannot use database');
		}
	}

	function server_info() {
		return mysqli_get_server_info($this->sql);
	}

	function pw_update($SQL_1, $SQL_2, $SQL_3) {
		$rt = $this->get_one($SQL_1, MYSQLI_NUM);
		if (isset($rt[0])) {
			$this->update($SQL_2);
		} else {
			$this->update($SQL_3);
		}
	}

	function insert_id() {
		return mysqli_insert_id($this->sql);
	}

	function get_value($SQL, $result_type = MYSQLI_NUM, $field = 0) {
		$query = $this->query($SQL);
		$rt =& $this->fetch_array($query, $result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}

	function get_one($SQL, $result_type = MYSQLI_ASSOC) {
		$query = $this->query($SQL, 'Q');
		$rt =& $this->fetch_array($query, $result_type);
		return $rt;
	}

	function update($SQL, $lp = 1) {
		if ($this->lp == 1 && $lp) {
			$tmpsql6 = substr($SQL, 0, 6);
			$SQL = strtoupper($tmpsql6 . 'E') == 'REPLACE' ? 'REPLACE LOW_PRIORITY' . substr($SQL, 7) : $tmpsql6 . ' LOW_PRIORITY' . substr($SQL, 6);
		}
		return $this->query($SQL, 'U');
	}

	function query($SQL, $method = null, $error = true) {
		$originalSQL = $SQL;
		if ($this->dbpre != 'pw_') {
			$SQL = str_replace(array(' pw_', '`pw_', " 'pw_"), array(" $this->dbpre", "`$this->dbpre", " '$this->dbpre"), $SQL);
		}
		$query = @mysqli_query($this->sql, $SQL, ($method ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT));
		if (in_array(mysqli_errno($this->sql), array(2006, 2013)) && empty($query) && !defined('QUERY')) {
			define('QUERY', true);
			@mysqli_close($this->sql);
			sleep(2);
			$this->connect();
			$query = $this->query($SQL);
		}
		$method != 'U' && $this->query_num++;
		!$query && $error && $this->halt('Query Error: ' . $SQL);
		//sql hook start
		require_once R_P."lib/cloudwind/yunhook.php";
		yun_hook_sqlhook($originalSQL);
		//sql hook end
		return $query;
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		return mysqli_fetch_array($query, $result_type);
	}

	function affected_rows() {
		return mysqli_affected_rows($this->sql);
	}

	function num_rows($query) {
		return !is_bool($query) ? @mysqli_num_rows($query) : 0;
	}

	function num_fields($query) {
		return mysqli_num_fields($query);
	}

	function escape_string($str) {
		return mysqli_real_escape_string($this->sql, $str);
	}

	function free_result() {
		$void = func_get_args();
		foreach ($void as $query) {
			if ($query instanceof mysqli_result) {
				mysqli_free_result($query);
			}
		}
	}

	function close($linkid) {
		return @mysqli_close($linkid);
	}

	function halt($msg = null) {
		require_once (R_P . 'require/db_mysqli_error.php');
		new DB_ERROR($msg);
	}
}
?>