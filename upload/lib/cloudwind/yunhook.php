<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * Hook 统一入口文件
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-04-12
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */

function yun_hook_sqlhook($originalSQL) {
	if ($GLOBALS ['db_yunsearch_search'] && $GLOBALS ['db_yunsearch_hook']) {
		static $hookService;
		if (! $hookService) {
			require_once R_P . 'lib/cloudwind/yunhook.class.php';
			$hookService = new PW_Yunhook ();
		}
		$hookService->sqlhook ( $originalSQL, $GLOBALS ['db'] );
		return true;
	}
}

function yun_hook_iphook() {
	require_once R_P . 'lib/cloudwind/yunextendfactory.class.php';
	$factory = new PW_YunExtendFactory ();
	$controlService = $factory->getYunControlService ();
	if (! $controlService->ipControl ()) {
		return exit ( 'IP Forbidden' );
	}
	return true;
}