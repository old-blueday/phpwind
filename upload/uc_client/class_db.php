<?php
class UcDB {
	var $conn = 0;
	var $pconnect = 0;
	var $query_num = 0;
	var $charset;

	function UcDB($dbhost, $dbuser, $dbpw, $dbname, $pconnect = 0, $charset = ''){
		$this->pconnect = $pconnect;
		$this->charset  = $charset;
		$this->connect($dbhost, $dbuser, $dbpw, $dbname);
	}
	function connect($dbhost, $dbuser, $dbpw, $dbname){
		$this->conn = $this->pconnect == 0 ? @mysql_connect($dbhost, $dbuser, $dbpw, true) : @mysql_pconnect($dbhost, $dbuser, $dbpw);
		if (!$this->conn || mysql_errno($this->conn) != 0) {
		    $this->halt('Connect('.$this->pconnect.') to MySQL failed');
		}
		$serverinfo = mysql_get_server_info($this->conn);
		if ($serverinfo > '4.1' && $this->charset) {
			mysql_query("SET character_set_connection=" . $this->charset . ",character_set_results=" . $this->charset . ",character_set_client=binary", $this->conn);
		}
		if ($serverinfo > '5.0') {
			mysql_query("SET sql_mode=''", $this->conn);
		}
		if ($dbname && !@mysql_select_db($dbname, $this->conn)) {
			$this->halt('Cannot use database');
		}
	}
	function select_db($dbname){
		if (!@mysql_select_db($dbname, $this->conn)) {
			$this->halt('Cannot use database');
		}
	}
	function server_info(){
		return mysql_get_server_info($this->conn);
	}
	
	function fetch($sql, $args) {
		return $this->_query_and_fetch($this->_parse_sql($sql, $args));
	}
	function fetch_one($sql, $args) {
	    $records = $this->fetch($sql, $args);
		return $records === null ? null : $records[0];
	}
	function modify($sql, $args) {
	    return $this->_execute($this->_parse_sql($sql, $args));
	}
	function create($sql, $args) {
	    return $this->_execute($this->_parse_sql($sql, $args));
	}
	function remove($sql, $args) {
	    return $this->_execute($this->_parse_sql($sql, $args));
	}
	function _query_and_fetch($sql) {
		$result_set = mysql_query($sql, $this->conn);
		if ($result_set === false) {
		    $this->halt('select error');
		}
        $records = array();
        while (($record = mysql_fetch_assoc($result_set))) {
            $records[] = $record;
        }
		mysql_free_result($result_set);
		if ($records === array()) {
		    $records = null;
		}
        return $records;
	}
	function _parse_sql($sql, $args) {
        $begin_pos = 0;
        foreach ($args as $arg) {
            if (is_null($arg)) {
                $rep_str = 'NULL';
            } else if (is_string($arg)) {
                $rep_str = '\'' . addslashes($arg) . '\'';
            } else {
                $rep_str = $arg;
            }
            $pos_step = strlen($rep_str);
            $rep_pos  = strpos($sql, '?', $begin_pos);
            if ($rep_pos === false) {
                $this->halt("the number of args is not equal to the number of '?' in sql");
            }
            $sql = substr_replace($sql, $rep_str, $rep_pos, 1);
            $begin_pos = $rep_pos + $pos_step;
        }
        return $sql;
	}
	function _execute($sql) {
	    return mysql_query($sql, $this->conn);
	}
	
	function pw_update($sql_1, $sql_2, $sql_3){
		$rt = $this->get_one($sql_1, MYSQL_NUM);
		if (isset($rt[0])) {
			$this->update($sql_2);
		} else {
			$this->update($sql_3);
		}
	}
	function insert_id(){
		return $this->get_value('SELECT LAST_INSERT_ID()');
	}
	function get_value($sql, $result_type = MYSQL_NUM, $field = 0){
		$result_set = $this->query($sql);
		$rt =& $this->fetch_array($result_set, $result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}
	function get_one($sql, $result_type = MYSQL_ASSOC){
		$result_set = $this->query($sql,'Q');
		$rt =& $this->fetch_array($result_set, $result_type);
		return $rt;
	}
	function update($sql, $lp = 1){
		if ($GLOBALS['db_lp'] == 1 && $lp) {
			$tmpsql6 = substr($sql, 0, 6);
			if (strtoupper($tmpsql6 . 'E') == 'REPLACE') {
				$sql = 'REPLACE LOW_PRIORITY' . substr($sql, 7);
			} else {
				$sql = $tmpsql6 . ' LOW_PRIORITY' . substr($sql, 6);
			}
		}
		return $this->query($sql, 'U');
	}
	function query($sql, $method = null, $error = true){
		if ($method && function_exists('mysql_unbuffered_query')) {
			$result_set = @mysql_unbuffered_query($sql, $this->conn);
		} else {
			$result_set = @mysql_query($sql, $this->conn);
		}
		if (in_array(mysql_errno($this->conn), array(2006, 2013)) && empty($query) && $this->pconnect == 0 && !defined('QUERY')) {
			define('QUERY', true);
			@mysql_close($this->conn);
			sleep(2);
			include(D_P . 'data/sql_config.php');
			$this->connect($dbhost, $dbuser, $dbpw, $dbname);
			$result_set = $this->query($sql);
		}
		if ($method != 'U') {
			$this->query_num++;
		}
		if (!$result_set && $error) {
		    $this->halt('Query Error: ' . $sql);
		}
		return $result_set;
	}
	function fetch_array($result_set, $result_type = MYSQL_ASSOC){
		return mysql_fetch_array($result_set, $result_type);
	}
	function affected_rows(){
		return mysql_affected_rows($this->conn);
	}
	function num_rows($result_set){
		if (!is_bool($result_set)) {
			return mysql_num_rows($result_set);
		}
		return 0;
	}
	function num_fields($result_set){
		return mysql_num_fields($result_set);
	}
	function escape_string($str){
		return mysql_escape_string($str);
	}
	function free_result(){
		$void = func_get_args();
		foreach ($void as $result_set) {
			if (is_resource($result_set) && get_resource_type($result_set)==='mysql result') {
				mysql_free_result($result_set);
			}
		}
		unset($void);
	}
	function close($conn){
		return @mysql_close($conn);
	}
	function halt($msg = null){
		exit($msg);
		require_once(R_P.'require/db_mysql_error.php');
		new DB_ERROR($msg);
	}
}
?>