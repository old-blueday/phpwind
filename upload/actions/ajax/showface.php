<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('page','from'));
(!is_numeric($page) || $page < 1) && $page = 1;
$page > 1 && $pre_page = $page - 1;
$db_perpage = $from == 'reg' ? 10 : 12;
$img = @opendir("$imgdir/face");
$num = $pagenum = 0;
while ($imgname = @readdir($img)) {
	if ($imgname != "." && $imgname != ".." && $imgname != "" && eregi("\.(gif|jpg|png|bmp)$", $imgname)) {
		$num++;
		if ($num > ($page - 1) * $db_perpage && $num <= $page * $db_perpage) {
			$pagenum++;
			$imgname_array[] = $imgname;
		}
	}
}
$num > $db_perpage*$page && $next_page = $page + 1;
@closedir($img);
require_once PrintEot('ajax');
ajax_footer();
