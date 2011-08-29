<?php
require_once('global.php');

if ($c_htm || $db_hits_store == 2) {
	if (@filesize(D_P.'data/bbscache/hits.txt')<5120) {
		$lastupdate = $_COOKIE['lastupdate'];
		$onbbstime = $timestamp-$lastupdate;
		setCookie('lastupdate',$timestamp,0);

		if ($lastupdate && $onbbstime<=10) {
			setCookie('lastupdate','',0);
		} elseif (strlen($tid)<9 && is_numeric($tid)) {
			$handle=fopen(D_P."data/bbscache/hits.txt",'ab');
			flock($handle,LOCK_EX);
			fwrite($handle,$tid."\t");
			fclose($handle);
		}
	} else {
		@unlink(D_P.'data/bbscache/hits.txt');
	}
}
?>