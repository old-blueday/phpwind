<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 云搜索配置类
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
class YUN_Configs {
	
	function getBBSUrl() {
		global $db_bbsurl;
		return ($db_bbsurl) ? $db_bbsurl : 'http://' . $_SERVER['HTTP_HOST'];
	}
}