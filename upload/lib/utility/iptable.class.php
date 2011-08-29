<?php
!defined('P_W') && exit('Forbidden');

/**
 * ip地址查询
 *
 * @package IP
 */
class PW_IPTable {
	var $bsize;
	var $dirName;
	var $indexFile;

	/**
	 * @param $dirName
	 * @return unknown_type
	 */
	function PW_IPTable() {
		$this->bsize = 1024;
		$this->dirName = R_P . 'ipdata';
		$this->indexFile = R_P . 'ipdata/ipindex.dat';
	}

	/**
	 * @return the $indexFile
	 */
	function getIndexFile() {
		return $this->indexFile;
	}

	/**
	 * @param $ip
	 * @return unknown_type
	 */
	function getIpFrom($ip) {
		$unknowIp = "Unknown";
		if ($ip == $unknowIp || !$this->_isCorrectIpAddress($ip)) return $unknowIp;
		$d_ip = explode('.', $ip);
		$txt = $this->dirName . '/' . $d_ip[0] . '.txt';
		$tag_1 = $d_ip[0];
		$tag_2 = $d_ip[1];
		if (!file_exists($txt)) {
			$tag_1 = 0;
			$tag_2 = $d_ip[0];
			$txt = $this->dirName . '/' . '0.txt';
		} else {
			$d_ip[0] = $d_ip[1];
			$d_ip[1] = $d_ip[2];
			$d_ip[2] = $d_ip[3];
			$d_ip[3] = '';
		}
		$ipIndex = $this->_getIPIndex($tag_1, $tag_2);
		if (empty($ipIndex)) {
			return $unknowIp;
		} elseif ($ipIndex[0] == -1) {
			$offset = 0;
			$offsize = filesize($txt);
		} else {
			$offset = $ipIndex[0];
			$offsize = $ipIndex[1] - $ipIndex[0];
		}
		if ($offsize < 1) return $unknowIp;
		if ($handle = @fopen($txt, 'rb')) {
			flock($handle, LOCK_SH);
			fseek($handle, $offset, SEEK_SET);
			$d = "\n" . fread($handle, $offsize);
			$d .= fgets($handle, 100);
			$wholeIP = $d_ip[0] . '.' . $d_ip[1] . '.' . $d_ip[2];
			$d_ip[3] && $wholeIP .= '.' . $d_ip[3];
			$wholeIP = str_replace('255', '*', $wholeIP);
			$f = $l_d = 0;
			if (($s = strpos($d, "\n$wholeIP\t")) !== false) {
				$s = $s + $offset;
				fseek($handle, $s, SEEK_SET);
				$l_d = substr(fgets($handle, 100), 0, -1);
				$ip_a = explode("\t", $l_d);
				$ip_a[3] && $ip_a[2] .= ' ' . $ip_a[3];
				fclose($handle);
				return $ip_a[2];
			}
			$ip = $this->_d_ip($d_ip);
			while (!$f && !$l_d && ($wholeIP >= 0)) {
				if (($s = strpos($d, "\n" . $wholeIP . '.')) !== false) {
					$s = $s + $offset;
					list($l_d, $f) = $this->_s_ip($handle, $s, $ip);
					if ($f) return $f;
					while ($l_d && preg_match("/^\n$wholeIP/i", "\n" . $l_d) !== false) {
						list($l_d, $f) = $this->_s_ip($handle, $s, $ip, $l_d);
						if ($f) return $f;
					}
				}
				if (strpos($wholeIP, '.') !== false) {
					$wholeIP = substr($wholeIP, 0, strrpos(substr($wholeIP, 0, -1), '.'));
				} else {
					if ($txt == '0.txt') return $unknowIp;
					$wholeIP--;
				}
			}
		}
		return $unknowIp;
	}

	/**
	 * @param $ip_1
	 * @param $ip_2
	 * @return unknown_type
	 */
	function _getIPIndex($ip_1, $ip_2) {
		$index = array();
		if ($handle = @fopen($this->indexFile, 'rb')) {
			$offset = ($ip_1 * $this->bsize) + ($ip_2 * 4);
			fseek($handle, $offset, SEEK_SET);
			if (!feof($handle)) {
				$c1 = unpack('Nkey', fread($handle, 4));
				$c1 = $c1['key'];
				$c2 = 0;
				while (!feof($handle) && $c2 == 0) {
					$c2 = unpack('Nkey', fread($handle, 4));
					$c2 = $c2['key'];
				}
				if ($c1 != 0 && $c2 != 0) {
					$index = array($c1, $c2);
				}
			}
		} else {
			$index = array(-1, -1);
		}
		return $index;
	}

	/**
	 * @param $db
	 * @param $s
	 * @param $ip
	 * @param $l_d
	 * @return unknown_type
	 */
	function _s_ip($db, $s, $ip, $l_d = null) {
		if (empty($l_d)) {
			fseek($db, $s, SEEK_SET);
			$l_d = fgets($db, 100);
		}
		$ip_a = explode("\t", $l_d);
		$ip_a[0] = $this->_d_ip(explode('.', $ip_a[0]));
		$ip_a[1] = $this->_d_ip(explode('.', $ip_a[1]));
		if ($ip < $ip_a[0]) {
			$f = $l_d = '';
		} elseif ($ip >= $ip_a[0] && $ip <= $ip_a[1]) {
			fclose($db);
			$ip_a[3] && $ip_a[2] .= ' ' . $ip_a[3];
			$f = $ip_a[2];
			$l_d = '';
		} else {
			$f = '';
			$l_d = fgets($db, 100);
		}
		return array($l_d, $f);
	}

	/**
	 * @param $d_ip
	 * @return unknown_type
	 */
	function _d_ip($d_ip) {
		$d_ips = '';
		foreach ($d_ip as $value) {
			$d_ips .= '.' . sprintf("%03d", str_replace('*', '255', $value));
		}
		return substr($d_ips, 1);
	}

	/**
	 * public method
	 * @return unknown_type
	 */
	function createIpIndex() {
		if (is_dir($this->dirName)) {
			$dir = @opendir($this->dirName);
			while (false !== ($fileName = @readdir($dir))) {
				if ($this->_isCorrectFile($fileName)) {
					$fileArray[] = $fileName;
				}
			}
			closedir($dir);
		}
		if (!empty($fileArray)) {
			if ($handle = fopen($this->indexFile, 'wb')) {
				for ($i = 0; $i < 256; $i++) {
					fseek($handle, $i * $this->bsize, SEEK_SET);
					$fileName = $i . '.txt';
					if (in_array($fileName, $fileArray)) {
						$fcontent = $this->_getfcontent($this->dirName . '/' . $fileName);
						if ($fcontent) {
							$_addr = '';
							for ($j = 0; $j < 256; $j++) {
								if (isset($fcontent[$j])) {
									$_addr .= pack('N', $fcontent[$j]);
								} else {
									$_addr .= pack('N', 0x0);
								}
							}
							fwrite($handle, $_addr);
						}
					}
				}
				fclose($handle);
			}
		}
	}

	/**
	 * 判断是否是正确的IP地址
	 * @param string $add
	 * @return boolean
	 */
	function _isCorrectIpAddress($add) {
		return preg_match('/^((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]\d)|(\d))(\.((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]\d)|(\d))){3}$/', $add);
	}

	/**
	 * 判断是否正确的文件名称
	 * @param $fileName
	 * @return unknown_type
	 */
	function _isCorrectFile($fileName) {
		$result = true;
		if ($fileName == '.' || $fileName == '..') {
			$result = false;
		} else {
			$finfo = pathinfo($fileName);
			if ($finfo['extension'] != 'txt') {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * @param $fileName
	 * @return unknown_type
	 */
	function _getfcontent($fileName) {
		$fcontent = array();
		if ($handle = fopen($fileName, 'rb')) {
			while (!feof($handle)) {
				$position = ftell($handle);
				$line = fgets($handle, 256);
				if (trim($line)) {
					$l = explode("\t", $line);
					if (count($l) > 2) {
						$ip_1 = explode('.', $l[0]);
						$ip_2 = explode('.', $l[1]);
						$ip_1 && $ip_1 = $ip_1[0];
						$ip_2 && $ip_2 = $ip_2[0];
						if (!isset($fcontent[$ip_1])) {
							$fcontent[$ip_1] = $position;
						}
						if (!isset($fcontent[$ip_2])) {
							$fcontent[$ip_2] = $position;
						}
					}
				}
			}
			fclose($handle);
		}
		return $fcontent;
	}

}
?>