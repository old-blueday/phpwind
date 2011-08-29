<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 云服务申请
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-3-25
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class Yun_Apply {
	
	function apply($siteurl, $sitename, $bossname, $bossphone, $marksite) {
		if (! $siteurl || ! $sitename || ! $marksite) {
			return false;
		}
		return $this->_sendPost ( array ('siteurl' => $siteurl, 'sitename' => $sitename, 'charset' => $GLOBALS ['db_charset'], 'bossname' => $bossname, 'bossphone' => $bossphone, 'marksite' => $marksite ) );
	}
	function _sendPost($data) {
		require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
		$factory = new PW_YunExtendFactory ();
		$httpClientService = $factory->getHttpClientService ();
		return $httpClientService->post ( "http://" . trim ( $this->getYunHost (), "/" ) . "/index.php?c=apply&a=apply", $data, 5 );
	}
	function getYunHost() {
		require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
		$factory = new PW_YunExtendFactory ();
		$settingService = $factory->getYunSettingService ();
		return $settingService->getSearchHost ();
	}
}