<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);

S::gp(array('category', 'title'));


$title = trim($title);
$category = intval($category);

$new_stopic_id = $stopic_service->addSTopic(array("title"=>$title, "category_id"=>$category));

echo $new_stopic_id ? "success\t".$new_stopic_id : 'error';

ajax_footer();

?>