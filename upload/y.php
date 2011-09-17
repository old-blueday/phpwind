<?php
define ( 'SCR', 'yi' );
/**
 * 云计算入口文件(开启社区云时代)
 * @link http://www.phpwind.com
 * @link http://www.phpwind.net/thread-htm-fid-131.html
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
define ( "COL", 1 );
require_once ('global.php');
@header ( "Content-Type:text/html; charset=utf8" );
CloudWind::yunRouter ();