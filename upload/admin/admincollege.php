<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('admintype'));
!$admintype && $admintype = 'products';
$basename="$admin_file?adminjob=admincollege&admintype=$admintype";
require_once (R_P . 'require/posthost.php');
$code1 = "HKLUOIU(^D^)_DI)_)";
$code2 = "@6219301&^%$#(+_&%))";
$data = PostHost("http://nt.phpwind.net/stat.php?action=visist&site=$db_bbsurl&verify=".md5($code1.'download'.$code2), "POST");
if($admintype == 'products'){
	ObHeader('http://www.phpwind.net/daxue/index.php?a=product');
}elseif($admintype == 'stylesource'){
	ObHeader('http://www.phpwind.net/daxue/index.php?a=style');
}else{
	ObHeader('http://www.phpwind.net/daxue/index.php?a=hack');
}
?>