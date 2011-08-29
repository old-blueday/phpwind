<?php
!defined('P_W') && exit('Forbidden');

$cachetime = pwFilemtime(D_P . "data/bbscache/tagdb.php");
if (!file_exists(D_P . "data/bbscache/tagdb.php") || $timestamp - $cachetime > 3600) {
	$tagnum = max($db_tagindex, 200);
	$tagdb = array();
	$query = $db->query("SELECT * FROM pw_tags WHERE ifhot='0' ORDER BY num DESC" . S::sqlLimit($tagnum));
	while ($rs = $db->fetch_array($query)) {
		$tagdb[$rs['tagname']] = $rs['num'];
	}
	/** writeover(D_P . "data/bbscache/tagdb.php", "<?php\r\n\$tagdb=" . pw_var_export($tagdb) . ";\r\n?>"); **/
	pwCache::setData(D_P . "data/bbscache/tagdb.php", "<?php\r\n\$tagdb=" . pw_var_export($tagdb) . ";\r\n?>");
} else {
	include_once pwCache::getPath(D_P . "data/bbscache/tagdb.php");
}
foreach ($tagdb as $key => $num) {
	echo $key . ',' . $num . "\t";
}
ajax_footer();
