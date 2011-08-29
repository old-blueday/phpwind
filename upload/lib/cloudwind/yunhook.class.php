<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云搜索钩子服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class PW_Yunhook {
	function sqlhook($sql, $db) {
		if (! $sql || ! $db) {
			return false;
		}
		require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
		$factory = new PW_YunExtendFactory ();
		$factory->getAggregateService ()->collectSQL ( $sql, $db );
		return true;
	}
}