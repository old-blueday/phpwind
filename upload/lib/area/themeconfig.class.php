<?php
!defined ('P_W') && exit('Forbidden');
class PW_ThemeConfig{
	var $config;
	function PW_ThemeConfig () {
		//$this->init();
	}

	function getThemes() {
		$tplPath = $this->config['dir'];
		$temp = array();
		if ($fp1 = opendir($tplPath)) {
			while ($tpldir = readdir($fp1)) {
				if (strpos($tpldir,'.')!==false) continue;
				$ifconfig = $this->getThemeConfigFile($tpldir) ? 1 : 0;
				$temp[] = array('dir'=>$tpldir,'ifconfig'=>$ifconfig);
			}
		}
		return $temp;
	}

	function getPages($theme) {
		if (strpos($theme,'.')!==false) return array();
		$tplPath = $this->config['dir'].'/'.$theme;
		$temp = array();
		if ($fp1 = opendir($tplPath)) {
			while ($tpldir = readdir($fp1)) {
				if (strpos($tpldir,'.htm')===false || in_array($tpldir,array('header.htm','footer.htm'))) continue;
				$temp[] = $tplPath.'/'.$tpldir;
			}
		}
		return $temp;
	}

	function getThemeConfigFile($theme) {
		$filedir = S::escapePath($this->config['dir'].'/'.$theme.'/'.$this->config['configfile']);
		if (file_exists($filedir)) {
			return $filedir;
		}
		return false;
	}

	function init($mode='area') {
		$this->config = array(
			'dir'=>R_P.'mode/'.$mode.'/themes',
			'configfile'=>'config.php',
		);
	}
}
?>