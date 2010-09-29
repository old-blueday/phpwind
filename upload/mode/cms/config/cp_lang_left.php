<?php
!defined('P_W') && exit('Forbidden');
if (isset($db_modes['area'])) {
	$nav_left['cms'] = array('name' => '文章模式', 
		'items' => array(
				'cms_article',
				'cms_column',
				'cms_purview',
		)
	);
}
?>