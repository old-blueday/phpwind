<?php

class appmodel {

	var $base;
	var $db;

	function __construct(&$base) {
		$this->appmodel($base);
	}

	function appmodel(&$base) {
		$this->base =& $base;
		$this->db =& $base->db;
	}

	function applist($appid = null) {
		static $list = null;
		if (!isset($list)) {
			$list = array();
			$query = $this->db->query("SELECT * FROM pw_ucapp");
			while ($rt = $this->db->fetch_array($query)) {
				$list[$rt['id']] = $rt;
			}
		}
		return isset($appid) ? $list[$appid] : $list;
	}

	function isUc($appid) {
		$app = $this->applist($appid);
		return ($app['uc'] == 1);
	}

	function checkColumns() {
		$apps = array();
		$ucid = 0;
		foreach ($this->applist() as $key => $app) {
			if (!$app['uc']) {
				$apps[] = $key;
			} else {
				$ucid = $key;
			}
		}
		$this->alterTable('pw_ucsyncredit', $apps);
		$this->alterTable('pw_ucnotify', $apps, $ucid);
	}

	function alterTable($table, $apps, $ucid = null) {
		if ($ucid) {
			$apps[] = $ucid;
		}
		$col = array();
		$query = $this->db->query("SHOW COLUMNS FROM $table LIKE 'app%'");
		while ($rt = $this->db->fetch_array($query)) {
			$col[] = substr($rt['Field'], 3);
		}
		if ($addcol = array_diff($apps, $col)) {
			$sql = '';
			foreach ($addcol as $v) {
				$sql .= "ADD app{$v} TINYINT(1) NOT NULL,";
			}
			$this->db->query("ALTER TABLE $table " . rtrim($sql, ','));
		}
		if ($delcol = array_diff($col, $apps)) {
			$sql = '';
			foreach ($delcol as $v) {
				$sql .= "DROP app{$v},";
			}
			$this->db->query("ALTER TABLE $table " . rtrim($sql, ','));
		}
	}

	function post_params($apikey, $mode, $method, $args = array()) {
		$url = '';
		$params = array();
		$params['mode'] = $mode;
		$params['method'] = $method;
		$params['format'] = 'PHP';
		$params['charset'] = 'gbk';
		$params['type'] = 'uc';
		$params['v'] = '1.0';
		$params['params'] = $args ? serialize($args) : '';

		ksort($params);
		$str = '';
		foreach ($params as $k => $v) {
			if ($v) {
				$str .= $k . '=' . $v . '&';
				$url .= $k . '=' . urlencode($v) . '&';
			}
		}
		$url .= 'sig=' . md5($str . $apikey);
		return $url;
	}

	function post_url($site, $interface) {
		!$interface && $interface = 'pw_api.php';
		return rtrim($site, '/') . "/{$interface}?";
	}

	function urlformat($url, $interface, $apikey, $mode, $method, $args = array()) {
		return $this->post_url($url, $interface) . $this->post_params($apikey, $mode, $method, $args);
	}

	function ucfopen($url, $interface, $apikey, $mode, $method, $args = array(), $limit = 5) {
		$url = $this->post_url($url, $interface);
		$parse = @parse_url($url);
		if (empty($parse)) return false;
		if (!$parse['port']) {
			$parse['port'] = '80';
		}
		$parse['host'] = str_replace(array('http://','https://'), array('','ssl://'), "$parse[scheme]://").$parse['host'];
		if (!$fp = @fsockopen($parse['host'],$parse['port'],$errnum,$errstr,$limit)) {
			return array('errCode' => -1, 'errMessage' => 'connect fail');
			//return false;
		}
		$gp = 'GET';
		$parse['path'] = str_replace(array('\\','//'),'/',$parse['path'])."?$parse[query]";
		$wlength = $wdata = '';
		if ($data = $this->post_params($apikey, $mode, $method, $args)) {
			$gp	 = 'POST';
			$wlength = "Content-length: ".strlen($data)."\r\n";
			$wdata	 = $data;
		}
		$write = "$gp $parse[path] HTTP/1.0\r\nHost: $parse[host]\r\nContent-type: application/x-www-form-urlencoded\r\n{$wlength}Connection: close\r\n\r\n$wdata";
		@fwrite($fp,$write);
		while ($data = @fread($fp, 4096)) {
			$responseText .= $data;
		}
		@fclose($fp);
		$responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
		if ($responseText && is_array($responseText = unserialize($responseText))) {
			return $responseText;
		} else {
			return array('errCode' => -1, 'errMessage' => 'connect fail');
		}
	}
}
?>