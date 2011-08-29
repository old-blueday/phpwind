<?php
/**
 * 云控制服务类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
defined('P_W') || exit('Forbidden');
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class Yun_Control extends PW_YunBase {
	function ipControl() {
		$setting = $this->getYunSetting ();
		if ($setting ['ipcontrol'] == 100) {
			return true;
		}
		if (! ($GLOBALS ['onlineip'])) {
			return false;
		}
		list ( $ip1, $ip2, $ip3 ) = explode ( ".", $GLOBALS ['onlineip'] );
		if (! in_array ( $ip1 . "." . $ip2 . "." . $ip3 . ".x", $this->getIpLists ( $setting ['iplist'] ) )) {
			return false;
		}
		return true;
	}
	function getIpLists($iplists) {
		$iplists = ($iplists) ? explode ( "|", $iplists ) : array ();
		return array_merge ( array ('110.75.164.x', '110.75.168.x', '110.75.171.x', '110.75.172.x', '110.75.173.x', '110.75.174.x', '110.75.175.x', '110.75.176.x', '110.75.167.x' ), $iplists );
	}
}