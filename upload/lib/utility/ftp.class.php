<?php
!function_exists('readover') && exit('Forbidden');

@set_time_limit(1000);

/**
 * ftp操作对象
 *
 * @package FTP
 */
class FTP {

	var $sock;
	var $rootpath;
	var $timeout;
	var $data_connection;

	function FTP($ftp_server, $ftp_port = 21, $ftp_user, $ftp_pass, $ftp_dir = '', $ftp_timeout = 10) {
		$errno = 0;
		$errstr = '';
		$this->sock = @fsockopen($ftp_server, $ftp_port, $errno, $errstr, $ftp_timeout);
		if (!$this->sock || !$this->checkcmd()) {
			$this->showerror('ftp_connect_failed');
		}
		@stream_set_timeout($this->sock, $ftp_timeout);

		if (!$this->sendcmd('USER', $ftp_user)) {
			$this->showerror('ftp_user_failed');
		}
		if (!$this->sendcmd('PASS', $ftp_pass)) {
			$this->showerror('ftp_pass_failed');
		}
		$this->rootpath = $this->pwd();
		if ($ftp_dir) {
			$this->rootpath .= trim(str_replace('\\', '/', $ftp_dir), '/') . '/';
		}
		$this->timeout = $ftp_timeout;
		$this->chdir($this->rootpath);
		return true;
	}

	function pwd() {
		$this->sendcmd('PWD', '', false);
		if (!($path = $this->checkcmd(true)) || !preg_match("/^[0-9]{3} \"(.+?)\"/", $path, $matchs)) {
			return '/';
		}
		return $matchs[1] . ((substr($matchs[1], -1) == '/') ? '' : '/');
	}

	function checkFile($filename) {
		return (str_replace(array('..', '.php.'), '', $filename) != $filename || preg_match('/\.php$/i', $filename));
	}

	function get($localfile, $remotefile, $mode = 'I') {
		if ($this->checkFile($localfile)) {
			$this->showerror("Error：illegal file type！（{$localfile}）");
		}
		if ($this->checkFile($remotefile)) {
			$this->showerror("Error：illegal file type！（{$remotefile}）");
		}
		$mode != 'I' && $mode = 'A';
		if (!$this->sendcmd('TYPE', $mode)) {
			$this->showerror('Error：TYPE command failed');
		}
		$this->open_data_connection();
		if (!$this->sendcmd('RETR', $remotefile)) {
			$this->close_data_connection();
			return false;
		}
		if (!($fp = @fopen($localfile, 'wb'))) {
			$this->showerror("Error：Cannot read file \"$localfile\"");
		}
		while (!@feof($this->data_connection)) {
			@fwrite($fp, @fread($this->data_connection, 4096));
		}
		@fclose($fp);
		$this->close_data_connection();

		if (!$this->checkcmd()) {
			return false;
			//$this->showerror('Error：GET command failed');
		}
		return true;
	}

	function upload($localfile, $remotefile, $mode = 'I') {
		if ($this->checkFile($localfile)) {
			$this->showerror("Error：illegal file type！（{$localfile}）");
		}
		if ($this->checkFile($remotefile)) {
			$this->showerror("Error：illegal file type！（{$remotefile}）");
		}
		if ($savedir = dirname($remotefile)) {
			$this->mkdir($savedir);
		}
		$remotefile = $this->rootpath . S::escapeDir($remotefile);
		if (!($fp = @fopen($localfile, 'rb'))) {
			$this->showerror("Error：Cannot read file \"$localfile\"");
		}
		// 'I' == BINARY mode
		// 'A' == ASCII mode
		$mode != 'I' && $mode = 'A';
		$this->delete($remotefile);
		if (!$this->sendcmd('TYPE', $mode)) {
			$this->showerror('Error：TYPE command failed');
		}
		$this->open_data_connection();
		$this->sendcmd('STOR', $remotefile);
		while (!@feof($fp)) {
			@fwrite($this->data_connection, @fread($fp, 4096));
		}
		@fclose($fp);
		$this->close_data_connection();

		if (!$this->checkcmd()) {
			$this->showerror('Error：PUT command failed');
		} else {
			$this->sendcmd('SITE CHMOD', base_convert(0644, 10, 8) . " $remotefile");
		}
		return $this->size($remotefile);
	}
	function size($file) {
		$this->sendcmd('SIZE', $file, false);
		if (!($size_port = $this->checkcmd(true))) {
			$this->showerror('Error：Check SIZE command failed');
		}
		return preg_replace("/^[0-9]{3} ([0-9]+)\r\n/", "\\1", $size_port);
	}
	function delete($file) {
		return $this->sendcmd('DELE', $this->rootpath . S::escapeDir($file));
	}
	function rename($oldname, $newname) {
		if ($savedir = dirname($newname)) {
			$this->mkdir($savedir);
		}
		$oldname = $this->rootpath . S::escapeDir($oldname);
		$this->sendcmd('RNFR', $oldname);
		return $this->sendcmd('RNTO', $newname);
	}
	function file_exists($filename) {
		$directory = substr($filename, 0, strrpos($filename, '/'));
		$filename = str_replace("$directory/", '', $filename);
		if ($directory) {
			$directory = $this->rootpath . $directory . '/';
		} else {
			$directory = $this->rootpath;
		}
		$this->chdir($directory);
		$list = $this->nlist();
		$this->chdir($this->rootpath);
		if (!empty($list) && in_array($filename, $list)) {
			return true;
		}
		return false;
	}
	function nlist($dir = '') {
		$this->open_data_connection();
		$this->sendcmd('NLST', $dir);
		$list = array();
		while (!@feof($this->data_connection)) {
			$list[] = preg_replace('/[\r\n]/', '', @fgets($this->data_connection, 512));
		}
		$this->close_data_connection();
		if (!$this->checkcmd(true)) {
			$this->showerror('Error：LIST command failed');
		}
		return $list;
	}
	function close_data_connection() {
		return @fclose($this->data_connection);
	}
	function open_data_connection() {
		$this->sendcmd('PASV', '', false);
		if (!($ip_port = $this->checkcmd(true))) {
			$this->showerror('Error：Check PASV command failed');
		}
		if (!preg_match('/[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+/', $ip_port, $temp)) {
			$this->showerror("Error：Illegal ip-port format($ip_port)");
		}
		$temp = explode(',', $temp[0]);
		$server_ip = "$temp[0].$temp[1].$temp[2].$temp[3]";
		$server_port = $temp[4] * 256 + $temp[5];
		if (!$this->data_connection = @fsockopen($server_ip, $server_port, $errno, $errstr, $this->timeout)) {
			$this->showerror("Error：Cannot open data connection to $server_ip:$server_port<br />Error：$errstr ($errno)");
		}
		@stream_set_timeout($this->data_connection, $this->timeout);
		return true;
	}
	function mkdir($dir) {
		$dir = explode('/', S::escapeDir($dir));
		$dirs = '';
		$result = false;
		$base777 = base_convert(0777, 10, 8);
		for ($i = 0, $count = count($dir); $i < $count; $i++) {
			if (strpos($dir[$i], '.') === 0) {
				continue;
			}
			$result = $this->sendcmd('MKD', $dir[$i]);
			$this->sendcmd('SITE CHMOD', "$base777 $dir[$i]");
			$this->chdir($this->rootpath . $dirs . $dir[$i]);
			$dirs .= "$dir[$i]/";
		}
		$this->chdir($this->rootpath);
		return $result;
	}
	function chdir($dir) {
		$dir = (($dir[0] != '/') ? '/' : '') . $dir;
		if ($dir !== '/' && substr($dir, -1) == '/') {
			$dir = substr($dir, 0, -1);
		}
		if (!$this->sendcmd('CWD', $dir)) {
			$this->showerror('ftp_cwd_failed');
		}
		return true;
	}
	function close() {
		if (!$this->sock) {
			return false;
		}
		if (!$this->sendcmd('QUIT') || !fclose($this->sock)) {
			$this->showerror('Error：QUIT command failed', false);
		}
		return true;
	}
	function showerror($lang, $close = true) {
		$close && $this->close();
		Showmsg($lang);
	}
	function sendcmd($cmd, $args = '', $check = true) {
		!empty($args) && $cmd .= " $args";
		fputs($this->sock, "$cmd\r\n");
		if ($check === true && !$this->checkcmd()) {
			return false;
		}
		return true;
	}
	function checkcmd($return = false) {
		$resp = $rcmd = '';
		$i = 0;
		do {
			$rcmd = fgets($this->sock, 512);
			$resp .= $rcmd;
		} while (++$i < 20 && !preg_match('/^\d{3}\s/is', $rcmd));

		if (!preg_match('/^[123]/', $rcmd)) {
			return false;
		}
		return $return ? $resp : true;
	}
}
?>