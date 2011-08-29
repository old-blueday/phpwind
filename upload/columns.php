<?php
define('COL',1);
require_once('global.php');

$url = ($pwServer['HTTP_REFERER'] && strpos($pwServer['HTTP_REFERER'],$db_adminfile)===false && strpos($pwServer['HTTP_REFERER'],$db_bbsurl)!==false) ? $pwServer['HTTP_REFERER'] : $db_bfn;
if ($_GET['m'] == 'bbs') {
	$url = $db_bbsurl.'/index.php?m=bbs';
}
bbsSeoSettings('index');
if ($db_columns) {
	if ($_GET['action'] == 'columns') {
		extract(L::style());
		Cookie('columns',2);
		require_once PrintEot('columns');exit;
	} else {
		Cookie('columns','1');
		echo "<script type=\"text/javascript\">top.location.href=\"".$url."\"</script>";
		exit;
	}
} else {
	ObHeader('index.php');
}
?>