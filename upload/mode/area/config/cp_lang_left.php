<?php
!defined('P_W') && exit('Forbidden');
if (isset($db_modes['area'])) {
	$nav_left['area'] = array('name' => '门户模式', 
		'items' => array(
			/*'areaCore' => array('name' => '门户核心设置', 
				'items' => array('area_channel_manage', 'area_module', 'area_selecttpl', 'area_level_manage', 
					'area_static_manage')),*/
			'area_channel_manage',
			'area_module',
			//'area_selecttpl',
			'area_level_manage',
			'area_static_manage',
			'area_pushdata'
		)
	);
}
?>