<?php
!defined('M_P') && exit('Forbidden');
$USCR = 'square_weibo';

pwCache::getData(D_P.'data/bbscache/o_config.php');

$o_sitename = $o_sitename ? $o_sitename : $db_bbsname;
if (!$o_browseopen) {
	ObHeader('u.php');
} else {
	ObHeader("mode.php?m=o");
}