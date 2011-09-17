<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Core_Factory {
	var $_service = array ();
	
	function getAesService() {
		if (! $this->_service ['aesService']) {
			require_once CLOUDWIND . '/client/core/public/core.aes.class.php';
			$this->_service ['aesService'] = new CloudWind_Core_Aes ();
		}
		return $this->_service ['aesService'];
	}
	
	function getHttpClientService() {
		if (! $this->_service ['httpClientService']) {
			require_once CLOUDWIND . '/client/core/public/core.httpclient.class.php';
			$this->_service ['httpClientService'] = new CloudWind_Core_HttpClient ();
		}
		return $this->_service ['httpClientService'];
	}
	
	function getChineseService($source, $target) {
		require_once CLOUDWIND . '/client/core/public/core.chinese.class.php';
		return new CloudWind_Core_Chinese ( $source, $target );
	}

}