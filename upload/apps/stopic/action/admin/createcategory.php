<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);

S::gp(array('name'));

$name = trim($name);
if ('' == $name || $stopic_service->isCategoryExist($name)) {
	echo "error";
} else {
	$new_category_id = $stopic_service->addCategory(array("title"=>$name, "creator"=>$admin_name));
	echo "success\t".$new_category_id;
}
ajax_footer();

