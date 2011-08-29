<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索扩展工厂服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class PW_YunExtendFactory {
	var $_service = array ();
	function getAggregateService() {
		if (! $this->_service ['aggregate']) {
			require_once R_P . 'lib/cloudwind/extend/aggregate.class.php';
			$this->_service ['aggregate'] = new PW_Aggregate ();
		}
		return $this->_service ['aggregate'];
	}
	
	function getVerifySettingService() {
		if (! $this->_service ['verifysetting']) {
			require_once R_P . 'lib/cloudwind/extend/verifysetting.class.php';
			$this->_service ['verifysetting'] = new PW_VerifySetting ();
		}
		return $this->_service ['verifysetting'];
	}
	
	function getYunInstallService() {
		if (! $this->_service ['yunInstallService']) {
			require_once R_P . 'lib/cloudwind/extend/yuninstall.class.php';
			$this->_service ['yunInstallService'] = new PW_YunInstall ();
		}
		return $this->_service ['yunInstallService'];
	}
	
	function getAesService() {
		if (! $this->_service ['aesService']) {
			require_once R_P . 'lib/cloudwind/extend/aes.class.php';
			$this->_service ['aesService'] = new PW_AES ();
		}
		return $this->_service ['aesService'];
	}
	
	function getHttpClientService() {
		if (! $this->_service ['httpClientService']) {
			require_once R_P . 'lib/cloudwind/extend/httpclient.class.php';
			$this->_service ['httpClientService'] = new PW_HttpClient ();
		}
		return $this->_service ['httpClientService'];
	}
	
	function getYunCheckServerService() {
		if (! $this->_service ['YunCheckServerService']) {
			require_once R_P . 'lib/cloudwind/extend/yuncheckserver.class.php';
			$this->_service ['YunCheckServerService'] = new Yun_CheckServer ();
		}
		return $this->_service ['YunCheckServerService'];
	}
	
	function getYunApplyService() {
		if (! $this->_service ['YunApplyService']) {
			require_once R_P . 'lib/cloudwind/extend/yunapply.class.php';
			$this->_service ['YunApplyService'] = new Yun_Apply ();
		}
		return $this->_service ['YunApplyService'];
	}
	
	function getChineseService($source, $target) {
		require_once R_P . 'lib/cloudwind/extend/chinese.class.php';
		return new Chinese ( $source, $target );
	}
	
	function getYunSettingService() {
		if (! $this->_service ['YunSettingService']) {
			require_once R_P . 'lib/cloudwind/extend/yunsetting.class.php';
			$this->_service ['YunSettingService'] = new PW_YUNSetting ();
		}
		return $this->_service ['YunSettingService'];
	}
	function getYunControlService() {
		if (! $this->_service ['YunControlService']) {
			require_once R_P . 'lib/cloudwind/extend/yuncontrol.class.php';
			$this->_service ['YunControlService'] = new Yun_Control ();
		}
		return $this->_service ['YunControlService'];
	}
}