<?php
!defined('P_W') && exit('Forbidden');
return array(
	'title' => '文章权限',
	'field' => array(
		'cms_post' => array(
			'name' => '发表文章',
			'type' => 'radio',
			'value'=> array(
				'1'=>'开启',
				'0'=>'关闭'
			),
			'descrip' => ''
		),
		/*
		'cms_postcheck' => array(
			'name' => '发表文章需审核',
			'type' => 'radio',
			'value'=> array(
				'1'=>'开启',
				'0'=>'关闭'
			),
			'descrip' => '开启后，该用户组发表的文章需要审核通过后才能显示'
		),
		*/
	),
);
?>