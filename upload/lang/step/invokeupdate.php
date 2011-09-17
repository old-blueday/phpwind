<?php
require_once ('../../global.php');
$invokeService = L::loadClass('invokeservice', 'area');
$pageInvokeDB = L::loadDB('PageInvoke', 'area');
$count = $pageInvokeDB->searchCount(array());
$perpage = 50;
S::gp(array(
	'page'
),'',2);
!$page && $page = 1;
$maxPage = ceil($count/$perpage);

if ($page<=$maxPage) {
	$pageInvokes = $pageInvokeDB->searchPageInvokes(array(),$page,$perpage);
	foreach ($pageInvokes as $value) {
		$invokeName = $value['invokename'];
		unset($value['id'],$value['invokename']);
		$invokeService->updateInvokeByName($invokeName,$value);
	}
	$REQUEST_URI = $_SERVER['SCRIPT_URI'];
	$page++;
	$URL = $REQUEST_URI."?page=".$page;
	echo "<script>setTimeout(\"window.location='$URL'\",200)</script>";
} else {
	echo '转换完毕';
}
?>